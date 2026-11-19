<?php

declare(strict_types=1);

namespace App\Enums;

/** Groups FAQs by the audience/page they are shown to. */
enum FaqCategory: string
{
    /** Study-abroad questions (home page). */
    case Students = 'students';

    /** Visa & immigration questions (about page). */
    case Immigration = 'immigration';
}
