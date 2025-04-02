<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Movie extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'imdb_id',
        'title',
        'year',
        'type',
        'poster',
        'omdb_raw',
    ];

    protected $casts = [
        'omdb_raw' => 'array',
    ];

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }
}
