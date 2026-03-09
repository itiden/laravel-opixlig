<?php

namespace Itiden\Opixlig\Utils;

use Illuminate\Support\Facades\Config;
use InvalidArgumentException;

final class Manipulations
{
    /** @var array<string, string> */
    private const ALIASES = [
        'width' => 'w',
        'height' => 'h',
        'format' => 'fm',
        'quality' => 'q',
    ];

    /** @var list<string> Keys extracted into structured return fields by resolve() */
    private const RESOLVED_KEYS = ['w', 'h', 'fm', 'q', 'fit', 'placeholder', 'widths'];

    /**
     * Resolve a preset + inline overrides into a structured result.
     *
     * @param  array<string, mixed>  $overrides  Inline prop values (using friendly or Glide keys).
     * @return array{w: int, h: int, fm: string, q: int|string, fit: string, placeholder: string, widths: list<int>, manipulations: array<string, string|int>}
     */
    public static function resolve(string $preset, array $overrides = []): array
    {
        $presetValues = [];

        if ($preset !== '') {
            $raw = Config::get("opixlig.presets.{$preset}");

            if (! is_array($raw)) {
                throw new InvalidArgumentException("Opixlig preset '{$preset}' is not defined.");
            }

            $presetValues = self::normalize($raw);
        }

        $overrides = self::normalize($overrides);

        $w = self::resolveInt($overrides, $presetValues, 'w');
        $h = self::resolveInt($overrides, $presetValues, 'h');

        self::validateDimensions($w, $h);

        $fm = self::resolveString($overrides, $presetValues, 'fm', Config::get('opixlig.defaults.format', 'webp'));
        $q = $overrides['q'] ?? $presetValues['q'] ?? Config::get('opixlig.defaults.quality', 75);
        $q = is_int($q) || is_string($q) ? $q : 75;
        $fit = self::resolveString($overrides, $presetValues, 'fit', '');
        $placeholder = self::resolveString($overrides, $presetValues, 'placeholder', Config::get('opixlig.defaults.placeholder', 'empty'));

        /** @var list<int> $defaultWidths */
        $defaultWidths = Config::get('opixlig.defaults.widths', []);

        $widths = match (true) {
            isset($overrides['widths']) && is_array($overrides['widths']) => $overrides['widths'],
            isset($presetValues['widths']) && is_array($presetValues['widths']) => $presetValues['widths'],
            default => $defaultWidths,
        };

        self::validateWidths($widths);

        $presetManipulations = collect($presetValues)->except(self::RESOLVED_KEYS)->all();
        $overrideManipulations = collect($overrides)->except(self::RESOLVED_KEYS)->all();

        $manipulations = array_filter([
            'fm' => $fm,
            'q' => $q,
            'fit' => $fit,
        ], static fn (mixed $value): bool => $value !== '' && $value !== null);

        /** @var array<string, string|int> $merged */
        $merged = array_merge($presetManipulations, $manipulations, $overrideManipulations);

        /** @var list<int> $widths */
        return [
            'w' => $w,
            'h' => $h,
            'fm' => $fm,
            'q' => $q,
            'fit' => $fit,
            'placeholder' => $placeholder,
            'widths' => $widths,
            'manipulations' => $merged,
        ];
    }

    /**
     * @param  array<string, mixed>  $manipulations
     * @return array<string, mixed>
     */
    public static function normalize(array $manipulations): array
    {
        $normalized = [];

        foreach ($manipulations as $key => $value) {
            $normalized[self::ALIASES[$key] ?? $key] = $value;
        }

        return $normalized;
    }

    /**
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

    /** @return array<string, string> */
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

    /**
     * Validate that dimensions are either both non-zero or both zero.
     *
     * @throws InvalidArgumentException
     */
    private static function validateDimensions(int $w, int $h): void
    {
        if (($w === 0) !== ($h === 0)) {
            throw new InvalidArgumentException(
                'Opixlig requires both width and height to be set together, or neither. Received: w='.$w.', h='.$h.'.'
            );
        }
    }

    /**
     * Validate that widths is a non-empty list of positive integers.
     *
     * @param  array<mixed>  $widths
     *
     * @throws InvalidArgumentException
     */
    private static function validateWidths(array $widths): void
    {
        if ($widths === []) {
            throw new InvalidArgumentException('Opixlig widths must be a non-empty list of positive integers.');
        }

        foreach ($widths as $width) {
            if (! is_int($width) || $width <= 0) {
                throw new InvalidArgumentException(
                    'Opixlig widths must be a non-empty list of positive integers. Got: '.json_encode($width).'.'
                );
            }
        }
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @param  array<string, mixed>  $preset
     */
    private static function resolveInt(array $overrides, array $preset, string $key): int
    {
        $value = $overrides[$key] ?? $preset[$key] ?? 0;

        return is_int($value) ? $value : 0;
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @param  array<string, mixed>  $preset
     */
    private static function resolveString(array $overrides, array $preset, string $key, mixed $default): string
    {
        $value = $overrides[$key] ?? $preset[$key] ?? $default;

        return is_string($value) ? $value : '';
    }
}
