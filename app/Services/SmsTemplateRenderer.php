<?php

namespace App\Services;

class SmsTemplateRenderer
{
    /**
     * Render an admin-configurable SMS template (Settings > SMS Templates),
     * falling back to $default when the setting is unset or blank — so a
     * fresh install (or a template the admin clears out) behaves exactly
     * like the hardcoded message this replaced. Placeholders use {{name}}
     * syntax and are substituted with plain strtr(), not a template engine,
     * so nothing in $vars can execute anything.
     *
     * @param  array<string, string>  $vars
     */
    public static function render(string $settingKey, string $default, array $vars): string
    {
        $stored = setting($settingKey, '');
        $template = trim((string) $stored) !== '' ? $stored : $default;

        $replacements = [];
        foreach ($vars as $key => $value) {
            $replacements['{{'.$key.'}}'] = (string) $value;
        }

        return strtr($template, $replacements);
    }
}
