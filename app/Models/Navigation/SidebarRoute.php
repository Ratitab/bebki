<?php

namespace App\Models\Navigation;

use MongoDB\Laravel\Eloquent\Model;

class SidebarRoute extends Model
{
    protected $connection = 'mongodb';

    protected $collection = 'sidebar_routes';

    protected $fillable = ['key', 'routes', 'support_routes'];

    protected $casts = [
        'routes' => 'array',
        'support_routes' => 'array',
    ];
}
