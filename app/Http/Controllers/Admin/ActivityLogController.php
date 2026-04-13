<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;

/**
 * Activity Log Viewer — Admin only, rank >= 8
 */
class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $query = Activity::with('causer')->latest();

        // Filter by date range
        if ($from = $request->input('from')) {
            $query->whereDate('created_at', '>=', $from);
        }
        if ($to = $request->input('to')) {
            $query->whereDate('created_at', '<=', $to);
        }

        // Filter by user (causer)
        if ($userId = $request->input('user_id')) {
            $query->where('causer_id', $userId)->where('causer_type', User::class);
        }

        // Filter by log name / action type
        if ($logName = $request->input('log_name')) {
            $query->where('log_name', $logName);
        }

        $logs     = $query->paginate(25)->withQueryString();
        $users    = User::select('id', 'name', 'email')->get();
        $logNames = Activity::distinct()->pluck('log_name')->filter()->sort()->values();

        return view('admin.activity-log.index', compact('logs', 'users', 'logNames'));
    }
}
