<?php

use App\Models\Review;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use Mary\Traits\Toast;

new class extends Component {
    use WithPagination, Toast;

    public ?Review $modalReview = null;
    public bool $showModal = false;
    public bool $canEdit = false;

    public string $search = '';
    public int $page = 1;
    public int $perPage = 10;
    public array $sortBy = ['column' => 'created_at', 'direction' => 'desc'];

    public bool $drawer = false;

    protected $queryString = ['page'];

    public function mount()
    {
        $reviewId = request()->route('review');

        if ($reviewId) {
            $review = Review::with(['movie', 'user'])->findOrFail($reviewId);
            $this->modalReview = $review;
            $this->showModal = true;
            $this->canEdit = auth()->check() &&
                (auth()->user()->hasRole('admin') || auth()->id() === $review->user_id);
        }
    } 

    public function clear(): void
    {
        $this->reset(['search', 'sortBy']);
        $this->success('Filter zurückgesetzt.');
    }

    public function delete($id): void
    {
        $review = Review::find($id);

        if (!$review) {
            $this->error("Review nicht gefunden.");
            return;
        }

        $user = auth()->user();

        $isAdmin = $user->hasRole('admin');
        $isOwner = $review->user_id === $user->id;

        if (!($isAdmin || $isOwner)) {
            $this->error("Du darfst diese Bewertung nicht löschen.");
            return;
        }

        $review->delete();

        $this->success("Review #$id wurde gelöscht.");
    }

    public function save()
    {
        if (! $this->canEdit || !$this->modalReview) return;

        $this->validate([
            'modalReview.rating' => 'required|integer|min:1|max:10',
            'modalReview.content' => 'required|string|min:3',
        ]);

        $this->modalReview->save();

        $this->dispatch('toast', ['title' => 'Review gespeichert.', 'type' => 'success']);
        return redirect()->route('reviews.index');
    }

    public function headers(): array
    {
        return [
            ['key' => 'movie_title', 'label' => 'Film'],
            ['key' => 'user_name', 'label' => 'Benutzer'],
            ['key' => 'rating', 'label' => 'Bewertung'],
            ['key' => 'review', 'label' => 'Inhalt', 'sortable' => false],
            ['key' => 'created_at', 'label' => 'Datum'],
        ];
    }

    public function reviews(): LengthAwarePaginator
    {
        $query = Review::query()
            ->with(['user', 'movie'])
            ->whereHas('movie')
            ->whereHas('user');

        if (!empty($this->search)) {
            $query->where(function ($q) {
                $term = '%' . $this->search . '%';
                $q->whereRaw('reviews.review ilike ?', [$term])
                  ->orWhereHas('movie', fn($q) => $q->whereRaw('title ilike ?', [$term]))
                  ->orWhereHas('user', fn($q) => $q->whereRaw('name ilike ?', [$term]));
            });
        }

        if (!empty($this->sortBy['column']) && in_array($this->sortBy['direction'], ['asc', 'desc'])) {
            if (in_array($this->sortBy['column'], ['rating', 'created_at', 'id'])) {
                $query->orderBy($this->sortBy['column'], $this->sortBy['direction']);
            }
        }

        $perPage = $this->perPage === -1 ? $query->count() : $this->perPage;

        return $query->paginate($perPage)->through(fn($r) => [
            'id' => $r->id,
            'user_id' => $r->user_id, 
            'movie_title' => optional($r->movie)->title ?? 'Unbekannt',
            'user_name' => optional($r->user)->name ?? 'Unbekannt',
            'rating' => $r->rating,
            'review' => \Str::limit($r->review, 100),
            'created_at' => $r->created_at->format('d.m.Y'),
        ]);
    }

    public function with(): array
    {
        return [
            'reviews' => $this->reviews(),
            'headers' => $this->headers(),
        ];
    }
};
?>

<div>
<!-- HEADER -->
<x-header title="Bewertungen" separator>
    <x-slot:middle class="!justify-end">
        <x-input placeholder="Suchen…" wire:model.live.debounce="search" clearable icon="o-magnifying-glass" />
    </x-slot:middle>
    <x-slot:actions>
        <x-button label="Filter" @click="$wire.drawer = true" responsive icon="o-funnel" />
    </x-slot:actions>
</x-header>

<!-- TABELLE -->
<x-card shadow>
    <x-table 
        :headers="$headers" 
        :rows="$reviews" 
        :sort-by="$sortBy"  
        with-pagination
        per-page="perPage"
        :per-page-values="[10, 25, 50, 100, -1]" 
    >
    @scope('actions', $review)
        <x-button
        icon="o-eye"
        label="Ansehen"
        @click="$dispatch('showReview', { id: {{ $review['id'] }} })"
    />
    @endscope
    </x-table>

</x-card>

<!-- FILTER DRAWER -->
<x-drawer wire:model="drawer" title="Filter" right separator with-close-button class="lg:w-1/3">
    <x-input placeholder="Suchen…" wire:model.live.debounce="search" icon="o-magnifying-glass" />

    <x-slot:actions>
        <x-button label="Zurücksetzen" icon="o-x-mark" wire:click="clear" spinner />
        <x-button label="Fertig" icon="o-check" class="btn-primary" @click="$wire.drawer = false" />
    </x-slot:actions>
</x-drawer>
<livewire:reviews.edit />
<livewire:reviews.show />
</div>