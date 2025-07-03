<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Page;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CMSSeeder extends Seeder
{
    const ADMIN = '1';

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create categories
        $this->createCategories();

        // Create posts for each role
        $this->createPosts();

        // Create pages for each role
        $this->createPages();
    }

    private function createCategories(): void
    {
        $categories = [
            'Environment',
            'Sports'
        ];

        foreach ($categories as $value) {
            Category::create([
                'name' => $value
            ]);
        }
    }

    private function createPosts()
    {
        $postTitles = [
            'environment' => [
                'Climate Change Impact on Ocean Ecosystems',
                'Renewable Energy Solutions for Rural Communities',
                'The Future of Sustainable Agriculture',
                'Plastic Pollution in Marine Life',
                'Green Technology Innovations in 2025',
                'Carbon Footprint Reduction Strategies',
                'Wildlife Conservation Success Stories',
                'Sustainable Urban Planning Initiatives',
                'Environmental Policy Changes This Year',
                'Clean Water Access in Developing Nations'
            ],
            'sports' => [
                'World Cup Qualifiers Update',
                'Olympic Training Regimens Revealed',
                'Rise of Women in Professional Sports',
                'Technology in Modern Athletics',
                'Sports Nutrition and Performance',
                'Injury Prevention in Contact Sports',
                'Youth Sports Development Programs',
                'Professional Athletes Mental Health',
                'Extreme Sports Safety Measures',
                'Sports Economics and Team Valuations'
            ]
        ];

        $users = User::all();
        $categories = Category::all();
        foreach ($users as $user) {
            // Create 5 posts for each category (10 total per user)
            foreach (['environment', 'sports'] as $categoryKey) {
                for ($i = 0; $i < 5; $i++) {
                    $title = $postTitles[$categoryKey][$i] ?? "Sample {$categoryKey} post " . ($i + 1);

                    $post = Post::create([
                        'title' => $title,
                        'content' => $this->generatePostContent($title, $categoryKey),
                        'excerpt' => $this->generateExcerpt($title),
                        'status' => $this->getRandomStatus(),
                        'type' => Post::TYPE,
                        'author_id' => $user->id,
                        'parent' => 0,
                        'created_at' => now()->subDays(rand(1, 30)),
                        'updated_at' => now()->subDays(rand(0, 7)),
                    ]);

                    // Attach category
                    $category = Category::where('slug', $categoryKey)->first();
                    $post->categories()->attach($category->id);
                }
            }
        }
    }

    private function generatePostContent(string $title, string $category): string
    {
        $templates = [
            'environment' => [
                "In today's rapidly changing world, {$title} has become a critical issue that demands our immediate attention. Environmental scientists and researchers worldwide are working tirelessly to understand and address these challenges.",
                "Recent studies have shown significant developments in this area. The implications for future generations are profound, and action must be taken now.",
                "Through innovative approaches and sustainable practices, we can make a meaningful difference. Community involvement and policy changes are essential components of any successful environmental initiative."
            ],
            'sports' => [
                "The world of sports continues to evolve, and {$title} represents an exciting development in athletic competition. Athletes are pushing boundaries and achieving new levels of performance.",
                "Training methodologies have advanced significantly, incorporating cutting-edge technology and scientific research. The dedication and commitment of today's athletes is truly inspiring.",
                "Fans around the world are witnessing history in the making. The passion and energy surrounding these events create unforgettable moments that unite people across cultures."
            ]
        ];

        return implode("\n\n", $templates[$category] ?? $templates['environment']);
    }

    private function generateExcerpt(string $title): string
    {
        return "This article explores {$title} and provides insights into the latest developments, trends, and implications for the future.";
    }

    private function getRandomStatus(): string
    {
        $statuses = [Post::PUBLISHED, Post::DRAFT];
        $weights = [80, 20]; // 80% published, 20% draft

        return rand(1, 100) <= $weights[0] ? $statuses[0] : $statuses[1];
    }

    private function createPages()
    {
        $pageData = [
            [
                'title' => 'About Us',
                'content' => trim($this->getAboutPageContent()),
            ],
            [
                'title' => 'Contact Us',
                'content' => trim($this->getContactPageContent()),
            ]
        ];

        $user = User::whereHas('roles', function ($query) {
            $query->where('id', '=', self::ADMIN);
        })->first();

        foreach ($pageData as $data) {
            Page::create([
                'title' => $data['title'],
                'content' => $data['content'],
                'excerpt' => Str::limit(strip_tags($data['content']), 150),
                'status' => Post::PUBLISHED,
                'type' => Page::TYPE,
                'author_id' => $user->id,
                'parent' => 0,
                'created_at' => now()->subDays(rand(10, 60)),
                'updated_at' => now()->subDays(rand(1, 10)),
            ]);
        }
    }

    private function getAboutPageContent(): string
    {
        return "
            <h2>Welcome to Our Platform</h2>
            <p>We are dedicated to providing high-quality content across various topics including environmental issues and sports. Our team of experienced writers and editors work tirelessly to bring you accurate, engaging, and informative articles.</p>

            <h3>Our Mission</h3>
            <p>To inform, educate, and inspire our readers through compelling storytelling and in-depth analysis of the topics that matter most in today's world.</p>

            <h3>Our Team</h3>
            <p>Our diverse team includes environmental scientists, sports analysts, and experienced journalists who are passionate about their respective fields.</p>
        ";
    }

    private function getContactPageContent(): string
    {
        return "
            <h2>Get in Touch</h2>
            <p>We'd love to hear from you! Whether you have questions, suggestions, or would like to contribute to our platform, don't hesitate to reach out.</p>

            <h3>Contact Information</h3>
            <ul>
                <li><strong>Email:</strong> contact@example.com</li>
                <li><strong>Phone:</strong> +1 (555) 123-4567</li>
                <li><strong>Address:</strong> 123 Main Street, City, State 12345</li>
            </ul>

            <h3>Editorial Submissions</h3>
            <p>If you're interested in contributing articles or have story ideas, please send your proposals to editorial@example.com</p>
        ";
    }
}
