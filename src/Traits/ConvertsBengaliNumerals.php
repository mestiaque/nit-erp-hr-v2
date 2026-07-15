<?php

namespace ME\Hr\Traits;

use Illuminate\Http\Request;

trait ConvertsBengaliNumerals
{
    protected function isBangla(Request $request): bool
    {
        return $request->query('lang', 'en') === 'bn';
    }

    protected function localizeNumber($value, bool $bangla)
    {
        if (blank($value)) {
            return $value;
        }

        return $bangla ? en2bnNumber((string) $value) : $value;
    }

    protected function localizeDate($date, bool $bangla, string $format = 'd/m/Y'): ?string
    {
        if (blank($date)) {
            return null;
        }

        return $bangla
            ? bn_date($date, $format)
            : \Carbon\Carbon::parse($date)->format($format);
    }

    protected function localizeName(?string $bnName, ?string $enName, bool $bangla): ?string
    {
        return $bangla ? ($bnName ?? $enName) : ($enName ?? $bnName);
    }
}
