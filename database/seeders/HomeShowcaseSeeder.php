<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

use App\Models\Call;
use App\Models\Post;
use App\Models\Partner;
use App\Models\Organization;
use App\Models\Program;
use App\Models\Specialization;
use App\Models\User;

/**
 * Сидер демонстраційного контенту головної сторінки: реалістичні Calls, Posts
 * (статті / FAQ / історії успіху) та Partners замість lorem-ipsum даних фабрик.
 * Залежить від ProgramSeeder, SpecializationSeeder, UserSeeder, CallSeeder.
 */
class HomeShowcaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedCalls();
        $this->seedPosts();
        $this->seedPartners();
    }

    /**
     * Залишає відкритою офіційну весняну заявку та додає 3 нові реалістичні відкриті Calls.
     */
    private function seedCalls(): void
    {
        $openTitles = [
            'Official NTI Spring Call 2026',
            'Enterprise Challenge: AI-Powered Logistics Assistant',
            'Green Campus Innovation Grant',
            'Mobile App Accelerator: Student Life Edition',
        ];

        // Закриваємо всі інші наявні Calls (фабричні, з випадковим статусом),
        // щоб на головній сторінці залишився тільки куратований набір.
        Call::whereNotIn('title', $openTitles)
            ->where('status', '!=', Call::STATUS_CLOSED)
            ->update(['status' => Call::STATUS_CLOSED]);

        $grantProgram = Program::where('type', 'grant')->first();
        $practiceProgram = Program::where('type', 'practice')->first() ?? $grantProgram;

        $bySlug = fn (string $slug) => Specialization::where('slug', $slug)->first();

        $calls = [
            [
                'program_id'  => $practiceProgram->id,
                'title'       => 'Enterprise Challenge: AI-Powered Logistics Assistant',
                'description' => "Our enterprise partner is looking for a student team to design and prototype an AI-powered assistant that optimizes last-mile delivery routes and predicts shipment delays. You'll work with a real anonymized logistics dataset, get weekly check-ins with the partner's engineering team, and present a working MVP at the final demo day. A strong fit for teams interested in machine learning, optimization algorithms, and applied data science.",
                'deadline'    => now()->addWeeks(4),
                'status'      => Call::STATUS_OPEN,
                'budget'      => '80000.00',
                'specs'       => [$bySlug('ai-data-technologies'), $bySlug('software-development')],
            ],
            [
                'program_id'  => $grantProgram->id,
                'title'       => 'Green Campus Innovation Grant',
                'description' => 'NTI is funding student-led projects that make the UKF campus more sustainable. We welcome ideas such as IoT sensor networks for energy and water monitoring, smart waste-sorting systems, green-mobility apps, and dashboards that help students and staff track the campus carbon footprint. Selected teams receive grant funding, lab access, and mentoring from the Faculty of Natural Sciences.',
                'deadline'    => now()->addWeeks(10),
                'status'      => Call::STATUS_OPEN,
                'budget'      => '35000.00',
                'specs'       => [$bySlug('iot-embedded-systems'), $bySlug('web-applications')],
            ],
            [
                'program_id'  => $grantProgram->id,
                'title'       => 'Mobile App Accelerator: Student Life Edition',
                'description' => "Build the app you wish existed as a student. We're looking for mobile-first projects that improve everyday campus life — timetable and exam planners, club and event finders, campus navigation, or peer study-group matching. The strongest teams get a development grant, UX mentoring, and the chance to pilot their app with real UKF students next semester.",
                'deadline'    => now()->addWeeks(6),
                'status'      => Call::STATUS_OPEN,
                'budget'      => '45000.00',
                'specs'       => [$bySlug('software-development'), $bySlug('web-applications')],
            ],
        ];

        foreach ($calls as $data) {
            $specs = array_filter($data['specs']);
            unset($data['specs']);

            $call = Call::firstOrCreate(['title' => $data['title']], $data);

            if (!empty($specs)) {
                $call->specializations()->syncWithoutDetaching(
                    collect($specs)->pluck('id')->toArray()
                );
            }
        }
    }

    /**
     * Перезаписує посади 3 статтями, 5 FAQ та 3 історіями успіху з реальним текстом.
     */
    private function seedPosts(): void
    {
        Schema::disableForeignKeyConstraints();
        Post::truncate();
        Schema::enableForeignKeyConstraints();

        $editor = User::where('email', 'editor@nti.sk')->first();
        $teamLeader = User::where('email', 'leader@student.nti.sk')->first();
        $student = User::where('email', 'student@student.nti.sk')->first();
        $mentor = User::where('email', 'mentor@nti.sk')->first();

        $articles = [
            [
                'title' => 'NTI Launches Spring 2026 Incubation Cycle',
                'excerpt' => 'Applications for the Spring 2026 cycle are now open, with three new calls covering AI, sustainability, and mobile development.',
                'content' => "<p>The Nitra Technological Incubator is officially opening its Spring 2026 incubation cycle. Students and teams from across UKF are invited to apply to the <strong>Official NTI Spring Call 2026</strong> as well as three brand-new enterprise and grant opportunities published this week.</p><p>This cycle introduces a streamlined application process, faster review times, and an expanded mentor pool drawn from partner companies across Slovakia. Information sessions will be held on campus throughout the month — keep an eye on the Open Calls section for deadlines.</p><p>Whether you have a fully formed product idea or just a problem worth solving, our team is ready to help you take the first step.</p>",
                'published_days_ago' => 2,
            ],
            [
                'title' => 'Meet the Mentors: Industry Experts Joining NTI This Semester',
                'excerpt' => "A growing group of engineers, product managers, and founders are joining NTI as mentors for the new incubation cycle.",
                'content' => "<p>One of the things that makes NTI different is direct access to people who have shipped real products. This semester we're welcoming a new group of mentors with backgrounds spanning fintech, robotics, cloud infrastructure, and UX design.</p><p>Mentors meet with teams on a weekly basis, offering feedback on technical architecture, product direction, and presentation skills ahead of milestone reviews. Several of this semester's mentors are alumni of NTI programs themselves, now working at partner companies.</p><p>If you're part of an active team, your mentor assignment will appear on your project dashboard within the first two weeks of the cycle.</p>",
                'published_days_ago' => 6,
            ],
            [
                'title' => 'Campus Tech Lab Receives New AI Research Equipment',
                'excerpt' => 'UKF has upgraded the shared tech lab with new GPU workstations and IoT prototyping kits available to all incubation teams.',
                'content' => "<p>Thanks to a university infrastructure investment, the shared tech lab on campus now includes several new GPU-equipped workstations suitable for training and fine-tuning machine learning models, along with a refreshed set of IoT prototyping kits — microcontrollers, sensors, and 3D-printing access.</p><p>Any student participating in an active NTI program can book lab time through the facilities portal. Priority slots are reserved for teams working on AI, data, and IoT-related calls, including the new Green Campus Innovation Grant.</p><p>Lab orientation sessions are held every Tuesday afternoon — no prior booking required for the first visit.</p>",
                'published_days_ago' => 10,
            ],
        ];

        foreach ($articles as $a) {
            Post::create([
                'author_id' => $editor?->id,
                'type' => Post::TYPE_ARTICLE,
                'title' => $a['title'],
                'slug' => Str::slug($a['title']),
                'excerpt' => $a['excerpt'],
                'content' => $a['content'],
                'featured_image' => null,
                'meta_title' => $a['title'],
                'meta_description' => $a['excerpt'],
                'og_image' => null,
                'is_published' => true,
                'published_at' => now()->subDays($a['published_days_ago']),
            ]);
        }

        $faqs = [
            [
                'q' => 'Who can apply to NTI programs?',
                'a' => '<p>Any current student of Constantine the Philosopher University in Nitra can apply, regardless of faculty or year of study. Teams typically have 2-5 members, but individual applications are also accepted — see the next question for details.</p>',
            ],
            [
                'q' => 'What is the difference between Program A and Program B?',
                'a' => "<p><strong>Program A (Start-up Core)</strong> is for teams with their own idea who want to develop it from scratch into a working prototype, with access to university grants and lab facilities.</p><p><strong>Program B (Enterprise Partnering)</strong> connects students with real technological challenges issued by partner companies, often including project funding and a fast-track to employment.</p>",
            ],
            [
                'q' => 'How long does the incubation process take?',
                'a' => '<p>Most incubation cycles run for one academic semester (roughly 12-16 weeks), structured around milestones: kickoff and planning, development sprints with mentor consultations, a mid-cycle review, and a final evaluation where teams present their MVP to a board.</p>',
            ],
            [
                'q' => 'Do I need a team to apply, or can I apply individually?',
                'a' => '<p>You can apply individually. Many participants join without a pre-formed team and are matched with others through our team-formation tools based on complementary skills and shared interest in a specialization.</p>',
            ],
            [
                'q' => 'What kind of support do mentors provide?',
                'a' => '<p>Mentors offer weekly consultations covering technical architecture reviews, product and business model feedback, and coaching for milestone presentations. They are industry professionals from partner companies or experienced NTI alumni.</p>',
            ],
        ];

        foreach ($faqs as $i => $f) {
            Post::create([
                'author_id' => $editor?->id,
                'type' => Post::TYPE_FAQ,
                'title' => $f['q'],
                'slug' => Str::slug($f['q']),
                'excerpt' => null,
                'content' => $f['a'],
                'featured_image' => null,
                'meta_title' => $f['q'],
                'meta_description' => null,
                'og_image' => null,
                'is_published' => true,
                'published_at' => now()->subDays($i + 1),
            ]);
        }

        $stories = [
            [
                'author' => $teamLeader,
                'title' => 'From Lecture Hall to Launch: How Team Vega Built a Logistics Platform for a Local Retailer',
                'excerpt' => 'A team of four computer science students partnered with a regional retailer to replace a spreadsheet-based inventory process with a live web platform.',
                'content' => "<p>When our team joined Program B, we were paired with a regional retail chain struggling to track inventory across five stores using shared spreadsheets. Over one semester, we built a web-based inventory and restocking platform with real-time stock alerts and a simple dashboard for store managers.</p><p>The hardest part wasn't the code — it was learning to translate vague business pain points into a product backlog, and to demo working software every two weeks instead of disappearing for months. Our mentor, an engineering lead at the partner company, pushed us to cut scope ruthlessly and ship something real.</p><p>By the final evaluation, the platform was running a pilot in one store, and two of our teammates were offered part-time roles to continue the rollout.</p>",
                'published_days_ago' => 14,
            ],
            [
                'author' => $student,
                'title' => 'Building an Accessibility-First Mobile App: My NTI Journey',
                'excerpt' => "I joined NTI with one goal: build a navigation app for visually impaired students. Here's what I learned along the way.",
                'content' => "<p>I came into Program A with a single idea: a campus navigation app with voice guidance and high-contrast modes for visually impaired students. I had no prior mobile development experience, which felt intimidating during the first information session.</p><p>The structured milestones helped a lot — instead of trying to build everything at once, I focused first on a working prototype for a single building, then expanded. Weekly mentor sessions kept me honest about what was realistic for one semester.</p><p>By the final demo day, the app supported voice-guided routes for three campus buildings, and I received a certificate along with feedback that's shaping the next version, which I'm now building with two new teammates.</p>",
                'published_days_ago' => 21,
            ],
            [
                'author' => $mentor,
                'title' => 'Mentoring the Next Generation: Lessons from Three Incubation Cycles',
                'excerpt' => 'After mentoring more than a dozen student teams, here are the patterns that consistently separate teams that ship from teams that stall.',
                'content' => "<p>Over three incubation cycles at NTI, I've mentored teams building everything from logistics dashboards to accessibility apps. The teams that consistently ship working products share a few habits: they talk to real users early, even before the product looks good; they treat milestone reviews as checkpoints rather than deadlines to dread; and they're willing to cut features that don't serve their core use case.</p><p>The teams that stall usually spend too long polishing a feature nobody asked for, or avoid showing unfinished work until it's 'ready' — which often means never.</p><p>If there's one piece of advice I give every new team: bring your roughest prototype to your first mentor session. The earlier we can poke holes in an idea, the more time you have to fix it.</p>",
                'published_days_ago' => 28,
            ],
        ];

        foreach ($stories as $s) {
            Post::create([
                'author_id' => $s['author']?->id,
                'type' => Post::TYPE_SUCCESS_STORY,
                'title' => $s['title'],
                'slug' => Str::slug($s['title']),
                'excerpt' => $s['excerpt'],
                'content' => $s['content'],
                'featured_image' => null,
                'meta_title' => $s['title'],
                'meta_description' => $s['excerpt'],
                'og_image' => null,
                'is_published' => true,
                'published_at' => now()->subDays($s['published_days_ago']),
            ]);
        }
    }

    /**
     * Перезаписує партнерів 8 фіктивними, але реалістичними організаціями (без зображень).
     */
    private function seedPartners(): void
    {
        Schema::disableForeignKeyConstraints();
        Partner::truncate();
        Schema::enableForeignKeyConstraints();

        $partners = [
            ['name' => 'Nitra Software House', 'sector' => 'Software Development', 'website' => 'https://www.nitrasoftwarehouse.example', 'featured' => true],
            ['name' => 'Dunaj Digital Solutions', 'sector' => 'Digital Consulting', 'website' => 'https://www.dunajdigital.example', 'featured' => true],
            ['name' => 'VegaTech Systems', 'sector' => 'Enterprise Software', 'website' => 'https://www.vegatechsystems.example', 'featured' => true],
            ['name' => 'CloudPeak Slovakia', 'sector' => 'Cloud Infrastructure', 'website' => 'https://www.cloudpeak.example', 'featured' => true],
            ['name' => 'BrightCode Studios', 'sector' => 'Mobile Development', 'website' => 'https://www.brightcodestudios.example', 'featured' => false],
            ['name' => 'Slovak Robotics Lab', 'sector' => 'Robotics & IoT', 'website' => 'https://www.slovakroboticslab.example', 'featured' => false],
            ['name' => 'FinTech Nitra', 'sector' => 'Financial Technology', 'website' => 'https://www.fintechnitra.example', 'featured' => false],
            ['name' => 'GreenWave Energy', 'sector' => 'Renewable Energy', 'website' => 'https://www.greenwaveenergy.example', 'featured' => false],
        ];

        foreach ($partners as $p) {
            $organization = Organization::firstOrCreate(
                ['name' => $p['name']],
                [
                    'sector' => $p['sector'],
                    'website_link' => $p['website'],
                    'description' => "{$p['name']} is a partner of the Nitra Technological Incubator, collaborating on student projects in {$p['sector']}.",
                    'status' => Organization::STATUS_ACTIVE,
                ]
            );

            Partner::create([
                'organization_id' => $organization->id,
                'logo_path' => null,
                'website_link' => $p['website'],
                'is_featured' => $p['featured'],
            ]);
        }
    }
}
