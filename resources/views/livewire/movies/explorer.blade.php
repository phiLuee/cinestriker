<?php


use App\Models\Movie;
use App\Models\Review;
use App\Data\Omdb\MovieData;
use App\Data\Omdb\ApiSearchResult;
use App\Services\OmdbService;
use Livewire\Volt\Component;
use Mary\Traits\Toast;

new
#[Layout('layouts.app')]
#[Title('Explorer')]
class extends Component
{
    use Toast;

    /**
     * Abhängigkeit auf den OmdbService
     */
    protected OmdbService $omdbService;

    /**
     * Reactive Properties
     */
    #[Reactive]
    public string $refreshToken = '';

    #[Reactive]
    public int $page = 1;

    #[Reactive]
    public int $perPage = 10;

    #[Reactive]
    public string $search = '';

    #[Reactive]
    public bool $onlyRated = false;

    #[Reactive]
    public bool $onlyOwn = false;

    #[Reactive]
    public bool $showReviewModal = false;

    #[Reactive]
    public ?int $selectedMovieId = null;

    #[Reactive]
    public string $sort = 'reviews_count_desc';

    public array $results = [];
    public int $lastPage = 1;
    public bool $loading = false;

    /**
     * Livewire boot-Methode (Abhängigkeiten injizieren).
     */
    public function boot(OmdbService $omdbService): void
    {
        $this->omdbService = $omdbService;

        // Beispiel: Falls eine E-Mail-Überprüfung anstoßen sollte
        // if (auth()->check() && !auth()->user()->hasVerifiedEmail()) {
        //     $this->warning('Bitte bestätige deine E-Mail-Adresse, um alle Funktionen nutzen zu können.');
        // }
    }

    /**
     * Mount-Methode für Initialisierung.
     */
    public function mount(): void
    {
        $this->refreshToken = Str::uuid()->toString();
        $this->hydrateMovies();          // Erste Seite laden
        $this->fetchTotalAndLastPage();  // lastPage berechnen
    }

    /**
     * Livewire-Hook: Wird ausgeführt, bevor eine Property geändert wird.
     * Hier z. B. Reset der Seite bei Filteränderungen.
     */
    public function updating(string $name, mixed $value): void
    {
        if (in_array($name, ['search', 'onlyRated', 'onlyOwn'])) {
            $this->page = 1;
        }
    }

    /**
     * Livewire-Hook: Wird ausgeführt, nachdem eine Property geändert wurde.
     * Hier triggern wir bei bestimmten Props ein sofortiges Refresh.
     */
    public function updated(string $name): void
    {
        if (in_array($name, ['search', 'onlyRated', 'onlyOwn', 'page', 'sort'])) {
            $this->refreshToken = Str::uuid()->toString(); 
            $this->refresh();
        }
    }

    /**
     * Computed Property: Liefert die Gesamtanzahl passender Filme
     * (entweder aus der DB oder – wenn Suchbegriff >= 3 Zeichen – per API).
     */
    public function total(): int
    {
        // Wenn mindestens 3 Zeichen gesucht wird und wir NICHT nur
        // eigene bzw. bewertete Filme wollen => API-Ergebnis nehmen
        $fetchFromApi = (strlen($this->search) >= 3 && !$this->onlyRated && !$this->onlyOwn);

        if ($fetchFromApi) {
            return $this->fetchSearchResultIfNeeded()->total;
        }

        return $this->baseQuery()->count();
    }

    /**
     * Führt ein Refresh aus: Seite auf 1, Suchergebnis aktualisieren etc.
     */
    public function refresh(): void
    {
        $token = $this->refreshToken;

        $this->loading = true;
        $this->page = 1;
        $this->results = [];

        // Bei Bedarf externe Suche (API) anstoßen
        $this->fetchSearchResultIfNeeded();
        $this->fetchTotalAndLastPage();

        $movies = $this->baseQuery()
            ->paginate($this->perPage, ['*'], 'page', $this->page)
            ->getCollection()
            ->map(fn (Movie $movie) => MovieData::fromModel($movie)->toArray())
            ->all();

        // Concurrency-Check: Hat sich das Token in der Zwischenzeit geändert?
        if ($token !== $this->refreshToken) {
            return;
        }

        $this->results = $movies;
        $this->loading = false;
    }

    /**
     * Öffnet das Modal für eine Review.
     */
    public function openReviewModal(int $movieId): void
    {
        $this->selectedMovieId = $movieId;
        $this->showReviewModal = true;
    }

    /**
     * Führt die Suche in der externen API nur aus, wenn nötig.
     */
    protected function fetchSearchResultIfNeeded(): ApiSearchResult
    {
        // Bei mindestens 3 Zeichen, wenn wir nicht onlyRated/onlyOwn filtern
        if (strlen($this->search) >= 3 && !$this->onlyRated && !$this->onlyOwn) {
            return $this->omdbService->searchMovies($this->search, $this->page);
        }

        // Sonst leeres Ergebnis
        return new ApiSearchResult(collect(), 0);
    }

    /**
     * Aktualisiert die lastPage anhand der computed total().
     */
    protected function fetchTotalAndLastPage(): void
    {
        $this->lastPage = max(1, (int) ceil($this->total() / $this->perPage));
    }

    /**
     * Initiales Laden der Filme (Seite 1).
     */
    protected function hydrateMovies(): void
    {
        $this->results = $this->baseQuery()
            ->paginate($this->perPage, ['*'], 'page', $this->page)
            ->getCollection()
            ->map(fn (Movie $movie) => MovieData::fromModel($movie)->toArray())
            ->values()
            ->all();
    }

    /**
     * Basiskonfiguration des Queries (mit Filtern, Sortierung, Relationen).
     */
    protected function baseQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return Movie::query()
            ->withCount('reviews')
            ->withAvg('reviews', 'rating')
            ->with([
                'reviews' => fn ($q) =>
                    $q->with('user', 'likes')->orderByDesc('created_at')->limit(3)
            ])
            ->when(strlen($this->search) >= 3, fn ($q) =>
                $q->where('title', 'like', '%' . $this->search . '%')
            )
            ->when(auth()->check(), fn ($q) =>
            $q->addSelect([ 
                    'user_review' => \App\Models\Review::select('id')
                        ->whereColumn('reviews.movie_id', 'movies.id')
                        ->where('user_id', auth()->id())
                        ->limit(1)
                ])
            )
            ->when($this->onlyOwn && auth()->check(), fn ($q) =>
                $q->whereExists(function ($sub) {
                    $sub->selectRaw(1)
                        ->from('reviews')
                        ->whereColumn('reviews.movie_id', 'movies.id')
                        ->where('reviews.user_id', auth()->id());
                })
            )
            ->when($this->onlyRated, fn ($q) =>
                $q->has('reviews')
            )
            ->when($this->sort === 'reviews_count_desc', fn ($q) =>
                $q->orderByDesc('reviews_count')
            )
            ->when($this->sort === 'avg_rating_desc', fn ($q) =>
                $q->orderByDesc('reviews_avg_rating')
            )
            ->when($this->sort === 'title_asc', fn ($q) =>
                $q->orderBy('title')
            )
            ->when($this->sort === 'title_desc', fn ($q) =>
                $q->orderByDesc('title')
            );
    }

    /**
     * Lädt weitere Filme (Infinite Scrolling), falls noch Seiten übrig sind.
     */
    public function loadMore(?string $token = null): void
    {
        if ($this->page >= $this->lastPage) {
            return;
        }

        if ($token !== null && $token !== $this->refreshToken) {
            return;
        }

        $this->page++;
        $this->loading = true;

        // Falls wir wieder auf die API-Suche zugreifen müssen
        $this->fetchSearchResultIfNeeded();

        $movies = $this->baseQuery()
            ->paginate($this->perPage, ['*'], 'page', $this->page)
            ->getCollection()
            ->map(fn (Movie $movie) => MovieData::fromModel($movie)->toArray())
            ->values()
            ->all();

        // Concurrency-Check
        if ($token !== null && $token !== $this->refreshToken) {
            return;
        }

        // Bestehende Ergebnisse erweitern
        $this->results = [...$this->results, ...$movies];
        $this->loading = false;
    }
}
?>

@php
$grouped = [
        'Movies' => [
            ['id' => 'reviews_count_desc', 'name' => 'Anzahl Bewertungen (absteigend)'],
            ['id' => 'avg_rating_desc', 'name' => 'Bewertung (absteigend)'],
            ['id' => 'title_asc', 'name' => 'Titel A–Z'],
            ['id' => 'title_desc', 'name' => 'Titel Z–A']
        ]
    ];
@endphp


<div class="space-y-4">
    <div class="flex flex-wrap gap-4 items-center">
        <input type="text" wire:model.live.debounce.500ms="search"
               placeholder="Film suchen..." class="border px-3 py-2 rounded w-full md:w-1/3" />

        <label class="flex items-center space-x-2">
            <input type="checkbox"
                wire:model.live="onlyRated"
                x-on:change="setTimeout(() => $wire.call('refresh'), 50)">
            <span>Nur bewertete Filme</span>
        </label>

        <label class="flex items-center space-x-2">
            <input type="checkbox"
                wire:model.live="onlyOwn"
                x-on:change="setTimeout(() => $wire.call('refresh'), 50)">
            <span>Nur meine Bewertungen</span>
        </label>

        <div class="flex items-center space-x-2">
            <label for="sortSelect" class="text-sm">Sortieren nach:</label>
            <x-select-group id="sortSelect" wire:model.live="sort" :options="$grouped">
            </x-select-group>
        </div>
    </div>
    <div class="flex flex-wrap gap-4 items-center">
        <p>{{ $this->total() }} </p>
    </div>

    @if (empty($results))
        <div class="text-gray-500">Keine Filme gefunden oder Suchbegriff zu kurz.</div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            @foreach ($results as $movieArray)
                @php
                    $movie = MovieData::from($movieArray); // zurück in MovieData-Objekt
                    $actionsHtml = view('livewire.movies.partials.actions', ['movie' => $movieArray])->render();
                @endphp
                <livewire:movies.movie-card :movieArray="$movieArray" :key="'movie-card-id-' . $movieArray['id']" :actions="$actionsHtml" />
            @endforeach
            <livewire:reviews.edit />
        </div>
        {{-- Pagination --}}
        {{-- Sentinel Element fürs Lazy Loading --}}
        <div
            x-data="{ busy: false }"
            x-intersect.debounce.300ms="
                if (!busy) {
                    busy = true;
                    $wire.call('loadMore', '{{ $refreshToken }}')
                        .then(() => busy = false);
                }
            "
            class="h-16"
        >
            <div wire:loading wire:target="loadMore" class="text-center text-gray-500">
                Lade weitere Filme...
            </div>
        </div>
        {{-- Pagination End  --}}
    @endif
</div>