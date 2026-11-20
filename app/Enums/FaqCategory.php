<?php

declare(strict_types=1);

namespace App\Enums;

/** Groups FAQs by topic / the page they are shown on. */
enum FaqCategory: string
{
    /** General study-abroad questions (home page, FAQs page). */
    case Students = 'students';

    /** Germany & Italy study questions (about page). */
    case Immigration = 'immigration';

    /** FAQs page topics, from the matching pages of the old eurogrades.com site. */
    case Germany = 'germany';

    case Italy = 'italy';

    case Ielts = 'ielts';

    case GermanLanguage = 'german-language';

    case ItalianLanguage = 'italian-language';

    /** Attestation Services detail page (old site: mea-attestion.php). */
    case Attestation = 'attestation';
}
