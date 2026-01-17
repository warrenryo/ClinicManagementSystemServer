<?php

namespace App\Helpers;

use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

trait SearchableTrait
{
    public function scopeSearch(
        Builder $query,
        ?string $search,
        array $columns = [],
        array $relations = [],
        array $enumMappings = [],
        array $dateColumns = [] // NEW: Specify which columns are dates ['column_name', ...]
    ): Builder {
        $search = trim((string) $search);

        if ($search === '') {
            return $query;
        }

        $keywords = array_filter(explode(' ', $search));

        return $query->where(function (Builder $q) use ($keywords, $columns, $relations, $enumMappings, $dateColumns, $search) {

            // 1️⃣ User table columns (only if columns array is not empty)
            if (!empty($columns) && !$this->isEmptyArray($columns)) {
                $q->where(function (Builder $sub) use ($columns, $keywords, $enumMappings, $dateColumns) {
                    foreach ($keywords as $keyword) {
                        $sub->where(function (Builder $inner) use ($columns, $keyword, $enumMappings, $dateColumns) {
                            foreach ($columns as $column) {
                                // Skip empty strings
                                if (empty($column)) {
                                    continue;
                                }

                                // Check if this is a date column
                                if (in_array($column, $dateColumns)) {
                                    continue; // Skip - will be handled in date section
                                }

                                // Check if this column has an enum mapping on current model
                                $currentModelEnums = $enumMappings[''] ?? [];

                                if (isset($currentModelEnums[$column])) {
                                    $enumClass = $currentModelEnums[$column];

                                    if (method_exists($enumClass, 'searchByName')) {
                                        $matchingValues = $enumClass::searchByName($keyword);

                                        if (!empty($matchingValues)) {
                                            $inner->orWhereIn($column, $matchingValues);
                                        }
                                    }
                                } else {
                                    $inner->orWhere($column, 'LIKE', "%{$keyword}%");
                                }
                            }
                        });
                    }
                });
            }

            // 1.5️⃣ Handle enum-only searches (when columns are empty but enums are specified)
            if ((empty($columns) || $this->isEmptyArray($columns)) && !empty($enumMappings[''])) {
                $q->where(function (Builder $sub) use ($keywords, $enumMappings) {
                    foreach ($keywords as $keyword) {
                        $sub->where(function (Builder $inner) use ($keyword, $enumMappings) {
                            foreach ($enumMappings[''] as $column => $enumClass) {
                                if (method_exists($enumClass, 'searchByName')) {
                                    $matchingValues = $enumClass::searchByName($keyword);

                                    if (!empty($matchingValues)) {
                                        $inner->orWhereIn($column, $matchingValues);
                                    }
                                }
                            }
                        });
                    }
                });
            }

            // 1.75️⃣ Handle date searches
            if (!empty($dateColumns)) {
                $q->orWhere(function (Builder $sub) use ($dateColumns, $search) {
                    $this->applyDateSearch($sub, $dateColumns, $search);
                });
            }

            // 2️⃣ Related tables with enum support
            foreach ($relations as $relation => $fields) {
                if (!method_exists($q->getModel(), $relation)) {
                    continue;
                }

                $q->orWhereHas($relation, function (Builder $rel) use ($fields, $keywords, $enumMappings, $relation) {
                    foreach ($keywords as $keyword) {
                        $rel->where(function (Builder $subRel) use ($fields, $keyword, $enumMappings, $relation) {
                            foreach ($fields as $field) {
                                // Check if this field has an enum mapping
                                if (isset($enumMappings[$relation][$field])) {
                                    $enumClass = $enumMappings[$relation][$field];

                                    if (method_exists($enumClass, 'searchByName')) {
                                        $matchingValues = $enumClass::searchByName($keyword);

                                        if (!empty($matchingValues)) {
                                            $subRel->orWhereIn($field, $matchingValues);
                                        }
                                    }
                                } else {
                                    // Regular string search
                                    $subRel->orWhere($field, 'LIKE', "%{$keyword}%");
                                }
                            }
                        });
                    }
                });
            }
        });
    }

    /**
     * Apply date search logic for various formats
     */
    private function applyDateSearch(Builder $query, array $dateColumns, string $search): void
    {
        foreach ($dateColumns as $column) {
            $query->orWhere(function (Builder $sub) use ($column, $search) {
                // Try to parse as full date: "January 22, 2026" or "2026-01-22"
                try {
                    $date = Carbon::parse($search);
                    $sub->whereDate($column, $date->format('Y-m-d'));
                    return;
                } catch (\Exception $e) {
                    // Not a full date, continue to other formats
                }

                // Match by month and year: "January 2026"
                if (preg_match('/^([a-zA-Z]+)\s+(\d{4})$/', $search, $matches)) {
                    try {
                        $monthYear = Carbon::parse($matches[1] . ' 1, ' . $matches[2]);
                        $sub->whereMonth($column, $monthYear->month)
                            ->whereYear($column, $monthYear->year);
                        return;
                    } catch (\Exception $e) {
                        // Invalid month name
                    }
                }

                // Match by year only: "2026"
                if (preg_match('/^\d{4}$/', $search)) {
                    $sub->whereYear($column, $search);
                    return;
                }

                // Match by month name only: "January"
                try {
                    $month = Carbon::parse($search . ' 1');
                    $sub->whereMonth($column, $month->month);
                    return;
                } catch (\Exception $e) {
                    // Not a valid month name
                }

                // Match using MySQL DATE_FORMAT for flexible searching
                // This catches formats like "Jan 22", "22 Jan", etc.
                $sub->whereRaw("DATE_FORMAT({$column}, '%M %d, %Y') LIKE ?", ["%{$search}%"])
                    ->orWhereRaw("DATE_FORMAT({$column}, '%d %M %Y') LIKE ?", ["%{$search}%"])
                    ->orWhereRaw("DATE_FORMAT({$column}, '%M %d') LIKE ?", ["%{$search}%"])
                    ->orWhereRaw("DATE_FORMAT({$column}, '%b %d, %Y') LIKE ?", ["%{$search}%"]);
            });
        }
    }

    /**
     * Check if array is empty or contains only empty strings
     */
    private function isEmptyArray(array $arr): bool
    {
        return empty(array_filter($arr, fn($value) => !empty($value)));
    }
}
