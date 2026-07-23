<?php

namespace Farukcoder\FeatureHeatmap\Models;

use Illuminate\Database\Eloquent\Model;

class FeatureUsageLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'controller_action',
        'route_name',
        'method',
        'uri',
        'user_id',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];
}
