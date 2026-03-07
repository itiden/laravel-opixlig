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
            ->map(function (mixed $value, string $key): string {
                assert(is_string($value) || is_int($value));

                return "{$key}-{$value}";
            })
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
            ->mapWithKeys(function (string $pair): array {
                [$key, $value] = explode('-', $pair, 2);

                return [$key => $value];
            })
            ->toArray();

        ksort($manipulations);

        /** @var array<string, string> $manipulations */
        return $manipulations;
    }
}
