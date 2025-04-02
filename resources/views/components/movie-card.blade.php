<x-card class="overflow-hidden h-full bg-white/10 backdrop-blur-md border border-white/20 rounded-xl shadow-lg p-4">
    <div class="flex h-full">
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
                    @if (!is_null($average))
                        <x-badge color="info" value="Ø {{ number_format($average, 1) }}/5" />
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
                        {{ $actions }}
                    @else
                        {{-- Fallback falls keine Actions übergeben wurden --}}
                        <x-button disabled color="secondary">Keine Aktion definiert</x-button>
                    @endisset
                @endauth
            </div>
            @if($reviews)
                {{-- Rezensionen unten --}}
                <div class="mt-4 space-y-2">
                    <h3 class="font-semibold text-base text-muted">Rezensionen:</h3>

                    @forelse ($reviews ?? [] as $review)
                        <div class="bg-base-200 p-2 rounded border border-base-300 text-sm">
                            <span class="font-semibold">{{ $review['user']['name'] ?? 'Unbekannter Nutzer' }}</span>:
                            <x-badge small color="info" value="{{ $review['rating'] ?? '-' }}/5" />
                            <div class="text-xs text-muted">
                                {{ $review['review'] ?? '' }}
                            </div>
                           

                            <x-button wire:click="likeReview({{ $review['id'] }})" wire:key="like {{ $review['id'] }}" class="text-sm" spinner>
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

</x-card>
