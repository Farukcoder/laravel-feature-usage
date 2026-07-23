<?php

namespace Farukcoder\FeatureHeatmap\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class FeatureUsageSummary extends Model
{
    protected $fillable = [
        'controller_action',
        'route_name',
        'user_id',
        'usage_date',
        'hit_count',
    ];

    protected $casts = [
        'usage_date' => 'date',
    ];

    /**
     * Scope to filter records within a given date range.
     */
    public function scopeBetweenDates(Builder $query, string $from, string $to): Builder
    {
        return $query->whereBetween('usage_date', [$from, $to]);
    }

    /**
     * Group by feature (controller_action) and date to get total hits.
     * Used for the Feature × Time heatmap.
     */
    public static function heatmapByFeatureAndDate(string $from, string $to): \Illuminate\Support\Collection
    {
        return static::query()
            ->betweenDates($from, $to)
            ->selectRaw('controller_action, usage_date, SUM(hit_count) as total')
            ->groupBy('controller_action', 'usage_date')
            ->orderBy('usage_date')
            ->get();
    }

    /**
     * Group by feature and user to see how many times each user accessed each feature.
     * Used for the Feature × User heatmap.
     */
    public static function heatmapByFeatureAndUser(string $from, string $to): \Illuminate\Support\Collection
    {
        return static::query()
            ->betweenDates($from, $to)
            ->selectRaw('controller_action, user_id, SUM(hit_count) as total')
            ->groupBy('controller_action', 'user_id')
            ->orderByDesc('total')
            ->get();
    }

    /**
     * Return features that have not been used in the last $days days.
     * Cross-reference with your routes list to identify dead features.
     */
    public static function unusedSince(int $days): \Illuminate\Support\Collection
    {
        return static::query()
            ->select('controller_action')
            ->groupBy('controller_action')
            ->havingRaw('MAX(usage_date) < ?', [now()->subDays($days)->toDateString()])
            ->get();
    }
}
