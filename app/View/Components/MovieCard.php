<?php

namespace App\View\Components;

use Closure;
use App\Data\Omdb\MovieData;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class MovieCard extends Component
{
    public MovieData $movie;
    public $average;
    public $reviews;
    public $renderReviewActions;

    /**
     * Create a new component instance.
     */
    public function __construct(MovieData $movie, $average, $reviews = null, $renderReviewActions = null)
    {
        $this->movie = $movie;
        $this->average = $average;
        $this->reviews = $reviews;
        $this->renderReviewActions = $renderReviewActions;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.movie-card');
    }

    public function likeReview(int $reviewId): void
    {
        $review = \App\Models\Review::find($reviewId);
        if (!$review) {
            // $this->dispatch('toast', ['title' => 'Review nicht gefunden', 'type' => 'error']);
            return;
        }

        // Beispiel: Like als einfache Zählvariable erhöhen
        // In der Praxis würdest du wahrscheinlich prüfen, ob der Nutzer bereits geliked hat
        $review->increment('likes_count');

        // $this->dispatch('toast', ['title' => 'Review geliked', 'type' => 'success']);
    }
}
