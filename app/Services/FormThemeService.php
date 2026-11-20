<?php

namespace App\Services;

class FormThemeService
{
    /**
     * All registered form color themes.
     *
     * @var array<string, array<string, string>>
     */
    protected static array $themes = [
        'indigo' => [
            'id' => 'indigo',
            'name' => 'Indigo Modern',
            'primary' => '#4f46e5',
            'hover' => '#4338ca',
            'light' => '#eef2ff',
            'border' => '#c7d2fe',
            'text' => '#3730a3',
            'ring' => 'rgba(79, 70, 229, 0.25)',
        ],
        'blue' => [
            'id' => 'blue',
            'name' => 'Biru Bahari',
            'primary' => '#2563eb',
            'hover' => '#1d4ed8',
            'light' => '#eff6ff',
            'border' => '#bfdbfe',
            'text' => '#1e40af',
            'ring' => 'rgba(37, 99, 235, 0.25)',
        ],
        'emerald' => [
            'id' => 'emerald',
            'name' => 'Hijau Zamrud',
            'primary' => '#059669',
            'hover' => '#047857',
            'light' => '#ecfdf5',
            'border' => '#a7f3d0',
            'text' => '#065f46',
            'ring' => 'rgba(5, 150, 105, 0.25)',
        ],
        'teal' => [
            'id' => 'teal',
            'name' => 'Toska Elegan',
            'primary' => '#0d9488',
            'hover' => '#0f766e',
            'light' => '#f0fdfa',
            'border' => '#99f6e4',
            'text' => '#115e59',
            'ring' => 'rgba(13, 148, 136, 0.25)',
        ],
        'rose' => [
            'id' => 'rose',
            'name' => 'Merah Delima',
            'primary' => '#e11d48',
            'hover' => '#be123c',
            'light' => '#fff1f2',
            'border' => '#fecdd3',
            'text' => '#9f1239',
            'ring' => 'rgba(225, 29, 72, 0.25)',
        ],
        'amber' => [
            'id' => 'amber',
            'name' => 'Jingga Surya',
            'primary' => '#d97706',
            'hover' => '#b45309',
            'light' => '#fffbeb',
            'border' => '#fde68a',
            'text' => '#92400e',
            'ring' => 'rgba(217, 119, 6, 0.25)',
        ],
        'purple' => [
            'id' => 'purple',
            'name' => 'Ungu Violet',
            'primary' => '#7c3aed',
            'hover' => '#6d28d9',
            'light' => '#f5f3ff',
            'border' => '#ddd6fe',
            'text' => '#5b21b6',
            'ring' => 'rgba(124, 58, 237, 0.25)',
        ],
        'slate' => [
            'id' => 'slate',
            'name' => 'Abu Korporat',
            'primary' => '#334155',
            'hover' => '#1e293b',
            'light' => '#f8fafc',
            'border' => '#cbd5e1',
            'text' => '#0f172a',
            'ring' => 'rgba(51, 65, 85, 0.25)',
        ],
    ];

    /**
     * Get all available themes.
     */
    public static function all(): array
    {
        return self::$themes;
    }

    /**
     * Get a specific theme by key (or default if not found).
     */
    public static function get(?string $key): array
    {
        $themeKey = strtolower(trim((string)$key));
        return self::$themes[$themeKey] ?? self::$themes['indigo'];
    }

    /**
     * Check if a theme key exists.
     */
    public static function has(?string $key): bool
    {
        return isset(self::$themes[strtolower(trim((string)$key))]);
    }

    /**
     * Generate inline CSS custom property string for a theme.
     */
    public static function getCssVariables(?string $key): string
    {
        $theme = self::get($key);

        return sprintf(
            '--theme-primary: %s; --theme-primary-hover: %s; --theme-primary-light: %s; --theme-primary-border: %s; --theme-primary-text: %s; --theme-primary-ring: %s;',
            $theme['primary'],
            $theme['hover'],
            $theme['light'],
            $theme['border'],
            $theme['text'],
            $theme['ring']
        );
    }
}
