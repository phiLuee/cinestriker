<?php

use App\Models\Movie;
use App\Models\Review;
use Livewire\Volt\Component;
use Mary\Traits\Toast;

new class extends Component {
    use Toast;

    // Für bestehende Reviews (Editing)
    public ?Review $modalReview = null;
    // Für neue Reviews: Speichere den Film (critical field) separat
    public ?int $movieId = null;
    public ?string $movieTitle = null;

    public bool $showModal = false;
    public bool $canEdit = false;

    // Felder, die im Formular bearbeitet werden
    public int $rating = 1;
    public string $review = '';

    protected $listeners = [
        'editReview' => 'openEdit',
        'newReview'  => 'openNew',
    ];

    // Bestehendes Review laden und bearbeiten
    public function openEdit(int $reviewId): void
    {
        $this->resetErrorBag();
        $review = Review::with(['movie', 'user'])->find($reviewId);
        if (!$review) return;

        $this->authorizeEdit($review);
        $this->setReviewData($review);
        $this->showModal = true;
    }

    // Neues Review anlegen: Hier speichern wir die movie_id und den Titel getrennt
    public function openNew(int $movieId): void
    {
        $this->resetErrorBag();
        $movie = Movie::find($movieId);
        if (!auth()->check() || !$movie) return;

        // Kritische Felder separat speichern
        $this->movieId    = $movie->id;
        $this->movieTitle = $movie->title;
        $this->rating     = 1;
        $this->review     = '';
        $this->canEdit    = true;
        // modalReview wird auf null gesetzt – bei neuen Reviews greifen wir auf die separaten Felder
        $this->modalReview = null;
        $this->showModal   = true;
    }

    // Berechtigung prüfen (z. B. ob der Nutzer Admin ist oder der Ersteller des Reviews)
    protected function authorizeEdit(Review $review): void
    {
        $this->canEdit = auth()->check() &&
                         (auth()->user()->hasRole('admin') || auth()->id() === $review->user_id);
    }

    // Bestehende Review-Daten in die Komponentenfelder übernehmen
    protected function setReviewData(Review $review): void
    {
        $this->modalReview = $review;
        $this->rating      = $review->rating ?? 1;
        $this->review      = $review->review ?? '';
    }

    // Formular/Modal schließen und alle Felder zurücksetzen
    public function close(): void
    {
        $this->reset(['modalReview', 'showModal', 'canEdit', 'rating', 'review', 'movieId', 'movieTitle']);
    }

    // Speichern: Unterscheidung zwischen Update (existierendes Review) und Create (neues Review)
    public function save(): void
    {
        $this->validate([
            'rating' => 'required|integer|min:1|max:5',
            'review' => 'required|string|min:3',
        ]);

        // Update eines bestehenden Reviews
        if ($this->modalReview && $this->modalReview->exists) {
            $this->authorizeEdit($this->modalReview);
            if (!$this->canEdit) {
                $this->error('Keine Berechtigung.');
                return;
            }
            $this->modalReview->rating = $this->rating;
            $this->modalReview->review = $this->review;
            $this->modalReview->save();
            $this->success('Review aktualisiert.');
        } else {
            // Neues Review: movieId muss gesetzt sein
            if (!$this->movieId) {
                $this->error('Kein Film zugeordnet.');
                return;
            }
            $review = new Review();
            $review->movie_id = $this->movieId;
            $review->user_id  = auth()->id();
            $review->rating   = $this->rating;
            $review->review   = $this->review;
            $review->save();
            $this->success('Review erstellt.');
        }

        $this->close();
    }

    public function delete($id): void
    {
        $review = Review::find($id);
        if (!$review) return;

        $user = auth()->user();
        if (!($user->hasRole('admin') || $review->user_id === $user->id)) return;

        $review->delete();
        $this->dispatch('toast', ['title' => 'Review gelöscht.', 'type' => 'success']);
    }
};

?>

<x-modal wire:model.defer="showModal" title="{{ $modalReview?->exists ? 'Bewertung bearbeiten' : 'Neue Bewertung' }}">
    <div class="space-y-4">
        @if ($modalReview)
            <div class="flex justify-between text-sm text-muted">
                <div><strong>Film:</strong> {{ $modalReview->movie?->title ?? 'Unbekannt' }}</div>
                <div><strong>Von:</strong> {{ $modalReview->user?->name ?? 'Unbekannt' }}</div>
            </div>
        @endif

        @if ($canEdit)
            <x-input label="Bewertung" type="number" min="1" max="10" wire:model.defer="rating" />
            <x-textarea label="Text" wire:model.defer="review" rows="5" />
            <div class="flex justify-between items-center">
                @if($modalReview?->exists)
                    <x-button wire:click="delete({{ $modalReview->id }})" label="Löschen" class="btn-error" />
                @else
                    <span class="text-sm text-muted">Neue Bewertung wird erstellt</span>
                @endif
                <x-button wire:click="save" label="Speichern" class="btn-primary" />
            </div>
        @elseif($modalReview?->exists)
            <x-badge color="info" value="{{ $modalReview->rating }}/5" />
            <p class="text-sm">{{ $modalReview->review }}</p>
        @endif
    </div>

    <x-slot:footer>
        <x-button outline label="Schließen" wire:click="close" />
    </x-slot:footer>
</x-modal>
