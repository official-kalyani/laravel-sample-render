<?php
namespace App\Helpers;

class ResumeParser
{
    public static function parse(string $text): array
    {
        return [
            'name' => self::match('/Name[:\s]+([A-Z][a-z]+\s[A-Z][a-z]+)/', $text),
            'email' => self::match('/[\w\.-]+@[\w\.-]+\.\w+/', $text),
            'phone' => self::match('/\+?\d[\d\s\-\(\)]{9,}/', $text),
            'education' => self::match('/Education[:\s]+(.+?)(Skills|Experience|$)/is', $text),
            'skills' => self::match('/Skills[:\s]+(.+?)(Experience|Education|$)/is', $text),
            'experience' => self::match('/Experience[:\s]+(.+?)(Education|Skills|$)/is', $text),
        ];
    }

    private static function match($pattern, $text)
    {
        preg_match($pattern, $text, $matches);
        return $matches[1] ?? null;
    }
}
