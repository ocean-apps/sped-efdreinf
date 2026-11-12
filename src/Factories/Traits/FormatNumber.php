<?php

namespace NFePHP\EFDReinf\Factories\Traits;

use InvalidArgumentException;

trait FormatNumber
{
    /**
     * Format a decimal without converting it to binary floating point.
     * Input must be a decimal string (or integer), with a dot as decimal separator.
     */
    protected static function format($value = null, $decimals = 2, $separator = ',')
    {
        if ($value === null) {
            return null;
        }

        if (! is_string($value) && ! is_int($value)) {
            throw new InvalidArgumentException(
                'Decimal value must be a string or integer.'
            );
        }

        $value = (string) $value;

        if (! preg_match('/^[+-]?[0-9]+(?:\.[0-9]+)?$/D', $value)) {
            throw new InvalidArgumentException(
                'Invalid decimal value: ' . $value
            );
        }

        if (! is_int($decimals) || $decimals < 0 || $decimals > 12) {
            throw new InvalidArgumentException(
                'Invalid decimal scale.'
            );
        }

        // Round half away from zero using BCMath; never cast to float.
        $negative = str_starts_with($value, '-');
        $absolute = ltrim($value, '+-');
        $offset = '0.' . str_repeat('0', $decimals) . '5';
        $rounded = bcadd($absolute, $offset, $decimals);
        $isZero = bccomp($rounded, '0', $decimals) === 0;
        $parts = explode('.', $rounded, 2);
        $integer = ltrim($parts[0], '0');
        $integer = $integer === '' ? '0' : $integer;
        $fraction = $decimals > 0
            ? $separator . str_pad($parts[1] ?? '', $decimals, '0')
            : '';

        return ($negative && ! $isZero ? '-' : '') . $integer . $fraction;
    }
}
