<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Spatie\Activitylog\Models\Activity;

/**
 * Admin Dashboard — overview stats
 */
class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_users'    => User::count(),
            'active_users'   => User::where('is_active', true)->count(),
            'inactive_users' => User::where('is_active', false)->count(),
            'deleted_users'  => User::onlyTrashed()->count(),
            'locked_users'   => User::where('locked_until', '>', now())->count(),
            'recent_logs'    => Activity::with('causer')->latest()->limit(10)->get(),
        ];

        return view('admin.dashboard', compact('stats'));
    }
}
