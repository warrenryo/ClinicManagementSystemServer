<?php

namespace App\Helpers;

use Illuminate\Database\Eloquent\Builder;

trait SearchableTrait
{
    public function scopeSearch(Builder $query, ?string $search, array $columns = [], array $relations = []): Builder
    {
        $search = trim((string)$search);
        if (!$search) return $query;

        return $query->where(function (Builder $q) use ($search, $columns, $relations) {
            // Direct columns
            $first = true;
            foreach ($columns as $column) {
                if ($first) {
                    $q->where($column, 'LIKE', "%{$search}%");
                    $first = false;
                } else {
                    $q->orWhere($column, 'LIKE', "%{$search}%");
                }
            }

            // Related models
            foreach ($relations as $relation => $fields) {
                if (!method_exists($q->getModel(), $relation)) continue;

                $q->orWhereHas($relation, function (Builder $subQuery) use ($fields, $search) {
                    $firstRel = true;
                    foreach ($fields as $field) {
                        if ($firstRel) {
                            $subQuery->where($field, 'LIKE', "%{$search}%");
                            $firstRel = false;
                        } else {
                            $subQuery->orWhere($field, 'LIKE', "%{$search}%");
                        }
                    }
                });
            }
        });
    }
}
