<?php

declare(strict_types=1);

use App\Enums\FaqCategory;

/*
 * Service detail page content, keyed by service slug (see ContentSeeder::seedServiceDetails()).
 * Copy adapted from the old eurogrades.com pages: germany.php, italy.php, ielts.php,
 * german_language.php, italian_language.php and attestation_services.php / mea-attestion.php.
 *
 * Banners are the page banners of the matching old-site pages (assets/*_bg.jpg).
 *
 * Metric percentages are placeholders (the old site has no such figures): replace them with
 * EuroGrades' real numbers when available.
 */
return [
    'study-in-germany' => [
        'banner' => 'banners/germany.webp',
        'overview' => <<<'TXT'
            Germany is one of the best destinations for studying abroad, offering world-class education, affordable tuition, and excellent career opportunities. Public universities in Germany provide high-quality education at little to no tuition fees, making it a cost-effective choice for international students. Whether you want to study engineering, business, or medicine, Germany has top-ranked universities that offer globally recognized degrees.

            One of the biggest advantages of studying in Germany is the strong focus on research and practical learning. Many universities collaborate with industries, giving students hands-on experience and better job prospects. EuroGrades guides you through every step, from choosing the right public university to securing your student visa.
            TXT,
        'offer_title' => 'End-to-End Germany Admissions',
        'offer_highlight' => 'Germany Admissions',
        'offer_intro' => 'Our counselors work closely with you so every step of your application is completed accurately and on time, from your first shortlist to your arrival in Germany.',
        'features' => [
            'University and course selection across Germany’s 400+ universities, with a focus on low- or no-tuition public universities in student cities like Munich, Berlin and Frankfurt.',
            'Application and document preparation, including proof of language proficiency (IELTS 5.5+ for many English-taught programs) and the financial proof needed to cover living expenses.',
            'Student visa guidance for the national D visa, the student applicant visa and the language course visa, plus advice on part-time work and the 18-month post-study job-seeking visa.',
        ],
        'images' => [
            ['services/gallery/advisor-world-map.webp', 'EuroGrades counselor planning a student’s study abroad journey'],
            ['services/gallery/student-campus.webp', 'International student on a university campus'],
        ],
        'why_title' => 'Why Choose EuroGrades for Germany',
        'why_highlight' => 'EuroGrades',
        'why_intro' => 'Whether you plan to pursue a bachelor’s or master’s degree in engineering, business, IT or medicine, our team simplifies the process and guides you toward the best public universities for your future.',
        'metrics' => [
            ['Admission Success Rate', 95],
            ['Student Visa Approval Rate', 97],
            ['Document Accuracy & Verification', 98],
            ['On-Time Application Processing', 96],
        ],
        'why_outro' => 'While many programs are offered in English, learning German can be a huge advantage, helping you integrate better and expand your career opportunities. If you’re looking for an affordable, high-quality education with strong career prospects, studying in Germany is a smart choice, and EuroGrades is with you all the way.',
        'faq_category' => FaqCategory::Germany,
        'faq_intro' => 'Our FAQ section answers the most common questions students ask about studying in Germany, so you can plan your journey with ease.',
    ],

    'study-in-italy' => [
        'banner' => 'banners/italy.webp',
        'overview' => <<<'TXT'
            Italy has fast become one of the top choices for international students, offering high-quality education at a relatively low cost. Public universities in Italy charge significantly lower tuition fees than institutions in the US, UK, or Australia, making it a smart option for students seeking top-notch education on a budget.

            Many universities in Italy offer programs in English, especially in business, fashion, design, architecture, and engineering. Italy is also home to global giants like Ferrari, Fiat, Prada, Luxottica, and Barilla, giving students direct access to internships and job opportunities with multinational brands.
            TXT,
        'offer_title' => 'End-to-End Italy Admissions',
        'offer_highlight' => 'Italy Admissions',
        'offer_intro' => 'From shortlisting universities in Rome, Milan, Pisa or Bologna to your visa appointment, our counselors make sure every step is completed accurately and efficiently.',
        'features' => [
            'University and course selection at affordable public universities, including English-taught programs in business, fashion, design, architecture and engineering.',
            'Application, scholarship and document support, with guidance on merit- and need-based scholarships offered by the Italian government and universities.',
            'Visa guidance for the long-stay D visa (programs over 90 days) and the short-stay C visa, plus advice on part-time work of up to 20 hours per week and post-study stay permits.',
        ],
        'images' => [
            ['services/gallery/counselling-session.webp', 'EuroGrades counselors advising a student on universities abroad'],
            ['services/gallery/student-travel.webp', 'Student holding her passport and tickets at the airport'],
        ],
        'why_title' => 'Why Choose EuroGrades for Italy',
        'why_highlight' => 'EuroGrades',
        'why_intro' => 'Located in the heart of Europe, Italy opens doors to travel, networking and careers across the EU. Our team helps you find the right university and program for your goals and budget.',
        'metrics' => [
            ['Admission Success Rate', 94],
            ['Student Visa Approval Rate', 96],
            ['Document Accuracy & Verification', 98],
            ['On-Time Application Processing', 95],
        ],
        'why_outro' => 'Whether you’re aiming for a global career or simply want to study in a culturally rich, affordable country, Italy checks all the right boxes. With world-renowned universities and a growing international student community, it offers the perfect mix of education, culture, and opportunity.',
        'faq_category' => FaqCategory::Italy,
        'faq_intro' => 'Find clear answers to the most common questions about studying in Italy: costs, language, scholarships, work and student life.',
    ],

    'ielts-preparation' => [
        'banner' => 'banners/ielts.webp',
        'overview' => <<<'TXT'
            The International English Language Testing System (IELTS) is a globally recognized English proficiency exam for anyone who wants to study, work, or migrate abroad. It assesses four key language skills: Listening, Reading, Writing, and Speaking, and is accepted by universities, employers, immigration authorities, and professional organizations worldwide.

            The test is scored on a 9-band scale, from non-user (Band 1) to expert user (Band 9). With no pass or fail, IELTS measures your ability to communicate effectively in English. At EuroGrades, our structured IELTS preparation courses help you achieve your desired band score with expert guidance and practice.
            TXT,
        'offer_title' => 'Coaching for Every IELTS Module',
        'offer_highlight' => 'IELTS Module',
        'offer_intro' => 'We offer a structured, result-driven approach that ensures you’re fully equipped for the exam, with focused training for each part of the test.',
        'features' => [
            'Listening: understanding different accents and dialects, keyword and note-taking techniques, and practice tests with real exam-style recordings.',
            'Reading: techniques to scan and skim passages quickly, understanding every question type, and time-management tips for completing all tasks.',
            'Writing: Task 1 and Task 2 strategies for Academic and General Training, structuring essays and reports, and sample responses with trainer feedback.',
            'Speaking: building fluency and confidence, improving pronunciation and vocabulary, and mock interviews that recreate real exam scenarios.',
        ],
        'images' => [
            ['services/gallery/advisor-meeting.webp', 'IELTS trainer reviewing a student’s progress'],
            ['services/gallery/student-campus.webp', 'Student preparing for her IELTS exam'],
        ],
        'why_title' => 'Why Choose EuroGrades for IELTS',
        'why_highlight' => 'EuroGrades',
        'why_intro' => 'Expert instructors, comprehensive study material, mock tests with performance tracking, real-time feedback, doubt-clearing sessions and guidance for exam registration: everything you need to reach your target band.',
        'metrics' => [
            ['Students Reaching Their Target Band', 92],
            ['Mock Test Practice Coverage', 100],
            ['Speaking Confidence Improvement', 95],
            ['Student Satisfaction', 97],
        ],
        'why_outro' => 'IELTS is recognized globally by universities, employers, and immigration authorities as proof of English proficiency. With test dates throughout the year and both Academic and General Training modules, it is a reliable way to achieve your global aspirations, and our trainers prepare you for both.',
        'faq_category' => FaqCategory::Ielts,
        'faq_intro' => 'From registration and fees to results and score validity, here are answers to the questions students ask most about the IELTS exam.',
    ],

    'german-language-course' => [
        'banner' => 'banners/german-language.webp',
        'overview' => <<<'TXT'
            Proficiency in German can open doors to world-class education, better job prospects, and seamless integration into German-speaking countries. That is why our affordable, structured German language courses in India are designed for students who want to learn efficiently.

            We offer A1 to B2 level training with expert guidance. Our courses focus on practical learning, helping you develop real-life communication skills while also meeting university and visa requirements. Our curriculum covers grammar, vocabulary, pronunciation, and cultural insights for a well-rounded learning experience.
            TXT,
        'offer_title' => 'German Training From A1 to B2',
        'offer_highlight' => 'From A1 to B2',
        'offer_intro' => 'EuroGrades offers structured courses that guide you through every level, so you can speak, read and write German with confidence.',
        'features' => [
            'A1 Level: the absolute beginner level, covering basic vocabulary and grammar, speaking and listening, reading and writing, pronunciation and cultural insights.',
            'A2 Level: understand frequently used expressions, with expanded vocabulary and grammar, conversational skills, listening comprehension and practical usage.',
            'B1 Level: deal with the main situations you meet in a German-speaking country, through advanced grammar, fluent conversations and real-life scenarios.',
            'B2 Level: hold conversations with native speakers and be easily understood, with the academic and professional readiness universities expect.',
        ],
        'images' => [
            ['services/gallery/advisor-world-map.webp', 'German language trainer with a student'],
            ['services/gallery/counselling-session.webp', 'Students in a live interactive language class'],
        ],
        'why_title' => 'Why Learn German With EuroGrades',
        'why_highlight' => 'EuroGrades',
        'why_intro' => 'Affordable pricing, beginner-friendly lessons, small batch sizes, certified trainers, a structured curriculum, flexible learning modes, live interactive classes and TestDaF and DSH preparation.',
        'metrics' => [
            ['Students Passing on Their First Attempt', 94],
            ['Live Interactive Practice', 100],
            ['Speaking & Listening Focus', 90],
            ['Student Satisfaction', 97],
        ],
        'why_outro' => 'Germany is home to top multinational companies, and German proficiency boosts both job prospects and admission chances at tuition-free public universities. It also helps you navigate Austria, Switzerland and other German-speaking regions, and by the end of the course your transition to studying in Germany will be smooth and hassle-free.',
        'faq_category' => FaqCategory::GermanLanguage,
        'faq_intro' => 'Answers to the most common questions about our German language courses: levels, exams, class formats and fees.',
    ],

    'italian-language-course' => [
        'banner' => 'banners/italian-language.webp',
        'overview' => <<<'TXT'
            Learning Italian is more than picking up a new language: it opens doors to exciting opportunities across Europe, especially in Italy. Whether you’re planning to study or work abroad, fluency in Italian gives you a real edge in international environments.

            Our Italian Language Course is designed for ambitious learners in India. From basic A1 to advanced C1 levels, we help you build strong communication skills, understand the culture, and gain the confidence to thrive in Italian-speaking settings, with flexible schedules to fit your timelines.
            TXT,
        'offer_title' => 'Italian Training From A1 to C1',
        'offer_highlight' => 'From A1 to C1',
        'offer_intro' => 'Led by experienced trainers, our classes blend interactive sessions with real-life scenarios, so you’re not just learning words, you’re learning to live the language.',
        'features' => [
            'A1 – Beginner: basic phrases, greetings and everyday vocabulary, ideal for complete beginners starting their Italian journey.',
            'A2 – Elementary: commonly used expressions about shopping, family and local geography, with confidence in short conversations and simple reading.',
            'B1 – Intermediate: more complex sentences, travel situations, work-related conversations and personal topics handled with greater fluency.',
            'B2 – Upper Intermediate: effective, spontaneous communication and clear opinions in technical discussions, ideal for academic or work settings in Italy.',
            'C1 – Advanced: read complex texts, write essays and engage in fluent conversations without hesitation, with CILS and CELI exam preparation.',
        ],
        'images' => [
            ['services/gallery/advisor-meeting.webp', 'Italian language trainer during a one-to-one session'],
            ['services/gallery/counselling-session.webp', 'Students practising conversation in a small batch'],
        ],
        'why_title' => 'Why Learn Italian With EuroGrades',
        'why_highlight' => 'EuroGrades',
        'why_intro' => 'Affordable pricing, beginner-friendly lessons, small batch sizes, certified trainers, a structured curriculum, flexible learning modes, live interactive classes and CILS and CELI exam preparation.',
        'metrics' => [
            ['Students Passing on Their First Attempt', 93],
            ['Live Interactive Practice', 100],
            ['Conversation & Culture Focus', 90],
            ['Student Satisfaction', 98],
        ],
        'why_outro' => 'Knowing Italian opens doors to global careers in fashion, automotive, art, culinary and tourism, and makes Italy’s affordable public universities easier to access. Spoken by over 85 million people worldwide, it adds a unique edge to your profile and helps you experience Italian culture more deeply.',
        'faq_category' => FaqCategory::ItalianLanguage,
        'faq_intro' => 'Answers to the questions learners ask most about Italian: required levels, certification exams, fluency timelines and life in Italy.',
    ],

    'attestation-services' => [
        'banner' => 'banners/attestation.webp',
        'overview' => <<<'TXT'
            All original documents or true copies that need attestation or an Apostille from the Ministry of External Affairs (MEA) must first be authenticated by the designated authorities of the State or Union Territory where they were issued.

            Personal documents such as birth or marriage certificates are authenticated by the Home or General Administration Department, educational documents by the State Education Department (State HRD), and commercial documents by the respective Chambers of Commerce. Each state has different requirements and authorities, and our team handles the whole process for you.
            TXT,
        'offer_title' => 'Complete Document Attestation',
        'offer_highlight' => 'Document Attestation',
        'offer_intro' => 'After state authentication there are two types of MEA attestation, Apostille and General. We take care of both, along with every step around them.',
        'features' => [
            'MEA Apostille for personal documents (birth, death and marriage certificates, affidavits, power of attorney) and educational certificates, accepted in the member countries of the Hague Convention.',
            'MEA General (normal) attestation for countries that are not members of the Hague Convention and do not accept the Apostille.',
            'State HRD and State Home Department authentication of educational and personal documents, plus UAE attestation and embassy legalisation across India.',
            'Professional document translation by certified translators into major languages including German, Italian, French, Spanish, Arabic, Japanese and Chinese.',
        ],
        'images' => [
            ['services/gallery/document-approval.webp', 'Attested document being handed over with an approval stamp'],
            ['services/gallery/passports-on-desk.webp', 'Passports and legalised documents ready for travel'],
        ],
        'why_title' => 'Why Choose Our Attestation Team',
        'why_highlight' => 'Attestation Team',
        'why_intro' => 'Our embassy legalisation service saves you the time of researching how to prepare, certify and submit documents to each embassy. Instead of guessing, let our experts hand-process your order.',
        'metrics' => [
            ['Documents Accepted First Time', 98],
            ['Document Accuracy & Verification', 99],
            ['On-Time Processing', 96],
            ['Client Satisfaction', 97],
        ],
        'why_outro' => 'Document legalisation varies from one embassy to another, and not all documents are accepted unless they are correctly presented. Our fully inclusive apostille and embassy legalisation service makes sure every necessary step is taken, so your documents are legalised as quickly as possible.',
        'faq_category' => FaqCategory::Attestation,
        'faq_intro' => 'Clear answers about apostille, MEA attestation, state authentication, embassy legalisation and translation.',
    ],
];
