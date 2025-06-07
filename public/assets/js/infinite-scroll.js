/**
 * Infinite Scroll Module for Posts
 * Handles loading more posts automatically and manually
 */

// Wait for DOM and ensure config is available
document.addEventListener("DOMContentLoaded", function () {
    console.log("🚀 Starting infinite scroll...");

    // Check if pagination config exists
    if (!window.paginationConfig) {
        console.error("❌ Pagination config not found!");
        return;
    }

    let currentPage = window.paginationConfig.currentPage;
    let hasMore = window.paginationConfig.hasMore;
    let loading = false;

    const postsContainer = document.getElementById("posts-container");
    const loadingIndicator = document.getElementById("loading-indicator");

    if (!postsContainer) {
        console.error("❌ Posts container not found!");
        return;
    }

    console.log("✅ Elements found");
    console.log("📄 Current page:", currentPage);
    console.log("🔄 Has more:", hasMore);

    // Create load more button
    const loadMoreBtn = document.createElement("button");
    loadMoreBtn.className = "btn btn-primary btn-lg d-block mx-auto my-4";
    loadMoreBtn.innerHTML =
        "📥 Load More Posts (Page " + (currentPage + 1) + ")";
    loadMoreBtn.style.display = hasMore ? "block" : "none";

    // Insert button
    const container = postsContainer.parentNode;
    container.insertBefore(loadMoreBtn, loadingIndicator);

    async function loadMorePosts() {
        if (loading || !hasMore) {
            console.log("⏸️ Load blocked:", {
                loading,
                hasMore,
            });
            return;
        }

        loading = true;
        const nextPage = currentPage + 1;

        console.log("📡 Loading page:", nextPage);

        // Update UI
        loadMoreBtn.innerHTML = "⏳ Loading page " + nextPage + "...";
        loadMoreBtn.disabled = true;
        loadingIndicator.style.display = "block";

        try {
            const url = `${window.paginationConfig.loadUrl}?page=${nextPage}`;
            console.log("📡 Fetching:", url);

            const response = await fetch(url, {
                method: "GET",
                headers: {
                    "X-Requested-With": "XMLHttpRequest",
                    Accept: "application/json",
                    "Content-Type": "application/json",
                },
            });

            console.log("📡 Response:", response.status, response.statusText);

            if (!response.ok) {
                throw new Error(
                    `HTTP ${response.status}: ${response.statusText}`
                );
            }

            const contentType = response.headers.get("content-type");
            console.log("📄 Content-Type:", contentType);

            if (!contentType || !contentType.includes("application/json")) {
                throw new Error("Expected JSON response, got: " + contentType);
            }

            const data = await response.json();
            console.log("📦 Data received:", data);

            if (data.success && data.posts) {
                // Parse and append new posts
                const tempDiv = document.createElement("div");
                tempDiv.innerHTML = data.posts;

                const newPosts = tempDiv.querySelectorAll(".social-post");
                console.log("➕ New posts found:", newPosts.length);

                if (newPosts.length > 0) {
                    newPosts.forEach((post, index) => {
                        post.style.opacity = "0";
                        post.style.transform = "translateY(20px)";
                        post.style.transition = "all 0.3s ease";

                        postsContainer.appendChild(post);

                        setTimeout(() => {
                            post.style.opacity = "1";
                            post.style.transform = "translateY(0)";
                        }, index * 100);
                    });

                    // Update state
                    currentPage = data.currentPage;
                    hasMore = data.hasMore;

                    console.log("✅ Posts added successfully");
                    console.log("📄 New current page:", currentPage);
                    console.log("🔄 Still has more:", hasMore);

                    // Update button
                    if (hasMore) {
                        loadMoreBtn.innerHTML =
                            "📥 Load More Posts (Page " +
                            (currentPage + 1) +
                            ")";
                        loadMoreBtn.disabled = false;
                    } else {
                        loadMoreBtn.innerHTML = "🎉 No more posts to load!";
                        loadMoreBtn.disabled = true;
                        document.getElementById("end-of-posts").style.display =
                            "block";
                    }
                } else {
                    console.log("⚠️ No posts in response");
                    hasMore = false;
                    loadMoreBtn.innerHTML = "🎉 No more posts!";
                    loadMoreBtn.disabled = true;
                }
            } else {
                throw new Error("Invalid response: " + JSON.stringify(data));
            }
        } catch (error) {
            console.error("❌ Error loading posts:", error);
            loadMoreBtn.innerHTML =
                "🔄 Error! Click to retry (Page " + nextPage + ")";
            loadMoreBtn.disabled = false;
        } finally {
            loading = false;
            loadingIndicator.style.display = "none";
        }
    }

    // Button click handler
    loadMoreBtn.addEventListener("click", loadMorePosts);

    // Smart scroll handler - loads when 3-5 posts away from bottom
    let scrollTimeout;
    window.addEventListener("scroll", function () {
        if (scrollTimeout) return;

        scrollTimeout = setTimeout(function () {
            const scrollTop = window.pageYOffset;
            const windowHeight = window.innerHeight;
            const documentHeight = document.documentElement.scrollHeight;

            // Calculate average post height
            const posts = document.querySelectorAll(".social-post");
            let averagePostHeight = 400; // Default fallback

            if (posts.length > 0) {
                let totalHeight = 0;
                for (let i = 0; i < Math.min(5, posts.length); i++) {
                    totalHeight += posts[i].offsetHeight;
                }
                averagePostHeight = totalHeight / Math.min(5, posts.length);
            }

            // Load when user is 3-5 posts away from bottom
            const postsAwayFromBottom = 4; // Trigger when 4 posts away
            const triggerDistance = averagePostHeight * postsAwayFromBottom;

            const distanceFromBottom =
                documentHeight - (scrollTop + windowHeight);
            const shouldLoad = distanceFromBottom <= triggerDistance;

            // Debug info (remove in production)
            if (posts.length > 0 && scrollTop > 100) {
                // Only log when actually scrolling
                console.log("📏 Scroll metrics:", {
                    averagePostHeight: Math.round(averagePostHeight),
                    postsAwayFromBottom: postsAwayFromBottom,
                    triggerDistance: Math.round(triggerDistance),
                    distanceFromBottom: Math.round(distanceFromBottom),
                    shouldLoad: shouldLoad,
                    totalPosts: posts.length,
                });
            }

            if (shouldLoad) {
                console.log(
                    "📜 Scroll triggered - " +
                        postsAwayFromBottom +
                        " posts from bottom"
                );
                loadMorePosts();
            }

            scrollTimeout = null;
        }, 150); // Reduced timeout for more responsive feel
    });

    // Global test function
    window.testLoad = function () {
        console.log("🧪 Manual test load");
        loadMorePosts();
    };

    console.log("✅ Infinite scroll initialized!");
    console.log("🧪 Use testLoad() in console to test manually");
});
