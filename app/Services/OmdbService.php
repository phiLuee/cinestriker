<?php

namespace App\Services;

use App\Models\Movie;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use App\Data\Omdb\MovieData;
use App\Data\Omdb\ApiSearchResult;
use Illuminate\Support\Str;

class OmdbService
{
    protected string $apiUrl;
    protected string $apiKey;

    public function __construct()
    {
        $this->apiUrl = config('services.omdb.url');
        $this->apiKey = config('services.omdb.key');
    }

    /**
     * Filme anhand eines Suchbegriffs abrufen und lokal speichern
     */
    public function searchMovies(string $query, int $page = 1): ApiSearchResult
    {
        $cacheKey = 'movies_search_' . Str::slug($query) . "_{$page}";

        return Cache::remember($cacheKey, now()->addMinutes(10), function () use ($query, $page) {
            $response = Http::timeout(5)->get($this->apiUrl, [
                'apikey' => $this->apiKey,
                's' => $query,
                'page' => $page
            ]);

            if (! $response->successful()) {
                return new ApiSearchResult(collect(), 0);
            }

            $body = $response->json();
            $results = $body['Search'] ?? [];
            $total = isset($body['totalResults']) ? (int) $body['totalResults'] : 0;

            $movies = collect($results)->map(function ($item) {
                // Film lokal speichern oder aktualisieren
                $movie = Movie::updateOrCreate(
                    ['imdb_id' => $item['imdbID']],
                    [
                        'title' => $item['Title'] ?? null,
                        'year' => $item['Year'] ?? null,
                        'poster' => $item['Poster'] ?? null,
                        'omdb_raw' => $item,
                    ]
                );

                return MovieData::fromModel($movie);
            });

            return new ApiSearchResult($movies, $total);
        });
    }


    /**
     * Holt Film-Details von OMDb und speichert/aktualisiert lokal
     * Gibt immer ein Movie-Modell oder null zurück
     */
    public function getMovieById(string $imdbId): ?MovieData
    {
        $cacheKey = "movie_{$imdbId}";

        $data = Cache::remember($cacheKey, now()->addDays(7), function () use ($imdbId) {
            $response = Http::timeout(5)->get($this->apiUrl, [
                'apikey' => $this->apiKey,
                'i' => $imdbId
            ]);

            return $response->successful() ? $response->json() : null;
        });

        if (! $data || ! isset($data['Title'])) {
            return null;
        }

        $movie = Movie::updateOrCreate(
            ['imdb_id' => $imdbId],
            [
                'title'     => $data['Title'] ?? null,
                'year'      => $data['Year'] ?? null,
                'poster'    => $data['Poster'] ?? null,
                'omdb_raw'  => $data,
            ]
        );

        // Rückgabe als Data-Objekt
        return MovieData::fromModel($movie);
    }
}
