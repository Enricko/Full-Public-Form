<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\Post;
use App\Models\Comment;
use App\Models\Hashtag;
use App\Models\Like;
use App\Models\Attachment;
use App\Models\SavedPost;
use App\Models\UserFollow;
use App\Models\HashtagFollow;

class PublicForumSeeder extends Seeder
{
    public function run()
    {
        // Create sample users
        $users = [
            [
                'username' => 'Crocodilo',
                'email' => 'crocodilo@example.com',
                'password' => Hash::make('password'),
                'display_name' => 'Crocodilo',
                'role' => 'admin',
                'avatar_url' => 'https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=150&h=150&fit=crop&crop=face',
            ],
            [
                'username' => 'JavaScriptMaster',
                'email' => 'jsmaster@example.com',
                'password' => Hash::make('password'),
                'display_name' => 'JavaScript Master',
                'role' => 'user',
                'avatar_url' => 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=150&h=150&fit=crop&crop=face',
            ],
            [
                'username' => 'desaintin',
                'email' => 'desaintin@example.com',
                'password' => Hash::make('password'),
                'display_name' => 'desaintin',
                'role' => 'user',
                'avatar_url' => 'https://images.unsplash.com/photo-1494790108755-2616b612b5bc?w=150&h=150&fit=crop&crop=face',
            ],
            [
                'username' => 'techbabe',
                'email' => 'techbabe@example.com',
                'password' => Hash::make('password'),
                'display_name' => 'techbabe',
                'role' => 'user',
                'avatar_url' => 'https://images.unsplash.com/photo-1438761681033-6461ffad8d80?w=150&h=150&fit=crop&crop=face',
            ],
            [
                'username' => 'techgurujon',
                'email' => 'techguru@example.com',
                'password' => Hash::make('password'),
                'display_name' => 'Tech Guru Jon',
                'role' => 'user',
                'avatar_url' => 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?w=150&h=150&fit=crop&crop=face',
            ],
            [
                'username' => 'codecrafter',
                'email' => 'codecrafter@example.com',
                'password' => Hash::make('password'),
                'display_name' => 'Code Crafter',
                'role' => 'user',
                'avatar_url' => 'https://images.unsplash.com/photo-1519345182560-3f2917c472ef?w=150&h=150&fit=crop&crop=face',
            ],
        ];

        $createdUsers = [];
        foreach ($users as $userData) {
            $createdUsers[] = User::create($userData);
        }

        // Create sample hashtags
        $hashtagsData = [
            'javascript',
            'webdevelopment',
            'coding',
            'workspacegoals',
            'php',
            'laravel',
            'nodejs',
            'react',
            'typescript',
            'vue',
            'python',
            'machinelearning',
            'ai',
            'blockchain',
            'cybersecurity',
            'devops',
            'frontend',
            'backend',
            'fullstack',
            'mobile',
        ];

        $createdHashtags = [];
        foreach ($hashtagsData as $hashtagName) {
            $createdHashtags[] = Hashtag::create(['name' => $hashtagName]);
        }

        // Create sample posts with different content types
        $postsData = [
            [
                'user_id' => 1, // Crocodilo
                'content' => 'Just finished the backend implementation for our new messaging feature. Excited to see this go live next week! #WebDevelopment #Coding',
                'attachments' => [
                    [
                        'file_name' => 'backend_architecture.png',
                        'file_path' => 'https://images.unsplash.com/photo-1555066931-4365d14bab8c?w=800&h=600',
                        'file_type' => 'image/png',
                        'file_size' => 245760,
                    ]
                ],
                'hashtags' => ['webdevelopment', 'coding']
            ],
            [
                'user_id' => 2, // JavaScriptMaster
                'content' => 'Our new office setup is ready! 🚀 #WorkspaceGoals',
                'attachments' => [
                    [
                        'file_name' => 'office_setup.jpg',
                        'file_path' => 'https://images.unsplash.com/photo-1497366216548-37526070297c?w=800&h=600',
                        'file_type' => 'image/jpeg',
                        'file_size' => 342100,
                    ]
                ],
                'hashtags' => ['workspacegoals']
            ],
            [
                'user_id' => 2, // JavaScriptMaster
                'content' => 'This is a great news! Will the messaging feature include group chats? 🤔',
                'hashtags' => ['javascript', 'webdevelopment']
            ],
            [
                'user_id' => 3, // desaintin
                'content' => 'What tech stack did you use for the backend implementation? Looking forward to using it!',
                'hashtags' => ['webdevelopment', 'coding']
            ],
            [
                'user_id' => 4, // techbabe
                'content' => 'Congrats on shipping this feature! Looking forward to using it. 👏',
                'hashtags' => []
            ],
            [
                'user_id' => 5, // techgurujon
                'content' => 'Working on a new React component library. Here\'s a sneak peek of the documentation site!',
                'attachments' => [
                    [
                        'file_name' => 'react_docs_preview.png',
                        'file_path' => 'https://images.unsplash.com/photo-1517077304055-6e89abbf09b0?w=800&h=600',
                        'file_type' => 'image/png',
                        'file_size' => 456780,
                    ],
                    [
                        'file_name' => 'component_demo.mp4',
                        'file_path' => 'https://sample-videos.com/zip/10/mp4/SampleVideo_360x240_1mb.mp4',
                        'file_type' => 'video/mp4',
                        'file_size' => 1048576,
                    ]
                ],
                'hashtags' => ['react', 'javascript', 'frontend']
            ],
            [
                'user_id' => 6, // codecrafter
                'content' => 'I love JavaScript more than you know my love #javascript',
                'attachments' => [
                    [
                        'file_name' => 'js_love_meme.jpg',
                        'file_path' => 'https://images.unsplash.com/photo-1627398242454-45a1465c2479?w=800&h=600',
                        'file_type' => 'image/jpeg',
                        'file_size' => 178900,
                    ]
                ],
                'hashtags' => ['javascript']
            ],
            [
                'user_id' => 1, // Crocodilo
                'content' => 'Check out the quick demo of our new feature! 🎥',
                'attachments' => [
                    [
                        'file_name' => 'feature_demo.mp4',
                        'file_path' => 'https://sample-videos.com/zip/10/mp4/SampleVideo_640x360_2mb.mp4',
                        'file_type' => 'video/mp4',
                        'file_size' => 2097152,
                    ]
                ],
                'hashtags' => ['webdevelopment', 'coding']
            ],
            [
                'user_id' => 3, // desaintin
                'content' => 'Nice! Did you consider using Firebase instead?',
                'hashtags' => []
            ],
            [
                'user_id' => 4, // techbabe
                'content' => 'Learning TypeScript has been a game-changer for my development workflow. Here are some resources I found helpful:',
                'attachments' => [
                    [
                        'file_name' => 'typescript_resources.pdf',
                        'file_path' => 'attachments/typescript_resources.pdf',
                        'file_type' => 'application/pdf',
                        'file_size' => 892000,
                    ]
                ],
                'hashtags' => ['typescript', 'javascript', 'learning']
            ],
        ];

        $createdPosts = [];
        foreach ($postsData as $index => $postData) {
            $post = Post::create([
                'user_id' => $postData['user_id'],
                'content' => $postData['content'],
                'created_at' => now()->subDays(rand(0, 30))->subHours(rand(0, 23)),
            ]);

            // Add attachments if they exist
            if (isset($postData['attachments'])) {
                foreach ($postData['attachments'] as $order => $attachmentData) {
                    Attachment::create([
                        'post_id' => $post->id,
                        'user_id' => $postData['user_id'],
                        'file_name' => $attachmentData['file_name'],
                        'file_path' => $attachmentData['file_path'],
                        'file_size' => $attachmentData['file_size'],
                        'file_type' => $attachmentData['file_type'],
                        'upload_order' => $order,
                    ]);
                }
            }

            // Add hashtags if they exist
            if (!empty($postData['hashtags'])) {
                foreach ($postData['hashtags'] as $hashtagName) {
                    $hashtag = collect($createdHashtags)->firstWhere('name', $hashtagName);
                    if ($hashtag) {
                        $post->hashtags()->attach($hashtag->id);
                        $hashtag->increment('post_count');
                    }
                }
            }

            $createdPosts[] = $post;
        }

        // Create sample comments
        $commentsData = [
            [
                'post_id' => 1,
                'user_id' => 2,
                'content' => 'This is a great news! Will the messaging feature include group chats?',
            ],
            [
                'post_id' => 1,
                'user_id' => 3,
                'content' => 'What tech stack did you use for the backend implementation?',
            ],
            [
                'post_id' => 1,
                'user_id' => 1,
                'parent_comment_id' => 1, // Reply to first comment
                'content' => 'Yes! Group chats will be included in the next iteration.',
            ],
            [
                'post_id' => 1,
                'user_id' => 1,
                'parent_comment_id' => 2, // Reply to second comment
                'content' => 'We used Node.js with Express and Socket.IO for real-time messaging. The database is MongoDB.',
            ],
            [
                'post_id' => 2,
                'user_id' => 4,
                'content' => 'Congrats on shipping this feature! Looking forward to using it.',
            ],
            [
                'post_id' => 6,
                'user_id' => 1,
                'content' => 'That component library looks amazing! When will it be available?',
            ],
            [
                'post_id' => 6,
                'user_id' => 5,
                'parent_comment_id' => 6,
                'content' => 'Planning to release it next month! Still working on the documentation.',
            ],
            [
                'post_id' => 8,
                'user_id' => 3,
                'content' => 'Nice! Did you consider using Firebase instead?',
            ],
        ];

        $createdComments = [];
        foreach ($commentsData as $commentData) {
            $comment = Comment::create(array_merge($commentData, [
                'created_at' => now()->subDays(rand(0, 15))->subHours(rand(0, 23)),
            ]));
            $createdComments[] = $comment;

            // Update post comment count
            Post::find($commentData['post_id'])->increment('comment_count');
        }

        // Create sample likes for posts
        $postLikes = [
            ['user_id' => 2, 'post_id' => 1],
            ['user_id' => 3, 'post_id' => 1],
            ['user_id' => 4, 'post_id' => 1],
            ['user_id' => 5, 'post_id' => 1],
            ['user_id' => 1, 'post_id' => 2],
            ['user_id' => 3, 'post_id' => 2],
            ['user_id' => 4, 'post_id' => 2],
            ['user_id' => 1, 'post_id' => 6],
            ['user_id' => 2, 'post_id' => 6],
            ['user_id' => 3, 'post_id' => 6],
            ['user_id' => 6, 'post_id' => 7],
            ['user_id' => 2, 'post_id' => 7],
            ['user_id' => 4, 'post_id' => 8],
            ['user_id' => 5, 'post_id' => 8],
        ];

        foreach ($postLikes as $likeData) {
            Like::create($likeData);
            Post::find($likeData['post_id'])->increment('like_count');
        }

        // Create sample likes for comments
        $commentLikes = [
            ['user_id' => 1, 'comment_id' => 1],
            ['user_id' => 4, 'comment_id' => 1],
            ['user_id' => 2, 'comment_id' => 2],
            ['user_id' => 5, 'comment_id' => 6],
            ['user_id' => 1, 'comment_id' => 8],
        ];

        foreach ($commentLikes as $likeData) {
            Like::create($likeData);
            Comment::find($likeData['comment_id'])->increment('like_count');
        }

        // Create sample saved posts
        $savedPosts = [
            ['user_id' => 2, 'post_id' => 1],
            ['user_id' => 3, 'post_id' => 1],
            ['user_id' => 4, 'post_id' => 6],
            ['user_id' => 5, 'post_id' => 6],
            ['user_id' => 1, 'post_id' => 7],
            ['user_id' => 6, 'post_id' => 8],
        ];

        foreach ($savedPosts as $savedData) {
            SavedPost::create($savedData);
        }

        // Create user follows
        $userFollows = [
            ['follower_id' => 2, 'following_id' => 1], // JavaScriptMaster follows Crocodilo
            ['follower_id' => 3, 'following_id' => 1], // desaintin follows Crocodilo
            ['follower_id' => 4, 'following_id' => 1], // techbabe follows Crocodilo
            ['follower_id' => 5, 'following_id' => 1], // techgurujon follows Crocodilo
            ['follower_id' => 1, 'following_id' => 2], // Crocodilo follows JavaScriptMaster
            ['follower_id' => 3, 'following_id' => 2], // desaintin follows JavaScriptMaster
            ['follower_id' => 1, 'following_id' => 5], // Crocodilo follows techgurujon
            ['follower_id' => 2, 'following_id' => 5], // JavaScriptMaster follows techgurujon
            ['follower_id' => 6, 'following_id' => 1], // codecrafter follows Crocodilo
            ['follower_id' => 1, 'following_id' => 6], // Crocodilo follows codecrafter
        ];

        foreach ($userFollows as $followData) {
            UserFollow::create($followData);
        }

        // Create hashtag follows
        $hashtagFollows = [
            ['user_id' => 1, 'hashtag_id' => 1], // Crocodilo follows javascript
            ['user_id' => 1, 'hashtag_id' => 2], // Crocodilo follows webdevelopment
            ['user_id' => 2, 'hashtag_id' => 1], // JavaScriptMaster follows javascript
            ['user_id' => 2, 'hashtag_id' => 4], // JavaScriptMaster follows workspacegoals
            ['user_id' => 3, 'hashtag_id' => 2], // desaintin follows webdevelopment
            ['user_id' => 3, 'hashtag_id' => 3], // desaintin follows coding
            ['user_id' => 4, 'hashtag_id' => 9], // techbabe follows typescript
            ['user_id' => 5, 'hashtag_id' => 8], // techgurujon follows react
            ['user_id' => 5, 'hashtag_id' => 1], // techgurujon follows javascript
            ['user_id' => 6, 'hashtag_id' => 1], // codecrafter follows javascript
        ];

        foreach ($hashtagFollows as $followData) {
            HashtagFollow::create($followData);
        }

        // Update share counts for some posts
        $shareCounts = [
            1 => 12, // First post has 12 shares
            2 => 5,  // Second post has 5 shares
            6 => 8,  // React component library post has 8 shares
            7 => 3,  // JavaScript love post has 3 shares
        ];

        foreach ($shareCounts as $postId => $shareCount) {
            Post::find($postId)->update(['share_count' => $shareCount]);
        }

        $this->command->info('✅ PublicForum seeder completed successfully!');
        $this->command->info('📊 Created:');
        $this->command->info('   - ' . count($createdUsers) . ' users');
        $this->command->info('   - ' . count($createdHashtags) . ' hashtags');
        $this->command->info('   - ' . count($createdPosts) . ' posts');
        $this->command->info('   - ' . count($createdComments) . ' comments');
        $this->command->info('   - ' . count($postLikes) . ' post likes');
        $this->command->info('   - ' . count($commentLikes) . ' comment likes');
        $this->command->info('   - ' . count($savedPosts) . ' saved posts');
        $this->command->info('   - ' . count($userFollows) . ' user follows');
        $this->command->info('   - ' . count($hashtagFollows) . ' hashtag follows');
    }
}
