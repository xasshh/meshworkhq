<?php

namespace Database\Seeders;

use App\Models\Skill;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class SkillSeeder extends Seeder
{
    public function run(): void
    {
        $skills = [
            // Creative
            ['category' => 'creative', 'name' => 'Brand Strategy'],
            ['category' => 'creative', 'name' => 'Graphic Design'],
            ['category' => 'creative', 'name' => 'Logo & Identity'],
            ['category' => 'creative', 'name' => 'Illustration'],
            ['category' => 'creative', 'name' => 'Motion Graphics'],
            ['category' => 'creative', 'name' => 'Photography'],
            ['category' => 'creative', 'name' => 'Videography'],
            ['category' => 'creative', 'name' => 'Copywriting'],
            ['category' => 'creative', 'name' => 'Content Strategy'],
            ['category' => 'creative', 'name' => 'UX/UI Design'],

            // Digital
            ['category' => 'digital', 'name' => 'Web Development'],
            ['category' => 'digital', 'name' => 'Mobile App Development'],
            ['category' => 'digital', 'name' => 'Social Media Management'],
            ['category' => 'digital', 'name' => 'SEO & SEM'],
            ['category' => 'digital', 'name' => 'Paid Advertising'],
            ['category' => 'digital', 'name' => 'Email Marketing'],
            ['category' => 'digital', 'name' => 'Data Analytics'],
            ['category' => 'digital', 'name' => 'E-commerce'],
            ['category' => 'digital', 'name' => 'Influencer Marketing'],
            ['category' => 'digital', 'name' => 'Community Management'],

            // Events
            ['category' => 'events', 'name' => 'Event Planning'],
            ['category' => 'events', 'name' => 'Event Production'],
            ['category' => 'events', 'name' => 'MC / Host'],
            ['category' => 'events', 'name' => 'DJ / Music'],
            ['category' => 'events', 'name' => 'Catering & Food'],
            ['category' => 'events', 'name' => 'Decoration & Styling'],
            ['category' => 'events', 'name' => 'Live Entertainment'],
            ['category' => 'events', 'name' => 'Venue Management'],

            // PR & Comms
            ['category' => 'pr_comms', 'name' => 'Public Relations'],
            ['category' => 'pr_comms', 'name' => 'Media Relations'],
            ['category' => 'pr_comms', 'name' => 'Crisis Communications'],
            ['category' => 'pr_comms', 'name' => 'Influencer Relations'],
            ['category' => 'pr_comms', 'name' => 'Press & Editorial'],

            // Business
            ['category' => 'business', 'name' => 'Business Development'],
            ['category' => 'business', 'name' => 'Market Research'],
            ['category' => 'business', 'name' => 'Strategy Consulting'],
            ['category' => 'business', 'name' => 'Financial Modelling'],
            ['category' => 'business', 'name' => 'Legal & Compliance'],
            ['category' => 'business', 'name' => 'HR & Recruitment'],
        ];

        foreach ($skills as $sort => $skill) {
            Skill::firstOrCreate(
                ['slug' => Str::slug($skill['name'])],
                [
                    'name' => $skill['name'],
                    'category' => $skill['category'],
                    'is_active' => true,
                    'sort_order' => $sort + 1,
                ]
            );
        }
    }
}
