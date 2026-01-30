<?php

namespace App\Traits;

use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Expression;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

trait HandlesSorting
{
    /**
     * Apply sorting and grouping to a query based on request parameters.
     *
     * @param  array<string, string|Expression|Closure>  $sortableColumns  Map of URL key => DB column, Expression, or Closure
     * @param  array<string>  $groupableColumns  URL keys that can be grouped
     */
    protected function applySorting(
        Builder $query,
        Request $request,
        array $sortableColumns,
        string $defaultSort,
        string $defaultDirection = 'asc',
        array $groupableColumns = [],
    ): Builder {
        $sort = $request->query('sort');
        $direction = $request->query('direction', 'asc');
        $group = $request->query('group');

        $direction = in_array($direction, ['asc', 'desc']) ? $direction : 'asc';

        if ($group && in_array($group, $groupableColumns) && isset($sortableColumns[$group])) {
            $this->applyOrderBy($query, $sortableColumns[$group], 'asc');
        }

        if ($sort && isset($sortableColumns[$sort])) {
            if (! ($group && $group === $sort)) {
                $this->applyOrderBy($query, $sortableColumns[$sort], $direction);
            } elseif ($group === $sort) {
                $query->reorder();
                $this->applyOrderBy($query, $sortableColumns[$group], $direction);
            }
        } else {
            $defaultColumn = $sortableColumns[$defaultSort] ?? $defaultSort;
            $this->applyOrderBy($query, $defaultColumn, $defaultDirection);
        }

        return $query;
    }

    /**
     * Apply an orderBy clause handling strings, Expressions, and Closures.
     */
    private function applyOrderBy(Builder $query, string|Expression|Closure $column, string $direction): void
    {
        if ($column instanceof Closure) {
            $column($query, $direction);
        } elseif ($column instanceof Expression) {
            $query->orderBy($column, $direction);
        } else {
            $query->orderBy($column, $direction);
        }
    }

    /**
     * Get sort parameters to pass to the view.
     *
     * @param  array<string, string>  $sortableColumns
     * @param  array<string>  $groupableColumns
     * @return array{sort: ?string, direction: string, group: ?string}
     */
    protected function getSortParameters(
        Request $request,
        array $sortableColumns,
        array $groupableColumns = [],
    ): array {
        $sort = $request->query('sort');
        $direction = $request->query('direction', 'asc');
        $group = $request->query('group');

        return [
            'sort' => $sort && isset($sortableColumns[$sort]) ? $sort : null,
            'direction' => in_array($direction, ['asc', 'desc']) ? $direction : 'asc',
            'group' => $group && in_array($group, $groupableColumns) && isset($sortableColumns[$group]) ? $group : null,
        ];
    }

    /**
     * Get all unique group values from the query for cross-page grouping.
     *
     * @param  string|Expression|Closure  $column  The database column or closure
     * @param  Closure|null  $transformer  Optional function to transform raw values into display format
     * @return Collection<int, array{key: string, label: string}>
     */
    protected function getAllGroupValues(
        Builder $query,
        string|Expression|Closure $column,
        ?Closure $transformer = null,
    ): Collection {
        if ($column instanceof Closure) {
            return collect();
        }

        $columnName = $column instanceof Expression ? (string) $column : $column;

        $values = (clone $query)
            ->reorder()
            ->select($columnName)
            ->distinct()
            ->orderBy($columnName)
            ->pluck($columnName);

        if ($transformer) {
            return $values->map($transformer)->unique('key')->values();
        }

        return $values->map(fn ($value) => [
            'key' => (string) $value,
            'label' => (string) $value,
        ])->values();
    }
}
