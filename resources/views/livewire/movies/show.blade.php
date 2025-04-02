<?php

use App\Models\Review;
use App\Models\Movie;
use App\Services\OmdbService;
use Livewire\WithPagination;
use Livewire\Volt\Component;

new #[Layout('layouts.app')] class extends Component
{
    use WithPagination;

    public Movie $movie;
    public string $imdbId;

    public function mount(OmdbService $omdb, string $imdbId)
    {
        $this->imdbId = $imdbId;
        $this->movie = $omdb->getMovieById($imdbId);
    }

    public function reviews()
    {
        return Review::with('user', 'likes')
            ->where('movie_id', $this->movie->id)
            ->orderByDesc('created_at')
            ->paginate(5); // Anzahl Reviews pro Seite
    }

    public function with()
    {
        return [
            'movie' => $this->movie,
            'reviews' => $this->reviews(),
        ];
    }
}

?>

<div class="max-w-4xl mx-auto p-6 space-y-6">
    <h1 class="text-3xl font-bold">{{ $movie['Title'] ?? 'Film nicht gefunden' }}</h1>

    @if (!empty($movie))
        <div class="flex gap-6 items-start mt-4">
            <img src="{{ $movie['Poster'] }}" class="w-32 h-auto rounded shadow" alt="Poster">

            <div class="space-y-2">
                <div class="flex flex-wrap gap-2 text-sm">
                    <x-badge color="info" value="Jahr: {{ $movie['Year'] }}" />
                    <x-badge color="primary" value="{{ $movie['Genre'] ?? 'Unbekannt' }}" />
                    <x-badge color="secondary" value="{{ $movie['Runtime'] ?? 'Laufzeit unbekannt' }}" />
                </div>
                <p class="text-sm text-muted mt-2">{{ $movie['Plot'] }}</p>
            </div>
        </div>
    @endif

    <div class="border-t border-base-300 my-8">
        <span class="block -mt-3 bg-base-100 px-2 text-sm text-muted w-fit">Rezensionen</span>
    </div>

    @forelse ($reviews as $review)
        <x-card class="bg-base-200 border border-base-300">
            <div class="flex justify-between items-center">
                <div>
                    <p class="font-semibold">{{ $review->user->name }}</p>
                    <p class="text-sm text-muted">{{ $review->created_at->format('d.m.Y') }}</p>
                </div>

                <x-badge color="info" small value="{{ $review->rating }}/5" />
            </div>

            <div class="mt-2 text-sm text-gray-700">
                {{ $review->review }}
            </div>

            <div class="text-xs text-muted mt-3">
                ❤️ {{ $review->likes->count() }} Likes
            </div>
        </x-card>
    @empty
        <x-card type="info" title="Noch keine Bewertungen">
            Dieser Film wurde bisher noch nicht bewertet.
        </x-card>
    @endforelse

    <div class="mt-4">
        {{ $reviews->links() }}
    </div>
</div>
