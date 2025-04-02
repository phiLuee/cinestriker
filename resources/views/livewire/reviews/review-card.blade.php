<div class="card bg-base-100 shadow">
    <div class="card-body">
        <h2 class="card-title">
            {{ $review->movie->title }}
            <span class="badge">{{ $review->rating }}/10</span>
        </h2>
        <p>{{ $review->content }}</p>
        <div class="text-sm text-gray-500">
            Verfasst von <strong>{{ $review->user->name }}</strong> am {{ $review->created_at->format('d.m.Y') }}
        </div>
    </div>
</div>