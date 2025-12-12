<?php

namespace App\Support;

use App\Models\SiteSetting;

class LoginContent
{
    public static function defaults(): array
    {
        return [
            'brand_accent' => 'Suite',
            'badge' => 'Ranked as #1 group-buy tools service',
            'headline_prefix' => 'The only',
            'headline_accent' => 'Group Buy Tools',
            'headline_suffix' => "membership you'll ever require!",
            'lead' => 'Access 400+ premium SEO tools, AI assistants, and design assets inside a single membership. Fast onboarding, secure access, and zero individual subscriptions.',
            'logo_path' => null,
            'perks' => [
                'Instant Access',
                '99.9% Uptime',
                'Simple Pricing',
            ],
            'card_chip' => 'Welcome back',
            'card_title' => 'Sign in to continue',
            'card_subtitle' => 'Manage orders, stock, and your sales dashboard from one place.',
        ];
    }

    public static function current(): array
    {
        $stored = json_decode(SiteSetting::value('login_content', '[]'), true);
        $defaults = static::defaults();

        if (! is_array($stored)) {
            return $defaults;
        }

        return array_replace_recursive($defaults, $stored);
    }
}
