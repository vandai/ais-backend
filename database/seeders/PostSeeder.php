<?php

namespace Database\Seeders;

use App\Models\Post;
use App\Models\PostCategory;
use App\Models\User;
use Illuminate\Database\Seeder;

class PostSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $author = User::where('email', 'admin@admin.com')->first();
        if (!$author) {
            $this->command->warn('Admin user not found. Please run UserSeeder first.');
            return;
        }

        $posts = [
            [
                'title' => 'The History of Arsenal Indonesia Supporters',
                'slug' => 'the-history-of-arsenal-indonesia-supporters',
                'excerpt' => 'A deep dive into the origins and growth of Arsenal Indonesia Supporters community since its founding.',
                'contents' => '<h2>The Beginning</h2><p>Arsenal Indonesia Supporters (AIS) was founded by a group of passionate Arsenal fans who wanted to create a community for fellow Gooners in Indonesia.</p><h2>Growth Over the Years</h2><p>From humble beginnings with just a handful of members, AIS has grown to become one of the largest Arsenal supporter groups in Southeast Asia.</p><p>Today, we have chapters in major cities across Indonesia, organizing match screenings, community events, and charity activities.</p>',
                'status' => 'published',
                'categories' => ['article'],
            ],
            [
                'title' => 'AIS Annual Gathering 2026 Announced',
                'slug' => 'ais-annual-gathering-2026-announced',
                'excerpt' => 'Mark your calendars! The biggest Arsenal Indonesia Supporters event of the year is coming this March.',
                'contents' => '<h2>Save the Date</h2><p>We are excited to announce that the AIS Annual Gathering 2026 will be held on March 15-16, 2026 in Jakarta.</p><h2>What to Expect</h2><ul><li>Meet and greet with fellow Gooners</li><li>Live match screening</li><li>Exclusive merchandise</li><li>Special guest appearances</li></ul><p>Registration will open on February 1st. Stay tuned for more details!</p>',
                'status' => 'published',
                'categories' => ['news'],
            ],
            [
                'title' => 'Arsenal 3-1 Chelsea: Gunners Dominate London Derby',
                'slug' => 'arsenal-3-1-chelsea-gunners-dominate-london-derby',
                'excerpt' => 'Arsenal secured a convincing victory over Chelsea at Emirates Stadium with goals from Saka, Havertz, and Martinelli.',
                'contents' => '<h2>Match Summary</h2><p>Arsenal delivered a masterclass performance against Chelsea, dominating the London derby from start to finish.</p><h2>First Half</h2><p>Bukayo Saka opened the scoring in the 23rd minute with a stunning strike from outside the box. The Gunners continued to press and Kai Havertz doubled the lead just before halftime.</p><h2>Second Half</h2><p>Chelsea pulled one back through Cole Palmer, but Gabriel Martinelli sealed the victory with a clinical finish in the 78th minute.</p><h2>Player Ratings</h2><p>Man of the Match: Bukayo Saka (9/10)</p>',
                'status' => 'published',
                'categories' => ['match-report'],
            ],
            [
                'title' => 'New Membership Benefits for 2026',
                'slug' => 'new-membership-benefits-for-2026',
                'excerpt' => 'Exciting updates to our membership program with exclusive perks for all AIS members.',
                'contents' => '<h2>Enhanced Membership Program</h2><p>We are thrilled to announce significant upgrades to our membership benefits for 2026.</p><h2>New Benefits Include:</h2><ul><li>Priority access to match screening events</li><li>Exclusive member-only merchandise discounts</li><li>Early bird registration for gatherings</li><li>Digital membership card</li><li>Monthly newsletter with exclusive content</li></ul><p>Current members will automatically receive these benefits. New members can register through our website.</p>',
                'status' => 'published',
                'categories' => ['news', 'article'],
            ],
            [
                'title' => 'Arsenal 2-0 Manchester City: Title Race Heats Up',
                'slug' => 'arsenal-2-0-manchester-city-title-race-heats-up',
                'excerpt' => 'A crucial victory against the defending champions puts Arsenal in pole position for the Premier League title.',
                'contents' => '<h2>The Biggest Game of the Season</h2><p>In what many called the title decider, Arsenal rose to the occasion with a stunning performance against Manchester City.</p><h2>Tactical Masterclass</h2><p>Mikel Arteta set up his team perfectly, pressing high and exploiting the spaces left by City fullbacks.</p><h2>Goals</h2><p>Martin Odegaard opened the scoring with a brilliant team goal in the 34th minute. William Saliba headed home from a corner to make it 2-0 in the second half.</p><h2>What This Means</h2><p>Arsenal now sit 5 points clear at the top of the table with 10 games remaining.</p>',
                'status' => 'published',
                'categories' => ['match-report'],
            ],
        ];

        foreach ($posts as $postData) {
            $categories = $postData['categories'];
            unset($postData['categories']);

            $postData['author_id'] = $author->id;

            $post = Post::updateOrCreate(
                ['slug' => $postData['slug']],
                $postData
            );

            // Attach categories
            $categoryIds = PostCategory::whereIn('slug', $categories)->pluck('id')->toArray();
            $post->categories()->sync($categoryIds);
        }
    }
}
