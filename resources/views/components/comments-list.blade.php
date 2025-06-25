@if($comments->count() > 0)
    @foreach($comments as $comment)
        <div class="comment-item">
            <div class="comment-header">
                <div>You commented on <a href="#" class="user-link">{{ $comment->post->user->username }}'s post</a> ·
                    <span class="comment-date">{{ $comment->created_at->diffForHumans() }}</span>
                </div>
            </div>

            <div class="original-post-preview">
                <div class="user-info">
                    <span class="username">{{ $comment->post->user->username }} </span> &nbsp; · &nbsp;
                    <span class="post-date">{{ $comment->post->created_at->diffForHumans() }}</span>
                </div>
                <div class="post-content">
                    {{ Str::limit($comment->post->content, 150) }}
                </div>
                @if($comment->post->attachments && $comment->post->attachments->count() > 0)
                    @php
                        $firstImage = $comment->post->attachments->first();
                    @endphp
                    @if($firstImage && $firstImage->isImage())
                        <div class="post-image-container">
                            <img src="{{ $firstImage->file_path }}" alt="Post image" class="post-preview-image">
                        </div>
                    @endif
                @endif
            </div>

            <div class="comment-content">
                {{ $comment->content }}
            </div>

            <div class="comment-actions">
                <button class="action-btn like-btn" style="color: {{ $comment->isLikedBy(auth()->user()) ? '#FA2C8B' : '' }}">
                    <i class="far fa-heart"></i>
                    <span>{{ number_format($comment->like_count ?? 0) }}</span>
                </button>
                <button class="action-btn reply-btn">
                    <i class="far fa-comment"></i> Reply
                </button>
            </div>
        </div>
    @endforeach
@else
    <div class="text-center py-5">
        <i class="fas fa-comments fa-3x text-muted mb-3"></i>
        <h5 class="text-muted">No comments yet</h5>
        <p class="text-muted">Posts you've commented on will appear here.</p>
    </div>
@endif