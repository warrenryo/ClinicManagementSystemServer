<?php

namespace App\Helpers;

trait SearchableEnums
{
    public function label(): string
    {
        return str_replace('_', ' ', ucwords(strtolower($this->name)));
    }

    public static function searchByName(string $search): array
    {
        $search = strtolower($search);
        $matches = [];

        foreach (self::cases() as $case) {
            $label = method_exists($case, 'label')
                ? $case->label()
                : str_replace('_', ' ', ucwords(strtolower($case->name)));

            if (str_contains(strtolower($label), $search)) {
                $matches[] = $case->value;
            }
        }

        return $matches;
    }
}
