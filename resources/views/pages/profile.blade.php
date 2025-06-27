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
                        <img src="{{ $user->avatar_url ? asset('storage/' . $user->avatar_url) : asset('assets/images/profile.png') }}" alt="Avatar" id="profileAvatar" ondblclick="profilePage.openAvatarModal(this)" style="cursor: pointer;"
                            title="Double-click to view full size" />
                    </div>
                </div>
                <div class="profile-info">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 id="profileName">{{ $user->username }}</h3>
                            <div class="text-muted" id="profileBio">{{ $user->bio }}</div>
                            <div class="text-muted small">Account created {{ $user->created_at->format('M Y') }}</div>
                            @if(!$isOwnProfile)
                                <div class="text-muted small">
                                    <i class="fas fa-id-badge me-1"></i>
                                    User ID: {{ $user->id }}
                                </div>
                            @endif
                        </div>

                        @if($isOwnProfile)
                            <button class="edit-profile-btn" id="editProfileBtn" onclick="profilePage.openEditModal()">
                                <i class="fas fa-edit me-1"></i> Edit Profile
                            </button>
                        @else
                            <div class="d-flex gap-2">
                                <button class="{{ isset($isFollowing) && $isFollowing ? 'follow-btn outline' : 'follow-btn' }}" id="followBtn" onclick="profilePage.toggleFollow({{ $user->id }})">
                                    <i class="fas {{ isset($isFollowing) && $isFollowing ? 'fa-user-check' : 'fa-user-plus' }} me-1"></i>
                                    <span id="followBtnText">{{ isset($isFollowing) && $isFollowing ? 'Following' : 'Follow' }}</span>
                                </button>
                            </div>
                        @endif
                    </div>

                    <div class="profile-stats">
                        <div class="fw-bold text-muted small mb-2">COMMUNITY STATISTICS</div>
                        <div class="row">
                            <div class="col-md-3">
                                <div>Posts</div>
                                <div class="fw-bold">{{ number_format($stats['posts_count']) }}</div>
                            </div>
                            <div class="col-md-3">
                                <div>Likes</div>
                                <div class="fw-bold">{{ number_format($stats['likes_count']) }}</div>
                            </div>
                            <div class="col-md-3">
                                <div>Followers</div>
                                <div class="fw-bold" id="followersCount">{{ number_format($stats['followers_count']) }}</div>
                            </div>
                            <div class="col-md-3">
                                <div>Following</div>
                                <div class="fw-bold" id="followingCount">{{ number_format($stats['following_count']) }}</div>
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
                @if($isOwnProfile)
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
                @endif
            </ul>

            <!-- FIXED: Added missing opening < for div -->
            <div class="tab-content" id="profileTabsContent">
                <div class="tab-pane fade show active" id="posts" role="tabpanel" aria-labelledby="posts-tab">
                    @if(($tab ?? 'posts') === 'comments')
                        @if(isset($comments) && $comments->count() > 0)
                            @include('components.comments-list', ['comments' => $comments])
                        @else
                            <div class="text-center py-5">
                                <i class="fas fa-comments fa-3x text-muted mb-3"></i>
                                <h5 class="text-muted">No comments yet</h5>
                                <p class="text-muted">
                                    @if($isOwnProfile)
                                        Posts you've commented on will appear here.
                                    @else
                                        {{ $user->username }} hasn't commented on any posts yet.
                                    @endif
                                </p>
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
                                        @if($isOwnProfile)
                                            Create your first post to see it here!
                                        @else
                                            {{ $user->username }} hasn't posted anything yet.
                                        @endif
                                    @elseif($tab === 'likes')
                                        @if(!$isOwnProfile)
                                            Liked posts are private.
                                        @else
                                            Posts you like will appear here.
                                        @endif
                                    @elseif($tab === 'saved')
                                        @if(!$isOwnProfile)
                                            Saved posts are private.
                                        @else
                                            Posts you save will appear here.
                                        @endif
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

            <!-- Avatar Viewer Modal -->
            <div class="custom-modal" id="avatarViewerModal" style="z-index: 1060;">
                <div class="custom-modal-dialog" style="max-width: 90%; max-height: 90%;">
                    <div class="custom-modal-content" style="background: rgba(0, 0, 0, 0.9); border: none; border-radius: 10px;">
                        <div class="custom-modal-header" style="border-bottom: 1px solid rgba(255, 255, 255, 0.2); background: rgba(0, 0, 0, 0.8);">
                            <h5 class="custom-modal-title" style="color: white;">
                                <i class="fas fa-user-circle me-2"></i>{{ $user->username }}'s Profile Picture
                            </h5>
                            <button type="button" class="custom-close" onclick="profilePage.closeAvatarModal()" style="color: white; font-size: 1.5rem;">
                                &times;
                            </button>
                        </div>
                        <div class="custom-modal-body text-center" style="padding: 20px;">
                            <img id="fullSizeAvatar" src="" alt="Full Size Avatar" style="max-width: 100%; max-height: 70vh; border-radius: 10px; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.5);" />
                            <div class="mt-3">
                                <small class="text-muted" style="color: rgba(255, 255, 255, 0.7) !important;">
                                    <i class="fas fa-info-circle me-1"></i>
                                    Click outside or press ESC to close
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Edit Profile Modal (Only for own profile) -->
            @if($isOwnProfile)
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
                                                <img src="{{ $user->avatar_url ? asset('storage/' . $user->avatar_url) : asset('assets/images/profile.png') }}" alt="Profile Picture" id="previewAvatar"
                                                    style="width: 100%; height: 100%; object-fit: cover; cursor: pointer;" ondblclick="profilePage.openAvatarModal(this)" title="Double-click to view full size" />
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
            @endif
        </div>

        <!-- Profile Page JavaScript -->
        <script>
            // Set current tab and user info for JavaScript
            window.currentTab = '{{ $tab ?? "posts" }}';
            window.profileUserId = {{ $user->id }};
            window.isOwnProfile = {{ $isOwnProfile ? 'true' : 'false' }};
            window.isFollowing = {{ isset($isFollowing) ? ($isFollowing ? 'true' : 'false') : 'false' }};

            // Create a global object to hold all profile page functions
            window.profilePage = {
                currentTab: '{{ $tab ?? "posts" }}',
                userId: {{ $user->id }},
                isOwnProfile: {{ $isOwnProfile ? 'true' : 'false' }},
                isFollowing: {{ isset($isFollowing) ? ($isFollowing ? 'true' : 'false') : 'false' }},
                isLoading: false,

                // Function to get the correct URL based on whether it's own profile or not
                getProfileUrl: function (endpoint) {
                    if (this.isOwnProfile) {
                        return '/profile/' + endpoint;
                    } else {
                        return '/profile/' + this.userId + '/' + endpoint;
                    }
                },

                // Function to open the avatar viewer modal
                openAvatarModal: function (imgElement) {
                    console.log("Opening avatar modal");
                    const modal = document.getElementById("avatarViewerModal");
                    const fullSizeImg = document.getElementById("fullSizeAvatar");

                    // Set the full-size image source
                    fullSizeImg.src = imgElement.src;

                    // Show the modal
                    modal.style.display = "block";

                    // Add fade-in effect
                    requestAnimationFrame(() => {
                        modal.style.opacity = "1";
                    });
                },

                // Function to close the avatar viewer modal
                closeAvatarModal: function () {
                    console.log("Closing avatar modal");
                    const modal = document.getElementById("avatarViewerModal");

                    // Add fade-out effect
                    modal.style.opacity = "0";

                    // Hide the modal after transition
                    setTimeout(() => {
                        modal.style.display = "none";
                    }, 200);
                },

                // Function to open the edit modal (only for own profile)
                openEditModal: function () {
                    if (!this.isOwnProfile) {
                        this.showNotification('You can only edit your own profile.', 'error');
                        return;
                    }
                    console.log("Opening edit modal");
                    document.getElementById("customEditModal").style.display = "block";
                },

                // Function to close the edit modal
                closeEditModal: function () {
                    console.log("Closing edit modal");
                    const modal = document.getElementById("customEditModal");
                    if (modal) {
                        modal.style.display = "none";
                    }
                },

                // Function to message user
                messageUser: function (userId) {
                    console.log("Messaging user:", userId);
                    this.showNotification('Messaging functionality will be implemented soon!', 'info');
                },

                // Function to switch tabs with AJAX
                switchTab: function (tabId) {
                    if (this.isLoading || this.currentTab === tabId) return;

                    // Check if trying to access private tabs on other users' profiles
                    if (!this.isOwnProfile && (tabId === 'likes' || tabId === 'saved')) {
                        this.showNotification('This section is private.', 'error');
                        return;
                    }

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
                    const url = this.getProfileUrl('switch-tab');
                    fetch(url, {
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

                // Function to preview the selected image (only for own profile)
                previewImage: function (input) {
                    if (!this.isOwnProfile) return;

                    console.log("Previewing image");
                    if (input.files && input.files[0]) {
                        var reader = new FileReader();
                        reader.onload = function (e) {
                            document.getElementById("previewAvatar").src = e.target.result;
                        };
                        reader.readAsDataURL(input.files[0]);
                    }
                },

                // Function to toggle follow/unfollow
                toggleFollow: function (userId) {
                    if (this.isOwnProfile) {
                        this.showNotification('You cannot follow yourself.', 'error');
                        return;
                    }

                    const followBtn = document.getElementById('followBtn');
                    const followBtnText = document.getElementById('followBtnText');
                    const followersCount = document.getElementById('followersCount');
                    const icon = followBtn.querySelector('i');

                    // Disable button during request
                    followBtn.disabled = true;

                    fetch(`/profile/${userId}/follow`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                this.isFollowing = data.following;

                                // Update button
                                if (data.following) {
                                    followBtn.className = 'follow-btn outline';
                                    followBtnText.textContent = 'Following';
                                    icon.className = 'fas fa-user-check me-1';
                                } else {
                                    followBtn.className = 'follow-btn';
                                    followBtnText.textContent = 'Follow';
                                    icon.className = 'fas fa-user-plus me-1';
                                }

                                // Update followers count
                                followersCount.textContent = new Intl.NumberFormat().format(data.followers_count);

                                this.showNotification(data.message, 'success');
                            } else {
                                this.showNotification(data.message, 'error');
                            }
                        })
                        .catch(error => {
                            console.error('Error toggling follow:', error);
                            this.showNotification('An error occurred. Please try again.', 'error');
                        })
                        .finally(() => {
                            followBtn.disabled = false;
                        });
                },

                // Function to show notification
                showNotification: function (message, type = 'success') {
                    console.log("Showing notification:", message);
                    var notificationArea = document.getElementById("notificationArea");
                    notificationArea.innerHTML = "";

                    const alertClass = type === 'success' ? 'alert-success' : (type === 'error' ? 'alert-danger' : 'alert-info');
                    const icon = type === 'success' ? 'fa-check-circle' : (type === 'error' ? 'fa-exclamation-circle' : 'fa-info-circle');

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
                    console.log("Initializing profile page for user:", this.userId, "Own profile:", this.isOwnProfile);

                    // Close modals when clicking outside of them
                    window.addEventListener("click", function (event) {
                        const editModal = document.getElementById("customEditModal");
                        const avatarModal = document.getElementById("avatarViewerModal");

                        if (editModal && event.target === editModal) {
                            profilePage.closeEditModal();
                        }

                        if (event.target === avatarModal) {
                            profilePage.closeAvatarModal();
                        }
                    });

                    // Close avatar modal with ESC key
                    document.addEventListener("keydown", function (event) {
                        if (event.key === "Escape") {
                            const avatarModal = document.getElementById("avatarViewerModal");
                            if (avatarModal && avatarModal.style.display === "block") {
                                profilePage.closeAvatarModal();
                            }
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

                // Add CSS styles for the avatar modal and profile interactions
                const style = document.createElement('style');
                style.textContent = `
                               #avatarViewerModal {
                                   transition: opacity 0.2s ease-in-out;
                                   opacity: 0;
                               }

                               #avatarViewerModal.show {
                                   opacity: 1;
                               }

                               .profile-avatar img:hover {
                                   transform: scale(1.05);
                                   transition: transform 0.2s ease-in-out;
                               }

                               #previewAvatar:hover {
                                   transform: scale(1.05);
                                   transition: transform 0.2s ease-in-out;
                               }

                               .nav-tabs .nav-link:disabled {
                                   opacity: 0.5;
                                   cursor: not-allowed;
                               }

                               /* Additional CSS to prevent layout issues */
                               .profile-container {
                                   max-width: 100%;
                                   overflow-x: hidden;
                               }

                               .custom-modal {
                                   position: fixed;
                                   top: 0;
                                   left: 0;
                                   width: 100%;
                                   height: 100%;
                                   background: rgba(0, 0, 0, 0.5);
                                   display: none;
                                   z-index: 1050;
                               }

                               .custom-modal-dialog {
                                   position: relative;
                                   margin: 1.75rem auto;
                                   max-width: 500px;
                                   pointer-events: none;
                               }

                               .custom-modal-content {
                                   position: relative;
                                   display: flex;
                                   flex-direction: column;
                                   pointer-events: auto;
                                   background: #fff;
                                   border: 1px solid rgba(0, 0, 0, 0.2);
                                   border-radius: 0.3rem;
                                   outline: 0;
                               }

                               .custom-modal-header {
                                   display: flex;
                                   align-items: center;
                                   justify-content: space-between;
                                   padding: 1rem 1rem;
                                   border-bottom: 1px solid #dee2e6;
                                   border-top-left-radius: calc(0.3rem - 1px);
                                   border-top-right-radius: calc(0.3rem - 1px);
                               }

                               .custom-modal-title {
                                   margin-bottom: 0;
                                   line-height: 1.5;
                               }

                               .custom-close {
                                   background: none;
                                   border: 0;
                                   font-size: 1.5rem;
                                   font-weight: 700;
                                   line-height: 1;
                                   color: #000;
                                   text-shadow: 0 1px 0 #fff;
                                   opacity: 0.5;
                                   cursor: pointer;
                               }

                               .custom-close:hover {
                                   opacity: 0.75;
                               }

                               .custom-modal-body {
                                   position: relative;
                                   flex: 1 1 auto;
                                   padding: 1rem;
                               }

                               .custom-modal-footer {
                                   display: flex;
                                   align-items: center;
                                   justify-content: flex-end;
                                   padding: 0.75rem;
                                   border-top: 1px solid #dee2e6;
                                   border-bottom-right-radius: calc(0.3rem - 1px);
                                   border-bottom-left-radius: calc(0.3rem - 1px);
                               }

                               .btn-secondary {
                                   color: #6c757d;
                                   background-color: transparent;
                                   border: 1px solid #6c757d;
                                   padding: 0.375rem 0.75rem;
                                   font-size: 0.875rem;
                                   border-radius: 0.25rem;
                                   cursor: pointer;
                                   text-decoration: none;
                                   display: inline-block;
                                   font-weight: 400;
                                   line-height: 1.5;
                                   text-align: center;
                                   vertical-align: middle;
                                   user-select: none;
                               }

                               .btn-secondary:hover {
                                   color: #fff;
                                   background-color: #6c757d;
                                   border-color: #6c757d;
                               }

                               .btn-danger {
                                   color: #fff;
                                   background-color: #dc3545;
                                   border: 1px solid #dc3545;
                                   padding: 0.375rem 0.75rem;
                                   font-size: 0.875rem;
                                   border-radius: 0.25rem;
                                   cursor: pointer;
                                   text-decoration: none;
                                   display: inline-block;
                                   font-weight: 400;
                                   line-height: 1.5;
                                   text-align: center;
                                   vertical-align: middle;
                                   user-select: none;
                               }

                               .btn-danger:hover {
                                   background-color: #c82333;
                                   border-color: #bd2130;
                               }

                               @media (max-width: 768px) {
                                   .custom-modal-dialog {
                                       margin: 0.5rem;
                                       max-width: calc(100% - 1rem);
                                   }
                               }
                           `;
                document.head.appendChild(style);

                console.log('✅ Profile Page initialized');
            });
        </script>
    </div>
@endsection