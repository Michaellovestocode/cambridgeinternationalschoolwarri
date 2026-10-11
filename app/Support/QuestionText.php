<?php

namespace App\Support;

class QuestionText
{
    /**
     * Keep the small set of formatting supported by the question editor and
     * discard all attributes and unsupported markup from pasted content.
     */
    public static function sanitize(?string $text): string
    {
        $text = html_entity_decode($text ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = str_replace(["\xc2\xa0", '&nbsp;'], ' ', $text);
        $allowed = '<p><br><strong><b><em><i><u><ol><ul><li><sub><sup>';
        $text = strip_tags($text, $allowed);

        return preg_replace_callback(
            '~</?(p|br|strong|b|em|i|u|ol|ul|li|sub|sup)\b[^>]*>~i',
            static function (array $match): string {
                preg_match('~^<(\/)?([a-z]+)~i', $match[0], $parts);
                $tag = strtolower($parts[2]);

                return '<' . (($parts[1] ?? '') === '/' ? '/' : '') . $tag . '>';
            },
            $text
        ) ?? '';
    }
}
