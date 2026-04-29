<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Spatie\Activitylog\Models\Activity;

/**
 * AuditLogController — Module 6: Audit Logging & Reporting
 *
 * Access tiers:
 *  - Rank 5+  → can view logs (index, show)
 *  - Rank 8+  → full access including subject-type filter and export
 *
 * Every visit to the audit log page is itself logged.
 */
class AuditLogController extends Controller
{
    // ── Index ─────────────────────────────────────────────────────────────────

    public function index(Request $request): \Illuminate\View\View|\Illuminate\Http\Response
    {
        $user = Auth::user();

        // ── CSV export ────────────────────────────────────────────────────────
        if ($request->input('export') === 'csv') {
            abort_unless($user->hasMinimumRank(5), 403, 'Insufficient rank for export.');
            return $this->exportCsv($request);
        }

        // ── Build query ───────────────────────────────────────────────────────
        $query = Activity::with('causer')->latest();

        // Date range
        if ($from = $request->input('from')) {
            $query->whereDate('created_at', '>=', $from);
        }
        if ($to = $request->input('to')) {
            $query->whereDate('created_at', '<=', $to);
        }

        // Filter by causer (user)
        if ($userId = $request->input('user_id')) {
            $query->where('causer_id', $userId)
                  ->where('causer_type', User::class);
        }

        // Filter by log_name / action type
        if ($logName = $request->input('log_name')) {
            $query->where('log_name', $logName);
        }

        // Filter by subject type (Evidence, ChainOfCustody, User, etc.)
        if ($subjectType = $request->input('subject_type')) {
            $map = [
                'Evidence'       => \App\Models\Evidence::class,
                'User'           => \App\Models\User::class,
                'ChainOfCustody' => \App\Models\ChainOfCustody::class,
                'EvidenceHash'   => \App\Models\EvidenceHash::class,
            ];
            if (isset($map[$subjectType])) {
                $query->where('subject_type', $map[$subjectType]);
            }
        }

        // Filter by IP address (stored in properties JSON)
        if ($ip = $request->input('ip')) {
            $query->where('properties->ip', $ip);
        }

        // Search description
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhere('log_name', 'like', "%{$search}%");
            });
        }

        $logs = $query->paginate(25)->withQueryString();

        // ── Sidebar data ──────────────────────────────────────────────────────
        $users    = User::select('id', 'name', 'rank')->orderBy('name')->get();
        $logNames = Activity::distinct()->pluck('log_name')->filter()->sort()->values();

        // ── Summary stats ─────────────────────────────────────────────────────
        $stats = $this->getSummaryStats();

        // ── Log this page access ──────────────────────────────────────────────
        activity('audit_log_access')
            ->causedBy($user)
            ->withProperties([
                'ip'      => $request->ip(),
                'filters' => $request->only(['from', 'to', 'user_id', 'log_name', 'subject_type', 'ip', 'search']),
            ])
            ->log('Audit log page accessed');

        return view('audit-logs.index', compact('logs', 'users', 'logNames', 'stats'));
    }

    // ── Show single log entry ─────────────────────────────────────────────────

    public function show(int $id): \Illuminate\View\View
    {
        $log = Activity::with('causer')->findOrFail($id);

        // Log this detail view
        activity('audit_log_access')
            ->causedBy(Auth::user())
            ->withProperties([
                'log_id' => $id,
                'ip'     => request()->ip(),
            ])
            ->log("Audit log entry #{$id} viewed");

        return view('audit-logs.show', compact('log'));
    }

    // ── CSV Export ────────────────────────────────────────────────────────────

    private function exportCsv(Request $request): Response
    {
        $query = Activity::with('causer')->latest();

        // Apply same filters as index
        if ($from = $request->input('from')) {
            $query->whereDate('created_at', '>=', $from);
        }
        if ($to = $request->input('to')) {
            $query->whereDate('created_at', '<=', $to);
        }
        if ($userId = $request->input('user_id')) {
            $query->where('causer_id', $userId)->where('causer_type', User::class);
        }
        if ($logName = $request->input('log_name')) {
            $query->where('log_name', $logName);
        }
        if ($subjectType = $request->input('subject_type')) {
            $map = [
                'Evidence'       => \App\Models\Evidence::class,
                'User'           => \App\Models\User::class,
                'ChainOfCustody' => \App\Models\ChainOfCustody::class,
                'EvidenceHash'   => \App\Models\EvidenceHash::class,
            ];
            if (isset($map[$subjectType])) {
                $query->where('subject_type', $map[$subjectType]);
            }
        }
        if ($ip = $request->input('ip')) {
            $query->where('properties->ip', $ip);
        }
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhere('log_name', 'like', "%{$search}%");
            });
        }

        // Cap export at 5000 rows to prevent memory exhaustion
        $logs = $query->limit(5000)->get();

        // Log the export action
        activity('audit_log_export')
            ->causedBy(Auth::user())
            ->withProperties([
                'ip'         => $request->ip(),
                'row_count'  => $logs->count(),
                'filters'    => $request->only(['from', 'to', 'user_id', 'log_name', 'subject_type']),
            ])
            ->log('Audit log exported to CSV');

        // Build CSV
        $rows   = [];
        $rows[] = ['ID', 'Timestamp', 'User', 'Rank', 'Log Type', 'Description', 'Subject Type', 'Subject ID', 'IP Address', 'Properties'];

        foreach ($logs as $log) {
            $subjectShort = $log->subject_type
                ? class_basename($log->subject_type)
                : '';

            $ip = $log->properties['ip'] ?? '';

            // Strip sensitive keys before export
            $props = $log->properties->except(['password', 'token', 'secret'])->toJson();

            $rows[] = [
                $log->id,
                $log->created_at->format('Y-m-d H:i:s'),
                $log->causer?->name ?? 'System',
                $log->causer?->rank ?? '',
                $log->log_name,
                $log->description,
                $subjectShort,
                $log->subject_id ?? '',
                $ip,
                $props,
            ];
        }

        $csv = '';
        foreach ($rows as $row) {
            $csv .= implode(',', array_map(fn ($v) => '"' . str_replace('"', '""', (string) $v) . '"', $row)) . "\n";
        }

        $filename = 'audit_log_' . now()->format('Ymd_His') . '.csv';

        return response($csv, 200, [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Cache-Control'       => 'no-store',
        ]);
    }

    // ── Summary Stats ─────────────────────────────────────────────────────────

    private function getSummaryStats(): array
    {
        $today = now()->toDateString();

        $totalLogs  = Activity::count();
        $logsToday  = Activity::whereDate('created_at', $today)->count();

        // Most active user today
        $mostActiveRow = Activity::whereDate('created_at', $today)
            ->whereNotNull('causer_id')
            ->selectRaw('causer_id, COUNT(*) as cnt')
            ->groupBy('causer_id')
            ->orderByDesc('cnt')
            ->first();

        $mostActiveUser = $mostActiveRow
            ? User::find($mostActiveRow->causer_id)
            : null;

        // Critical events (integrity failures, rank access denials)
        $criticalCount = Activity::whereIn('log_name', [
            'integrity_alert',
            'rank_access',
            'auth',
        ])->whereDate('created_at', $today)->count();

        return [
            'total_logs'       => $totalLogs,
            'logs_today'       => $logsToday,
            'most_active_user' => $mostActiveUser,
            'critical_today'   => $criticalCount,
        ];
    }
}
