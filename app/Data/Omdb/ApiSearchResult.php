<?php

namespace App\Data\Omdb;

use Illuminate\Support\Collection;
use Spatie\LaravelData\Data;

class ApiSearchResult extends Data
{
    /**
     * @param Collection<int, MovieData> $movies
     */
    public function __construct(
        public Collection $movies,
        public int $total,
    ) {}
}
