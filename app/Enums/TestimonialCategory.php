<?php

declare(strict_types=1);

namespace App\Enums;

/** The EuroRank service a testimonial is about. */
enum TestimonialCategory: string
{
    /** University admissions & student visas (Germany / Italy). */
    case Admissions = 'admissions';

    case Ielts = 'ielts';

    case German = 'german';

    case Italian = 'italian';
}
