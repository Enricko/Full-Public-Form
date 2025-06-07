@foreach($posts as $post)
    <div class="social-post" data-post-id="{{ $post->id }}">
        <!-- Post Header -->
        <div class="post-header">
            <img src="{{ $post->user->avatar_url ?? asset('assets/images/profile.png') }}" alt="Profile"
                class="post-avatar" />
            <div class="post-info">
                <div class="post-author">
                    <a href="#" class="text-decoration-none">
                        {{ $post->user->username }}
                    </a>
                    <span class="post-date">· {{ $post->created_at->diffForHumans() }}</span>
                </div>
                <div class="post-meta">{{ $post->user->display_name }}</div>
            </div>
        </div>

        <!-- Post Content -->
        @if ($post->content)
            <div class="post-content">
                {!! nl2br(e($post->content)) !!}
            </div>
        @endif

        <!-- Post Media -->
        @if ($post->attachments->count() > 0)
            <div class="post-media-container">
                @foreach($post->attachments as $index => $attachment)
                    @if (str_starts_with($attachment->file_type, 'image/'))
                        <!-- Image -->
                        <div class="post-media single-image">
                            <img src="{{ $attachment->file_path }}" alt="{{ $attachment->file_name }}"
                                class="post-image" />
                        </div>
                    @elseif(str_starts_with($attachment->file_type, 'video/'))
                        <!-- Video -->
                        <div class="post-media video-container">
                            <video id="video-{{ $post->id }}-{{ $index }}" controls class="video-element">
                                <source src="{{ $attachment->file_path }}" type="{{ $attachment->file_type }}" />
                                Your browser does not support the video tag.
                            </video>
                            <div class="video-placeholder" onclick="playVideo('video-{{ $post->id }}-{{ $index }}')">
                                <div class="video-thumbnail-wrapper">
                                    <video class="video-thumbnail-source" muted>
                                        <source src="{{ $attachment->file_path }}" type="{{ $attachment->file_type }}" />
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
                        <!-- Other Files -->
                        <div class="post-media file-attachment">
                            <div class="file-item">
                                <div class="file-icon">
                                    @if(str_contains($attachment->file_type, 'pdf'))
                                        <i class="fas fa-file-pdf text-danger"></i>
                                    @elseif(str_contains($attachment->file_type, 'word') || str_contains($attachment->file_type, 'document'))
                                        <i class="fas fa-file-word text-primary"></i>
                                    @elseif(str_contains($attachment->file_type, 'excel') || str_contains($attachment->file_type, 'spreadsheet'))
                                        <i class="fas fa-file-excel text-success"></i>
                                    @elseif(str_contains($attachment->file_type, 'zip') || str_contains($attachment->file_type, 'archive'))
                                        <i class="fas fa-file-archive text-warning"></i>
                                    @elseif(str_contains($attachment->file_type, 'text'))
                                        <i class="fas fa-file-alt text-info"></i>
                                    @else
                                        <i class="fas fa-file text-muted"></i>
                                    @endif
                                </div>
                                <div class="file-info">
                                    <div class="file-name">{{ $attachment->file_name }}</div>
                                    <div class="file-details">
                                        <span class="file-size">{{ number_format($attachment->file_size / 1024, 1) }} KB</span>
                                        <span class="file-type">{{ strtoupper(pathinfo($attachment->file_name, PATHINFO_EXTENSION)) }}</span>
                                    </div>
                                </div>
                                <div class="file-actions">
                                    <a href="{{ $attachment->file_path }}" target="_blank" class="btn btn-sm btn-outline-secondary me-1">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ $attachment->file_path }}" download class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-download"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endif
                @endforeach

                <!-- Show attachment count if more than 3 -->
                @if($post->attachments->count() > 3)
                    <div class="attachment-summary">
                        <i class="fas fa-paperclip"></i>
                        {{ $post->attachments->count() }} files attached
                        <small class="text-muted">
                            ({{ $post->attachments->where('file_type', 'like', 'image/%')->count() }} images,
                             {{ $post->attachments->where('file_type', 'like', 'video/%')->count() }} videos,
                             {{ $post->attachments->where('file_type', 'not like', 'image/%')->where('file_type', 'not like', 'video/%')->count() }} other files)
                        </small>
                    </div>
                @endif
            </div>
        @endif

        <!-- Post Actions -->
        <div class="post-actions">
            <!-- Like Button -->
            <button
                class="action-btn like-btn {{ auth()->check() && $post->isLikedBy(auth()->user()) ? 'liked' : '' }}"
                data-post-id="{{ $post->id }}" {{ !auth()->check() ? 'onclick="showLoginModal()"' : '' }}>
                <i class="{{ auth()->check() && $post->isLikedBy(auth()->user()) ? 'fas' : 'far' }} fa-heart"></i>
                <span>{{ number_format($post->like_count) }}</span>
            </button>

            <!-- Comment Button -->
            <button class="action-btn comment-btn" onclick="loadPage('comment', {{ $post->id }})">
                <i class="far fa-comment"></i>
                <span>{{ number_format($post->comment_count) }}</span>
            </button>

            <!-- Share Button -->
            <button class="action-btn repost-btn" data-post-id="{{ $post->id }}"
                {{ !auth()->check() ? 'onclick="showLoginModal()"' : 'onclick="sharePost(' . $post->id . ')"' }}>
                <i class="fas fa-retweet"></i>
                <span>{{ number_format($post->share_count) }}</span>
            </button>

            <!-- Share External Button -->
            <button class="action-btn share-btn" onclick="shareExternal({{ $post->id }})">
                <i class="far fa-share-square"></i>
            </button>

            <!-- Save Button -->
            <button
                class="action-btn save-btn {{ auth()->check() && $post->isSavedBy(auth()->user()) ? 'saved' : '' }}"
                data-post-id="{{ $post->id }}" {{ !auth()->check() ? 'onclick="showLoginModal()"' : '' }}>
                <i class="{{ auth()->check() && $post->isSavedBy(auth()->user()) ? 'fas' : 'far' }} fa-bookmark"></i>
            </button>
        </div>
    </div>
@endforeach
