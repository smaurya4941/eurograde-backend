<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\CommentStatus;
use App\Enums\FaqCategory;
use App\Enums\TestimonialCategory;
use App\Enums\UniversityType;
use App\Models\Country;
use App\Models\Faq;
use App\Models\Post;
use App\Models\PostCategory;
use App\Models\PostComment;
use App\Models\Service;
use App\Models\SiteSetting;
use App\Models\SiteStat;
use App\Models\SuccessStory;
use App\Models\Tag;
use App\Models\TeamMember;
use App\Models\Testimonial;
use App\Models\University;
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
        $this->seedSiteSettings();
        $this->seedTeam();
        $this->seedTestimonials();
        $this->seedSuccessStories();
        $this->seedCountries();
        $this->seedServices();
        $this->seedServiceDetails();
        $this->seedUniversities();
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

    /**
     * Contact details (from eurogrades.com), social links (placeholders until real profiles are
     * provided) and the headline counters. Existing values are kept, so edits made later survive re-seeding.
     */
    private function seedSiteSettings(): void
    {
        $settings = [
            'contact.phone' => '+44 20 4154 2541',
            'contact.phone_alt' => '+44 20 4154 2542',
            'contact.email' => 'contact@eurogrades.com',
            'contact.address' => '128A, Sewak Park, Uttam Nagar, New Delhi 110059, India',
            'contact.hours' => 'Mon – Fri: 9:00 AM – 6:00 PM',
            'contact.hours_weekend' => 'Saturday: 9:00 AM – 4:00 PM',
            'social.facebook' => 'https://facebook.com',
            'social.instagram' => 'https://instagram.com',
            'social.x' => 'https://x.com',
            'social.youtube' => 'https://youtube.com',
        ];

        foreach ($settings as $key => $value) {
            SiteSetting::query()->firstOrCreate(['key' => $key], ['value' => $value, 'is_public' => true]);
        }

        // [key, label, value, suffix]
        $stats = [
            ['counselors', 'Expert Counselors', 80, '+'],
            ['students', 'Students Studying Abroad', 300, '+'],
            ['universities', 'Global Partner Universities', 50, '+'],
        ];

        foreach ($stats as $index => [$key, $label, $value, $suffix]) {
            SiteStat::query()->firstOrCreate(['key' => $key], [
                'label' => $label, 'value' => $value, 'suffix' => $suffix, 'sort_order' => $index + 1, 'is_active' => true,
            ]);
        }
    }

    /** What EuroGrades offers (copy adapted from eurogrades.com). */
    private function seedServices(): void
    {
        $countries = Country::query()->pluck('id', 'slug');

        // [slug, title, excerpt, country slug]
        $services = [
            ['study-in-germany', 'Study in Germany', 'World-class education with little to no tuition fees at Germany’s public universities, plus strong career prospects…', 'germany'],
            ['study-in-italy', 'Study in Italy', 'High-quality, affordable degrees in business, fashion, design, architecture and engineering at Italy’s top universities…', 'italy'],
            ['ielts-preparation', 'IELTS Preparation', 'Achieve your target band score with structured, result-driven coaching across Listening, Reading, Writing and Speaking…', null],
            ['german-language-course', 'German Language Course', 'Affordable A1 to B2 German training with certified trainers, live classes and TestDaF and DSH preparation…', null],
            ['italian-language-course', 'Italian Language Course', 'Structured A1 to C1 Italian training for students and professionals, including CILS and CELI exam preparation…', null],
            ['attestation-services', 'Attestation Services', 'MEA apostille and attestation, State HRD authentication, embassy legalisation and document translation…', null],
        ];

        foreach ($services as $index => [$slug, $title, $excerpt, $country]) {
            Service::withTrashed()->updateOrCreate(['slug' => $slug], [
                'title' => $title,
                'excerpt' => $excerpt,
                'icon_path' => "services/icons/{$slug}.svg",
                'image_path' => "services/{$slug}.webp",
                'country_id' => $country === null ? null : ($countries[$country] ?? null),
                'sort_order' => $index + 1,
                'is_active' => true,
                'deleted_at' => null,
            ]);
        }
    }

    /**
     * Service detail page content (database/seeders/content/services.php). Checklist items and
     * gallery photos are matched by position, metrics by label.
     */
    private function seedServiceDetails(): void
    {
        /** @var array<string, array<string, mixed>> $details */
        $details = require self::CONTENT_SOURCE.'/services.php';

        foreach ($details as $slug => $detail) {
            $service = Service::withTrashed()->where('slug', $slug)->first();
            if ($service === null) {
                continue;
            }

            $service->update([
                'banner_image_path' => $detail['banner'],
                'overview' => $detail['overview'],
                'offer_title' => $detail['offer_title'],
                'offer_highlight' => $detail['offer_highlight'],
                'offer_intro' => $detail['offer_intro'],
                'why_title' => $detail['why_title'],
                'why_highlight' => $detail['why_highlight'],
                'why_intro' => $detail['why_intro'],
                'why_outro' => $detail['why_outro'],
                'faq_category' => $detail['faq_category'],
                'faq_intro' => $detail['faq_intro'],
            ]);

            foreach ($detail['features'] as $index => $description) {
                $service->features()->updateOrCreate(['sort_order' => $index + 1], ['description' => $description]);
            }
            $service->features()->where('sort_order', '>', count($detail['features']))->delete();

            foreach ($detail['images'] as $index => [$path, $alt]) {
                $service->images()->updateOrCreate(['sort_order' => $index + 1], ['image_path' => $path, 'alt' => $alt]);
            }
            $service->images()->where('sort_order', '>', count($detail['images']))->delete();

            foreach ($detail['metrics'] as $index => [$label, $value]) {
                $service->metrics()->updateOrCreate(['label' => $label], ['value' => $value, 'sort_order' => $index + 1]);
            }
        }
    }

    /** Partner public universities (logos from eurogrades.com). */
    private function seedUniversities(): void
    {
        $countries = Country::query()->pluck('id', 'slug');

        // [name, country slug, city, website]
        $universities = [
            ['University of Cologne', 'germany', 'Cologne', 'https://www.uni-koeln.de'],
            ['Universität Hamburg', 'germany', 'Hamburg', 'https://www.uni-hamburg.de'],
            ['University of Stuttgart', 'germany', 'Stuttgart', 'https://www.uni-stuttgart.de'],
            ['University of Freiburg', 'germany', 'Freiburg', 'https://uni-freiburg.de'],
            ['University of Bologna', 'italy', 'Bologna', 'https://www.unibo.it'],
            ['Sapienza University of Rome', 'italy', 'Rome', 'https://www.uniroma1.it'],
            ['Politecnico di Milano', 'italy', 'Milan', 'https://www.polimi.it'],
            ['University of Milan', 'italy', 'Milan', 'https://www.unimi.it'],
        ];

        foreach ($universities as $index => [$name, $country, $city, $website]) {
            $slug = str($name)->ascii()->slug()->toString();

            University::query()->updateOrCreate(['slug' => $slug], [
                'country_id' => $countries[$country],
                'name' => $name,
                'city' => $city,
                'logo_path' => "universities/{$slug}.webp",
                'website_url' => $website,
                'type' => UniversityType::Public,
                'is_partner' => true,
                'sort_order' => $index + 1,
                'is_active' => true,
            ]);
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

    /**
     * Student reviews from the old eurogrades.com site (home, about, IELTS and language pages).
     * Featured entries are the case studies shown with their "case overview" tags.
     */
    private function seedTestimonials(): void
    {
        $admissions = TestimonialCategory::Admissions;
        $ielts = TestimonialCategory::Ielts;
        $german = TestimonialCategory::German;
        $italian = TestimonialCategory::Italian;

        // [slug, name, category, program, origin, destination, achievement, quote, highlights, featured]
        $testimonials = [
            ['neha-sharma', 'Neha Sharma', $admissions, 'Master’s Student', 'India', 'Germany', null,
                'I was overwhelmed with the process of studying abroad, but EuroGrades made everything easy. They helped me apply to a top public university in Germany, and I even secured a tuition-free spot! Their support didn’t end after admission, they guided me through the visa process and settling in too.',
                ['University Admission', 'Tuition-Free Program', 'Student Visa', 'Settling-In Support'], true],
            ['naveen-bhatt', 'Naveen Bhatt', $admissions, 'Business Student', 'India', 'Italy', null,
                'I always wanted to study in Italy but wasn’t sure how to afford it. EuroGrades helped me find an affordable public university and walked me through every step, from applications to housing. Now I’m living my dream in Italy, studying what I love without financial stress!',
                ['University Selection', 'Application Support', 'Housing Assistance', 'Affordable Study Plan'], true],
            ['shivani-mittal', 'Shivani Mittal', $admissions, 'Master’s Student', 'India', 'Germany', null,
                'I never thought studying in Germany could be this easy. From applying to getting accepted at LMU Munich, everything just fell into place with the guidance and support of EuroGrades. Best decision I ever made!',
                ['University Admission', 'Application Support', 'Student Visa', 'Pre-Departure Support'], true],
            ['vasu-das', 'Vasu Das', $admissions, 'Business Student', 'India', 'Italy', null,
                'I always thought studying abroad was out of reach, but EuroGrades made it so simple and affordable. They helped me get into the University of Bologna, and now I’m living my dream of studying in Italy. It’s been an amazing journey!',
                ['University Admission', 'Affordable Pathway', 'Student Visa', 'Application Support'], true],
            ['aisha-khan', 'Aisha Khan', $admissions, 'Art Student', 'India', 'Italy', null,
                'EuroGrades took the stress out of studying abroad. They helped me choose the right university in Italy, apply for scholarships, and even find accommodation. I couldn’t have done it without their support. Now, I’m studying architecture in Rome and loving every moment!',
                ['University Selection', 'Scholarship Application', 'Accommodation Support', 'Student Visa'], true],
            ['anup-gupta', 'Anup Gupta', $ielts, 'IELTS Preparation', null, null, 'Band 8',
                'EuroGrades’ faculty has been very helpful and supportive. During the last few days before the exam, they actually stretched beyond the classroom to help us resolve our doubts and queries. Without EuroGrades, I would not have been able to score band 8 and get into my dream college.',
                ['IELTS Coaching', 'Band 8 Score', 'Doubt-Clearing Sessions', 'University Admission'], true],
            ['shipli-pradhan', 'Shipli Pradhan', $ielts, 'IELTS Preparation', null, null, 'Band 9',
                'I enrolled in the EuroGrades IELTS preparation classes. The faculty was extremely patient in helping me address my queries, which resulted in me getting a band 9. Regardless of the number of students in the class, everyone gets equal attention, and the faculty makes sure of it.',
                [], false],
            ['ashlay-thomas', 'Ashlay Thomas', $ielts, 'IELTS Preparation', null, null, 'Band 8',
                'After an 8-week preparatory course with EuroGrades, I scored a band 8 on my IELTS exam. They explained the format, what to expect, and quick tips to solve the questions quickly and effectively. Attending EuroGrades was a fruitful experience on the whole.',
                [], false],
            ['aditya', 'Aditya', $german, 'German Language', null, null, 'B1 Level',
                'What I loved most about EuroGrades was how practical their approach was. Instead of just memorizing grammar rules, we actually used German in real-life scenarios. The trainers were patient, and the small class sizes meant we got plenty of personal attention. I passed my A2 exam on the first try!',
                [], false],
            ['riya-malhotra', 'Riya Malhotra', $italian, 'Italian Language', null, null, 'C1 Intermediate',
                'Before EuroGrades, Italian was just a dream. But the way they teach, so interactive and fun, made learning super easy! I can now hold real conversations and even passed my B2 level with flying colours. If you’re thinking about learning Italian, this is the place to start!',
                [], false],
            ['neha', 'Neha', $german, 'German Language', null, null, 'B1 Level',
                'I struggled with pronunciation at first, but the audio exercises and speaking practice at EuroGrades helped me improve fast. The best part? The classes were affordable, flexible, and well-paced. Now, I can confidently understand German news and even chat with native speakers!',
                [], false],
            ['pallavi-sharma', 'Pallavi Sharma', $italian, 'Italian Language', null, null, 'C1 Advanced',
                'Learning Italian was something I always wanted but never got around to. EuroGrades reignited that passion. The faculty is patient, the pace is just right, and I never felt lost. Today, I understand opera lyrics and Italian films without subtitles, pure joy.',
                [], false],
            ['ritika', 'Ritika', $german, 'German Language', null, null, 'B1 Level',
                'As someone who had no prior knowledge of German, EuroGrades provided me with a solid foundation in the language. The teachers are supportive and always ready to help. Thanks for being instrumental in my success!',
                [], false],
            ['neha-sethi', 'Neha Sethi', $italian, 'Italian Language', null, null, 'B2 Upper Intermediate',
                'I joined EuroGrades on a whim, thinking I’d drop out halfway like I always do with online courses. But the vibe here is different! The classes are relaxed, the trainers are supportive, and I actually look forward to them.',
                [], false],
        ];

        foreach ($testimonials as $index => [$slug, $name, $category, $program, $origin, $destination, $achievement, $quote, $highlights, $featured]) {
            Testimonial::query()->updateOrCreate(['slug' => $slug], [
                'name' => $name,
                'avatar_path' => "testimonials/{$slug}.webp",
                'category' => $category,
                'program' => $program,
                'origin' => $origin,
                'destination' => $destination,
                'achievement' => $achievement,
                'quote' => $quote,
                'rating' => 5,
                'highlights' => $highlights === [] ? null : $highlights,
                'is_featured' => $featured,
                'sort_order' => $index + 1,
                'is_active' => true,
            ]);
        }
    }

    /** Portrait wall on the testimonials page (placeholder names until real student photos are provided). */
    private function seedSuccessStories(): void
    {
        $stories = [
            ['Liam Thompson', 'Berlin, Germany'],
            ['Isabella Johnson', 'Milan, Italy'],
            ['Ava Davis', 'Munich, Germany'],
            ['James Wilson', 'Rome, Italy'],
            ['Benjamin Garcia', 'Bologna, Italy'],
            ['Ethan Brown', 'Hamburg, Germany'],
            ['Elijah Rodriguez', 'Turin, Italy'],
            ['Charlie Martinez', 'Frankfurt, Germany'],
            ['Adam Lee', 'Florence, Italy'],
            ['Oliver Smith', 'Stuttgart, Germany'],
        ];

        foreach ($stories as $index => [$name, $destination]) {
            $slug = str($name)->slug()->toString();

            SuccessStory::query()->updateOrCreate(['slug' => $slug], [
                'name' => $name,
                'destination' => $destination,
                'photo_path' => "success-stories/{$slug}.webp",
                'sort_order' => $index + 1,
                'is_active' => true,
            ]);
        }
    }

    /**
     * Study destinations with the facts published on eurogrades.com (germany.php / italy.php).
     * Child rows are matched by their natural key, so re-seeding updates rather than duplicates.
     */
    private function seedCountries(): void
    {
        $countries = [
            [
                'country' => [
                    'name' => 'Germany',
                    'slug' => 'germany',
                    'iso_code' => 'DE',
                    'flag_path' => 'countries/flags/germany.svg',
                    'cover_image_path' => 'countries/covers/germany-hero.webp',
                    'banner_image_path' => 'banners/germany.webp',
                    'excerpt' => 'World-class, tuition-free public universities, research-driven programs and strong career prospects in Europe’s largest economy.',
                    // Paragraphs are separated by a blank line.
                    'overview' => "Unlock world-class education in Germany with EuroGrades. Whether you are planning a bachelor’s or master’s degree, preparing for TestDaF or IELTS, or applying for your student visa, our experienced counselors guide you through every step, from choosing the right public university and course to your arrival in Germany.\n\nGermany offers world-class education, affordable or tuition-free public universities, and strong career prospects. The education system is focused on research and practical learning, which helps students gain valuable skills while experiencing a rich cultural environment. With more than 400 universities and nearly 50,000 Indian students, it is one of the most popular study destinations in Europe.",
                    'why_choose_intro' => 'Germany is one of the most popular destinations for international students seeking quality education at little or no tuition cost, strong career prospects, and a high standard of living.',
                    'why_choose_image_path' => 'countries/why/germany.webp',
                    'cities_intro' => 'Explore, learn, and thrive in Germany’s top student cities, where education meets opportunity.',
                    'faq_intro' => 'Our FAQ section answers the most common questions about studying in Germany, from tuition fees and living costs to language requirements, part-time work and student visas.',
                    'part_time_work' => 'International students can work up to 20 hours per week during the semester and full-time during breaks. Part-time jobs in retail, tutoring, and hospitality are common.',
                    'post_study_work' => 'Germany offers excellent post-study work opportunities for international graduates. After completing a degree, students can apply for an 18-month job-seeking visa to find employment. With a strong economy and demand for skilled professionals, graduates can secure well-paying jobs, especially in engineering, IT, and healthcare.',
                    'faq_category' => FaqCategory::Germany,
                    'meta_title' => 'Study in Germany',
                    'meta_description' => 'Study at tuition-free public universities in Germany: costs, visas, requirements and top student cities, with expert guidance from EuroGrades.',
                ],
                // [label, value, highlighted on card]
                'stats' => [
                    ['Tuition Fee (Yearly)', '$1,500 (Public)', true],
                    ['Living Cost (Monthly)', '$1,000 – $1,085', true],
                    ['Indian Students', '49,483 (2023/24)', false],
                    ['Average Salary', '$54,000', false],
                    ['Universities', '400+', false],
                ],
                'requirements' => [
                    ['Recognized school diploma or bachelor’s degree', 'A high school diploma for bachelor’s programs, or a recognized bachelor’s degree for master’s programs.'],
                    ['Proof of language proficiency', 'IELTS/TOEFL for English-taught programs (min. IELTS 5.5 at many public universities), or TestDaF/DSH for German-taught programs.'],
                    ['University admission letter', 'Confirmation of admission from a German higher education institution.'],
                    ['Proof of financial resources', 'Funds to cover your living expenses while studying.'],
                    ['Health insurance', 'Valid health insurance coverage for your stay in Germany.'],
                    ['Valid passport', 'A passport valid for the duration of your planned stay.'],
                ],
                // [name, description, type, fee]
                'visas' => [
                    ['Student Visa (Visum zu Studienzwecken)', 'For students who have already been accepted to a German higher education institution to start a degree program (e.g. bachelor’s, master’s).', 'Long-stay, national D visa', '€75 ($81.15)'],
                    ['Student Applicant Visa (Visum zur Studienbewerbung)', 'For students who have applied to a German higher education institution or preparatory course but haven’t yet received a letter of admission.', 'Longer than 9 months', '€75 ($81.15)'],
                    ['Language Course Visa', 'For intensive German language courses in Germany, potentially leading to a student visa later.', 'National / long-term visa (D visa)', '€75 ($81.15)'],
                ],
                'living_costs' => [
                    ['Rent', '€300 to €700'],
                    ['Food', '€150 to €300'],
                    ['Transport', '€30 to €100'],
                    ['Miscellaneous', '€100 to €250'],
                ],
                // [title, description]
                'reasons' => [
                    ['World-Class Education', 'Low or no tuition fees at most public universities, with research-focused, practical programs.'],
                    ['Strong Career Prospects', 'An 18-month job-seeking visa after graduation and high demand for engineers, IT and healthcare professionals.'],
                    ['Work While You Study', 'Work up to 20 hours a week during the semester and full-time during breaks to support your living costs.'],
                ],
                // [name, description, image, universities]
                'cities' => [
                    ['Munich', 'QS Best City for Students with 10 universities. A major hub for engineering, automotive, and IT careers.', 'countries/cities/munich.webp', 10],
                    ['Berlin', 'Germany’s capital with 33 universities, a vibrant student life, and a strong job market in technology and startups.', 'countries/cities/berlin.webp', 33],
                    ['Frankfurt', 'Germany’s financial center with 7 universities and headquarters of major banks and international corporations.', 'countries/cities/frankfurt.webp', 7],
                    ['Hamburg', 'Germany’s largest port city and home of partner university Universität Hamburg, with strong logistics, trade, aviation, and media industries.', null, null],
                ],
            ],
            [
                'country' => [
                    'name' => 'Italy',
                    'slug' => 'italy',
                    'iso_code' => 'IT',
                    'flag_path' => 'countries/flags/italy.svg',
                    'cover_image_path' => 'countries/covers/italy-hero.webp',
                    'banner_image_path' => 'banners/italy.webp',
                    'excerpt' => 'Affordable public universities, globally respected programs in design, architecture and engineering, and a rich cultural life.',
                    'overview' => "Study in Italy with EuroGrades. From choosing an affordable public university and applying for scholarships to Italian language preparation, student visas, and accommodation, our counselors support you at every step of your journey.\n\nItaly combines quality education, rich culture, affordable living, and access to top European companies. Its universities are globally respected, especially in architecture, fashion, art, and engineering, and many offer English-taught programs, making Italy a great choice for international students.",
                    'why_choose_intro' => 'Italy is one of Europe’s more affordable study destinations, combining globally respected universities, scholarships for international students, and an unmatched cultural experience.',
                    'why_choose_image_path' => 'countries/why/italy.webp',
                    'cities_intro' => 'Explore, learn, and thrive in Italy’s top student cities, where education meets opportunity.',
                    'faq_intro' => 'Our FAQ section answers the most common questions about studying in Italy, from tuition fees and scholarships to language requirements, part-time work and life after graduation.',
                    'part_time_work' => 'International students in Italy can work part-time for up to 20 hours per week during their studies, gaining practical experience, improving their Italian, and supporting their living expenses.',
                    'post_study_work' => 'Italy offers excellent post-study work opportunities, especially for graduates in fashion, design, engineering, and hospitality. Graduates can apply for a stay-back permit (up to 12 months) to look for a job and convert it into a work visa once employed.',
                    'faq_category' => FaqCategory::Italy,
                    'meta_title' => 'Study in Italy',
                    'meta_description' => 'Study at affordable public universities in Italy: costs, visas, requirements and top student cities, with expert guidance from EuroGrades.',
                ],
                'stats' => [
                    ['Tuition Fee (Yearly)', '$1,000 – $4,800 (Public)', true],
                    ['Living Cost (Monthly)', '$750 – $1,075', true],
                    ['Indian Students', '10,000+ (2023/24)', false],
                    ['Average Salary', '€32,450 ($33,825)', false],
                    ['Universities', '100+', false],
                ],
                'requirements' => [
                    ['Academic transcripts', 'Certificates and transcripts from your previous studies.'],
                    ['Valid passport', 'A passport valid for the duration of your planned stay.'],
                    ['Language proficiency scores', 'IELTS/TOEFL for English-taught programs, or B2-level Italian (CILS/CELI) for Italian-taught programs.'],
                    ['Entrance exam or motivation letter', 'Required by some universities and programs, such as IMAT for medicine.'],
                    ['Proof of financial resources', 'Funds to cover your living expenses while studying.'],
                    ['Health insurance', 'Valid health insurance coverage for your stay in Italy.'],
                ],
                'visas' => [
                    ['D-Visa', 'Also known as the long-stay visa, required for study programs longer than 90 days.', 'Long-stay, national D visa', '€50'],
                    ['C-Visa', 'A short-stay visa for study programs of 90 days or less.', 'Short-stay visa', '€90'],
                ],
                'living_costs' => [
                    ['Rent', '€300 to €700'],
                    ['Food', '€150 to €450'],
                    ['Transport', '€22 to €45'],
                    ['Miscellaneous', '€90 to €250'],
                ],
                'reasons' => [
                    ['Affordable Quality Education', 'Low tuition fees at public universities and scholarships based on merit or financial need.'],
                    ['Globally Respected Programs', 'Renowned for architecture, fashion, art, design, and engineering, with many English-taught courses.'],
                    ['Career Opportunities in Europe', 'Work 20 hours a week while studying and stay up to 12 months after graduation to find a job.'],
                ],
                'cities' => [
                    ['Rome', 'Italy’s capital with 19 universities, where history, culture, and student life meet.', 'countries/cities/rome.webp', 19],
                    ['Milan', 'Global capital of fashion, design, and business, home to 14 universities including Politecnico di Milano.', 'countries/cities/milan.webp', 14],
                    ['Pisa', 'A classic university town with 3 universities and a strong tradition in science and engineering.', 'countries/cities/pisa.webp', 3],
                    ['Bologna', 'Home of the world’s oldest university and a lively, affordable student city.', 'countries/cities/bologna.webp', 3],
                ],
            ],
        ];

        foreach ($countries as $index => $data) {
            $country = Country::withTrashed()->updateOrCreate(
                ['slug' => $data['country']['slug']],
                [...$data['country'], 'sort_order' => $index + 1, 'is_active' => true, 'deleted_at' => null],
            );

            foreach ($data['stats'] as $i => [$label, $value, $highlighted]) {
                $country->stats()->updateOrCreate(['label' => $label], [
                    'value' => $value, 'is_highlighted' => $highlighted, 'sort_order' => $i + 1,
                ]);
            }
            foreach ($data['requirements'] as $i => [$title, $description]) {
                $country->requirements()->updateOrCreate(['title' => $title], [
                    'description' => $description, 'sort_order' => $i + 1,
                ]);
            }
            foreach ($data['visas'] as $i => [$name, $description, $type, $fee]) {
                $country->visas()->updateOrCreate(['name' => $name], [
                    'description' => $description, 'visa_type' => $type, 'fee' => $fee, 'sort_order' => $i + 1,
                ]);
            }
            foreach ($data['living_costs'] as $i => [$label, $amount]) {
                $country->livingCosts()->updateOrCreate(['label' => $label], [
                    'amount' => $amount, 'sort_order' => $i + 1,
                ]);
            }
            foreach ($data['reasons'] as $i => [$title, $description]) {
                $country->reasons()->updateOrCreate(['title' => $title], [
                    'description' => $description, 'sort_order' => $i + 1,
                ]);
            }
            foreach ($data['cities'] as $i => [$name, $description, $image, $universities]) {
                $country->cities()->updateOrCreate(['name' => $name], [
                    'description' => $description, 'image_path' => $image,
                    'universities_count' => $universities, 'sort_order' => $i + 1,
                ]);
            }
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
            // FAQs page topics (old site: germany.php, italy.php, ielts.php, german_language.php, italian_language.php).
            FaqCategory::Germany->value => [
                ['Why is Germany a great choice for studying abroad?', 'Germany offers world-class education, affordable or tuition-free public universities, and strong career prospects. The education system is focused on research and practical learning, which helps students gain valuable skills while experiencing a rich cultural environment.'],
                ['Are public universities in Germany really tuition-free?', 'Yes! Most public universities in Germany charge little to no tuition fees for international students, except for a small administrative fee of around €100–€500 per semester.'],
                ['Can I study in Germany without knowing German?', 'Yes! Many universities offer English-taught programs, especially at the master’s level. However, learning German can help with daily life, part-time jobs, and career opportunities.'],
                ['What are the basic eligibility requirements for studying in Germany?', 'Requirements vary by program, but generally students need a recognized high school diploma or bachelor’s degree, proof of language proficiency (English or German), and financial proof to cover living expenses.'],
                ['What is the cost of living for students in Germany?', 'On average, students need around €600–€1,300 per month, depending on the city. Expenses include rent, food, transport, health insurance, and other personal costs.'],
                ['Can I work part-time while studying in Germany?', 'Yes, you can! International students can work up to 20 hours per week during the semester and full-time during breaks. Part-time jobs in retail, tutoring, and hospitality are common.'],
                ['What are the post-study work opportunities in Germany?', 'After graduation, students can apply for an 18-month job-seeking visa to find employment. Many international graduates secure well-paying jobs, especially in fields like engineering, IT, and healthcare.'],
                ['Do I need a student visa to study in Germany?', 'Yes! Non-EU students must apply for a German student visa, which requires proof of university admission, financial documents, and health insurance.'],
            ],
            FaqCategory::Italy->value => [
                ['Is studying in Italy expensive?', 'Not at all! Italy is one of the more affordable destinations in Europe. Public universities have low tuition fees and the cost of living is manageable, especially in smaller cities. Scholarships are available for low-income students, and part-time work further reduces financial stress.'],
                ['Are Italian universities internationally recognized?', 'Yes, Italian universities are globally respected, especially in fields like architecture, fashion, art, and engineering. Many institutions rank highly in international rankings and offer English-taught programs.'],
                ['Can I study in Italy without knowing Italian?', 'Many universities offer courses in English, especially at the postgraduate level. However, learning basic Italian can help you in daily life and with part-time jobs.'],
                ['What are the requirements to apply to Italian universities?', 'You typically need your academic transcripts, a passport, language proficiency scores (IELTS/TOEFL), and in some cases entrance exam results or a motivation letter. Requirements vary by university, so always check with your chosen institution.'],
                ['Do Italian universities offer scholarships to international students?', 'Yes, both the Italian government and universities offer scholarships based on merit or financial need. These may cover tuition fees, accommodation, or living expenses.'],
                ['Can I work while studying in Italy?', 'Yes, international students in Italy can work up to 20 hours per week during term and full-time during holidays. Knowledge of Italian can increase job opportunities.'],
                ['Is Italy a good destination for international students?', 'Definitely! Italy combines quality education, rich culture, affordable living, and access to top European companies, making it a great choice for international students.'],
                ['What are popular courses to study in Italy?', 'Popular courses include architecture, fashion design, culinary arts, business, engineering, and humanities. Italy is a global hub for art and creativity.'],
                ['What can I do after graduation in Italy?', 'You can apply for a stay-back permit (up to 12 months) to look for a job. If you get employed, you can convert your permit into a work visa and continue your career in Italy or the EU.'],
            ],
            FaqCategory::Ielts->value => [
                ['How do I register for the IELTS?', 'You can register online through the official IELTS website or at an authorized test center. Choose your preferred test date, fill out the application form, upload the required documents, and pay the registration fee to confirm your slot.'],
                ['How many times in a year is the IELTS exam held?', 'The IELTS exam is conducted multiple times throughout the year, typically up to four times a month depending on the test center, so you can choose a date that fits your preparation schedule.'],
                ['How many times can I take the IELTS exam?', 'There is no limit. You can retake the test as many times as you want to improve your score; each attempt requires a separate registration and fee.'],
                ['What is the validity of the IELTS exam?', 'Your IELTS score is valid for two years from the test date. After that, you will need to retake the exam if universities or immigration authorities require it.'],
                ['How soon do I get the score?', 'For the paper-based test, results are available 13 days after the test date. For the computer-based test, results are typically released within 3 to 5 days.'],
                ['If I take the IELTS more than once, which score do universities consider?', 'Most universities consider your best IELTS score, but check with your preferred institution for its specific policy, as some may require all attempts to be reported.'],
                ['What is the fee for the IELTS exam?', 'The fee varies by country and test center. In India it is approximately INR 16,250, but check the official IELTS website for the latest fee structure.'],
            ],
            FaqCategory::GermanLanguage->value => [
                ['Who can enroll in the German language course at EuroGrades?', 'Anyone interested in learning German can enroll! Whether you are a student planning to study in Germany or a professional looking for better job opportunities, our courses cater to all skill levels, from complete beginners to advanced learners refining their fluency.'],
                ['How long does it take to learn German?', 'It depends on your pace and target level. Basic levels (A1–A2) typically take 8–12 weeks each, while intermediate levels (B1–B2) can take several months. Consistent practice, interactive learning, and immersion speed up the process.'],
                ['Do I need prior knowledge of German to start?', 'No. Our A1 course is designed for complete beginners, covering the basics of grammar, vocabulary, and everyday conversation.'],
                ['What teaching methods does EuroGrades use?', 'We use an interactive, immersive approach that combines live classes, real-life conversations, listening exercises, and practical writing tasks, with role-play, group discussions, and personalized feedback to build confident communication.'],
                ['Will I get a certificate after completing the course?', 'Yes. After completing each level you receive a EuroGrades certificate that you can use for university applications, jobs, and visa processes. Our courses also prepare you for internationally recognized exams like Goethe, TestDaF, and TELC.'],
                ['Is learning German necessary for studying in Germany?', 'Not always, as many universities offer English-taught programs. However, knowing German improves your chances of admission, helps with daily life, and enhances job opportunities in Germany.'],
                ['Are the classes online or offline?', 'Both. Our interactive online sessions offer flexibility, while offline classes provide in-person learning for those who prefer a traditional classroom.'],
                ['How much do the German language courses cost?', 'Fees vary by level and format (online or offline). We offer affordable pricing with flexible payment options; contact us for detailed fees and current student and professional discounts.'],
            ],
            FaqCategory::ItalianLanguage->value => [
                ['Do I need to learn Italian to study in Italy?', 'While many universities offer programs in English, knowing Italian enhances your academic, social, and cultural experience. It is especially helpful if your course is in the humanities or arts, or involves local interaction.'],
                ['What level of Italian is required for Italian-taught programs?', 'Most Italian-taught university programs require at least a B2 level (CEFR). Some institutions accept B1 with a commitment to improve during your studies.'],
                ['Are there official exams for Italian language certification?', 'Yes, the most recognized exams are CILS (Certification of Italian as a Foreign Language) and CELI. Universities often require them for admission to Italian-medium courses.'],
                ['How long does it take to become fluent in Italian?', 'With consistent effort, reaching a conversational level (B1–B2) can take 6 to 12 months. Intensive courses speed this up, especially combined with immersion or study in Italy.'],
                ['Will learning Italian help me get a part-time job in Italy?', 'Yes. Many part-time roles in hospitality, retail, or tutoring prefer or require Italian-speaking candidates, so language skills give you a strong edge.'],
                ['Can I survive in Italy with only English?', 'In major cities and universities, yes, but daily life is much easier with basic Italian. Simple tasks like grocery shopping, asking for directions, or visiting a doctor need some local language skills.'],
                ['Is Italian hard to learn for English speakers?', 'Italian is a Romance language with phonetic spelling and regular grammar, making it relatively easy for English speakers compared to languages like German or Russian.'],
                ['Will learning Italian open up more academic and professional opportunities?', 'Yes. Beyond studying in Italy, Italian is spoken in parts of Switzerland and valued in international careers in fashion, art, culinary arts, diplomacy, and design.'],
            ],
            // Attestation Services page (old site: mea-attestion.php).
            FaqCategory::Attestation->value => [
                ['What is the difference between an Apostille and normal MEA attestation?', 'An Apostille is accepted in the member countries of the Hague Convention, so a document apostilled once is recognised by all of them without separate attestation for each country. Normal (general) MEA attestation is done for countries that are not members of the Hague Convention and do not accept the Apostille.'],
                ['Which documents can be apostilled?', 'Personal documents such as birth, death and marriage certificates, affidavits and power of attorney, and educational certificates such as degrees, diplomas, matriculation and secondary-level certificates.'],
                ['Do my documents need to be authenticated by the state first?', 'Yes. Before MEA attestation, documents must be authenticated by the designated authority of the state or union territory that issued them: the Home or General Administration Department for personal documents, the State Education Department (State HRD) for educational documents, and the Chambers of Commerce for commercial documents.'],
                ['What is embassy legalisation and when do I need it?', 'Many countries ask for embassy legalisation in addition to the apostille, which means submitting your documents to their embassy or consulate in India. Each embassy has its own process, and some documents only need the apostille certificate. We check what your destination requires and handle the submission for you.'],
                ['How does UAE attestation work?', 'Educational documents are attested by the Education Ministry of the concerned state and then by the Ministry of External Affairs before visiting the UAE Embassy’s Consular Section. Other documents, such as marriage, birth and experience certificates, need MEA attestation first. Commercial documents are attested by the MEA and can be submitted by authorised company employees.'],
                ['Do you translate documents as well?', 'Yes. Some attestation and legalisation work requires a translation into the language of your destination country. Our certified translators cover major languages including English, German, Italian, French, Spanish, Russian, Arabic, Japanese, Korean, Chinese and Portuguese, as well as Indian languages.'],
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
