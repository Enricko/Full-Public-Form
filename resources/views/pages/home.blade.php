@extends('index')

@section('title', 'Home - PublicForum')

@push('scripts')
    <script src="{{ asset('assets/js/video-player.js') }}"></script>
    <script src="{{ asset('assets/js/post-interactions.js') }}"></script>
@endpush

@section('content')
    <div class="container">
        <!-- Posts Container -->
        <div id="posts-container">
            @include('components.post-list', ['posts' => $posts])
        </div>

        <!-- Loading Indicator -->
        <div id="loading-indicator" class="text-center py-4" style="display: none;">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-2 text-muted">Loading more posts...</p>
        </div>

        <!-- End of Posts Indicator -->
        <div id="end-of-posts" class="text-center py-4" style="display: none;">
            <div class="text-muted">
                <i class="fas fa-check-circle fa-2x mb-2 text-success"></i>
                <h5>All caught up! 🎉</h5>
                <p class="mb-3">You've seen all the latest posts.</p>
                <button class="btn btn-outline-primary btn-sm" onclick="window.location.reload()">
                    <i class="fas fa-refresh"></i> Refresh for new posts
                </button>
            </div>
        </div>

        <!-- Empty State -->
        @if ($posts->count() == 0)
            <div class="text-center py-5">
                <div class="mb-4">
                    <i class="fas fa-comments fa-4x text-muted"></i>
                </div>
                <h4 class="text-muted">No posts yet</h4>
                <p class="text-muted mb-4">Be the first to share something with the community!</p>
                @auth
                    <a href="{{ route('posts.create') }}" class="btn btn-danger btn-lg">
                        <i class="fas fa-plus"></i> Create First Post
                    </a>
                @else
                    <button class="btn btn-danger btn-lg" onclick="showLoginModal()">
                        <i class="fas fa-sign-in-alt"></i> Login to Post
                    </button>
                @endauth
            </div>
        @endif
    </div>

    <!-- Pass PHP data to JavaScript and Initialize -->
    <script>
        // Configuration data from PHP
        window.paginationConfig = {
            currentPage: {{ $posts->currentPage() }},
            lastPage: {{ $posts->lastPage() }},
            hasMore: {{ $posts->hasMorePages() ? 'true' : 'false' }},
            total: {{ $posts->total() }},
            count: {{ $posts->count() }},
            loadUrl: '{{ url('/') }}'
        };

        console.log('🏠 Home page loaded');
        console.log('📊 Pagination data:', window.paginationConfig);
    </script>
    <script src="{{ asset('assets/js/infinite-scroll.js') }}"></script>
@endsection
