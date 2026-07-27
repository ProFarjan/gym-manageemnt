<?php

use App\Models\Setting;

if (! function_exists('setting')) {
    /**
     * Read an admin-configurable setting, falling back to $default when unset.
     * Backed by a cache that Setting::booted() invalidates on every write.
     */
    function setting(string $key, mixed $default = null): mixed
    {
        return Setting::cached()[$key] ?? $default;
    }
}
