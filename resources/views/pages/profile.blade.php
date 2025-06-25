@extends('index')

@section('title', 'Profile - PublicForum')

@push('scripts')
    <script src="{{ asset('assets/js/video-player.js') }}"></script>
    <script src="{{ asset('assets/js/post-interactions.js') }}"></script>
@endpush

@section('content')
    <div class="container">
        <div class="notification-area" id="notificationArea"></div>

        <div class="profile-container">
            <div class="profile-header">
                <div class="profile-banner"></div>
                <div class="profile-avatar">
                    <div class="profile-avatar-container">
                        <img src="{{ $user->avatar_url ? asset('storage/' . $user->avatar_url) : asset('assets/images/profile.png') }}" alt="Avatar" id="profileAvatar" />
                    </div>
                </div>
                <div class="profile-info">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 id="profileName">{{ $user->username }}</h3>
                            <div class="text-muted" id="profileBio">{{ $user->bio }}</div>
                            <div class="text-muted small">Account created {{ $user->created_at->format('M Y') }}</div>
                        </div>
                        <button class="edit-profile-btn" id="editProfileBtn" onclick="profilePage.openEditModal()">
                            <i class="fas fa-edit me-1"></i> Edit Profile
                        </button>
                    </div>

                    <div class="profile-stats">
                        <div class="fw-bold text-muted small mb-2">COMMUNITY STATISTICS</div>
                        <div class="row">
                            <div class="col-md-4">
                                <div>Posts</div>
                                <div class="fw-bold">{{ number_format($stats['posts_count']) }}</div>
                            </div>
                            <div class="col-md-4">
                                <div>Comments</div>
                                <div class="fw-bold">{{ number_format($stats['comments_count']) }}</div>
                            </div>
                            <div class="col-md-4">
                                <div>Likes</div>
                                <div class="fw-bold">{{ number_format($stats['likes_count']) }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <ul class="nav nav-tabs" id="profileTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link {{ ($tab ?? 'posts') === 'posts' ? 'active' : '' }}" id="posts-tab" onclick="profilePage.switchTab('posts')" type="button" role="tab">
                        Posts
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link {{ $tab === 'comments' ? 'active' : '' }}" id="comments-tab" onclick="profilePage.switchTab('comments')" type="button" role="tab">
                        Comments
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link {{ $tab === 'likes' ? 'active' : '' }}" id="likes-tab" onclick="profilePage.switchTab('likes')" type="button" role="tab">
                        Likes
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link {{ $tab === 'saved' ? 'active' : '' }}" id="saved-tab" onclick="profilePage.switchTab('saved')" type="button" role="tab">
                        Saved
                    </button>
                </li>
            </ul>

            <div class="tab-content" id="profileTabsContent">
                <div class="tab-pane fade show active" id="posts" role="tabpanel" aria-labelledby="posts-tab">
                    @if(($tab ?? 'posts') === 'comments')
                        @if(isset($comments) && $comments->count() > 0)
                            @include('components.comments-list', ['comments' => $comments])
                        @else
                            <div class="text-center py-5">
                                <i class="fas fa-comments fa-3x text-muted mb-3"></i>
                                <h5 class="text-muted">No comments yet</h5>
                                <p class="text-muted">Posts you've commented on will appear here.</p>
                            </div>
                        @endif
                    @else
                        @if ($posts->count() > 0)
                            @include('components.post-list', ['posts' => $posts])
                        @else
                            <div class="text-center py-5">
                                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                <h5 class="text-muted">No {{ $tab ?? 'posts' }} yet</h5>
                                <p class="text-muted">
                                    @if (($tab ?? 'posts') === 'posts')
                                        Create your first post to see it here!
                                    @elseif($tab === 'likes')
                                        Posts you like will appear here.
                                    @elseif($tab === 'saved')
                                        Posts you save will appear here.
                                    @endif
                                </p>
                            </div>
                        @endif
                    @endif
                </div>
            </div>

            <div id="loading-indicator" class="text-center py-4" style="display: none;">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-2 text-muted">Loading more posts...</p>
            </div>

            <div id="end-of-posts" class="text-center py-4" style="display: none;">
                <div class="text-muted">
                    <i class="fas fa-check-circle fa-2x mb-2 text-success"></i>
                    <h5>All caught up! 🎉</h5>
                    <p class="mb-3">No more {{ $tab ?? 'posts' }} to show.</p>
                </div>
            </div>

            <!-- Edit Profile Modal -->
            <div class="custom-modal" id="customEditModal">
                <div class="custom-modal-dialog">
                    <div class="custom-modal-content">
                        <div class="custom-modal-header">
                            <h5 class="custom-modal-title">Edit Profile</h5>
                            <button type="button" class="custom-close" onclick="profilePage.closeEditModal()">
                                &times;
                            </button>
                        </div>
                        <div class="custom-modal-body">
                            <form id="editProfileForm" action="{{ route('editProfile') }}" enctype="multipart/form-data" method="post">
                                @csrf
                                @method('PUT')
                                <div class="text-center mb-4">
                                    <label class="form-label">Profile Picture</label>
                                    <div class="position-relative mx-auto" style="width: 120px; height: 120px">
                                        <div style="width: 120px; height: 120px; border-radius: 50%; overflow: hidden; border: 3px solid #dc3545;">
                                            <img src="{{ $user->avatar_url ? asset('storage/' . $user->avatar_url) : asset('assets/images/profile.png') }}" alt="Profile Picture" id="previewAvatar" style="width: 100%; height: 100%; object-fit: cover" />
                                        </div>
                                        <div class="position-absolute bottom-0 end-0 bg-danger rounded-circle d-flex justify-content-center align-items-center" style="width: 36px; height: 36px; cursor: pointer; border: 2px solid #fff;"
                                            onclick="document.getElementById('avatarUpload').click()">
                                            <i class="fas fa-camera text-white" style="font-size: 16px"></i>
                                        </div>
                                        <input type="file" id="avatarUpload" accept="image/*" style="display: none" onchange="profilePage.previewImage(this)" name="avatar_url" />
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="displayName" class="form-label">Display Name</label>
                                    <input type="text" class="form-control" id="displayName" value="{{ $user->username }}" name="username" required />
                                </div>

                                <div class="mb-3">
                                    <label for="bioMe" class="form-label">Bio</label>
                                    <textarea class="form-control" id="bioMe" rows="4" name="bio">{{ $user->bio }}</textarea>
                                </div>
                                <div class="custom-modal-footer">
                                    <button type="button" class="btn-secondary mx-1" onclick="profilePage.closeEditModal()">
                                        Cancel
                                    </button>
                                    <button type="submit" class="btn-danger mx-1">
                                        Save Changes
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Profile Page JavaScript -->
            <script>
                // Set current tab for JavaScript
                window.currentTab = '{{ $tab ?? "posts" }}';

                // Create a global object to hold all profile page functions
                window.profilePage = {
                    currentTab: '{{ $tab ?? "posts" }}',
                    isLoading: false,

                    // Function to open the edit modal
                    openEditModal: function () {
                        console.log("Opening edit modal");
                        document.getElementById("customEditModal").style.display = "block";
                    },

                    // Function to close the edit modal
                    closeEditModal: function () {
                        console.log("Closing edit modal");
                        document.getElementById("customEditModal").style.display = "none";
                    },

                    // Function to switch tabs with AJAX
                    switchTab: function (tabId) {
                        if (this.isLoading || this.currentTab === tabId) return;

                        console.log("Switching to tab:", tabId);
                        this.isLoading = true;
                        this.currentTab = tabId;

                        // Update tab UI
                        document.querySelectorAll(".nav-tabs .nav-link").forEach(function (button) {
                            button.classList.remove("active");
                        });
                        document.getElementById(tabId + "-tab").classList.add("active");

                        // Show loading state
                        const postsContainer = document.querySelector('#posts');
                        if (postsContainer) {
                            postsContainer.innerHTML = `
                                        <div class="text-center py-5">
                                            <div class="spinner-border text-primary" role="status">
                                                <span class="visually-hidden">Loading...</span>
                                            </div>
                                            <p class="mt-2 text-muted">Loading ${tabId}...</p>
                                        </div>
                                    `;
                        }

                        // Make AJAX call to switch tab
                        fetch('/profile/switch-tab', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            body: JSON.stringify({ tab: tabId })
                        })
                            .then(response => response.json())
                            .then(data => {
                                console.log('Tab response:', data);
                                if (data.success && postsContainer) {
                                    postsContainer.innerHTML = data.posts;
                                    this.setupPostInteractions();
                                } else {
                                    console.error('Failed to load tab content');
                                    postsContainer.innerHTML = `
                                                <div class="text-center py-5">
                                                    <div class="text-danger">
                                                        <i class="fas fa-exclamation-triangle fa-2x mb-2"></i>
                                                        <h5>Failed to load content</h5>
                                                        <p>Please try again.</p>
                                                    </div>
                                                </div>
                                            `;
                                }
                            })
                            .catch(error => {
                                console.error('Error switching tab:', error);
                                if (postsContainer) {
                                    postsContainer.innerHTML = `
                                                <div class="text-center py-5">
                                                    <div class="text-danger">
                                                        <i class="fas fa-exclamation-triangle fa-2x mb-2"></i>
                                                        <h5>Error loading content</h5>
                                                        <p>Please check your connection and try again.</p>
                                                    </div>
                                                </div>
                                            `;
                                }
                            })
                            .finally(() => {
                                this.isLoading = false;
                            });
                    },

                    // Set up post interactions (use global PostInteractions if available)
                    setupPostInteractions: function () {
                        console.log('Setting up post interactions');

                        if (window.PostInteractions) {
                            // Use the global post interactions
                            PostInteractions.reinitialize();
                        } else {
                            // Fallback to local handlers
                            this.setupLocalInteractions();
                        }
                    },

                    // Local interaction handlers (fallback)
                    setupLocalInteractions: function () {
                        // Like buttons
                        document.querySelectorAll('.like-btn').forEach(button => {
                            if (!button.dataset.handlerSet) {
                                button.dataset.handlerSet = 'true';
                                button.addEventListener('click', (e) => {
                                    e.preventDefault();
                                    e.stopPropagation();
                                    this.handleLikeClick(button);
                                });
                            }
                        });

                        // Save buttons
                        document.querySelectorAll('.save-btn').forEach(button => {
                            if (!button.dataset.handlerSet) {
                                button.dataset.handlerSet = 'true';
                                button.addEventListener('click', (e) => {
                                    e.preventDefault();
                                    e.stopPropagation();
                                    this.handleSaveClick(button);
                                });
                            }
                        });

                        // Share buttons
                        document.querySelectorAll('.share-btn').forEach(button => {
                            if (!button.dataset.handlerSet) {
                                button.dataset.handlerSet = 'true';
                                button.addEventListener('click', (e) => {
                                    e.preventDefault();
                                    e.stopPropagation();
                                    this.handleShareClick(button);
                                });
                            }
                        });
                    },

                    // Handle like button clicks
                    handleLikeClick: function (button) {
                        const postId = button.dataset.postId;
                        const icon = button.querySelector('i');
                        const countSpan = button.querySelector('span');
                        const currentCount = parseInt(countSpan.textContent) || 0;
                        const isLiked = button.classList.contains('active');

                        // Optimistic update
                        if (isLiked) {
                            button.classList.remove('active');
                            button.style.color = '';
                            icon.classList.remove('fas');
                            icon.classList.add('far');
                            countSpan.textContent = currentCount - 1;
                        } else {
                            button.classList.add('active');
                            button.style.color = '#fa2c8b';
                            icon.classList.remove('far');
                            icon.classList.add('fas');
                            countSpan.textContent = currentCount + 1;
                        }

                        // Send to server
                        fetch(`/posts/${postId}/like`, {
                            method: 'POST',
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            }
                        })
                            .then(response => response.json())
                            .then(data => {
                                console.log('Like response:', data);
                                if (data.success) {
                                    countSpan.textContent = data.like_count;
                                    if (data.liked) {
                                        button.classList.add('active');
                                        button.style.color = '#fa2c8b';
                                        icon.classList.remove('far');
                                        icon.classList.add('fas');
                                    } else {
                                        button.classList.remove('active');
                                        button.style.color = '';
                                        icon.classList.remove('fas');
                                        icon.classList.add('far');
                                    }
                                    this.showNotification(data.message, 'success');
                                }
                            })
                            .catch(error => {
                                console.error('Error toggling like:', error);
                                // Revert on error
                                if (!isLiked) {
                                    button.classList.remove('active');
                                    button.style.color = '';
                                    icon.classList.remove('fas');
                                    icon.classList.add('far');
                                    countSpan.textContent = currentCount;
                                } else {
                                    button.classList.add('active');
                                    button.style.color = '#fa2c8b';
                                    icon.classList.remove('far');
                                    icon.classList.add('fas');
                                    countSpan.textContent = currentCount;
                                }
                            });
                    },

                    // Handle save button clicks
                    handleSaveClick: function (button) {
                        const postId = button.dataset.postId;
                        const icon = button.querySelector('i');
                        const isSaved = button.classList.contains('active');

                        // Optimistic update
                        if (isSaved) {
                            button.classList.remove('active');
                            button.style.color = '';
                            icon.classList.remove('fas');
                            icon.classList.add('far');
                        } else {
                            button.classList.add('active');
                            button.style.color = '#1da1f2';
                            icon.classList.remove('far');
                            icon.classList.add('fas');
                        }

                        // Send to server
                        fetch(`/posts/${postId}/save`, {
                            method: 'POST',
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            }
                        })
                            .then(response => response.json())
                            .then(data => {
                                console.log('Save response:', data);
                                if (data.success) {
                                    if (data.saved) {
                                        button.classList.add('active');
                                        button.style.color = '#1da1f2';
                                        icon.classList.remove('far');
                                        icon.classList.add('fas');
                                    } else {
                                        button.classList.remove('active');
                                        button.style.color = '';
                                        icon.classList.remove('fas');
                                        icon.classList.add('far');
                                    }
                                    this.showNotification(data.message, 'success');
                                }
                            })
                            .catch(error => {
                                console.error('Error toggling save:', error);
                                // Revert on error
                                if (!isSaved) {
                                    button.classList.remove('active');
                                    button.style.color = '';
                                    icon.classList.remove('fas');
                                    icon.classList.add('far');
                                } else {
                                    button.classList.add('active');
                                    button.style.color = '#1da1f2';
                                    icon.classList.remove('far');
                                    icon.classList.add('fas');
                                }
                            });
                    },

                    // Handle share button clicks
                    handleShareClick: function (button) {
                        const postId = button.dataset.postId;
                        const countSpan = button.querySelector('span');
                        const currentCount = parseInt(countSpan.textContent) || 0;

                        // Optimistic update
                        countSpan.textContent = currentCount + 1;

                        // Send to server
                        fetch(`/posts/${postId}/share`, {
                            method: 'POST',
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            }
                        })
                            .then(response => response.json())
                            .then(data => {
                                console.log('Share response:', data);
                                if (data.success) {
                                    countSpan.textContent = data.share_count;
                                    this.showNotification(data.message, 'success');
                                }
                            })
                            .catch(error => {
                                console.error('Error sharing post:', error);
                                // Revert on error
                                countSpan.textContent = currentCount;
                            });
                    },

                    // Function to preview the selected image
                    previewImage: function (input) {
                        console.log("Previewing image");
                        if (input.files && input.files[0]) {
                            var reader = new FileReader();
                            reader.onload = function (e) {
                                document.getElementById("previewAvatar").src = e.target.result;
                            };
                            reader.readAsDataURL(input.files[0]);
                        }
                    },

                    // Function to show notification
                    showNotification: function (message, type = 'success') {
                        console.log("Showing notification:", message);
                        var notificationArea = document.getElementById("notificationArea");
                        notificationArea.innerHTML = "";

                        const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
                        const icon = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';

                        var alertHTML = `
                                    <div class="alert ${alertClass} alert-dismissible fade show">
                                        <i class="fas ${icon} me-2"></i>
                                        ${message}
                                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                    </div>
                                `;

                        notificationArea.innerHTML = alertHTML;

                        // Auto dismiss after 5 seconds
                        setTimeout(function () {
                            var alert = notificationArea.querySelector(".alert");
                            if (alert) {
                                alert.classList.remove('show');
                            }
                        }, 5000);
                    },

                    // Initialize the profile page
                    init: function () {
                        console.log("Initializing profile page");

                        // Close modal when clicking outside of it
                        window.addEventListener("click", function (event) {
                            if (event.target === document.getElementById("customEditModal")) {
                                profilePage.closeEditModal();
                            }
                        });

                        // Set up initial post interactions
                        this.setupPostInteractions();

                        console.log("Profile page initialized");
                    }
                };

                // Document ready handler
                document.addEventListener("DOMContentLoaded", function () {
                    console.log('🚀 Initializing Profile Page...');

                    // Initialize the profile page
                    profilePage.init();

                    // Initialize global post interactions if available
                    if (window.PostInteractions) {
                        console.log('🔗 Initializing Post Interactions...');
                        PostInteractions.init();

                        // Trigger content loaded event for initial posts
                        setTimeout(() => {
                            document.dispatchEvent(new CustomEvent('ajaxContentLoaded'));
                        }, 100);
                    }

                    console.log('✅ Profile Page initialized');
                });
            </script>
        </div>
    </div>
@endsection