<?php

namespace App\Data\Omdb;

use App\Data\ReviewData;
use App\Models\Movie;
use App\Models\Review;
use Spatie\LaravelData\Data;
use Illuminate\Support\Collection;

class MovieData extends Data
{
    public function __construct(
        public int $id,
        public string $imdb_id,
        public string $title,
        public ?string $year,
        public ?string $poster,
        public ?string $type = null,
        public ?string $genre = null,
        public ?string $director = null,
        public ?string $actors = null,
        public ?string $plot = null,
        public ?string $language = null,
        public ?string $country = null,
        public ?string $runtime = null,
        public ?string $released = null,
        public ?string $rated = null,
        public ?string $writer = null,
        public ?string $awards = null,
        public ?string $imdbRating = null,
        public ?string $imdbVotes = null,
        public int $review_count = 0,
        public float $avg_rating = 0.0,
        /** @var Collection<ReviewData> */
        public Collection $topReviews = new Collection(),
        public int $user_review_id = 0,
    ) {}

    public static function fromModel(Movie $movie): self
    {
        $topReviews = $movie->reviews
            ->sortByDesc(fn($r) => $r->likes->count())
            ->take(3)
            ->map(fn($r) => ReviewData::fromModel($r))
            ->values();

        $raw = $movie->omdb_raw ?? [];

        return new self(
            id: $movie->id,
            imdb_id: $movie->imdb_id,
            title: $movie->title,
            year: $movie->year,
            poster: $movie->poster,
            type: $raw['type'] ?? null,
            genre: $raw['genre'] ?? null,
            director: $raw['director'] ?? null,
            actors: $raw['actors'] ?? null,
            plot: $raw['plot'] ?? null,
            language: $raw['language'] ?? null,
            country: $raw['country'] ?? null,
            runtime: $raw['runtime'] ?? null,
            released: $raw['released'] ?? null,
            rated: $raw['rated'] ?? null,
            writer: $raw['writer'] ?? null,
            awards: $raw['awards'] ?? null,
            imdbRating: $raw['imdbRating'] ?? null,
            imdbVotes: $raw['imdbVotes'] ?? null,
            review_count: $movie->reviews_count ?? $movie->reviews()->count(),
            avg_rating: round($movie->reviews_avg_rating ?? $movie->reviews()->avg('rating'), 1),
            topReviews: $topReviews,
            user_review_id: Review::where('movie_id', $movie->id)->where('user_id', auth()->id())->first()?->id ?? 0,
        );
    }

    public static function fromArray(array $data): self
    {
        $raw = $data['omdb_raw'] ?? [];

        return new self(
            id: $data['id'],
            imdb_id: $data['imdb_id'],
            title: $data['title'],
            year: $data['year'] ?? null,
            poster: $data['poster'] ?? null,
            type: $raw['type'] ?? null,
            genre: $raw['genre'] ?? null,
            director: $raw['director'] ?? null,
            actors: $raw['actors'] ?? null,
            plot: $raw['plot'] ?? null,
            language: $raw['language'] ?? null,
            country: $raw['country'] ?? null,
            runtime: $raw['runtime'] ?? null,
            released: $raw['released'] ?? null,
            rated: $raw['rated'] ?? null,
            writer: $raw['writer'] ?? null,
            awards: $raw['awards'] ?? null,
            imdbRating: $raw['imdbRating'] ?? null,
            imdbVotes: $raw['imdbVotes'] ?? null,
            review_count: $data['review_count'] ?? 0,
            avg_rating: (float) ($data['avg_rating'] ?? 0),
            topReviews: collect($data['topReviews'] ?? []),
            user_review_id: $data['user_review_id'] ?? false,
        );
    }
}
