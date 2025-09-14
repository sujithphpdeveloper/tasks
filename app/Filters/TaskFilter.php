<?php

namespace App\Filters;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class TaskFilter
{
    protected Request $request;

    protected static $filters = [
        'status',
        'priority',
        'assigned',
        'tags',
        'due_date_from',
        'due_date_to',
        'keyword'
    ];

    public static function apply(Builder $query, Request $request): Builder
    {
        $instance = new static();

        foreach (static::$filters as $filter) {
            if ($request->has($filter) && method_exists($instance, $filter)) {
                $instance->$filter($query, $request->get($filter));
            }
        }

        return $query;
    }

    protected static function status(Builder $query, $value): Builder
    {
        return $query->where('status', $value);
    }

    protected static function priority(Builder $query, $value): Builder
    {
        return $query->where('priority', $value);
    }

    protected static function assigned_to(Builder $query, $value): Builder
    {
        return $query->where('assigned_to', $value);
    }

    protected static function tags(Builder $query, $value): Builder
    {
        $tags = explode(',', $value);
        return $query->whereHas('tags', function ($query) use ($tags) {
            $query->whereIn('tags.id', $tags);
        });
    }

    protected static function due_date_from(Builder $query, $value): Builder
    {
        return $query->where('due_date', '>=', Carbon::parse($value)->startOfDay());
    }

    protected static function due_date_to(Builder $query, $value): Builder
    {
        return $query->where('due_date', '<=', Carbon::parse($value)->endOfDay());
    }

    protected static function keyword(Builder $query, $value): Builder
    {
        return $query->where(function ($query) use ($value) {
            $query->whereFullText(['title', 'description'], $value);
        });
    }

}
