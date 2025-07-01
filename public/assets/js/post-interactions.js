// public/assets/js/post-interactions.js

window.PostInteractions = {
    isInitialized: false,
    debugMode: false,

    // Initialize post interactions
    init: function() {
        if (this.isInitialized) {
            this.log('Already initialized, skipping...');
            return;
        }
        
        this.log('🔄 Initializing Post Interactions...');
        this.setupEventListeners();
        this.isInitialized = true;
        this.log('✅ Post Interactions initialized');
    },

    // Reinitialize interactions (for AJAX loaded content)
    reinitialize: function() {
        this.log('🔄 Reinitializing Post Interactions...');
        this.setupEventListeners();
        this.log('✅ Post Interactions reinitialized');
    },

    // Logging helper
    log: function(message, data = null) {
        if (this.debugMode || (window.AppConfig && window.AppConfig.debug)) {
            console.log('[PostInteractions]', message, data || '');
        }
    },

    // Set up event listeners for all post interaction buttons
    setupEventListeners: function() {
        // Remove existing listeners to prevent duplicates
        this.removeEventListeners();

        // Like buttons
        document.querySelectorAll('.like-btn').forEach(button => {
            button.addEventListener('click', this.handleLikeClick.bind(this));
        });

        // Save buttons  
        document.querySelectorAll('.save-btn').forEach(button => {
            button.addEventListener('click', this.handleSaveClick.bind(this));
        });

        // Share buttons
        document.querySelectorAll('.share-btn').forEach(button => {
            button.addEventListener('click', this.handleShareClick.bind(this));
        });

        // Comment buttons (if you want to handle them)
        document.querySelectorAll('.comment-btn').forEach(button => {
            button.addEventListener('click', this.handleCommentClick.bind(this));
        });

        this.log('📎 Event listeners attached to', {
            likes: document.querySelectorAll('.like-btn').length,
            saves: document.querySelectorAll('.save-btn').length,
            shares: document.querySelectorAll('.share-btn').length,
            comments: document.querySelectorAll('.comment-btn').length
        });
    },

    // Remove existing event listeners by cloning elements
    removeEventListeners: function() {
        document.querySelectorAll('.like-btn, .save-btn, .share-btn, .comment-btn').forEach(button => {
            const newButton = button.cloneNode(true);
            button.parentNode.replaceChild(newButton, button);
        });
    },

    // Handle like button clicks
    handleLikeClick: function(e) {
        e.preventDefault();
        e.stopPropagation();

        const button = e.currentTarget;
        const postId = button.dataset.postId;

        if (!postId) {
            this.log('❌ No post ID found');
            return;
        }

        const icon = button.querySelector('i');
        const countSpan = button.querySelector('span');
        const currentCount = parseInt(countSpan.textContent) || 0;
        const isLiked = button.classList.contains('active');

        this.log('❤️ Like clicked for post:', postId, 'Current liked:', isLiked);

        // Prevent multiple clicks
        if (button.disabled) return;
        button.disabled = true;
        button.classList.add('btn-loading');

        // Optimistic UI update
        if (isLiked) {
            this.unlikePost(button, icon, countSpan, currentCount - 1);
        } else {
            this.likePost(button, icon, countSpan, currentCount + 1);
        }

        // Send request to server
        fetch(`/posts/${postId}/like`, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': this.getCsrfToken()
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            return response.json();
        })
        .then(data => {
            this.log('❤️ Like response:', data);
            
            if (data.success) {
                // Update with server response
                if (data.liked) {
                    this.likePost(button, icon, countSpan, data.like_count);
                } else {
                    this.unlikePost(button, icon, countSpan, data.like_count);
                }
                
                if (data.message) {
                    this.showNotification(data.message, 'success');
                }
            } else {
                this.showNotification(data.message || 'Failed to toggle like', 'error');
                // Revert optimistic update
                this.revertLikeState(button, icon, countSpan, currentCount, isLiked);
            }
        })
        .catch(error => {
            this.log('❌ Error toggling like:', error);
            this.showNotification('Failed to toggle like. Please try again.', 'error');
            // Revert optimistic update
            this.revertLikeState(button, icon, countSpan, currentCount, isLiked);
        })
        .finally(() => {
            button.disabled = false;
            button.classList.remove('btn-loading');
        });
    },

    // Handle save button clicks
    handleSaveClick: function(e) {
        e.preventDefault();
        e.stopPropagation();

        const button = e.currentTarget;
        const postId = button.dataset.postId;

        if (!postId) {
            this.log('❌ No post ID found');
            return;
        }

        const icon = button.querySelector('i');
        const isSaved = button.classList.contains('active');

        this.log('🔖 Save clicked for post:', postId, 'Current saved:', isSaved);

        // Prevent multiple clicks
        if (button.disabled) return;
        button.disabled = true;
        button.classList.add('btn-loading');

        // Optimistic UI update
        if (isSaved) {
            this.unsavePost(button, icon);
        } else {
            this.savePost(button, icon);
        }

        // Send request to server
        fetch(`/posts/${postId}/save`, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': this.getCsrfToken()
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            return response.json();
        })
        .then(data => {
            this.log('🔖 Save response:', data);
            
            if (data.success) {
                if (data.saved) {
                    this.savePost(button, icon);
                } else {
                    this.unsavePost(button, icon);
                }
                
                if (data.message) {
                    this.showNotification(data.message, 'success');
                }
            } else {
                this.showNotification(data.message || 'Failed to toggle save', 'error');
                // Revert optimistic update
                this.revertSaveState(button, icon, isSaved);
            }
        })
        .catch(error => {
            this.log('❌ Error toggling save:', error);
            this.showNotification('Failed to toggle save. Please try again.', 'error');
            // Revert optimistic update
            this.revertSaveState(button, icon, isSaved);
        })
        .finally(() => {
            button.disabled = false;
            button.classList.remove('btn-loading');
        });
    },

    // Handle share button clicks
    handleShareClick: function(e) {
        e.preventDefault();
        e.stopPropagation();

        const button = e.currentTarget;
        const postId = button.dataset.postId;

        if (!postId) {
            this.log('❌ No post ID found');
            return;
        }

        const countSpan = button.querySelector('span');
        const currentCount = parseInt(countSpan.textContent) || 0;

        this.log('🔄 Share clicked for post:', postId);

        // Prevent multiple clicks
        if (button.disabled) return;
        button.disabled = true;
        button.classList.add('btn-loading');

        // Optimistic UI update
        countSpan.textContent = currentCount + 1;

        // Send request to server
        fetch(`/posts/${postId}/share`, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': this.getCsrfToken()
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            return response.json();
        })
        .then(data => {
            this.log('🔄 Share response:', data);
            
            if (data.success) {
                countSpan.textContent = data.share_count;
                if (data.message) {
                    this.showNotification(data.message, 'success');
                }
            } else {
                this.showNotification(data.message || 'Failed to share', 'error');
                countSpan.textContent = currentCount;
            }
        })
        .catch(error => {
            this.log('❌ Error sharing post:', error);
            this.showNotification('Failed to share post. Please try again.', 'error');
            countSpan.textContent = currentCount;
        })
        .finally(() => {
            button.disabled = false;
            button.classList.remove('btn-loading');
        });
    },

    // Handle comment button clicks (placeholder)
    handleCommentClick: function(e) {
        e.preventDefault();
        e.stopPropagation();

        const button = e.currentTarget;
        const postId = button.dataset.postId;

        this.log('💬 Comment clicked for post:', postId);
        
        // You can implement comment functionality here
        // For now, just show a message
        
        window.location.href = `/comment?id=${postId}`;
    },

    // UI Helper functions
    likePost: function(button, icon, countSpan, count) {
        button.classList.add('active');
        button.style.color = '#FA2C8B';
        icon.classList.remove('far');
        icon.classList.add('fas');
        if (count !== undefined) {
            countSpan.textContent = count;
        }
    },

    unlikePost: function(button, icon, countSpan, count) {
        button.classList.remove('active');
        button.style.color = '';
        icon.classList.remove('fas');
        icon.classList.add('far');
        if (count !== undefined) {
            countSpan.textContent = Math.max(0, count); // Ensure count doesn't go negative
        }
    },

    savePost: function(button, icon) {
        button.classList.add('active');
        button.style.color = '#1DA1F2';
        icon.classList.remove('far');
        icon.classList.add('fas');
    },

    unsavePost: function(button, icon) {
        button.classList.remove('active');
        button.style.color = '';
        icon.classList.remove('fas');
        icon.classList.add('far');
    },

    revertLikeState: function(button, icon, countSpan, originalCount, wasLiked) {
        if (wasLiked) {
            this.likePost(button, icon, countSpan, originalCount);
        } else {
            this.unlikePost(button, icon, countSpan, originalCount);
        }
    },

    revertSaveState: function(button, icon, wasSaved) {
        if (wasSaved) {
            this.savePost(button, icon);
        } else {
            this.unsavePost(button, icon);
        }
    },

    // Utility functions
    getCsrfToken: function() {
        const token = document.querySelector('meta[name="csrf-token"]');
        if (!token) {
            this.log('❌ CSRF token not found');
            return '';
        }
        return token.getAttribute('content');
    },

    showNotification: function(message, type = 'success') {
        this.log('📢 Notification:', message, type);
        
        // Try to find existing notification area
        let notificationArea = document.getElementById('notificationArea');
        
        // If no notification area exists, create a temporary one
        if (!notificationArea) {
            notificationArea = document.createElement('div');
            notificationArea.id = 'notificationArea';
            notificationArea.className = 'notification-area';
            notificationArea.style.cssText = `
                position: fixed;
                top: 80px;
                right: 20px;
                z-index: 9999;
                max-width: 400px;
            `;
            document.body.appendChild(notificationArea);
        }

        // Clear existing notifications
        notificationArea.innerHTML = '';

        // Determine alert class and icon
        let alertClass, icon;
        switch(type) {
            case 'success':
                alertClass = 'alert-success';
                icon = 'fa-check-circle';
                break;
            case 'error':
                alertClass = 'alert-danger';
                icon = 'fa-exclamation-circle';
                break;
            case 'info':
                alertClass = 'alert-info';
                icon = 'fa-info-circle';
                break;
            default:
                alertClass = 'alert-primary';
                icon = 'fa-bell';
        }

        // Create notification HTML
        const alertHTML = `
            <div class="alert ${alertClass} alert-dismissible fade show" role="alert" style="min-width: 300px; animation: slideIn 0.3s ease;">
                <i class="fas ${icon} me-2"></i>
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `;

        notificationArea.innerHTML = alertHTML;

        // Auto dismiss after 4 seconds
        setTimeout(() => {
            const alert = notificationArea.querySelector('.alert');
            if (alert && alert.classList.contains('show')) {
                alert.classList.remove('show');
                setTimeout(() => {
                    if (alert.parentNode) {
                        alert.remove();
                    }
                }, 150);
            }
        }, 4000);
    }
};

// Auto-initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    console.log('🚀 DOM ready, initializing PostInteractions...');
    PostInteractions.init();
});

// Reinitialize on AJAX content load
document.addEventListener('ajaxContentLoaded', function() {
    console.log('🔄 AJAX content loaded, reinitializing PostInteractions...');
    PostInteractions.reinitialize();
});

// Also reinitialize when new content is added to posts container
const observer = new MutationObserver(function(mutations) {
    mutations.forEach(function(mutation) {
        if (mutation.type === 'childList' && mutation.addedNodes.length > 0) {
            // Check if new nodes contain post elements
            const hasPostElements = Array.from(mutation.addedNodes).some(node => {
                return node.nodeType === 1 && (
                    node.classList?.contains('social-post') ||
                    node.querySelector?.('.social-post')
                );
            });
            
            if (hasPostElements) {
                console.log('🔄 New post elements detected, reinitializing...');
                setTimeout(() => PostInteractions.reinitialize(), 100);
            }
        }
    });
});

// Start observing the posts container when it exists
document.addEventListener('DOMContentLoaded', function() {
    const postsContainer = document.getElementById('posts-container');
    if (postsContainer) {
        observer.observe(postsContainer, {
            childList: true,
            subtree: true
        });
        console.log('📋 Started observing posts container for changes');
    }
});

// Add CSS for slide-in animation
const style = document.createElement('style');
style.textContent = `
    @keyframes slideIn {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
`;
document.head.appendChild(style);