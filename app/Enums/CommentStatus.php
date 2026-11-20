<?php

declare(strict_types=1);

namespace App\Enums;

/** Moderation state of a blog comment. Only approved comments are public. */
enum CommentStatus: string
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Spam = 'spam';
}
