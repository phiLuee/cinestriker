<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;

class Review extends Model
{
    protected $fillable = ['user_id', 'movie_id', 'rating', 'review'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function likes()
    {
        return $this->belongsToMany(User::class, 'review_likes')->withTimestamps();
    }

    public function isLikedBy(User $user)
    {
        return $this->likes()->where('user_id', $user->id)->exists();
    }

    public function movie()
    {
        return $this->belongsTo(Movie::class);
    }

    public function reviewedMovies(): Collection
    {
        return Review::select('movie_id')
            ->distinct()
            ->get()
            ->map(function ($entry) {
                $reviews = Review::where('movie_id', $entry->movie_id)->with('user')->get();
                $avg = $reviews->avg('rating');

                return [
                    'movie_id' => $entry->movie_id,
                    'reviews' => $reviews,
                    'average' => $avg,
                ];
            });
    }
}
