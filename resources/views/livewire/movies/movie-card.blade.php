<?php

use Livewire\Volt\Component;
use App\Data\Omdb\MovieData;
use App\Models\Movie;
use App\Models\Review;

new class extends Component {
    public array $movieArray;
    public string $actions = '';

    protected $listeners = ['refreshMovieCard' => 'refresh'];

    public function refresh(array $payload = []): void
    {
        $movieId = $payload['id'] ?? $this->movieArray['id'] ?? null;
        
        $model = Movie::query()
            ->withCount('reviews')
            ->withAvg('reviews', 'rating')
            ->with([
                'reviews' => fn ($q) => $q->with('user', 'likes')->orderByDesc('created_at')->limit(3),
            ])
            ->find($movieId);

        if ($model) {
            $this->movieArray = MovieData::fromModel($model)->toArray();
        }
    }

    public function likeReview(int $reviewId): void
    {
        $review = Review::find($reviewId);
        if (!$review) {
            $this->dispatch('toast', ['title' => 'Review nicht gefunden', 'type' => 'error']);
            return;
        }

        $userId = auth()->id();
        
        // Prüfe, ob der aktuelle Nutzer den Review bereits geliked hat
        if ($review->likes()->where('user_id', $userId)->exists()) {
            // Like entfernen
            $review->likes()->detach($userId);
            $this->dispatch('toast', ['title' => 'Like entfernt', 'type' => 'info']);
        } else {
            // Like hinzufügen
            $review->likes()->syncWithoutDetaching([$userId]);
            $this->dispatch('toast', ['title' => 'Review geliked', 'type' => 'success']);
        }
        
        $this->refresh();
    }
}; ?>

@php
    $movie = MovieData::from($movieArray);
@endphp

<div class="overflow-hidden h-full bg-white/10 backdrop-blur-md border border-white/20 rounded-xl shadow-lg p-4"">
        <div class="flex h-full">
            {{ $movieArray['id'] }}
            {{-- Hintergrundbild links --}}
            @if (!empty($movie->poster) && $movie->poster !== 'N/A')
                <div
                    class="w-40 min-h-[200px] bg-cover bg-center bg-no-repeat rounded-l shrink-0 -m-5 mr-0"
                    style="background-image: url('{{ $movie->poster }}')"
                ></div>
            @endif

            {{-- Rechter Bereich --}}
            <div class="flex-1 p-6 flex flex-col justify-between">
                <div class="space-y-3">
                    <h2 class="text-xl font-bold leading-snug">
                        {{ $movie->title ?? 'Unbekannter Titel' }}
                    </h2>

                    <div class="flex flex-wrap gap-2 text-sm">
                        @if (!is_null($movie->avg_rating))
                            <x-badge color="info" value="Ø {{ $movie->avg_rating }}/5" />
                        @endif

                        @if (!empty($movie->genre))
                            <x-badge color="primary" value="{{ $movie->genre }}" />
                        @endif

                        @if (!empty($movie->runtime))
                            <x-badge color="secondary" value="{{ $movie->runtime }}" />
                        @endif
                    </div>

                    @if (!empty($movie->plot))
                        <p class="text-sm text-muted line-clamp-4">{{ $movie->plot }}</p>
                    @endif
                </div>

                <div class="mt-4">
                    @auth
                        @isset($actions)
                            {!! $actions !!}
                        @else
                            {{-- Fallback falls keine Actions übergeben wurden --}}
                            <x-button disabled color="secondary">Keine Aktion definiert</x-button>
                        @endisset
                    @endauth
                </div>
                 @if($movie->topReviews)
                    {{-- Rezensionen unten --}}
                    <div class="mt-4 space-y-2">
                        <h3 class="font-semibold text-base text-muted">Rezensionen:</h3>

                        @forelse ($movie->topReviews ?? [] as $review)
                            <div class="bg-base-200 p-2 rounded border border-base-300 text-sm">
                                <span class="font-semibold">{{ $review['user']['name'] ?? 'Unbekannter Nutzer' }}</span>:
                                <x-badge small color="info" value="{{ $review['rating'] ?? '-' }}/5" />
                                <div class="text-xs text-muted">
                                    {{ $review['review'] ?? '' }}
                                </div>
                            

                                <x-button wire:click="likeReview({{ $review['id'] }})" wire:key="like-{{$movie->id}}-{{ $review['id'] }}" class="text-sm" spinner>
                                    👍 <x-badge small value="{{ count($review['likes']) }}" />
                                </x-button>
                            </div>
                        @empty
                            <p class="text-sm text-muted italic">Keine Rezensionen vorhanden.</p>
                        @endforelse
                    </div>
                @endif
            </div>
        </div>
</div>