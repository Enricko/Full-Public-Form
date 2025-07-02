<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Faker\Factory as Faker;
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
    private $faker;
    private $users = [];
    private $hashtags = [];
    private $posts = [];
    private $comments = [];

    public function __construct()
    {
        $this->faker = Faker::create();
    }

    public function run()
    {
        $this->command->info('🚀 Starting PublicForum seeder with 1000+ fake data...');

        // Create users (200 users)
        $this->createUsers(200);
        
        // Create hashtags (50 hashtags)
        $this->createHashtags(50);
        
        // Create posts (500 posts)
        $this->createPosts(500);
        
        // Create comments (800 comments)
        $this->createComments(800);
        
        // Create likes (2000 likes)
        $this->createLikes(2000);
        
        // Create saved posts (300 saved posts)
        $this->createSavedPosts(300);
        
        // Create user follows (400 follows)
        $this->createUserFollows(400);
        
        // Create hashtag follows (250 hashtag follows)
        $this->createHashtagFollows(250);

        $this->command->info('✅ PublicForum seeder completed successfully!');
        $this->printStats();
    }

    private function createUsers($count)
    {
        $this->command->info("Creating {$count} users...");
        
        // Create admin user first
        $adminUser = User::create([
            'username' => 'admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'display_name' => 'Administrator',
            'role' => 'admin',
            'bio' => 'This is the admin user for the PublicForum. Here to manage everything!',
            'avatar_url' => null,
            'created_at' => now()->subDays(rand(30, 365)),
        ]);
        $this->users[] = $adminUser;

        // Create predefined users
        $predefinedUsers = [


            [
                'username' => 'profileuser',
                'email' => 'profile@example.com', 
                'password' => Hash::make('password'),
                'display_name' => 'Profile Test User',
                'role' => 'user',
                'bio' => 'This is the main profile user for testing. Love coding and technology!',
                'avatar_url' => 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=150&h=150&fit=crop&crop=face',
            ],

            [
                'username' => 'Crocodilo',
                'email' => 'crocodilo@example.com',
                'display_name' => 'Crocodilo',
            'bio' => 'This is the admin user for the PublicForum. Here to manage everything!',

                'role' => 'admin',
            ],
            [
                'username' => 'JavaScriptMaster',
                'email' => 'jsmaster@example.com',
            'bio' => 'This is the admin user for the PublicForum. Here to manage everything!',
                'display_name' => 'JavaScript Master',
                'role' => 'user',
            ],
            [
                'username' => 'desaintin',
                'email' => 'desaintin@example.com',
            'bio' => 'This is the admin user for the PublicForum. Here to manage everything!',
                'display_name' => 'desaintin',
                'role' => 'user',
            ],
            [
                'username' => 'techbabe',
                'email' => 'techbabe@example.com',
            'bio' => 'This is the admin user for the PublicForum. Here to manage everything!',
                'display_name' => 'techbabe',
                'role' => 'user',
            ],
            [
                'username' => 'techgurujon',
                'email' => 'techguru@example.com',
            'bio' => 'This is the admin user for the PublicForum. Here to manage everything!',
                'display_name' => 'Tech Guru Jon',
                'role' => 'user',
            ],
        ];

        foreach ($predefinedUsers as $userData) {
            $user = User::create([
                'username' => $userData['username'],
                'email' => $userData['email'],
                'password' => Hash::make('password'),
                'display_name' => $userData['display_name'],
                'role' => $userData['role'],
                'bio' => $userData['bio'],
                'avatar_url' => null,
                'created_at' => now()->subDays(rand(30, 365)),
            ]);
            $this->users[] = $user;
        }

        // Create random users
        $remainingCount = $count - count($this->users);
        for ($i = 0; $i < $remainingCount; $i++) {
            $username = $this->faker->unique()->userName;
            $user = User::create([
                'username' => $username,
                'email' => $this->faker->unique()->safeEmail,
                'password' => Hash::make('password'),
                'display_name' => $this->faker->name,
                'bio' => $this->faker->sentence(rand(5, 15)),
                'role' => $this->faker->randomElement(['user', 'user', 'user', 'user', 'admin']), // 80% users, 20% admin
                'avatar_url' => null,
                'created_at' => now()->subDays(rand(1, 365)),
            ]);
            $this->users[] = $user;
        }
    }

    private function createHashtags($count)
    {
        $this->command->info("Creating {$count} hashtags...");
        
        $techHashtags = [
            'javascript', 'php', 'python', 'java', 'css', 'html', 'react', 'vue', 'angular',
            'nodejs', 'laravel', 'django', 'flask', 'express', 'mongodb', 'mysql', 'postgresql',
            'docker', 'kubernetes', 'aws', 'azure', 'gcp', 'devops', 'cicd', 'git', 'github',
            'typescript', 'graphql', 'rest', 'api', 'microservices', 'blockchain', 'ai', 'ml',
            'machinelearning', 'datascience', 'bigdata', 'cybersecurity', 'webdev', 'mobiledev',
            'ios', 'android', 'flutter', 'reactnative', 'frontend', 'backend', 'fullstack',
            'coding', 'programming', 'opensource'
        ];

        $generalHashtags = [
            'tech', 'startup', 'innovation', 'productivity', 'worklife', 'remote', 'freelance',
            'career', 'learning', 'tutorial', 'tips', 'news', 'trends', 'community', 'networking'
        ];

        $allHashtags = array_merge($techHashtags, $generalHashtags);
        
        // Add more random hashtags if needed
        while (count($allHashtags) < $count) {
            $allHashtags[] = $this->faker->unique()->word . 'dev';
        }

        foreach (array_slice($allHashtags, 0, $count) as $hashtagName) {
            $hashtag = Hashtag::create([
                'name' => strtolower($hashtagName),
                'post_count' => 0,
                'created_at' => now()->subDays(rand(1, 180)),
            ]);
            $this->hashtags[] = $hashtag;
        }
    }

    private function createPosts($count)
    {
        $this->command->info("Creating {$count} posts...");
        
        $postTemplates = [
            "Just finished working on %s! Excited to share this with the community. #%s #%s",
            "Check out this amazing %s implementation I've been working on! 🚀 #%s",
            "Learning %s has been a game-changer for my development workflow. Here's what I discovered: %s #%s #%s",
            "Our new %s project is ready! Looking forward to your feedback. #%s #%s",
            "Hot take: %s is %s than %s. What do you think? #%s #%s",
            "Just deployed a new %s feature using %s. The performance improvements are incredible! #%s #%s",
            "Working late on this %s project. The %s integration is trickier than expected. #%s #%s",
            "Excited to announce that our %s application now supports %s! #%s #%s #%s",
            "Question: What's your favorite %s library for %s development? #%s #%s",
            "Pro tip: Always %s your %s before pushing to production! #%s #%s",
        ];

        $techTerms = [
            'API', 'microservice', 'database', 'frontend', 'backend', 'authentication system',
            'real-time messaging', 'dashboard', 'mobile app', 'web application', 'component library',
            'data visualization', 'machine learning model', 'neural network', 'blockchain application'
        ];

        $adjectives = ['better', 'faster', 'more scalable', 'more secure', 'cleaner', 'more efficient'];
        $technologies = ['React', 'Vue', 'Angular', 'Node.js', 'Python', 'Java', 'PHP', 'Go', 'Rust'];

        for ($i = 0; $i < $count; $i++) {
            $template = $this->faker->randomElement($postTemplates);
            $selectedHashtags = $this->faker->randomElements($this->hashtags, rand(1, 4));
            
            // Generate content based on template
            $content = sprintf(
                $template,
                $this->faker->randomElement($techTerms),
                $this->faker->randomElement($adjectives),
                $this->faker->randomElement($technologies),
                $selectedHashtags[0]->name ?? 'coding',
                $selectedHashtags[1]->name ?? 'tech',
                $selectedHashtags[2]->name ?? 'development'
            );

            $post = Post::create([
                'user_id' => $this->faker->randomElement($this->users)->id,
                'content' => $content,
                'like_count' => 0,
                'comment_count' => 0,
                'share_count' => rand(0, 20),
                'created_at' => now()->subDays(rand(0, 60))->subHours(rand(0, 23)),
            ]);

            // Attach hashtags to post
            foreach ($selectedHashtags as $hashtag) {
                $post->hashtags()->attach($hashtag->id);
                $hashtag->increment('post_count');
            }

            // Maybe add attachments (30% chance)
            if ($this->faker->boolean(30)) {
                $this->createAttachment($post);
            }

            $this->posts[] = $post;
        }
    }

    private function createAttachment($post)
    {
        $attachmentTypes = [
            [
                'type' => 'image/jpeg',
                'extension' => 'jpg',
                'url_category' => 'technology',
                'size_range' => [100000, 500000]
            ],
            [
                'type' => 'image/png',
                'extension' => 'png',
                'url_category' => 'business',
                'size_range' => [150000, 600000]
            ],
            [
                'type' => 'video/mp4',
                'extension' => 'mp4',
                'url_category' => null,
                'size_range' => [1000000, 5000000]
            ],
            [
                'type' => 'application/pdf',
                'extension' => 'pdf',
                'url_category' => null,
                'size_range' => [500000, 2000000]
            ]
        ];

        $attachmentType = $this->faker->randomElement($attachmentTypes);
        $fileName = $this->faker->word . '_' . rand(1000, 9999) . '.' . $attachmentType['extension'];
        
        $filePath = $attachmentType['url_category'] 
            ? $this->faker->imageUrl(800, 600, $attachmentType['url_category'])
            : 'attachments/' . $fileName;

        Attachment::create([
            'post_id' => $post->id,
            'user_id' => $post->user_id,
            'file_name' => $fileName,
            'file_path' => $filePath,
            'file_size' => rand($attachmentType['size_range'][0], $attachmentType['size_range'][1]),
            'file_type' => $attachmentType['type'],
            'upload_order' => 0,
        ]);
    }

    private function createComments($count)
    {
        $this->command->info("Creating {$count} comments...");
        
        $commentTemplates = [
            "Great work on this! I've been looking for something like this.",
            "This is exactly what I needed. Thanks for sharing!",
            "How did you handle the %s part? I'm struggling with that.",
            "Love this implementation! Have you considered using %s instead?",
            "This is awesome! Can you share the source code?",
            "I tried something similar but ran into issues with %s. How did you solve it?",
            "Fantastic! This gave me some great ideas for my current project.",
            "Nice! I've been working on something similar. What challenges did you face?",
            "This is really helpful. Do you have any documentation for this?",
            "Amazing work! I'm definitely going to try this approach.",
            "Thanks for the detailed explanation. This clarifies a lot of things for me.",
            "I disagree with the %s approach. Have you tried %s?",
            "This is brilliant! How long did it take you to implement this?",
            "Great tutorial! Step %d was particularly helpful.",
            "I'm getting an error with %s. Any suggestions?",
        ];

        $techIssues = ['authentication', 'database connection', 'API integration', 'state management', 'error handling'];
        $alternatives = ['GraphQL', 'REST', 'WebSockets', 'Redis', 'MongoDB', 'PostgreSQL'];

        for ($i = 0; $i < $count; $i++) {
            $post = $this->faker->randomElement($this->posts);
            $template = $this->faker->randomElement($commentTemplates);
            
            $content = sprintf(
                $template,
                $this->faker->randomElement($techIssues),
                $this->faker->randomElement($alternatives),
                rand(1, 10)
            );

            // 20% chance of being a reply to existing comment
            $parentComment = null;
            if ($this->faker->boolean(20) && !empty($this->comments)) {
                $existingComments = array_filter($this->comments, function($comment) use ($post) {
                    return $comment->post_id === $post->id;
                });
                if (!empty($existingComments)) {
                    $parentComment = $this->faker->randomElement($existingComments);
                }
            }

            $comment = Comment::create([
                'post_id' => $post->id,
                'user_id' => $this->faker->randomElement($this->users)->id,
                'parent_comment_id' => $parentComment ? $parentComment->id : null,
                'content' => $content,
                'like_count' => 0,
                'created_at' => $post->created_at->addMinutes(rand(1, 10080)), // Within a week of post
            ]);

            $post->increment('comment_count');
            $this->comments[] = $comment;
        }
    }

    private function createLikes($count)
    {
        $this->command->info("Creating {$count} likes...");
        
        $createdLikes = [];
        
        for ($i = 0; $i < $count; $i++) {
            $user = $this->faker->randomElement($this->users);
            
            // 70% chance for post like, 30% for comment like
            if ($this->faker->boolean(70)) {
                $post = $this->faker->randomElement($this->posts);
                $likeKey = "post_{$post->id}_user_{$user->id}";
                
                if (!in_array($likeKey, $createdLikes)) {
                    Like::create([
                        'user_id' => $user->id,
                        'post_id' => $post->id,
                    ]);
                    $post->increment('like_count');
                    $createdLikes[] = $likeKey;
                }
            } else {
                if (!empty($this->comments)) {
                    $comment = $this->faker->randomElement($this->comments);
                    $likeKey = "comment_{$comment->id}_user_{$user->id}";
                    
                    if (!in_array($likeKey, $createdLikes)) {
                        Like::create([
                            'user_id' => $user->id,
                            'comment_id' => $comment->id,
                        ]);
                        $comment->increment('like_count');
                        $createdLikes[] = $likeKey;
                    }
                }
            }
        }
    }

    private function createSavedPosts($count)
    {
        $this->command->info("Creating {$count} saved posts...");
        
        $createdSaves = [];
        
        for ($i = 0; $i < $count; $i++) {
            $user = $this->faker->randomElement($this->users);
            $post = $this->faker->randomElement($this->posts);
            $saveKey = "user_{$user->id}_post_{$post->id}";
            
            if (!in_array($saveKey, $createdSaves)) {
                SavedPost::create([
                    'user_id' => $user->id,
                    'post_id' => $post->id,
                    'created_at' => $post->created_at->addMinutes(rand(1, 43200)), // Within a month
                ]);
                $createdSaves[] = $saveKey;
            }
        }
    }

    private function createUserFollows($count)
    {
        $this->command->info("Creating {$count} user follows...");
        
        $createdFollows = [];
        
        for ($i = 0; $i < $count; $i++) {
            $follower = $this->faker->randomElement($this->users);
            $following = $this->faker->randomElement($this->users);
            
            // Can't follow yourself
            if ($follower->id !== $following->id) {
                $followKey = "follower_{$follower->id}_following_{$following->id}";
                
                if (!in_array($followKey, $createdFollows)) {
                    UserFollow::create([
                        'follower_id' => $follower->id,
                        'following_id' => $following->id,
                        'created_at' => now()->subDays(rand(1, 180)),
                    ]);
                    $createdFollows[] = $followKey;
                }
            }
        }
    }

    private function createHashtagFollows($count)
    {
        $this->command->info("Creating {$count} hashtag follows...");
        
        $createdFollows = [];
        
        for ($i = 0; $i < $count; $i++) {
            $user = $this->faker->randomElement($this->users);
            $hashtag = $this->faker->randomElement($this->hashtags);
            $followKey = "user_{$user->id}_hashtag_{$hashtag->id}";
            
            if (!in_array($followKey, $createdFollows)) {
                HashtagFollow::create([
                    'user_id' => $user->id,
                    'hashtag_id' => $hashtag->id,
                    'created_at' => now()->subDays(rand(1, 120)),
                ]);
                $createdFollows[] = $followKey;
            }
        }
    }

    private function printStats()
    {
        $this->command->info('📊 Final Statistics:');
        $this->command->info('   - ' . User::count() . ' users');
        $this->command->info('   - ' . Hashtag::count() . ' hashtags');
        $this->command->info('   - ' . Post::count() . ' posts');
        $this->command->info('   - ' . Comment::count() . ' comments');
        $this->command->info('   - ' . Like::count() . ' likes');
        $this->command->info('   - ' . Attachment::count() . ' attachments');
        $this->command->info('   - ' . SavedPost::count() . ' saved posts');
        $this->command->info('   - ' . UserFollow::count() . ' user follows');
        $this->command->info('   - ' . HashtagFollow::count() . ' hashtag follows');
        $this->command->info('🎉 Total records created: ' . (
            User::count() + Hashtag::count() + Post::count() + Comment::count() + 
            Like::count() + Attachment::count() + SavedPost::count() + 
            UserFollow::count() + HashtagFollow::count()
        ));
    }
}