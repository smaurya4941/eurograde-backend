<?php

declare(strict_types=1);

namespace App\Support;

use Symfony\Component\HtmlSanitizer\HtmlSanitizer;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig;

/**
 * Sanitises rich article HTML before it is stored, so the frontend can render
 * it as-is. Only content markup survives: no scripts, styles, event handlers,
 * iframes or javascript: URLs.
 */
final class ArticleHtml
{
    private static ?HtmlSanitizer $sanitizer = null;

    public static function sanitize(string $html): string
    {
        return trim(self::sanitizer()->sanitize($html));
    }

    private static function sanitizer(): HtmlSanitizer
    {
        if (self::$sanitizer !== null) {
            return self::$sanitizer;
        }

        $config = (new HtmlSanitizerConfig)
            ->allowLinkSchemes(['https', 'http', 'mailto', 'tel'])
            ->allowRelativeLinks()
            ->allowMediaSchemes(['https', 'http'])
            ->allowRelativeMedias()
            ->forceAttribute('a', 'rel', 'noopener noreferrer')
            ->withMaxInputLength(200_000);

        $blocks = ['p', 'br', 'hr', 'h2', 'h3', 'h4', 'h5', 'h6', 'blockquote', 'cite', 'ul', 'ol', 'li', 'figure', 'figcaption'];
        $inline = ['strong', 'b', 'em', 'i', 'u', 's', 'sub', 'sup', 'code', 'mark', 'small'];
        $table = ['table', 'caption', 'thead', 'tbody', 'tfoot', 'tr', 'th', 'td'];

        foreach ([...$blocks, ...$inline, ...$table] as $element) {
            $config = $config->allowElement($element);
        }

        $config = $config
            ->allowElement('a', ['href', 'title', 'target'])
            ->allowElement('img', ['src', 'alt', 'width', 'height', 'loading']);

        return self::$sanitizer = new HtmlSanitizer($config);
    }
}
