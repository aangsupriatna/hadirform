<?php

namespace App\Services;

class FormHeaderPresetService
{
    /**
     * Preset header images provided out-of-the-box.
     *
     * @var array<string, array<string, string>>
     */
    protected static array $presets = [
        'preset_seminar' => [
            'id' => 'preset_seminar',
            'name' => 'Workshop & Seminar',
            'subtitle' => 'Transformasi Digital & Teknologi',
            'path' => '/images/headers/preset_seminar.svg',
        ],
        'preset_conference' => [
            'id' => 'preset_conference',
            'name' => 'Konferensi & Rapat Kerja',
            'subtitle' => 'Annual Summit & Koordinasi',
            'path' => '/images/headers/preset_conference.svg',
        ],
        'preset_education' => [
            'id' => 'preset_education',
            'name' => 'Pelatihan & Pendidikan',
            'subtitle' => 'Learning, Training & Growth',
            'path' => '/images/headers/preset_education.svg',
        ],
        'preset_formal' => [
            'id' => 'preset_formal',
            'name' => 'Acara Resmi & Kedinasan',
            'subtitle' => 'Protokoler, Upacara & Kenegaraan',
            'path' => '/images/headers/preset_formal.svg',
        ],
        'preset_collaboration' => [
            'id' => 'preset_collaboration',
            'name' => 'Diskusi & Komunitas',
            'subtitle' => 'Meetup, Forum & Kolaborasi',
            'path' => '/images/headers/preset_collaboration.svg',
        ],
        'preset_celebration' => [
            'id' => 'preset_celebration',
            'name' => 'Gala & Perayaan',
            'subtitle' => 'Gathering, Award & Perayaan',
            'path' => '/images/headers/preset_celebration.svg',
        ],
    ];

    /**
     * Get all provided preset headers.
     *
     * @return array<string, array<string, string>>
     */
    public static function all(): array
    {
        return self::$presets;
    }

    /**
     * Find a preset by id or path.
     */
    public static function find(?string $idOrPath): ?array
    {
        if (empty($idOrPath)) {
            return null;
        }

        if (isset(self::$presets[$idOrPath])) {
            return self::$presets[$idOrPath];
        }

        foreach (self::$presets as $preset) {
            if ($preset['path'] === $idOrPath) {
                return $preset;
            }
        }

        return null;
    }
}
