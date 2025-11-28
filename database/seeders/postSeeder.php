<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Post;

class postSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $posts = [
            "Manager", "Developer", "Designer", "Accountant", "HR Officer",
            "Marketing Executive", "Sales Executive", "Support Specialist",
            "Project Coordinator", "Team Lead", "Intern", "Consultant",
            "Analyst", "Administrator", "Engineer", "Technician",
            "Receptionist", "Supervisor", "Director", "Coordinator",
            "Researcher", "Trainer", "Planner", "Auditor",
            "Executive Officer", "Software Tester", "Product Owner",
            "Business Analyst", "Creative Director", "Operations Manager",
            "Customer Service Officer", "Data Entry Clerk", "Quality Analyst",
            "UX Designer", "UI Designer", "Content Writer", "Social Media Manager",
            "SEO Specialist", "IT Support", "Database Administrator", "Network Engineer",
            "Legal Advisor", "PR Officer", "Finance Manager", "Strategy Consultant",
            "Logistics Officer", "Procurement Officer", "Event Manager", "Editor",
            "Copywriter", "Digital Marketing Officer", "Brand Manager", "Product Manager"
        ];

        foreach ($posts as $postName) {
            Post::create(['name' => $postName]);
        }
    }
}
