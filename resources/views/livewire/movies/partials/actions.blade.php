@if($movie['user_review_id'])
    <x-button @click="$dispatch('editReview', { reviewId: {{ $movie['user_review_id'] }} })">
        Bewertung bearbeiten
    </x-button>
@else
    <x-button @click="$dispatch('newReview', { movieId:  {{ $movie['id'] }} })">
        Bewertung schreiben
    </x-button>
@endif