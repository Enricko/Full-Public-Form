@extends('index')

@section('title', 'Post by @' . $post->user->username . ' - PublicForum')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            @include('components.post-card', ['post' => $post])

            {{-- Comments section --}}
            <div class="comments-section mt-4" id="comments">
                <h5>Comments ({{ $post->comment_count }})</h5>

                @auth
                    {{-- Comment form --}}
                    <form action="{{ route('comments.store', $post) }}" method="POST" class="mb-4">
                        @csrf
                        <div class="mb-3">
                            <textarea name="content" class="form-control" rows="3"
                                      placeholder="Write a comment..." required></textarea>
                        </div>
                        <button type="submit" class="btn btn-danger btn-sm">Post Comment</button>
                    </form>
                @else
                    <div class="text-center py-3 mb-4 bg-light rounded">
                        <p class="mb-2">Join the conversation!</p>
                        <button class="btn btn-danger btn-sm" onclick="showLoginModal()">
                            Login to Comment
                        </button>
                    </div>
                @endauth

                {{-- Comments list --}}
                @forelse($post->comments->where('parent_comment_id', null) as $comment)
                    @include('components.comment', ['comment' => $comment])
                @empty
                    <p class="text-muted">No comments yet. Be the first to comment!</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
