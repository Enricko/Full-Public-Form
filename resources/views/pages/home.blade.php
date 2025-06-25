@extends('index')

@section('title', 'Home - PublicForum')

@push('scripts')
    <script src="{{ asset('assets/js/video-player.js') }}"></script>
    <script src="{{ asset('assets/js/post-interactions.js') }}"></script>
@endpush

@section('content')
    <div class="container">
        <!-- Notification Area for Interactions -->
        <div class="notification-area" id="notificationArea"></div>
        
        <!-- Success Message -->
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
                <i class="fas fa-check-circle me-2"></i>
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <!-- Error Message -->
        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <strong>Error:</strong>
                <ul class="mb-0 mt-2">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <!-- Quick Action Bar -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card bg-light border-0">
                    <div class="card-body py-3">
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center">
                                <img src="{{ asset('assets/images/profile.png') }}" alt="Profile" class="rounded-circle me-3" style="width: 40px; height: 40px;" />
                                <span class="text-muted">What's on your mind?</span>
                            </div>
                            <a href="{{ route('posts.create') }}" class="btn btn-danger">
                                <i class="fas fa-plus me-1"></i>Create Post
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

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
                <a href="{{ route('posts.create') }}" class="btn btn-danger btn-lg">
                    <i class="fas fa-plus"></i> Create First Post
                </a>
            </div>
        @endif
    </div>

    <!-- Initialize Page -->
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

        // Page initialization
        document.addEventListener('DOMContentLoaded', function() {
            console.log('🚀 Initializing Home Page...');
            
            // Auto-hide success/error messages after 5 seconds
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                setTimeout(function() {
                    if (alert && alert.classList.contains('show')) {
                        const bsAlert = new bootstrap.Alert(alert);
                        bsAlert.close();
                    }
                }, 5000);
            });

            // Initialize post interactions after a short delay to ensure all scripts are loaded
            setTimeout(() => {
                if (window.PostInteractions) {
                    console.log('🔗 Initializing Post Interactions...');
                    PostInteractions.init();
                    
                    // Trigger content loaded event for initial posts
                    document.dispatchEvent(new CustomEvent('ajaxContentLoaded'));
                } else {
                    console.warn('⚠️ PostInteractions not found, trying again...');
                    setTimeout(() => {
                        if (window.PostInteractions) {
                            PostInteractions.init();
                            document.dispatchEvent(new CustomEvent('ajaxContentLoaded'));
                        }
                    }, 500);
                }
            }, 100);

            console.log('✅ Home Page initialized');
        });
    </script>
    
    @if(isset($infiniteScroll) && $infiniteScroll)
        <script src="{{ asset('assets/js/infinite-scroll.js') }}"></script>
    @endif
@endsection