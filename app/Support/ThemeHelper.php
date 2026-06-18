<?php

namespace App\Support;

use App\Models\SiteSetting;

class ThemeHelper
{
    /**
     * Get the current active theme
     * 
     * @return string
     */
    public static function current(): string
    {
        try {
            $theme = SiteSetting::get()->theme ?? 'theme1';
            // Validate theme exists
            if (!in_array($theme, ['theme1', 'theme2', 'theme3', 'pure-rose'])) {
                return 'pure-rose';
            }
            return $theme;
        } catch (\Throwable $e) {
            return 'pure-rose';
        }
    }

    /**
     * Get the view path for a given view name with theme
     * 
     * @param string $view
     * @return string
     */
    public static function view(string $view): string
    {
        $theme = static::current();
        $themedView = "themes.{$theme}.{$view}";
        $fallbackView = "frontend.{$view}";
        if (view()->exists($themedView)) return $themedView;
        return $fallbackView;
    }

    /**
     * Get all available themes
     * 
     * @return array
     */
    public static function available(): array
    {
        return [
            'theme1' => [
                'name' => 'Theme 1',
                'description' => 'Classic Modern Design',
                'preview' => 'Purple gradient hero, clean layout'
            ],
            'theme2' => [
                'name' => 'Theme 2',
                'description' => 'Minimalist Dark/Light Theme',
                'preview' => 'Dark mode support, minimalist design'
            ],
            'theme3' => [
                'name' => 'Theme 3',
                'description' => 'Poppins Font, Modern Glass Solutions Theme',
                'preview' => 'Bootstrap 5, Font Awesome, Poppins font family'
            ],
            'pure-rose' => [
                'name' => 'PURE ROSE',
                'description' => 'Premium luxury floral boutique — burgundy, gold, cinematic',
                'preview' => 'Glassmorphism, occasions CRM, floral AI assistant'
            ]
        ];
    }
}

