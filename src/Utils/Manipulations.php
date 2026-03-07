<?php

namespace Itiden\Opixlig\Utils;

final class Manipulations
{
    /**
     * Convert a manipulations array to a URL-safe string.
     *
     * Format: key-value pairs joined by underscores (e.g. "fit-crop-center_fm-webp_q-75").
     * Underscores separate pairs, first dash separates key from value.
     *
     * @param  array<string, mixed>  $manipulations
     */
    public static function stringify(array $manipulations): string
    {
        ksort($manipulations);

        return collect($manipulations)
            ->map(fn ($value, $key) => "{$key}-{$value}")
            ->implode('_');
    }

    /**
     * Parse a manipulation string back into an associative array.
     *
     * @return array<string, string>
     */
    public static function parse(string $manipulations): array
    {
        $manipulations = collect(explode('_', $manipulations))
            ->mapWithKeys(function ($pair) {
                [$key, $value] = explode('-', $pair, 2);

                return [$key => $value];
            })
            ->toArray();

        ksort($manipulations);

        return $manipulations;
    }
}
