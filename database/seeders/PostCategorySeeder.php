<?php

namespace Database\Seeders;

use App\Models\PostCategory;
use Illuminate\Database\Seeder;

class PostCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Article',
                'slug' => 'article',
                'description' => 'General articles and blog posts',
            ],
            [
                'name' => 'News',
                'slug' => 'news',
                'description' => 'Latest news and updates',
            ],
            [
                'name' => 'Match Report',
                'slug' => 'match-report',
                'description' => 'Arsenal match reports and analysis',
            ],
        ];

        foreach ($categories as $category) {
            PostCategory::updateOrCreate(
                ['slug' => $category['slug']],
                $category
            );
        }
    }
}
