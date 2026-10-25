<?php

use Illuminate\Support\Carbon;

// Small global helpers used in Blade views. Autoloaded via composer.json "files".

if (! function_exists('eur')) {
    /** Format a number as Spanish euros: 1234.5 => "1.234,50 €" */
    function eur($amount): string
    {
        return number_format((float) $amount, 2, ',', '.').' €';
    }
}

if (! function_exists('qty')) {
    /** Format a quantity with its sale unit: (1.5, 'kg') => "1,5 kg", (3, 'unit') => "3 ud." */
    function qty($quantity, ?string $unit = null): string
    {
        $q = (float) $quantity;
        $text = rtrim(rtrim(number_format($q, 3, ',', '.'), '0'), ',');
        $labels = ['unit' => 'ud.', 'kg' => 'kg', 'pack' => 'paq.'];

        return $unit ? $text.' '.($labels[$unit] ?? $unit) : $text;
    }
}

if (! function_exists('fdate')) {
    /** Format a date/time in Spanish style: 25/10/2026 14:30 */
    function fdate($date, bool $withTime = true): string
    {
        if (! $date) {
            return '—';
        }
        $date = $date instanceof DateTimeInterface ? $date : Carbon::parse($date);

        return $date->format($withTime ? 'd/m/Y H:i' : 'd/m/Y');
    }
}

if (! function_exists('icon')) {
    /** Render a clean Lucide SVG icon: icon('package', 'w-4 h-4') */
    function icon(string $name, string $class = '', int $size = 18): string
    {
        return view('components.icon', ['name' => $name, 'class' => $class, 'size' => $size])->render();
    }
}
