(function () {
    console.log("Content script loaded");

    let currentPlayingVideo = null; // Track currently playing video

    // Force scroll to top on page load/refresh
    function forceScrollToTop() {
        // Disable browser scroll restoration
        if ("scrollRestoration" in history) {
            history.scrollRestoration = "manual";
        }

        // Force immediate scroll to top
        window.scrollTo(0, 0);
        document.documentElement.scrollTop = 0;
        document.body.scrollTop = 0;

        console.log("🔝 Forced scroll to top");
    }

    // Call immediately when script loads
    forceScrollToTop();

    // Also force on window load
    window.addEventListener("load", function () {
        setTimeout(forceScrollToTop, 50);
    });

    // Early scroll reset - before DOM is ready
    if (document.readyState === "loading") {
        forceScrollToTop();
    }

    makePostsClickable();

    document.addEventListener("DOMContentLoaded", function () {
        console.log("DOM loaded - applying clickable behavior to posts");

        // Force scroll to top again after DOM is loaded
        forceScrollToTop();

        makePostsClickable();
        setupVideoControls();

        // Delay the scroll reset slightly to ensure everything is loaded
        setTimeout(() => {
            forceScrollToTop();
            makePostsClickable();
        }, 100);

        observePageContentChanges();
    });

    // Setup video pause controls
    function setupVideoControls() {
        // Pause video when page becomes hidden (tab change, minimize, etc.)
        document.addEventListener("visibilitychange", function () {
            if (document.hidden && currentPlayingVideo) {
                console.log("Page hidden - pausing video");
                currentPlayingVideo.pause();
            }
        });

        // Pause video when window loses focus
        window.addEventListener("blur", function () {
            if (currentPlayingVideo) {
                console.log("Window lost focus - pausing video");
                currentPlayingVideo.pause();
            }
        });

        // Pause video when user navigates away
        window.addEventListener("beforeunload", function () {
            if (currentPlayingVideo) {
                currentPlayingVideo.pause();
            }
        });

        // Setup intersection observer to pause videos that go out of view
        if ("IntersectionObserver" in window) {
            const videoObserver = new IntersectionObserver(
                function (entries) {
                    entries.forEach((entry) => {
                        const video = entry.target;
                        if (!entry.isIntersecting && !video.paused) {
                            console.log("Video out of view - pausing");
                            video.pause();
                        }
                    });
                },
                {
                    threshold: 0.5, // Pause when less than 50% visible
                }
            );

            // Observe all video elements
            document
                .querySelectorAll("video.video-element")
                .forEach((video) => {
                    videoObserver.observe(video);
                });
        }
    }

    // Updated play video function with pause other videos
    function playVideo(videoId) {
        console.log("Playing video:", videoId);

        const video = document.getElementById(videoId);
        const placeholder = video
            ? video.parentElement.querySelector(".video-placeholder")
            : null;

        if (video && placeholder) {
            // Pause any currently playing video first
            pauseAllVideos();

            // Hide placeholder and show video
            placeholder.style.display = "none";
            video.style.display = "block";

            // Set as current playing video
            currentPlayingVideo = video;

            // Play the video
            video
                .play()
                .then(() => {
                    console.log("Video playing successfully");

                    // Add event listeners for this video
                    video.addEventListener("pause", function () {
                        if (currentPlayingVideo === video) {
                            currentPlayingVideo = null;
                        }
                    });

                    video.addEventListener("ended", function () {
                        if (currentPlayingVideo === video) {
                            currentPlayingVideo = null;
                        }
                        // Optionally show placeholder again when video ends
                        placeholder.style.display = "block";
                        video.style.display = "none";
                    });
                })
                .catch((error) => {
                    console.error("Video play error:", error);
                    placeholder.style.display = "block";
                    video.style.display = "none";
                    currentPlayingVideo = null;
                });
        } else {
            console.error("Video or placeholder not found for:", videoId);
        }
    }

    // Function to pause all videos
    function pauseAllVideos() {
        document.querySelectorAll("video.video-element").forEach((video) => {
            if (!video.paused) {
                console.log("Pausing video:", video.id);
                video.pause();

                // Show placeholder again
                const container = video.parentElement;
                const placeholder = container
                    ? container.querySelector(".video-placeholder")
                    : null;
                if (placeholder) {
                    video.style.display = "none";
                    placeholder.style.display = "block";
                }
            }
        });
        currentPlayingVideo = null;
    }

    // Export functions
    window.pauseAllVideos = pauseAllVideos;
    window.getCurrentPlayingVideo = function () {
        return currentPlayingVideo;
    };
})();

function makePostsClickable() {
    console.log("Making posts clickable...");

    const socialPosts = document.querySelectorAll(".social-post");
    console.log(`Found ${socialPosts.length} posts`);

    socialPosts.forEach((post, index) => {
        // Skip reposted content
        if (post.closest(".reposted-content")) {
            return;
        }

        // Set post ID if not already set
        if (!post.dataset.postId) {
            post.dataset.postId = index + 1;
        }

        // Remove existing event listener
        post.removeEventListener("click", postClickHandler);

        // Add new event listener
        post.addEventListener("click", postClickHandler);

        // Make it look clickable
        post.style.cursor = "pointer";

        // Add a class to identify clickable posts
        post.classList.add("clickable-post");

        console.log(`Made post ${post.dataset.postId} clickable`);
    });

    // Setup action buttons
    setupActionButtons();
}

function postClickHandler(event) {
    // Don't navigate if clicking on action buttons, videos, or links
    if (
        event.target.closest(".post-actions") ||
        event.target.closest(".video-placeholder") ||
        event.target.closest(".post-options") ||
        event.target.closest("video") ||
        event.target.tagName === "A" ||
        event.target.tagName === "BUTTON"
    ) {
        console.log("Click was on an action element - ignoring");
        return;
    }

    // Get the post and its ID
    const post = this;
    const postId = post.dataset.postId;
    console.log(`Post ${postId} clicked - navigating to comment page`);

    // Navigate to comment page
    loadPage("comment", postId);

    event.preventDefault();
    event.stopPropagation();
}

function setupActionButtons() {
    // Like buttons
    document.querySelectorAll(".like-btn").forEach((button) => {
        button.addEventListener("click", function (event) {
            // Stop event from bubbling up to post click
            event.stopPropagation();
        });
    });

    // Comment buttons
    document.querySelectorAll(".comment-btn").forEach((button) => {
        button.addEventListener("click", function (event) {
            const post = this.closest(".social-post");
            if (post) {
                const postId = post.dataset.postId;

                // Navigate to comment page
                loadPage("comment", postId);

                // Focus comment box if available
                localStorage.setItem("focusCommentBox", "true");

                event.stopPropagation();
            }
        });
    });

    // Repost buttons
    document.querySelectorAll(".repost-btn").forEach((button) => {
        button.addEventListener("click", function (event) {
            // Stop event from bubbling up to post click
            event.stopPropagation();
        });
    });

    // Share buttons
    document.querySelectorAll(".share-btn").forEach((button) => {
        button.addEventListener("click", function (event) {
            // Stop event from bubbling up to post click
            event.stopPropagation();
        });
    });

    // Save buttons
    document.querySelectorAll(".save-btn").forEach((button) => {
        button.addEventListener("click", function (event) {
            // Stop event from bubbling up to post click
            event.stopPropagation();
        });
    });

    // Post options (if any)
    document.querySelectorAll(".post-options").forEach((button) => {
        button.addEventListener("click", function (event) {
            // Stop event from bubbling up to post click
            event.stopPropagation();
        });
    });
}

function observePageContentChanges() {
    // Watch for changes in the main page content
    const pageContent = document.getElementById("page-content");

    // Create observer
    const observer = new MutationObserver(function (mutations) {
        // When content changes, reapply clickable behavior
        console.log("Page content changed - reapplying clickable behavior");
        setTimeout(makePostsClickable, 200);
    });

    // Start observing
    if (pageContent) {
        observer.observe(pageContent, { childList: true });
        console.log("Now observing page content for changes");
    }
}

// Updated playVideo function that pauses others first
function playVideo(videoId) {
    // Pause all other videos first
    if (typeof window.pauseAllVideos === "function") {
        window.pauseAllVideos();
    }

    console.log("Playing video:", videoId);

    const video = document.getElementById(videoId);
    const placeholder = video
        ? video.parentElement.querySelector(".video-placeholder")
        : null;

    if (video && placeholder) {
        // Hide placeholder and show video
        placeholder.style.display = "none";
        video.style.display = "block";

        // Play the video
        video
            .play()
            .then(() => {
                console.log("Video started playing");
            })
            .catch((error) => {
                console.error("Video play error:", error);
                placeholder.style.display = "block";
                video.style.display = "none";
            });
    } else {
        console.error("Video or placeholder not found for:", videoId);
    }
}

// Export functions to global scope
window.makePostsClickable = makePostsClickable;
window.playVideo = playVideo;
