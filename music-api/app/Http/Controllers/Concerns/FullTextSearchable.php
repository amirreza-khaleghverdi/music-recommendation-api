<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;

trait FullTextSearchable
{
    public function scopeSearch(Builder $query, string $term): Builder
    {
        return $query->whereRaw(
            "search_vector @@ plainto_tsquery('english', ?)",
            [$term]
        );
    }

    public function scopeOrderByRelevance(Builder $query, string $term): Builder
    {
        return $query->orderByRaw(
            "ts_rank(search_vector, plainto_tsquery('english', ?)) DESC",
            [$term]
        );
    }
}