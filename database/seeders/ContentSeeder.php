<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\CommentStatus;
use App\Enums\FaqCategory;
use App\Models\Faq;
use App\Models\Post;
use App\Models\PostCategory;
use App\Models\PostComment;
use App\Models\Tag;
use App\Models\TeamMember;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

/**
 * Initial public website content (team, FAQs, blog posts) and their images.
 * Idempotent: safe to re-run; existing rows are updated by their natural key.
 */
class ContentSeeder extends Seeder
{
    private const MEDIA_SOURCE = __DIR__.'/media';

    private const CONTENT_SOURCE = __DIR__.'/content';

    /** @var list<string> */
    private const RETIRED_FAQ_QUESTIONS = [
        'What services do you provide for students?',
        'How do I choose the right university and course?',
        'What documents are required for a student visa?',
        'Can you help me find scholarships?',
        'When Should I Start My Application?',
        'Do you provide support after receiving the visa?',
        'How do I know which visa program is right for me?',
        'Which countries do you provide immigration services for?',
        'What documents are typically required for a visa application?',
        'How long does the visa application process usually take?',
        'Do you assist with student visa applications?',
        'Can you help with permanent residency applications?',
        'What happens if my visa application is refused?',
        'Do you provide interview preparation support?',
    ];

    public function run(): void
    {
        $this->publishMedia();
        $this->seedTeam();
        $this->seedFaqs();
        $this->seedPosts();
    }

    /** Copy bundled images into the public disk (storage/app/public). */
    private function publishMedia(): void
    {
        $disk = Storage::disk('public');

        foreach (File::allFiles(self::MEDIA_SOURCE) as $file) {
            $target = str_replace('\\', '/', $file->getRelativePathname());

            if (! $disk->exists($target)) {
                $disk->put($target, $file->getContents());
            }
        }
    }

    private function seedTeam(): void
    {
        $socials = [
            ['network' => 'facebook', 'url' => 'https://facebook.com'],
            ['network' => 'instagram', 'url' => 'https://instagram.com'],
            ['network' => 'x', 'url' => 'https://x.com'],
            ['network' => 'youtube', 'url' => 'https://youtube.com'],
        ];

        // Bios appear in the author box on the articles each member writes.
        $members = [
            ['Sarah Mitchell', 'Senior Study Abroad Counselor',
                'Sarah helps students find affordable pathways to top public universities in Germany and Italy, turning a complex process into a clear, step-by-step plan.'],
            ['David Carter', 'Germany Admissions Advisor',
                'David guides students to the right German university and course, from tuition-free programs to applications that present each student at their best.'],
            ['Emily Thompson', 'Visa & Documentation Specialist',
                'Emily reviews every document before submission, making sure student visa and attestation files are complete, consistent and ready for scrutiny.'],
            ['Michael Reynolds', 'Italy Admissions Advisor',
                'Michael helps students secure places at Italy’s leading universities in business, design, architecture and engineering, including scholarship applications.'],
            ['Sophia Martinez', 'Language Programs Coordinator',
                'Sophia leads our German and Italian language courses, from A1 beginners to TestDaF, DSH, CILS and CELI exam preparation.'],
            ['James Wilson', 'IELTS Trainer',
                'James coaches students through all four IELTS modules with structured practice and feedback, helping them reach their target band score.'],
        ];

        foreach ($members as $index => [$name, $role, $bio]) {
            $slug = str($name)->slug()->toString();

            TeamMember::query()->updateOrCreate(['slug' => $slug], [
                'name' => $name,
                'role' => $role,
                'bio' => $bio,
                'photo_path' => "team/{$slug}.webp",
                'socials' => $socials,
                'sort_order' => $index + 1,
                'is_active' => true,
            ]);
        }
    }

    private function seedFaqs(): void
    {
        $studentTags = ['Student Visa Guidance', 'Document Preparation', 'Application Support'];

        // Content from eurogrades.com (home page and the Study in Germany / Italy pages).
        $faqs = [
            FaqCategory::Students->value => [
                ['Which country is best for studying abroad?', 'The best country depends on your budget, field of study, and career goals. Germany is ideal for tuition-free education and technical courses, while Italy offers affordable degrees in arts, business, and design. Both provide world-class education, global exposure, and excellent job opportunities.'],
                ['What is the eligibility for studying abroad?', 'It depends on the country and university you apply to, but common requirements include a high school diploma (or a bachelor’s degree for master’s programs), language test scores such as IELTS, TOEFL or German/Italian tests, a strong academic record, and financial proof for visa purposes.'],
                ['Can I study abroad for free?', 'Yes! Many public universities in countries like Germany offer tuition-free education for international students. You only need to cover your living expenses.'],
                ['How can I get a scholarship to study abroad?', 'Many public universities in Germany and Italy offer scholarships based on academic merit, financial need, or specific programs. You can apply for DAAD scholarships (Germany), Italian Government scholarships (Italy), and university-specific scholarships offered by individual institutions.'],
                ['Do I need to know German or Italian to study abroad?', 'It depends on your program. Many universities offer English-taught courses, especially for master’s programs. Learning the local language still helps with daily life and part-time jobs.'],
                ['What is the procedure for studying abroad?', 'The process is simpler than it looks: choose a university and course, submit your application with the required documents, receive your acceptance letter, apply for your student visa, and arrange accommodation and travel.'],
                ['What exams do I need to take to study abroad?', 'It depends on the university and country. Common tests include IELTS or TOEFL for English, TestDaF or DSH for German, IMAT for medical programs in Italy, and GRE or GMAT for certain master’s programs.'],
                ['Why is studying abroad a good idea?', 'Studying abroad brings global exposure and career opportunities, access to top-ranked universities, better internship and job prospects, and the chance to experience new cultures and languages.'],
                ['What are the requirements for study abroad programs?', 'Requirements depend on the university and course, but usually include academic transcripts and certificates, language proficiency scores (if required), a Statement of Purpose (SOP) and letters of recommendation, and financial proof for your visa application.'],
            ],
            FaqCategory::Immigration->value => [
                ['Why is Germany a great choice for studying abroad?', 'Germany offers world-class education, affordable or tuition-free public universities, and strong career prospects. Its education system focuses on research and practical learning, helping students gain valuable skills in a rich cultural environment.'],
                ['Are public universities in Germany really tuition-free?', 'Yes! Most public universities in Germany charge little to no tuition fees for international students, apart from a small administrative fee of around €100–€500 per semester.'],
                ['What is the cost of living for students in Germany?', 'On average, students need around €600–€1,300 per month depending on the city, covering rent, food, transport, health insurance and other personal costs.'],
                ['What are the post-study work opportunities in Germany?', 'After graduation, students can apply for an 18-month job-seeking visa. Many international graduates secure well-paying jobs, especially in engineering, IT and healthcare.'],
                ['Is studying in Italy expensive?', 'Not at all. Italy is one of Europe’s more affordable destinations: public universities have low tuition fees, living costs are manageable, scholarships support low-income students, and part-time work further reduces financial stress.'],
                ['Can I study in Italy without knowing Italian?', 'Yes. Many universities offer courses in English, especially at postgraduate level. Learning basic Italian still helps in daily life and with part-time jobs.'],
                ['Do Italian universities offer scholarships to international students?', 'Yes, both the Italian government and universities offer scholarships based on merit or financial need, which may cover tuition fees, accommodation or living expenses.'],
                ['Can I work while studying in Germany or Italy?', 'Yes. International students can work up to 20 hours per week during term and full-time during holidays. Knowing German or Italian increases your job opportunities.'],
            ],
        ];

        // Questions seeded by earlier versions of this seeder (theme placeholders), removed by
        // exact match so FAQs added through other tools are never touched.
        Faq::query()->whereIn('question', self::RETIRED_FAQ_QUESTIONS)->delete();

        foreach ($faqs as $category => $items) {
            foreach ($items as $index => [$question, $answer]) {
                Faq::query()->updateOrCreate(
                    ['category' => $category, 'question' => $question],
                    [
                        'answer' => $answer,
                        'tags' => $category === FaqCategory::Students->value ? $studentTags : null,
                        'sort_order' => $index + 1,
                        'is_active' => true,
                    ],
                );
            }
        }
    }

    private function seedPosts(): void
    {
        $categories = collect([
            'Financial Preparation',
            'Immigration Consultants',
            'Immigration Tips',
            'Permanent Residency',
            'Skilled Migration',
            'Student Visa',
            'Visa Rejection',
        ])->mapWithKeys(static fn (string $name) => [
            $name => PostCategory::query()->updateOrCreate(
                ['slug' => str($name)->slug()->toString()],
                ['name' => $name],
            )->id,
        ]);

        // Newest first: each post is published an hour before the previous one.
        $posts = [
            ['top-benefits-of-studying-abroad', 'Top Benefits of Studying Abroad for International Students', 'Financial Preparation',
                'Discover how international education can open doors to global career opportunities, cultural growth and lifelong international networks.'],
            ['common-reasons-visa-applications-get-rejected', 'Common Reasons Visa Applications Get Rejected', 'Visa Rejection',
                'Understand the most frequent causes of visa refusals and learn practical strategies to avoid them with a well-prepared application.'],
            ['how-immigration-consultants-simplify-the-visa-process', 'How Immigration Consultants Simplify the Visa Process', 'Immigration Consultants',
                'Discover how professional immigration consultants help applicants navigate complex procedures, prepare accurate documentation and save valuable time.'],
            ['understanding-permanent-residency-pathways', 'Understanding Permanent Residency Pathways', 'Permanent Residency',
                'Explore different residency programs available around the world and learn what qualifications you need to build a permanent future abroad.'],
            ['the-role-of-financial-preparation-in-visa-success', 'The Role of Financial Preparation in Visa Success', 'Financial Preparation',
                'Explore why financial documentation is critical to many visa applications and how to present proof of funds with confidence.'],
            ['understanding-the-immigration-process-step-by-step', 'Understanding the Immigration Process Step by Step', 'Financial Preparation',
                'Gain a clear understanding of the immigration journey, from eligibility assessment and documentation to approval and arrival.'],
            ['10-immigration-mistakes-that-could-delay-your-visa-approval', '10 Immigration Mistakes That Could Delay Your Visa Approval', 'Immigration Tips',
                'Applying for a visa can be an exciting step toward achieving your goals — avoid these common mistakes to keep your application on track.'],
            ['top-destinations-for-skilled-professionals-in-2026', 'Top Destinations For Skilled Professionals In 2026', 'Skilled Migration',
                'Explore the most popular countries offering excellent career opportunities, work permits, residency pathways and quality of life for skilled talent.'],
            ['student-visa-success-how-to-prepare-a-strong-application', 'Student Visa Success: How To Prepare A Strong Application', 'Student Visa',
                'Discover essential tips for choosing the right university, preparing documentation, writing compelling statements and acing your visa interview.'],
        ];

        // Each topic is written by the specialist for it.
        $authorFor = [
            'Financial Preparation' => 'sarah-mitchell',
            'Immigration Consultants' => 'sarah-mitchell',
            'Immigration Tips' => 'emily-thompson',
            'Permanent Residency' => 'sophia-martinez',
            'Skilled Migration' => 'michael-reynolds',
            'Student Visa' => 'david-carter',
            'Visa Rejection' => 'emily-thompson',
        ];
        $authors = TeamMember::query()->pluck('id', 'slug');

        $tagIds = collect(['Documentation', 'Immigration Tips', 'Visa Application', 'Visa Approval', 'Visa Success'])
            ->map(static fn (string $name) => Tag::query()->updateOrCreate(
                ['slug' => str($name)->slug()->toString()],
                ['name' => $name],
            )->id)
            ->all();

        // Newest six on June 18, the rest on June 17 (midday UTC, so the date reads the same in every time zone).
        $publishedAt = static fn (int $index): CarbonImmutable => $index < 6
            ? CarbonImmutable::parse('2026-06-18 12:00:00')->subMinutes($index * 10)
            : CarbonImmutable::parse('2026-06-17 12:00:00')->subMinutes(($index - 6) * 10);

        foreach ($posts as $index => [$slug, $title, $category, $excerpt]) {
            $post = Post::query()->updateOrCreate(['slug' => $slug], [
                'title' => $title,
                'post_category_id' => $categories[$category],
                'author_id' => $authors[$authorFor[$category]] ?? null,
                'excerpt' => $excerpt,
                'body' => File::get(self::CONTENT_SOURCE."/posts/{$slug}.html"),
                'image_path' => "blog/{$slug}.webp",
                'published_at' => $publishedAt($index),
            ]);

            $post->tags()->sync($tagIds);
        }

        $this->seedComments();
    }

    private function seedComments(): void
    {
        $post = Post::query()->where('slug', '10-immigration-mistakes-that-could-delay-your-visa-approval')->firstOrFail();
        $postedAt = CarbonImmutable::parse('2026-06-18 13:18:00');

        $comment = PostComment::query()->firstOrNew([
            'post_id' => $post->id,
            'author_email' => 'sophia.carter@example.com',
        ]);

        $comment->forceFill([
            'author_name' => 'Sophia Carter',
            'body' => 'The visa application process seemed overwhelming at first, but the professional guidance and personalized support made everything much easier.',
            'status' => CommentStatus::Approved,
            'approved_at' => $postedAt,
            'created_at' => $postedAt,
            'updated_at' => $postedAt,
        ])->save();
    }
}
