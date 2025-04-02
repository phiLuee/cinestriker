<?php

namespace Database\Seeders;

use App\Models\Movie;
use App\Services\OmdbService;
use Illuminate\Database\Seeder;

class MovieSeeder extends Seeder
{
    protected array $imdbIds = [
        'tt0111161',
        'tt0068646',
        'tt0468569',
        'tt1375666',
        'tt0133093',
        'tt0109830',
        'tt0120737',
        'tt0137523',
        'tt0167260',
        'tt0080684',
        'tt0110912',
        'tt0071562',
        'tt0108052',
        'tt6751668',
        'tt0114369',
        'tt0816692',
        'tt0120815',
        'tt0103064',
        'tt0253474',
        'tt0407887',
        'tt0056058',
        'tt0209144',
        'tt1853728',
        'tt0114814',
    ];

    public function run(): void
    {
        $omdb = app(OmdbService::class);

        foreach ($this->imdbIds as $imdbId) {
            $movieData = $omdb->getMovieById($imdbId);

            if (!$movieData) {
                continue;
            }

            Movie::updateOrCreate(
                ['imdb_id' => $movieData->imdb_id],
                [
                    'title'     => $movieData->title,
                    'year'      => $movieData->year,
                    'poster'    => $movieData->poster,
                    'omdb_raw'  => $movieData->toArray(), // Optional: Rohdaten speichern
                ]
            );
        }
    }
}
