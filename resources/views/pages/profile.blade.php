<div class="alert alert-info">
    <strong>Debug:</strong> Showing profile for User ID: {{ $user->id }} ({{ $user->username }})
</div>

@extends('index')

@section('title', 'Profile - PublicForum')

@section('content')
    <div class="container">
        <div class="notification-area" id="notificationArea">

        </div>

        <div class="profile-container">

            <div class="profile-header">
                <div class="profile-banner"></div>
                <div class="profile-avatar">
                    <div class="profile-avatar-container">
                        <img src="{{ asset('storage/' . $user->avatar_url) }}   " alt="Avatar" id="profileAvatar" />
                    </div>
                </div>
                <div class="profile-info">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 id="profileName">{{ $user->username }}</h3>
                            <div class="text-muted" id="profileBio">{{ $user->bio }}</div>
                            <div class="text-muted small">Account created {{ $user->created_at }}</div>
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
                                <div class="fw-bold">127</div>
                            </div>
                            <div class="col-md-4">
                                <div>Comments</div>
                                <div class="fw-bold">943</div>
                            </div>
                            <div class="col-md-4">
                                <div>Followers</div>
                                <div class="fw-bold">316</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>


            <ul class="nav nav-tabs" id="profileTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="posts-tab" onclick="profilePage.switchTab('posts')" type="button"
                        role="tab">
                        Posts
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="comments-tab" onclick="profilePage.switchTab('comments')" type="button"
                        role="tab">
                        Comments
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="likes-tab" onclick="profilePage.switchTab('likes')" type="button"
                        role="tab">
                        Likes
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="saved-tab" onclick="profilePage.switchTab('saved')" type="button"
                        role="tab">
                        Saved
                    </button>
                </li>
            </ul>


            {{-- Replace the tab content section --}}
            <div class="tab-content" id="profileTabsContent">
                <div class="tab-pane fade show active" id="posts" role="tabpanel" aria-labelledby="posts-tab">
                    @if ($posts->count() > 0)
                        @foreach ($posts as $post)
                            <div class="social-post" data-post-id="{{ $post->id }}">
                                <div class="post-header">
                                    <img src="{{ $post->user->avatar_url ? asset('storage/' . $post->user->avatar_url) : asset('assets/images/profile.png') }}"
                                        alt="Profile" class="post-avatar" />
                                    <div class="post-info">
                                        <div class="post-author">
                                            {{ $post->user->username }}
                                            <span class="post-date">· {{ $post->created_at->diffForHumans() }}</span>
                                        </div>
                                        <div class="post-meta">{{ $post->user->display_name ?? 'User' }}</div>
                                    </div>
                                </div>

                                @if ($post->content)
                                    <div class="post-content">
                                        {{ $post->content }}
                                    </div>
                                @endif

                                <div class="post-actions">
                                    <button class="action-btn like-btn {{ $post->is_liked ? 'liked' : '' }}"
                                        data-post-id="{{ $post->id }}">
                                        <i class="{{ $post->is_liked ? 'fas' : 'far' }} fa-heart"></i>
                                        <span>{{ $post->like_count ?? 0 }}</span>
                                    </button>
                                    <button class="action-btn comment-btn" data-post-id="{{ $post->id }}">
                                        <i class="far fa-comment"></i>
                                        <span>{{ $post->comment_count ?? 0 }}</span>
                                    </button>
                                    <button class="action-btn save-btn {{ $post->is_saved ? 'saved' : '' }}"
                                        data-post-id="{{ $post->id }}">
                                        <i class="{{ $post->is_saved ? 'fas' : 'far' }} fa-bookmark"></i>
                                    </button>
                                </div>
                            </div>
                        @endforeach
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
                                @elseif($tab === 'comments')
                                    Posts you've commented on will appear here.
                                @endif
                            </p>
                        </div>
                    @endif
                </div>
            </div>


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
                            <form id="editProfileForm" action="{{ route('editProfile') }}" enctype="multipart/form-data"
                                method="post">
                                @csrf
                                @method('PUT')
                                <div class="text-center mb-4">
                                    <label class="form-label">Profile Picture</label>
                                    <div class="position-relative mx-auto" style="width: 120px; height: 120px">
                                        <div
                                            style="
                    width: 120px;
                    height: 120px;
                    border-radius: 50%;
                    overflow: hidden;
                    border: 3px solid #dc3545;
                  ">
                                            <img src="{{ asset('storage/' . $user->avatar_url) }}" alt="Profile Picture"
                                                id="previewAvatar" style="width: 100%; height: 100%; object-fit: cover" />
                                        </div>
                                        <div class="position-absolute bottom-0 end-0 bg-danger rounded-circle d-flex justify-content-center align-items-center"
                                            style="
                    width: 36px;
                    height: 36px;
                    cursor: pointer;
                    border: 2px solid #fff;
                  "
                                            onclick="document.getElementById('avatarUpload').click()">
                                            <i class="fas fa-camera text-white" style="font-size: 16px"></i>
                                        </div>
                                        <input type="file" id="avatarUpload" accept="image/*" style="display: none"
                                            onchange="profilePage.previewImage(this)" name="avatar_url" />
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="displayName" class="form-label">Display Name</label>
                                    <input type="text" class="form-control" id="displayName"
                                        value="{{ $user->username }}" name="username" required />
                                </div>

                                <div class="mb-3">
                                    <label for="bioMe" class="form-label">Bio</label>
                                    <textarea class="form-control" id="bioMe" rows="4"name="bio">
{{ $user->bio }}</textarea>
                                </div>
                                <div class="custom-modal-footer">
                                    <button type="button" class="btn-secondary mx-1"
                                        onclick="profilePage.closeEditModal()">
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

            <script>
                // Video player functions
                function playVideo(videoId) {
                    const video = document.getElementById(videoId);
                    const placeholder = video.parentNode.querySelector(".video-placeholder");

                    if (video && placeholder) {
                        video.style.display = "block";
                        placeholder.style.display = "none";
                        video.play();
                    }
                }

                // Create a global object to hold all profile page functions
                window.profilePage = {
                    // Function to open the edit modal
                    openEditModal: function() {
                        console.log("Opening edit modal");
                        document.getElementById("customEditModal").style.display = "block";
                    },

                    // Function to close the edit modal
                    closeEditModal: function() {
                        console.log("Closing edit modal");
                        document.getElementById("customEditModal").style.display = "none";
                    },

                    // Function to switch tabs
                    switchTab: function(tabId) {
                        console.log("Switching to tab:", tabId);

                        // Remove active class from all tab buttons
                        var tabButtons = document.querySelectorAll(".nav-tabs .nav-link");
                        tabButtons.forEach(function(button) {
                            button.classList.remove("active");
                        });

                        // Add active class to the clicked tab button
                        document.getElementById(tabId + "-tab").classList.add("active");

                        // Hide all tab panes
                        var tabPanes = document.querySelectorAll(".tab-pane");
                        tabPanes.forEach(function(pane) {
                            pane.classList.remove("show");
                            pane.classList.remove("active");
                        });

                        // Show the selected tab pane
                        var selectedPane = document.getElementById(tabId);
                        selectedPane.classList.add("fade");
                        selectedPane.classList.add("show");
                        selectedPane.classList.add("active");

                        // After switching tabs, make posts clickable again
                        if (typeof window.makePostsClickable === 'function') {
                            setTimeout(function() {
                                window.makePostsClickable();
                            }, 100);
                        }
                    },

                    // Function to preview the selected image
                    previewImage: function(input) {
                        console.log("Previewing image");
                        if (input.files && input.files[0]) {
                            var reader = new FileReader();

                            reader.onload = function(e) {
                                document.getElementById("previewAvatar").src = e.target.result;
                            };

                            reader.readAsDataURL(input.files[0]);
                        }
                    },

                    // Function to save profile changes
                    saveProfile: function() {
                        console.log("Saving profile");

                        // Get form values
                        var displayName = document.getElementById("displayName").value;
                        var bioMe = document.getElementById("bioMe").value;

                        // Update profile info
                        document.getElementById("profileName").textContent = displayName;
                        document.getElementById("profileBio").textContent = bioMe;

                        // Update avatar if changed
                        var previewAvatar = document.getElementById("previewAvatar");
                        if (previewAvatar.src !== "../assets/images/post.jpg") {
                            document.getElementById("profileAvatar").src = previewAvatar.src;
                        }

                        // Close modal
                        this.closeEditModal();

                        // Show notification
                        this.showNotification();
                    },

                    // Function to show notification
                    showNotification: function() {
                        console.log("Showing notification");
                        var notificationArea = document.getElementById("notificationArea");
                        notificationArea.innerHTML = "";

                        var alertHTML = `
        <div class="alert alert-success">
          <i class="fas fa-check-circle me-2"></i>
          Profile updated successfully!
          <button type="button" class="btn-close" onclick="this.parentNode.style.display='none'"></button>
        </div>
      `;

                        notificationArea.innerHTML = alertHTML;

                        // Auto dismiss after 5 seconds
                        setTimeout(function() {
                            var alert = notificationArea.querySelector(".alert");
                            if (alert) {
                                alert.style.display = "none";
                            }
                        }, 5000);
                    },

                    // Initialize the profile page
                    init: function() {
                        console.log("Initializing profile page");

                        // Close modal when clicking outside of it
                        window.addEventListener("click", function(event) {
                            if (event.target === document.getElementById("customEditModal")) {
                                profilePage.closeEditModal();
                            }
                        });

                        // Make posts clickable
                        if (typeof window.makePostsClickable === 'function') {
                            window.makePostsClickable();
                        } else {
                            console.log("makePostsClickable function not found, setting up retry...");
                            let attempts = 0;
                            const checkInterval = setInterval(function() {
                                attempts++;
                                if (typeof window.makePostsClickable === 'function') {
                                    window.makePostsClickable();
                                    clearInterval(checkInterval);
                                    console.log("Successfully made posts clickable after retry");
                                } else if (attempts >= 10) {
                                    console.error("Failed to make posts clickable after 10 attempts");
                                    clearInterval(checkInterval);

                                    // Fallback direct implementation
                                    profilePage.applyFallbackClickHandlers();
                                }
                            }, 300);
                        }

                        console.log("Profile page initialized");
                    },

                    // Fallback implementation if makePostsClickable is not available
                    applyFallbackClickHandlers: function() {
                        console.log("Applying fallback click handlers");
                        document.querySelectorAll('.social-post').forEach((post, index) => {
                            if (!post.closest('.reposted-content')) {
                                if (!post.dataset.postId) {
                                    post.dataset.postId = index + 1;
                                }

                                post.onclick = function(event) {
                                    if (!event.target.closest('.post-actions') &&
                                        !event.target.closest('.video-placeholder') &&
                                        !event.target.closest('video') &&
                                        event.target.tagName !== 'A' &&
                                        event.target.tagName !== 'BUTTON') {
                                        console.log("Direct click handler: navigating to post " + this.dataset
                                            .postId);
                                        window.loadPage('comment', this.dataset.postId);
                                        event.preventDefault();
                                        event.stopPropagation();
                                    }
                                };

                                post.style.cursor = 'pointer';
                            }
                        });
                    }
                };

                // Document ready handler
                document.addEventListener("DOMContentLoaded", function() {
                    // Load the first frame of the video to use as thumbnail
                    const thumbnailVideos = document.querySelectorAll(
                        ".video-thumbnail-source"
                    );
                    thumbnailVideos.forEach((video) => {
                        // Load just enough of the video to show the first frame
                        video.addEventListener("loadeddata", function() {
                            // Pause immediately to just show the first frame
                            this.currentTime = 0.1; // Small offset to ensure we get a frame
                            this.pause();
                        });

                        // Make sure it's muted
                        video.muted = true;
                        video.preload = "metadata";
                        // Start loading
                        video.load();
                    });

                    // Handle video end event to show placeholder again
                    const videos = document.querySelectorAll(".video-element");
                    videos.forEach((video) => {
                        video.addEventListener("ended", function() {
                            this.style.display = "none";
                            const placeholder =
                                this.parentNode.querySelector(".video-placeholder");
                            if (placeholder) {
                                placeholder.style.display = "block";
                            }
                        });
                    });

                    // Handle interaction functionality for action buttons
                    function handleInteraction(button, type) {
                        if (type === "like" || type === "save") {
                            button.classList.toggle("active");

                            // Update icon if needed
                            const icon = button.querySelector("i");
                            if (type === "like") {
                                if (button.classList.contains("active")) {
                                    icon.className = "fas fa-heart";
                                    // Optionally increment counter
                                    const counter = button.querySelector("span");
                                    if (counter) {
                                        counter.textContent = parseInt(counter.textContent) + 1;
                                    }
                                } else {
                                    icon.className = "far fa-heart";
                                    // Optionally decrement counter
                                    const counter = button.querySelector("span");
                                    if (counter) {
                                        counter.textContent = parseInt(counter.textContent) - 1;
                                    }
                                }
                            } else if (type === "save") {
                                if (button.classList.contains("active")) {
                                    icon.className = "fas fa-bookmark";
                                } else {
                                    icon.className = "far fa-bookmark";
                                }
                            }
                        }

                        console.log(`${type} button clicked`);
                    }

                    // Attach event listeners to interaction buttons
                    const likeButtons = document.querySelectorAll(".like-btn");
                    likeButtons.forEach((button) => {
                        button.addEventListener("click", function() {
                            handleInteraction(this, "like");
                        });
                    });

                    // Comment buttons
                    const commentButtons = document.querySelectorAll(".comment-btn");
                    commentButtons.forEach((button) => {
                        button.addEventListener("click", function() {
                            handleInteraction(this, "comment");
                        });
                    });

                    // Repost buttons
                    const repostButtons = document.querySelectorAll(".repost-btn");
                    repostButtons.forEach((button) => {
                        button.addEventListener("click", function() {
                            handleInteraction(this, "repost");
                        });
                    });

                    // Share buttons
                    const shareButtons = document.querySelectorAll(".share-btn");
                    shareButtons.forEach((button) => {
                        button.addEventListener("click", function() {
                            handleInteraction(this, "share");
                        });
                    });

                    // Save buttons
                    const saveButtons = document.querySelectorAll(".save-btn");
                    saveButtons.forEach((button) => {
                        button.addEventListener("click", function() {
                            handleInteraction(this, "save");
                        });
                    });

                    // Initialize the profile page
                    profilePage.init();
                });
            </script>
        </div>
    </div>
@endsection
