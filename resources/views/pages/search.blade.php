@extends('index')

@section('title', 'Search - PublicForum')

@push('scripts')
<script src="{{ asset('assets/js/video-player.js') }}"></script>
<script src="{{ asset('assets/js/post-interactions.js') }}"></script>
@endpush

@section('content')
<div class="container">
    <!-- Page Header -->
    <h4 class="page-header mb-4 pb-2 border-bottom">
        @if(!empty($query))
        Search Results for "{{ $query }}"
        @else
        Search
        @endif
    </h4>

    <!-- Search Form -->
    <div class="search-container mb-4">
        <form id="searchForm" method="GET" action="{{ route('search') }}">
            <div class="d-flex">
                <input type="text"
                    id="searchInput"
                    name="q"
                    class="form-control me-2"
                    value="{{ $query }}"
                    placeholder="Search forums, users, or tags..." />
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search"></i> Search
                </button>
            </div>
        </form>
    </div>

    <!-- Search Filters -->
    <div class="search-filters mb-4">
        <form id="filtersForm" method="GET" action="{{ route('search') }}">
            <input type="hidden" name="q" value="{{ $query }}">

            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label">Sort by:</label>
                    <select name="sort" class="form-select">
                        <option value="relevance" {{ $sort == 'relevance' ? 'selected' : '' }}>Most Relevant</option>
                        <option value="recent" {{ $sort == 'recent' ? 'selected' : '' }}>Most Recent</option>
                        <option value="popular" {{ $sort == 'popular' ? 'selected' : '' }}>Most Popular</option>
                        <option value="commented" {{ $sort == 'commented' ? 'selected' : '' }}>Most Commented</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Time:</label>
                    <select name="time" class="form-select">
                        <option value="anytime" {{ $time == 'anytime' ? 'selected' : '' }}>Anytime</option>
                        <option value="today" {{ $time == 'today' ? 'selected' : '' }}>Today</option>
                        <option value="week" {{ $time == 'week' ? 'selected' : '' }}>This Week</option>
                        <option value="month" {{ $time == 'month' ? 'selected' : '' }}>This Month</option>
                        <option value="year" {{ $time == 'year' ? 'selected' : '' }}>This Year</option>
                    </select>
                </div>
            </div>

            <!-- Advanced Filters -->
            <div class="advanced-filters">
                <button type="button" class="btn btn-outline-secondary btn-sm mb-3" data-bs-toggle="collapse" data-bs-target="#advancedOptions">
                    <i class="fas fa-sliders-h"></i> Advanced Filters
                </button>

                <div class="collapse" id="advancedOptions">
                    <div class="card card-body bg-light">
                        <div class="row">
                            <div class="col-md-4">
                                <label class="form-label">Content Type:</label>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="content_type[]" value="image"
                                        {{ in_array('image', $contentType ?? []) ? 'checked' : '' }}>
                                    <label class="form-check-label">With Images</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="content_type[]" value="video"
                                        {{ in_array('video', $contentType ?? []) ? 'checked' : '' }}>
                                    <label class="form-check-label">With Videos</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="content_type[]" value="links"
                                        {{ in_array('links', $contentType ?? []) ? 'checked' : '' }}>
                                    <label class="form-check-label">With Links</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Date From:</label>
                                <input type="date" name="date_from" class="form-control mb-2" value="{{ $dateFrom ?? '' }}">
                                <label class="form-label">Date To:</label>
                                <input type="date" name="date_to" class="form-control" value="{{ $dateTo ?? '' }}">
                            </div>
                            <div class="col-md-4 d-flex align-items-end">
                                <div class="w-100">
                                    <button type="submit" class="btn btn-danger w-100 mb-2">Apply Filters</button>
                                    <a href="{{ route('search') }}?q={{ $query }}" class="btn btn-outline-secondary w-100">Reset</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- Result Tabs -->
    <ul class="nav nav-tabs mb-4" role="tablist">
        <li class="nav-item">
            <button class="nav-link active" data-tab="all" type="button">
                All Results
                @if($totalResults > 0)
                <span class="badge bg-secondary">{{ $totalResults }}</span>
                @endif
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link" data-tab="posts" type="button">
                Posts
                @if(isset($posts) && count($posts) > 0)
                <span class="badge bg-secondary">{{ count($posts) }}</span>
                @endif
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link" data-tab="users" type="button">
                Users
                @if(isset($users) && $users->count() > 0)
                <span class="badge bg-secondary">{{ $users->count() }}</span>
                @endif
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link" data-tab="hashtags" type="button">
                Hashtags
                @if(isset($hashtags) && $hashtags->count() > 0)
                <span class="badge bg-secondary">{{ $hashtags->count() }}</span>
                @endif
            </button>
        </li>
    </ul>

    <!-- Search Results -->
    @if(!empty($query))
    <div class="search-results">
        <p class="text-muted mb-4">Found {{ $totalResults }} results for "<strong>{{ $query }}</strong>"</p>

        <!-- All Results Tab -->
        <div id="all-tab-content" class="tab-content">
            <!-- Hashtags Section -->
            @if(isset($hashtags) && $hashtags->count() > 0)
            <div class="results-section mb-4">
                <h5 class="section-title">
                    <i class="fas fa-hashtag text-primary"></i> Hashtags
                </h5>
                @foreach($hashtags->take(3) as $hashtag)
                <div class="social-post">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <h6 class="mb-1 fw-bold">#{{ $hashtag->name }}</h6>
                                <small class="text-muted">
                                    {{ number_format($hashtag->post_count ?? 0) }} posts
                                </small>
                            </div>
                            <button class="btn btn-outline-primary btn-sm">Follow</button>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            @endif

            <!-- Users Section -->
            @if(isset($users) && $users->count() > 0)
            <div class="results-section mb-4">
                <h5 class="section-title">
                    <i class="fas fa-users text-success"></i> Users
                </h5>
                @foreach($users->take(3) as $user)
                <div class="social-post">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <img src="{{ $user->avatar_url ?? asset('assets/images/profile.png') }}"
                                class="rounded-circle me-3" width="40" height="40" alt="Avatar">
                            <div class="flex-grow-1">
                                <h6 class="mb-1 fw-bold">{{ $user->username }}</h6>
                                <small class="text-muted">{{ $user->display_name ?? $user->bio }}</small><br>
                                <small class="text-muted">{{ $user->posts->count() ?? 0 }} posts</small>
                            </div>
                            <button class="btn btn-outline-primary btn-sm">Follow</button>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            @endif

            <!-- Posts Section -->
            @if(isset($posts) && count($posts) > 0)
            <div class="results-section">
                <h5 class="section-title">
                    <i class="fas fa-comments text-info"></i> Posts
                </h5>
                <div id="posts-container">
                    @include('components.post-list', ['posts' => collect($posts)])
                </div>
            </div>
            @endif
        </div>

        <!-- Posts Only Tab -->
        <div id="posts-tab-content" class="tab-content d-none">
            @if(isset($posts) && count($posts) > 0)
            <div id="posts-only-container">
                @include('components.post-list', ['posts' => collect($posts)])
            </div>
            @else
            <div class="text-center py-5">
                <i class="fas fa-search fa-3x text-muted mb-3"></i>
                <h5>No posts found</h5>
                <p class="text-muted">Try different search terms or adjust your filters.</p>
            </div>
            @endif
        </div>

        <!-- Users Only Tab -->
        <div id="users-tab-content" class="tab-content d-none">
            @if(isset($users) && $users->count() > 0)
            @foreach($users as $user)
            <div class="social-post">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <img src="{{ $user->avatar_url ?? asset('assets/images/profile.png') }}"
                            class="rounded-circle me-3" width="50" height="50" alt="Avatar">
                        <div class="flex-grow-1">
                            <h6 class="mb-1 fw-bold">{{ $user->username }}</h6>
                            <p class="mb-1 text-muted">{{ $user->display_name ?? $user->bio }}</p>
                            <small class="text-muted">
                                {{ $user->posts->count() ?? 0 }} posts •
                                Member since {{ $user->created_at->format('M Y') }}
                            </small>
                        </div>
                        <button class="btn btn-outline-primary btn-sm">Follow</button>
                    </div>
                </div>
            </div>
            @endforeach
            @else
            <div class="text-center py-5">
                <i class="fas fa-users fa-3x text-muted mb-3"></i>
                <h5>No users found</h5>
                <p class="text-muted">Try different search terms.</p>
            </div>
            @endif
        </div>

        <!-- Hashtags Only Tab -->
        <div id="hashtags-tab-content" class="tab-content d-none">
            @if(isset($hashtags) && $hashtags->count() > 0)
            @foreach($hashtags as $hashtag)
            <div class="social-post">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h6 class="mb-1 fw-bold">#{{ $hashtag->name }}</h6>
                            <small class="text-muted">
                                {{ number_format($hashtag->post_count ?? 0) }} posts
                            </small>
                        </div>
                        <button class="btn btn-outline-primary btn-sm">Follow</button>
                    </div>
                </div>
            </div>
            @endforeach
            @else
            <div class="text-center py-5">
                <i class="fas fa-hashtag fa-3x text-muted mb-3"></i>
                <h5>No hashtags found</h5>
                <p class="text-muted">Try different search terms.</p>
            </div>
            @endif
        </div>
    </div>

    @else
    <!-- Empty Search State -->
    <div class="text-center py-5">
        <i class="fas fa-search fa-4x text-muted mb-4"></i>
        <h4 class="text-muted">Search PublicForum</h4>
        <p class="text-muted">Find posts, users, and hashtags</p>
    </div>
    @endif
</div>

<style>
    /* Search Page Styles */
    .search-container {
        position: relative;
    }

    .search-filters {
        background: #f8f9fa;
        padding: 1rem;
        border-radius: 0.5rem;
        border: 1px solid #dee2e6;
    }

    .nav-tabs .nav-link {
        border: none;
        color: #6c757d;
        font-weight: 500;
    }

    .nav-tabs .nav-link.active {
        color: #dc3545;
        border-bottom: 2px solid #dc3545;
        background: none;
    }

    .nav-tabs .nav-link:hover {
        color: #dc3545;
        border-color: transparent;
    }

    .results-section {
        border-bottom: 1px solid #dee2e6;
        padding-bottom: 1rem;
    }

    .section-title {
        color: #495057;
        font-weight: 600;
        margin-bottom: 1rem;
        padding-bottom: 0.5rem;
        border-bottom: 1px solid #e9ecef;
    }

    .social-post {
        background: white;
        border: 1px solid #dee2e6;
        border-radius: 0.5rem;
        margin-bottom: 0.75rem;
        transition: box-shadow 0.2s ease;
    }

    .social-post:hover {
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }

    .social-post .card-body {
        padding: 1rem;
    }

    .badge {
        font-size: 0.75rem;
    }

    .advanced-filters .form-check {
        margin-bottom: 0.5rem;
    }

    .form-select,
    .form-control {
        border-radius: 0.375rem;
    }

    .btn {
        border-radius: 0.375rem;
    }

    /* Loading states */
    .loading {
        opacity: 0.6;
        pointer-events: none;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .search-filters .row>div {
            margin-bottom: 1rem;
        }

        .nav-tabs .nav-link {
            font-size: 0.875rem;
            padding: 0.5rem 0.75rem;
        }

        .section-title {
            font-size: 1rem;
        }
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        console.log('🔍 Search page loaded');

        // Tab switching functionality
        const tabButtons = document.querySelectorAll('[data-tab]');
        const tabContents = document.querySelectorAll('.tab-content');

        tabButtons.forEach(button => {
            button.addEventListener('click', function() {
                const targetTab = this.getAttribute('data-tab');

                // Update active tab
                tabButtons.forEach(btn => btn.classList.remove('active'));
                this.classList.add('active');

                // Show/hide content
                tabContents.forEach(content => {
                    content.classList.add('d-none');
                });

                const targetContent = document.getElementById(targetTab + '-tab-content');
                if (targetContent) {
                    targetContent.classList.remove('d-none');
                }

                console.log('Switched to tab:', targetTab);
            });
        });

        // Auto-submit filters on change
        const filterSelects = document.querySelectorAll('#filtersForm select');
        const filterCheckboxes = document.querySelectorAll('#filtersForm input[type="checkbox"]');
        const filterDates = document.querySelectorAll('#filtersForm input[type="date"]');

        function autoSubmitFilters() {
            const form = document.getElementById('filtersForm');
            if (form && document.getElementById('searchInput').value.trim()) {
                form.submit();
            }
        }

        filterSelects.forEach(select => {
            select.addEventListener('change', autoSubmitFilters);
        });

        filterCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', autoSubmitFilters);
        });

        filterDates.forEach(input => {
            input.addEventListener('change', autoSubmitFilters);
        });

        // Search form enhancement
        const searchForm = document.getElementById('searchForm');
        const searchInput = document.getElementById('searchInput');

        searchForm.addEventListener('submit', function(e) {
            const query = searchInput.value.trim();
            if (!query) {
                e.preventDefault();
                searchInput.focus();
                searchInput.classList.add('is-invalid');
                setTimeout(() => {
                    searchInput.classList.remove('is-invalid');
                }, 2000);
                return false;
            }
        });

        // Initialize post interactions
        setTimeout(() => {
            if (window.PostInteractions) {
                console.log('🔗 Initializing Post Interactions...');
                PostInteractions.init();
                document.dispatchEvent(new CustomEvent('ajaxContentLoaded'));
            }
        }, 100);

        // Follow button interactions
        document.querySelectorAll('.btn').forEach(button => {
            if (button.textContent.trim() === 'Follow') {
                button.addEventListener('click', function() {
                    const isFollowing = this.textContent.trim() === 'Following';

                    if (isFollowing) {
                        this.textContent = 'Follow';
                        this.classList.remove('btn-primary');
                        this.classList.add('btn-outline-primary');
                    } else {
                        this.textContent = 'Following';
                        this.classList.remove('btn-outline-primary');
                        this.classList.add('btn-primary');
                    }

                    // Here you would typically make an AJAX call to update the follow status
                    console.log('Follow status toggled');
                });
            }
        });

        console.log('✅ Search page initialized');
    });
</script>

@endsection