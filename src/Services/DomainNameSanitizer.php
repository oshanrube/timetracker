<?php

namespace App\Services;

class DomainNameSanitizer
{
    public static function sanitize($string)
    {
        // Convert to lowercase and normalize Unicode characters
        $string = mb_strtolower($string, 'UTF-8');
        $string = \Normalizer::normalize($string, \Normalizer::NFKD);

        // Remove invalid characters
        $invalidChars = array('.', ' ', '_', '(', ')', ':', ';', '/', '@', '\\', '?', '%', '#', '[', ']');
        $string       = str_replace($invalidChars, '-', $string);

        // Replace multiple hyphens with a single hyphen
        $string = preg_replace('/-+/', '-', $string);

        // Trim leading and trailing hyphens
        $string = trim($string, '-');

        // Truncate to max length
        $string = mb_substr($string, 0, 63, 'UTF-8');

        // Ensure the string ends with a valid domain character (alphanumeric or hyphen)
        $lastChar = mb_substr($string, -1, 1, 'UTF-8');
        if (!ctype_alnum($lastChar) && $lastChar !== '-') {
            $string = mb_substr($string, 0, -1, 'UTF-8');
        }

        return $string;
    }
}