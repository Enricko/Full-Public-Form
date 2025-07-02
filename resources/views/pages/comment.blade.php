@extends('index')

@section('title', 'Post Details - PublicForum')

@section('content')
<div class="container">
  {{-- Back Button --}}
  <div class="mb-3">
    <a href="javascript:history.back()" class="btn btn-outline-secondary">
      <i class="fas fa-arrow-left"></i> Back
    </a>
  </div>

  {{-- Post Detail --}}
  <div class="social-post detail-view">
    <div class="post-header">
      <img src="{{ $post->user->avatar_url ?? asset('assets/images/profile.png') }}"
        alt="Profile" class="post-avatar" />
      <div class="post-info">
        <div class="post-author">
          <span id="post-author">{{ $post->user->username }}</span>
          <span class="post-date" id="post-date">· {{ $post->created_at->diffForHumans() }}</span>
        </div>
        <div class="post-meta" id="post-meta">{{ $post->user->display_name ?? $post->user->bio ?? 'User' }}</div>
      </div>

      <div class="follow-btn-container ms-auto">
        @if($isAuthenticated)
          <button class="follow-btn">Follow</button>
        @else
          <button class="follow-btn" onclick="showLoginPrompt()">Follow</button>
        @endif
      </div>
    </div>

    {{-- Post Content --}}
    @if($post->content)
    <div class="post-content" id="post-content">
      {!! nl2br(e($post->content)) !!}
    </div>
    @endif

    {{-- Post Hashtags --}}
    @if($post->hashtags && $post->hashtags->count() > 0)
    <div class="post-hashtags mb-2">
      @foreach($post->hashtags as $hashtag)
      <span class="badge bg-light text-primary me-1">
        <i class="fas fa-hashtag small"></i> {{ $hashtag->name }}
      </span>
      @endforeach
    </div>
    @endif

    {{-- Post Media --}}
    @if($post->attachments && $post->attachments->count() > 0)
    <div id="post-media-container">
      @foreach($post->attachments as $index => $attachment)
      @if(str_starts_with($attachment->file_type, 'image/'))
      <div class="post-media single-image">
        <img src="{{ asset('storage/' . $attachment->file_path) }}"
          alt="{{ $attachment->file_name }}" class="post-image" />
      </div>
      @elseif(str_starts_with($attachment->file_type, 'video/'))
      <div class="post-media video-container">
        <video id="detail-video-{{ $index }}" controls class="video-element">
          <source src="{{ asset('storage/' . $attachment->file_path) }}"
            type="{{ $attachment->file_type }}" />
          Your browser does not support the video tag.
        </video>
        <div class="video-placeholder" onclick="playVideo('detail-video-{{ $index }}')">
          <div class="video-thumbnail-wrapper">
            <video class="video-thumbnail-source" muted>
              <source src="{{ asset('storage/' . $attachment->file_path) }}"
                type="{{ $attachment->file_type }}" />
            </video>
            <div class="play-button">
              <i class="fas fa-play"></i>
            </div>
            <div class="video-duration">0:00</div>
          </div>
        </div>
        @if($attachment->file_name)
        <div class="video-title">{{ $attachment->file_name }}</div>
        @endif
      </div>
      @else
      {{-- Other Files --}}
      <div class="post-media file-attachment">
        <div class="file-item">
          <div class="file-icon">
            @if(str_contains($attachment->file_type, 'pdf'))
            <i class="fas fa-file-pdf text-danger"></i>
            @elseif(str_contains($attachment->file_type, 'word'))
            <i class="fas fa-file-word text-primary"></i>
            @elseif(str_contains($attachment->file_type, 'excel'))
            <i class="fas fa-file-excel text-success"></i>
            @else
            <i class="fas fa-file text-muted"></i>
            @endif
          </div>
          <div class="file-info">
            <div class="file-name">{{ $attachment->file_name }}</div>
            <div class="file-details">
              <span class="file-size">{{ number_format($attachment->file_size / 1024, 1) }} KB</span>
            </div>
          </div>
          <div class="file-actions">
            <a href="{{ asset('storage/' . $attachment->file_path) }}" target="_blank"
              class="btn btn-sm btn-outline-secondary">
              <i class="fas fa-eye"></i>
            </a>
          </div>
        </div>
      </div>
      @endif
      @endforeach
    </div>
    @endif

    {{-- Post Stats --}}
    <div class="post-stats">
      <div class="stat-item">
        <i class="far fa-heart"></i> <span id="like-count">{{ $post->likes_count }}</span> Likes
      </div>
      <div class="stat-item">
        <i class="far fa-comment"></i> <span id="comment-count">{{ $post->comments_count }}</span> Comments
      </div>
      <div class="stat-item">
        <i class="fas fa-retweet"></i> <span id="share-count">{{ $post->share_count ?? 0 }}</span> Shares
      </div>
    </div>

    {{-- Post Actions --}}
    <div class="post-actions">
      @if($isAuthenticated)
        <button class="action-btn like-btn {{ $post->is_liked ? 'active' : '' }}"
          data-post-id="{{ $post->id }}"
          style="color: {{ $post->is_liked ? '#FA2C8B' : '' }}">
          <i class="{{ $post->is_liked ? 'fas' : 'far' }} fa-heart"></i>
          <span>Like</span>
        </button>
      @else
        <button class="action-btn like-btn" onclick="showLoginPrompt()">
          <i class="far fa-heart"></i>
          <span>Like</span>
        </button>
      @endif

      <button class="action-btn comment-btn" id="focus-comment-btn">
        <i class="far fa-comment"></i> <span>Comment</span>
      </button>

      @if($isAuthenticated)
        <button class="action-btn share-btn" data-post-id="{{ $post->id }}">
          <i class="far fa-share-square"></i> <span>Share</span>
        </button>
      @else
        <button class="action-btn share-btn" onclick="showLoginPrompt()">
          <i class="far fa-share-square"></i> <span>Share</span>
        </button>
      @endif

      @if($isAuthenticated)
        <button class="action-btn save-btn {{ $post->is_saved ? 'active' : '' }}"
          data-post-id="{{ $post->id }}"
          style="color: {{ $post->is_saved ? '#1DA1F2' : '' }}">
          <i class="{{ $post->is_saved ? 'fas' : 'far' }} fa-bookmark"></i>
          <span>Save</span>
        </button>
      @else
        <button class="action-btn save-btn" onclick="showLoginPrompt()">
          <i class="far fa-bookmark"></i>
          <span>Save</span>
        </button>
      @endif
    </div>
  </div>

  {{-- Comment Form --}}
  @if($isAuthenticated && $currentUser)
  <div class="comment-form">
    <img src="{{ $currentUser->avatar_url ?? asset('assets/images/profile.png') }}"
      alt="Profile" class="comment-avatar" />
    <div class="comment-input-container">
      <form id="comment-form" method="POST" action="{{ route('comments.store') }}">
        @csrf
        <input type="hidden" name="post_id" value="{{ $post->id }}">
        <textarea name="content" class="comment-input"
          placeholder="Write your comment..." required></textarea>
        <div class="comment-actions">
          <div>
            <button type="button" class="emoji-btn"><i class="far fa-smile"></i></button>
            <button type="button" class="attachment-btn"><i class="far fa-image"></i></button>
          </div>
          <button type="submit" class="submit-comment-btn">Post Comment</button>
        </div>
      </form>
    </div>
  </div>
  @else
  {{-- Guest Comment Prompt --}}
  <div class="comment-form guest-comment-prompt">
    <div class="guest-prompt-content">
      <img src="{{ asset('assets/images/profile.png') }}" alt="Profile" class="comment-avatar" />
      <div class="guest-message">
        <h5>Join the conversation!</h5>
        <p>Sign in to share your thoughts and engage with the community.</p>
        <div class="guest-actions">
          <button class="btn btn-danger" onclick="showLoginModal()">Login</button>
          <button class="btn btn-outline-danger" onclick="showRegisterModal()">Sign Up</button>
        </div>
      </div>
    </div>
  </div>
  @endif

  {{-- Comments Section --}}
  <div class="comments-section">
    <div class="comments-header">
      <h3 class="comments-title">Comments</h3>
      <span class="comments-count">{{ $comments->total() }}</span>
    </div>

    @if($comments->count() > 0)
    <div id="comments-container">
      @foreach($comments as $comment)
      <div class="comment-thread">
        <div class="comment-item" data-comment-id="{{ $comment->id }}">
          <div class="comment-avatar">
            <img src="{{ $comment->user->avatar_url ?? asset('assets/images/profile.png') }}"
              alt="{{ $comment->user->username }}" />
          </div>
          <div class="comment-content">
            <div class="comment-header">
              <a href="#" class="comment-author">{{ $comment->user->username }}</a>
              <span class="comment-username">{{ $comment->user->display_name }}</span>
              <span class="comment-timestamp">{{ $comment->created_at->diffForHumans() }}</span>
            </div>
            <div class="comment-text">{{ $comment->content }}</div>
            <div class="comment-actions">
              @if($isAuthenticated)
                <button class="comment-action comment-like-btn {{ $comment->is_liked ? 'liked' : '' }}"
                  data-comment-id="{{ $comment->id }}">
                  <i class="{{ $comment->is_liked ? 'fas' : 'far' }} fa-heart"></i>
                  <span>{{ $comment->like_count }}</span>
                </button>
                <button class="comment-action reply-btn" data-comment-id="{{ $comment->id }}">
                  <i class="far fa-comment"></i> Reply
                </button>
              @else
                <button class="comment-action comment-like-btn-guest" onclick="showLoginPrompt()">
                  <i class="far fa-heart"></i>
                  <span>{{ $comment->like_count }}</span>
                </button>
                <button class="comment-action reply-btn-guest" onclick="showLoginPrompt()">
                  <i class="far fa-comment"></i> Reply
                </button>
              @endif
            </div>

            {{-- Reply Form (only for authenticated users) --}}
            @if($isAuthenticated)
            <div class="reply-form" id="reply-form-{{ $comment->id }}" style="display: none;">
              <form class="reply-form-inner" data-parent-id="{{ $comment->id }}">
                @csrf
                <input type="hidden" name="post_id" value="{{ $post->id }}">
                <input type="hidden" name="parent_comment_id" value="{{ $comment->id }}">
                <div class="d-flex gap-2">
                  <img src="{{ $currentUser->avatar_url ?? asset('assets/images/profile.png') }}"
                    alt="Profile" class="reply-avatar" />
                  <div class="flex-grow-1">
                    <textarea name="content" class="reply-input"
                      placeholder="Write a reply..." required></textarea>
                    <div class="reply-actions mt-2">
                      <button type="button" class="btn btn-sm btn-secondary cancel-reply">Cancel</button>
                      <button type="submit" class="btn btn-sm btn-primary">Reply</button>
                    </div>
                  </div>
                </div>
              </form>
            </div>
            @endif

            {{-- Replies --}}
            @if($comment->replies && $comment->replies->count() > 0)
            <div class="comment-replies">
              @foreach($comment->replies as $reply)
              <div class="comment-reply" data-comment-id="{{ $reply->id }}">
                <div class="comment-avatar">
                  <img src="{{ $reply->user->avatar_url ?? asset('assets/images/profile.png') }}"
                    alt="{{ $reply->user->username }}" />
                </div>
                <div class="comment-content">
                  <div class="comment-header">
                    <a href="#" class="comment-author">{{ $reply->user->username }}</a>
                    <span class="comment-username">{{ $reply->user->display_name }}</span>
                    <span class="comment-timestamp">{{ $reply->created_at->diffForHumans() }}</span>
                  </div>
                  <div class="comment-text">{{ $reply->content }}</div>
                  <div class="comment-actions">
                    @if($isAuthenticated)
                      <button class="comment-action comment-like-btn {{ $reply->is_liked ? 'liked' : '' }}"
                        data-comment-id="{{ $reply->id }}">
                        <i class="{{ $reply->is_liked ? 'fas' : 'far' }} fa-heart"></i>
                        <span>{{ $reply->like_count }}</span>
                      </button>
                    @else
                      <button class="comment-action comment-like-btn-guest" onclick="showLoginPrompt()">
                        <i class="far fa-heart"></i>
                        <span>{{ $reply->like_count }}</span>
                      </button>
                    @endif
                  </div>
                </div>
              </div>
              @endforeach
            </div>
            @endif
          </div>
        </div>
      </div>
      @endforeach
    </div>

    {{-- Load More Comments --}}
    @if($comments->hasMorePages())
    <div class="load-more-comments">
      <button class="load-more-btn" data-next-page="{{ $comments->currentPage() + 1 }}"
        data-post-id="{{ $post->id }}"
        data-is-authenticated="{{ $isAuthenticated ? 'true' : 'false' }}">
        Load More Comments
      </button>
    </div>
    @endif
    @else
    <div class="comments-empty">
      <i class="far fa-comments"></i>
      <h3>No comments yet</h3>
      <p>Be the first to comment on this post!</p>
    </div>
    @endif
  </div>
</div>

{{-- Add your existing CSS styles --}}
<style>
  /* Previous styles remain the same... */
  .comments-section {
    background-color: #ffffff;
    border-radius: 12px;
    padding: 24px;
    margin-top: 20px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
    border: 1px solid #e1e8ed;
  }

  .comments-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 20px;
    padding-bottom: 16px;
    border-bottom: 2px solid #f7f9fa;
  }

  .comments-title {
    font-size: 20px;
    font-weight: 700;
    color: #14171a;
    margin: 0;
  }

  .comments-count {
    background-color: #dc3545;
    color: white;
    padding: 4px 12px;
    border-radius: 16px;
    font-size: 14px;
    font-weight: 600;
  }

  .comment-thread {
    margin-bottom: 0;
    position: relative;
    border-bottom: 1px solid #f0f0f0;
    padding: 16px 0;
  }

  .comment-thread:last-child {
    border-bottom: none;
  }

  .comment-item {
    display: flex;
    gap: 12px;
    background-color: #ffffff;
    transition: all 0.2s ease;
    position: relative;
  }

  .comment-item:hover {
    background-color: #f7f9fa;
  }

  .comment-avatar {
    flex-shrink: 0;
    position: relative;
  }

  .comment-avatar img {
    width: 44px;
    height: 44px;
    border-radius: 50%;
    border: 2px solid #e1e8ed;
    object-fit: cover;
  }

  .comment-content {
    flex: 1;
    min-width: 0;
  }

  .comment-header {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 8px;
    flex-wrap: wrap;
  }

  .comment-author {
    font-weight: 700;
    color: #14171a;
    text-decoration: none;
    font-size: 15px;
  }

  .comment-author:hover {
    color: #dc3545;
    text-decoration: underline;
  }

  .comment-username {
    color: #657786;
    font-size: 14px;
  }

  .comment-timestamp {
    color: #657786;
    font-size: 14px;
    margin-left: auto;
  }

  .comment-text {
    color: #14171a;
    font-size: 15px;
    line-height: 1.5;
    margin-bottom: 12px;
    word-wrap: break-word;
  }

  .comment-actions {
    display: flex;
    align-items: center;
    gap: 24px;
    margin-top: 8px;
  }

  .comment-action {
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 6px 12px;
    border-radius: 20px;
    border: none;
    background: none;
    color: #657786;
    font-size: 14px;
    cursor: pointer;
    transition: all 0.2s ease;
    text-decoration: none;
  }

  .comment-action:hover {
    background-color: rgba(220, 53, 69, 0.1);
    color: #dc3545;
  }

  .comment-action.liked {
    color: #e0245e;
  }

  /* Guest-specific styles */
  .comment-like-btn-guest,
  .reply-btn-guest {
    opacity: 0.7;
    cursor: pointer;
  }

  .comment-like-btn-guest:hover,
  .reply-btn-guest:hover {
    background-color: rgba(220, 53, 69, 0.1);
    color: #dc3545;
    opacity: 1;
  }

  .guest-comment-prompt {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border-radius: 12px;
    padding: 20px;
    margin-bottom: 24px;
    border: 2px dashed #dee2e6;
    display: flex;
    align-items: center;
    gap: 16px;
  }

  .guest-prompt-content {
    display: flex;
    align-items: center;
    gap: 16px;
    width: 100%;
  }

  .guest-message {
    flex: 1;
  }

  .guest-message h5 {
    margin-bottom: 8px;
    color: #495057;
    font-weight: 600;
  }

  .guest-message p {
    margin-bottom: 16px;
    color: #6c757d;
    font-size: 14px;
  }

  .guest-actions {
    display: flex;
    gap: 12px;
  }

  .comment-replies {
    margin-top: 16px;
    margin-left: 56px;
    padding-left: 20px;
    border-left: 3px solid #e1e8ed;
    position: relative;
  }

  .comment-reply {
    margin-bottom: 16px;
    padding: 12px 16px;
    background-color: #f7f9fa;
    border-radius: 12px;
    border: 1px solid #e1e8ed;
    display: flex;
    gap: 12px;
  }

  .comment-form {
    background: white;
    border-radius: 12px;
    padding: 20px;
    margin-bottom: 24px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    border: 1px solid #e5e7eb;
    display: flex;
    gap: 16px;
  }

  .comment-form .comment-avatar {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    border: 2px solid #e5e7eb;
    object-fit: cover;
  }

  .comment-input-container {
    flex: 1;
  }

  .comment-input {
    width: 100%;
    min-height: 100px;
    padding: 16px;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    font-size: 16px;
    resize: vertical;
    outline: none;
    background: #fafbfc;
    transition: all 0.3s ease;
  }

  .comment-input:focus {
    border-color: rgba(220, 53, 69, 0.5);
    box-shadow: 0 0 0 4px rgba(220, 53, 69, 0.08);
  }

  .comment-form .comment-actions {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 16px;
    padding-top: 16px;
    border-top: 1px solid #f3f4f6;
  }

  .submit-comment-btn {
    padding: 10px 24px;
    background: #dc3545;
    color: white;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
  }

  .submit-comment-btn:hover {
    background: #c82333;
  }

  .load-more-comments {
    display: flex;
    justify-content: center;
    margin-top: 24px;
    padding-top: 20px;
    border-top: 1px solid #e1e8ed;
  }

  .load-more-btn {
    background-color: transparent;
    border: 2px solid #dc3545;
    color: #dc3545;
    padding: 12px 24px;
    border-radius: 25px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
  }

  .load-more-btn:hover {
    background-color: #dc3545;
    color: white;
  }

  .comments-empty {
    text-align: center;
    padding: 40px 20px;
    color: #657786;
  }

  .comments-empty i {
    font-size: 48px;
    margin-bottom: 16px;
    color: #e1e8ed;
  }

  .reply-form {
    margin-top: 12px;
    padding: 16px;
    background-color: #f8f9fa;
    border-radius: 8px;
    border: 1px solid #e1e8ed;
  }

  .reply-avatar {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    object-fit: cover;
  }

  .reply-input {
    width: 100%;
    min-height: 60px;
    padding: 12px;
    border: 1px solid #e5e7eb;
    border-radius: 6px;
    resize: vertical;
    outline: none;
  }

  .reply-actions {
    display: flex;
    gap: 8px;
    justify-content: flex-end;
  }

  /* Responsive Design */
  @media (max-width: 768px) {
    .comments-section {
      padding: 16px;
      margin: 16px;
      border-radius: 8px;
    }

    .comment-replies {
      margin-left: 0;
      padding-left: 16px;
    }

    .comment-actions {
      gap: 16px;
    }

    .guest-prompt-content {
      flex-direction: column;
      text-align: center;
    }

    .guest-actions {
      justify-content: center;
    }
  }
</style>

<script>
  // Global variables
  window.isAuthenticated = {{ $isAuthenticated ? 'true' : 'false' }};
  window.currentUserId = {{ $isAuthenticated ? ($currentUser->id ?? 'null') : 'null' }};

  document.addEventListener('DOMContentLoaded', function() {
    // Handle comment form submission (only for authenticated users)
    const commentForm = document.getElementById('comment-form');
    if (commentForm && window.isAuthenticated) {
      commentForm.addEventListener('submit', function(e) {
        e.preventDefault();

        const formData = new FormData(this);
        // Add user ID for backend authentication
        if (window.currentUserId) {
          formData.append('user_id', window.currentUserId);
        }

        const submitBtn = this.querySelector('.submit-comment-btn');
        const originalText = submitBtn.textContent;

        submitBtn.textContent = 'Posting...';
        submitBtn.disabled = true;

        fetch(this.action, {
            method: 'POST',
            body: formData,
            headers: {
              'X-Requested-With': 'XMLHttpRequest',
              'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
          })
          .then(response => response.json())
          .then(data => {
            if (data.success) {
              // Clear form
              this.querySelector('.comment-input').value = '';
              // Reload page to show new comment
              window.location.reload();
            } else {
              showNotification(data.message || 'Error posting comment', 'error');
            }
          })
          .catch(error => {
            console.error('Error:', error);
            showNotification('Error posting comment', 'error');
          })
          .finally(() => {
            submitBtn.textContent = originalText;
            submitBtn.disabled = false;
          });
      });
    }

    // Handle comment likes (only for authenticated users)
    document.querySelectorAll('.comment-like-btn').forEach(btn => {
      btn.addEventListener('click', function() {
        if (!window.isAuthenticated) {
          showLoginPrompt();
          return;
        }
        const commentId = this.dataset.commentId;
        toggleCommentLike(commentId, this);
      });
    });

    // Handle reply button clicks (only for authenticated users)
    document.querySelectorAll('.reply-btn').forEach(btn => {
      btn.addEventListener('click', function() {
        if (!window.isAuthenticated) {
          showLoginPrompt();
          return;
        }
        const commentId = this.dataset.commentId;
        const replyForm = document.getElementById(`reply-form-${commentId}`);

        if (replyForm.style.display === 'none') {
          // Hide all other reply forms
          document.querySelectorAll('.reply-form').forEach(form => {
            form.style.display = 'none';
          });

          replyForm.style.display = 'block';
          replyForm.querySelector('.reply-input').focus();
        } else {
          replyForm.style.display = 'none';
        }
      });
    });

    // Handle reply form submissions (only for authenticated users)
    document.querySelectorAll('.reply-form-inner').forEach(form => {
      form.addEventListener('submit', function(e) {
        e.preventDefault();

        if (!window.isAuthenticated) {
          showLoginPrompt();
          return;
        }

        const formData = new FormData(this);
        // Add user ID for backend authentication
        if (window.currentUserId) {
          formData.append('user_id', window.currentUserId);
        }

        const submitBtn = this.querySelector('button[type="submit"]');
        const originalText = submitBtn.textContent;

        submitBtn.textContent = 'Replying...';
        submitBtn.disabled = true;

        fetch('/comments', {
            method: 'POST',
            body: formData,
            headers: {
              'X-Requested-With': 'XMLHttpRequest',
              'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
          })
          .then(response => response.json())
          .then(data => {
            if (data.success) {
              // Clear form and hide it
              this.querySelector('.reply-input').value = '';
              this.closest('.reply-form').style.display = 'none';
              // Reload page to show new reply
              window.location.reload();
            } else {
              showNotification(data.message || 'Error posting reply', 'error');
            }
          })
          .catch(error => {
            console.error('Error:', error);
            showNotification('Error posting reply', 'error');
          })
          .finally(() => {
            submitBtn.textContent = originalText;
            submitBtn.disabled = false;
          });
      });
    });

    // Handle cancel reply buttons
    document.querySelectorAll('.cancel-reply').forEach(btn => {
      btn.addEventListener('click', function() {
        const replyForm = this.closest('.reply-form');
        replyForm.style.display = 'none';
        replyForm.querySelector('.reply-input').value = '';
      });
    });

    // Handle load more comments
    const loadMoreBtn = document.querySelector('.load-more-btn');
    if (loadMoreBtn) {
      loadMoreBtn.addEventListener('click', function() {
        const nextPage = this.dataset.nextPage;
        const postId = this.dataset.postId;
        const isAuthenticated = this.dataset.isAuthenticated === 'true';

        this.textContent = 'Loading...';
        this.disabled = true;

        let url = `/comments/load-more?post_id=${postId}&page=${nextPage}`;
        if (isAuthenticated && window.currentUserId) {
          url += `&user_id=${window.currentUserId}`;
        }

        fetch(url, {
            headers: {
              'X-Requested-With': 'XMLHttpRequest'
            }
          })
          .then(response => response.json())
          .then(data => {
            if (data.success && data.comments.length > 0) {
              // Add new comments to the container
              const container = document.getElementById('comments-container');

              data.comments.forEach(comment => {
                const commentHtml = createCommentHtml(comment, data.is_authenticated);
                container.insertAdjacentHTML('beforeend', commentHtml);
              });

              // Update pagination
              if (data.has_more) {
                this.dataset.nextPage = data.next_page;
                this.textContent = 'Load More Comments';
                this.disabled = false;
              } else {
                this.textContent = 'No more comments';
                this.disabled = true;
              }

              // Reinitialize event listeners for new comments
              initializeCommentInteractions();
            } else {
              this.textContent = 'No more comments';
              this.disabled = true;
            }
          })
          .catch(error => {
            console.error('Error:', error);
            this.textContent = 'Error loading comments';
            setTimeout(() => {
              this.textContent = 'Load More Comments';
              this.disabled = false;
            }, 2000);
          });
      });
    }

    // Handle post actions (like, save, share)
    if (typeof PostInteractions !== 'undefined') {
      PostInteractions.init();
    }

    // Handle video player
    if (typeof VideoPlayer !== 'undefined') {
      VideoPlayer.init();
    }

    // Focus comment box if flag is set
    if (localStorage.getItem('focusCommentBox') === 'true') {
      setTimeout(() => {
        const commentInput = document.querySelector('.comment-input');
        if (commentInput) {
          commentInput.focus();
          commentInput.scrollIntoView({
            behavior: 'smooth',
            block: 'center'
          });
          localStorage.removeItem('focusCommentBox');
        }
      }, 500);
    }

    // Focus comment box when comment button is clicked
    const focusCommentBtn = document.getElementById('focus-comment-btn');
    if (focusCommentBtn) {
      focusCommentBtn.addEventListener('click', function() {
        if (!window.isAuthenticated) {
          showLoginPrompt();
          return;
        }
        const commentInput = document.querySelector('.comment-input');
        if (commentInput) {
          commentInput.focus();
          commentInput.scrollIntoView({
            behavior: 'smooth',
            block: 'center'
          });
        }
      });
    }
  });

  function toggleCommentLike(commentId, button) {
    if (!window.isAuthenticated) {
      showLoginPrompt();
      return;
    }

    // Add user_id to the request
    const formData = new FormData();
    if (window.currentUserId) {
      formData.append('user_id', window.currentUserId);
    }

    fetch(`/comments/${commentId}/like`, {
        method: 'POST',
        body: formData,
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          const icon = button.querySelector('i');
          const count = button.querySelector('span');

          if (data.liked) {
            icon.classList.remove('far');
            icon.classList.add('fas');
            button.classList.add('liked');
          } else {
            icon.classList.remove('fas');
            icon.classList.add('far');
            button.classList.remove('liked');
          }

          count.textContent = data.like_count;
        } else {
          showNotification(data.message || 'Error liking comment', 'error');
        }
      })
      .catch(error => {
        console.error('Error:', error);
        showNotification('Error liking comment', 'error');
      });
  }

  function createCommentHtml(comment, isAuthenticated) {
    const repliesHtml = comment.replies ? comment.replies.map(reply => {
      const replyActions = isAuthenticated ? `
        <button class="comment-action comment-like-btn ${reply.is_liked ? 'liked' : ''}" data-comment-id="${reply.id}">
          <i class="${reply.is_liked ? 'fas' : 'far'} fa-heart"></i>
          <span>${reply.like_count}</span>
        </button>
      ` : `
        <button class="comment-action comment-like-btn-guest" onclick="showLoginPrompt()">
          <i class="far fa-heart"></i>
          <span>${reply.like_count}</span>
        </button>
      `;

      return `
        <div class="comment-reply" data-comment-id="${reply.id}">
          <div class="comment-avatar">
            <img src="${reply.user.avatar_url || '/assets/images/profile.png'}" alt="${reply.user.username}" />
          </div>
          <div class="comment-content">
            <div class="comment-header">
              <a href="#" class="comment-author">${reply.user.username}</a>
              <span class="comment-username">${reply.user.display_name || ''}</span>
              <span class="comment-timestamp">${reply.created_at}</span>
            </div>
            <div class="comment-text">${reply.content}</div>
            <div class="comment-actions">
              ${replyActions}
            </div>
          </div>
        </div>
      `;
    }).join('') : '';

    const commentActions = isAuthenticated ? `
      <button class="comment-action comment-like-btn ${comment.is_liked ? 'liked' : ''}" data-comment-id="${comment.id}">
        <i class="${comment.is_liked ? 'fas' : 'far'} fa-heart"></i>
        <span>${comment.like_count}</span>
      </button>
      <button class="comment-action reply-btn" data-comment-id="${comment.id}">
        <i class="far fa-comment"></i> Reply
      </button>
    ` : `
      <button class="comment-action comment-like-btn-guest" onclick="showLoginPrompt()">
        <i class="far fa-heart"></i>
        <span>${comment.like_count}</span>
      </button>
      <button class="comment-action reply-btn-guest" onclick="showLoginPrompt()">
        <i class="far fa-comment"></i> Reply
      </button>
    `;

    const replyForm = isAuthenticated ? `
      <div class="reply-form" id="reply-form-${comment.id}" style="display: none;">
        <form class="reply-form-inner" data-parent-id="${comment.id}">
          <input type="hidden" name="_token" value="${document.querySelector('meta[name="csrf-token"]').content}">
          <input type="hidden" name="post_id" value="${comment.post_id}">
          <input type="hidden" name="parent_comment_id" value="${comment.id}">
          <div class="d-flex gap-2">
            <img src="/assets/images/profile.png" alt="Profile" class="reply-avatar" />
            <div class="flex-grow-1">
              <textarea name="content" class="reply-input" placeholder="Write a reply..." required></textarea>
              <div class="reply-actions mt-2">
                <button type="button" class="btn btn-sm btn-secondary cancel-reply">Cancel</button>
                <button type="submit" class="btn btn-sm btn-primary">Reply</button>
              </div>
            </div>
          </div>
        </form>
      </div>
    ` : '';

    return `
      <div class="comment-thread">
        <div class="comment-item" data-comment-id="${comment.id}">
          <div class="comment-avatar">
            <img src="${comment.user.avatar_url || '/assets/images/profile.png'}" alt="${comment.user.username}" />
          </div>
          <div class="comment-content">
            <div class="comment-header">
              <a href="#" class="comment-author">${comment.user.username}</a>
              <span class="comment-username">${comment.user.display_name || ''}</span>
              <span class="comment-timestamp">${comment.created_at}</span>
            </div>
            <div class="comment-text">${comment.content}</div>
            <div class="comment-actions">
              ${commentActions}
            </div>
            ${replyForm}
            ${repliesHtml ? `<div class="comment-replies">${repliesHtml}</div>` : ''}
          </div>
        </div>
      </div>
    `;
  }

  function initializeCommentInteractions() {
    // Reinitialize event listeners for dynamically loaded comments
    document.querySelectorAll('.comment-like-btn:not([data-initialized])').forEach(btn => {
      btn.addEventListener('click', function() {
        const commentId = this.dataset.commentId;
        toggleCommentLike(commentId, this);
      });
      btn.setAttribute('data-initialized', 'true');
    });

    document.querySelectorAll('.reply-btn:not([data-initialized])').forEach(btn => {
      btn.addEventListener('click', function() {
        if (!window.isAuthenticated) {
          showLoginPrompt();
          return;
        }
        const commentId = this.dataset.commentId;
        const replyForm = document.getElementById(`reply-form-${commentId}`);

        if (replyForm && replyForm.style.display === 'none') {
          document.querySelectorAll('.reply-form').forEach(form => {
            form.style.display = 'none';
          });

          replyForm.style.display = 'block';
          replyForm.querySelector('.reply-input').focus();
        } else if (replyForm) {
          replyForm.style.display = 'none';
        }
      });
      btn.setAttribute('data-initialized', 'true');
    });

    document.querySelectorAll('.reply-form-inner:not([data-initialized])').forEach(form => {
      form.addEventListener('submit', function(e) {
        e.preventDefault();

        if (!window.isAuthenticated) {
          showLoginPrompt();
          return;
        }

        const formData = new FormData(this);
        if (window.currentUserId) {
          formData.append('user_id', window.currentUserId);
        }

        const submitBtn = this.querySelector('button[type="submit"]');
        const originalText = submitBtn.textContent;

        submitBtn.textContent = 'Replying...';
        submitBtn.disabled = true;

        fetch('/comments', {
            method: 'POST',
            body: formData,
            headers: {
              'X-Requested-With': 'XMLHttpRequest',
              'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
          })
          .then(response => response.json())
          .then(data => {
            if (data.success) {
              this.querySelector('.reply-input').value = '';
              this.closest('.reply-form').style.display = 'none';
              window.location.reload();
            } else {
              showNotification(data.message || 'Error posting reply', 'error');
            }
          })
          .catch(error => {
            console.error('Error:', error);
            showNotification('Error posting reply', 'error');
          })
          .finally(() => {
            submitBtn.textContent = originalText;
            submitBtn.disabled = false;
          });
      });
      form.setAttribute('data-initialized', 'true');
    });

    document.querySelectorAll('.cancel-reply:not([data-initialized])').forEach(btn => {
      btn.addEventListener('click', function() {
        const replyForm = this.closest('.reply-form');
        replyForm.style.display = 'none';
        replyForm.querySelector('.reply-input').value = '';
      });
      btn.setAttribute('data-initialized', 'true');
    });
  }

  // Video player function for detail page
  function playVideo(videoId) {
    if (window.VideoPlayer) {
      VideoPlayer.playVideo(videoId);
    } else {
      const video = document.getElementById(videoId);
      const placeholder = video?.parentNode?.querySelector(".video-placeholder");

      if (video && placeholder) {
        video.style.display = "block";
        placeholder.style.display = "none";
        video.play().catch(console.error);
      }
    }
  }

  // Auth-related functions
  function showLoginPrompt() {
    showNotification('Please log in to interact with posts and comments.', 'info');
    setTimeout(() => {
      if (typeof showLoginModal === 'function') {
        showLoginModal();
      }
    }, 1500);
  }

  function showNotification(message, type = 'success') {
    const notification = document.createElement('div');
    notification.className = `alert alert-${type === 'success' ? 'success' : type === 'error' ? 'danger' : 'info'} alert-dismissible fade show position-fixed`;
    notification.style.cssText = 'top: 20px; right: 20px; z-index: 1050; min-width: 300px;';
    notification.innerHTML = `
      <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'} me-2"></i>
      ${message}
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;

    document.body.appendChild(notification);

    setTimeout(() => {
      if (notification.parentNode) {
        notification.parentNode.removeChild(notification);
      }
    }, 5000);
  }
</script>

@endsection