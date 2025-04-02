<?php

use App\Models\Review;
use Livewire\Volt\Component;

new class extends Component {
    public ?Review $review = null;
    public bool $showModal = false;

    protected $listeners = [
        'showReview' => 'open',
    ];

    public function open(int $id): void
    {
        $review = Review::with(['movie', 'user'])->find($id);

        if (! $review) return;

        $this->review = $review;
        $this->showModal = true;
    }

    public function close(): void
    {
        $this->reset(['review', 'showModal']);
    }
};

?>

<x-modal wire:model.defer="showModal" title="Bewertung ansehen">
    @if ($review)
        <div class="space-y-4">
            <div class="flex justify-between text-sm text-muted">
                <div><strong>Film:</strong> {{ $review->movie->title ?? 'Unbekannt' }}</div>
                <div><strong>Von:</strong> {{ $review->user->name ?? 'Unbekannt' }}</div>
            </div>

            <x-badge color="info" value="{{ $review->rating }}/5" />
            <p class="text-sm">{{ $review->review }}</p>
        </div>
    @endif

    <x-slot:footer>
        <x-button outline label="Schließen" wire:click="close" />
    </x-slot:footer>
</x-modal>
