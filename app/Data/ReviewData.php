<?php

namespace App\Data;

use App\Models\Review;
use Spatie\LaravelData\Data;

class ReviewData extends Data
{
    public function __construct(
        public int $id,
        public int $user_id,
        public int $movie_id,
        public int $rating,
        public string $review,
        public string $created_at,
        public string $updated_at,
        public ?array $user = null,
        public array $likes = []
    ) {}

    public static function fromModel(Review $review): self
    {
        return new self(
            id: $review->id,
            user_id: $review->user_id,
            movie_id: $review->movie_id,
            rating: $review->rating,
            review: $review->review,
            created_at: $review->created_at?->toDateTimeString() ?? '',
            updated_at: $review->updated_at?->toDateTimeString() ?? '',
            user: $review->user ? [
                'id' => $review->user->id,
                'name' => $review->user->name,
            ] : null,
            likes: collect($review->likes)->map(fn($like) => [
                'id' => $like->id,
                'user_id' => $like->user_id,
            ])->all(),
        );
    }
}
