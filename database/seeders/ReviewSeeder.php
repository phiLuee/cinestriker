<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Movie;
use App\Models\Review;
use Illuminate\Database\Seeder;

class ReviewSeeder extends Seeder
{
    public function run()
    {
        $users = User::all();
        $movies = Movie::all();

        foreach ($movies as $movie) {
            $reviewers = $users->random(min(rand(5, 15), $users->count()));

            foreach ($reviewers as $user) {
                $review = Review::create([
                    'user_id' => $user->id,
                    'movie_id' => $movie->id,
                    'rating' => rand(1, 5),
                    'review' => fake()->sentence(),
                ]);

                $likers = $users->whereNotIn('id', [$user->id])->random(min(rand(1, 10), $users->count() - 1));
                foreach ($likers as $liker) {
                    $review->likes()->syncWithoutDetaching($liker->id);
                }
            }
        }
    }
}
