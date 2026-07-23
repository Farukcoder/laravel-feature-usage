<?php

namespace Farukcoder\FeatureHeatmap\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Farukcoder\FeatureHeatmap\Models\FeatureUsageSummary;
use Farukcoder\FeatureHeatmap\Models\FeatureUsageLog;
use DateTime;

class FeatureHeatmapController extends Controller
{
    /**
     * Check authorization gate if configured and defined.
     */
    protected function authorizeAccess(): void
    {
        $gate = config('feature-heatmap.authorization_gate');
        if ($gate && Gate::has($gate)) {
            Gate::authorize($gate);
        }
    }

    /**
     * Helper to safely sanitize date inputs (format: Y-m-d).
     */
    protected function sanitizeDate(?string $date, string $default): string
    {
        if (! $date) {
            return $default;
        }

        $d = DateTime::createFromFormat('Y-m-d', $date);
        return ($d && $d->format('Y-m-d') === $date) ? $date : $default;
    }

    /**
     * Helper to sanitize SQL column names to prevent raw SQL injection.
     */
    protected function sanitizeColumn(string $column, string $default): string
    {
        return preg_match('/^[a-zA-Z0-9_]+$/', $column) ? $column : $default;
    }

    /**
     * Check if package authentication is enabled (via config or env).
     */
    protected function isAuthEnabled(): bool
    {
        // 1. Check config if set
        $configVal = config('feature-heatmap.auth_enabled');
        if ($configVal !== null && $configVal !== false) {
            return true;
        }

        // 2. Check direct .env variables (if config was not published or returned false/null)
        $envAuth = env('FEATURE_HEATMAP_AUTH_ENABLED');
        if ($envAuth !== null) {
            return filter_var($envAuth, FILTER_VALIDATE_BOOLEAN);
        }

        // 3. Auto-enable if FEATURE_HEATMAP_USERNAME or FEATURE_HEATMAP_PASSWORD is set in .env
        if (env('FEATURE_HEATMAP_USERNAME') !== null || env('FEATURE_HEATMAP_PASSWORD') !== null) {
            return true;
        }

        return false;
    }

    /**
     * Check if package internal authentication is satisfied.
     */
    protected function isAuthenticated(Request $request): bool
    {
        if (! $this->isAuthEnabled()) {
            return true;
        }

        return (bool) $request->session()->get('feature_heatmap_auth', false);
    }

    /**
     * Render the main dashboard view.
     */
    public function index(Request $request)
    {
        $this->authorizeAccess();

        $from = $this->sanitizeDate($request->get('from'), now()->subDays(30)->toDateString());
        $to   = $this->sanitizeDate($request->get('to'), now()->toDateString());

        $authEnabled     = $this->isAuthEnabled();
        $isAuthenticated = $this->isAuthenticated($request);

        return view('feature-heatmap::dashboard', [
            'from'            => $from,
            'to'              => $to,
            'authEnabled'     => $authEnabled,
            'isAuthenticated' => $isAuthenticated,
        ]);
    }

    /**
     * Global heatmap JSON — fetched by the frontend chart layer.
     */
    public function data(Request $request)
    {
        $this->authorizeAccess();

        if (! $this->isAuthenticated($request)) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        $from = $this->sanitizeDate($request->get('from'), now()->subDays(30)->toDateString());
        $to   = $this->sanitizeDate($request->get('to'), now()->toDateString());

        $byFeatureAndDate = FeatureUsageSummary::heatmapByFeatureAndDate($from, $to);
        $byFeatureAndUser = FeatureUsageSummary::heatmapByFeatureAndUser($from, $to);
        $unusedFeatures   = FeatureUsageSummary::unusedSince(30);

        // Fallback to raw logs if summary tables are empty (aggregate command not run yet)
        if ($byFeatureAndDate->isEmpty() && $byFeatureAndUser->isEmpty()) {
            $byFeatureAndDate = FeatureUsageLog::query()
                ->whereBetween('created_at', [$from.' 00:00:00', $to.' 23:59:59'])
                ->selectRaw('controller_action, DATE(created_at) as usage_date, COUNT(*) as total')
                ->groupBy('controller_action', DB::raw('DATE(created_at)'))
                ->orderBy('usage_date')
                ->get();

            $byFeatureAndUser = FeatureUsageLog::query()
                ->whereBetween('created_at', [$from.' 00:00:00', $to.' 23:59:59'])
                ->selectRaw('controller_action, user_id, COUNT(*) as total')
                ->groupBy('controller_action', 'user_id')
                ->orderByDesc('total')
                ->get();
        }

        return response()->json([
            'by_feature_and_date' => $byFeatureAndDate,
            'by_feature_and_user' => $byFeatureAndUser,
            'unused_features'     => $unusedFeatures,
        ]);
    }

    /**
     * User-wise tracking: returns a summary table of all tracked users.
     * Joins feature_usage_logs with the auth users table to resolve names/emails.
     */
    public function userIndex(Request $request)
    {
        $this->authorizeAccess();

        if (! $this->isAuthenticated($request)) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        $from = $this->sanitizeDate($request->get('from'), now()->subDays(30)->toDateString());
        $to   = $this->sanitizeDate($request->get('to'), now()->toDateString());

        $nameCol  = $this->sanitizeColumn(config('feature-heatmap.user_name_column', 'name'), 'name');
        $emailCol = $this->sanitizeColumn(config('feature-heatmap.user_email_column', 'email'), 'email');

        $userModel  = config('auth.providers.users.model', \App\Models\User::class);
        $usersTable = (new $userModel)->getTable();

        $rows = FeatureUsageLog::query()
            ->whereBetween('feature_usage_logs.created_at', [$from.' 00:00:00', $to.' 23:59:59'])
            ->whereNotNull('feature_usage_logs.user_id')
            ->leftJoin($usersTable, $usersTable.'.id', '=', 'feature_usage_logs.user_id')
            ->selectRaw("
                feature_usage_logs.user_id,
                {$usersTable}.{$nameCol} as user_name,
                {$usersTable}.{$emailCol} as user_email,
                COUNT(*) as total_hits,
                COUNT(DISTINCT feature_usage_logs.controller_action) as distinct_features,
                MAX(feature_usage_logs.created_at) as last_seen
            ")
            ->groupBy(
                'feature_usage_logs.user_id',
                "{$usersTable}.{$nameCol}",
                "{$usersTable}.{$emailCol}"
            )
            ->orderByDesc('total_hits')
            ->get();

        return response()->json($rows);
    }

    /**
     * User-wise tracking: module-wise heatmap detail for a single user.
     * Returns controller_action × date breakdown + controller_action × total.
     */
    public function userDetail(Request $request, $userId)
    {
        $this->authorizeAccess();

        if (! $this->isAuthenticated($request)) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        $userId = (int) $userId;
        $from = $this->sanitizeDate($request->get('from'), now()->subDays(30)->toDateString());
        $to   = $this->sanitizeDate($request->get('to'), now()->toDateString());

        $nameCol  = $this->sanitizeColumn(config('feature-heatmap.user_name_column', 'name'), 'name');
        $emailCol = $this->sanitizeColumn(config('feature-heatmap.user_email_column', 'email'), 'email');

        $userModel  = config('auth.providers.users.model', \App\Models\User::class);
        $usersTable = (new $userModel)->getTable();

        // Resolve user identity
        $user = DB::table($usersTable)
            ->where('id', $userId)
            ->select('id', "{$nameCol} as name", "{$emailCol} as email")
            ->first();

        // Feature × date breakdown
        $byFeatureAndDate = FeatureUsageLog::query()
            ->where('user_id', $userId)
            ->whereBetween('created_at', [$from.' 00:00:00', $to.' 23:59:59'])
            ->selectRaw('controller_action, DATE(created_at) as usage_date, COUNT(*) as total')
            ->groupBy('controller_action', DB::raw('DATE(created_at)'))
            ->orderBy('usage_date')
            ->get();

        // Feature totals (for the bar chart)
        $featureTotals = FeatureUsageLog::query()
            ->where('user_id', $userId)
            ->whereBetween('created_at', [$from.' 00:00:00', $to.' 23:59:59'])
            ->selectRaw('controller_action, COUNT(*) as total, MAX(created_at) as last_used')
            ->groupBy('controller_action')
            ->orderByDesc('total')
            ->get();

        return response()->json([
            'user'               => $user,
            'by_feature_and_date'=> $byFeatureAndDate,
            'feature_totals'     => $featureTotals,
        ]);
    }

    /**
     * User-wise tracking: stream a CSV report for a single user.
     */
    public function userReport(Request $request, $userId)
    {
        $this->authorizeAccess();

        if (! $this->isAuthenticated($request)) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        $userId = (int) $userId;
        $from = $this->sanitizeDate($request->get('from'), now()->subDays(30)->toDateString());
        $to   = $this->sanitizeDate($request->get('to'), now()->toDateString());

        $nameCol  = $this->sanitizeColumn(config('feature-heatmap.user_name_column', 'name'), 'name');
        $emailCol = $this->sanitizeColumn(config('feature-heatmap.user_email_column', 'email'), 'email');

        $userModel  = config('auth.providers.users.model', \App\Models\User::class);
        $usersTable = (new $userModel)->getTable();

        $user = DB::table($usersTable)
            ->where('id', $userId)
            ->select("{$nameCol} as name", "{$emailCol} as email")
            ->first();

        $userName = $user ? ($user->name ?? 'user-'.$userId) : 'user-'.$userId;

        $logs = FeatureUsageLog::query()
            ->where('user_id', $userId)
            ->whereBetween('created_at', [$from.' 00:00:00', $to.' 23:59:59'])
            ->selectRaw('controller_action, route_name, method, uri, DATE(created_at) as usage_date, COUNT(*) as total')
            ->groupBy('controller_action', 'route_name', 'method', 'uri', DB::raw('DATE(created_at)'))
            ->orderBy('usage_date')
            ->orderByDesc('total')
            ->get();

        $safeFileNameUser = preg_replace('/[^a-zA-Z0-9_\-]/', '-', strtolower($userName));
        $filename = 'heatmap-report-'.$safeFileNameUser.'-'.now()->format('Ymd').'.csv';

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Cache-Control'       => 'no-cache, no-store, must-revalidate',
            'Pragma'              => 'no-cache',
            'Expires'             => '0',
        ];

        $callback = function () use ($logs, $user, $from, $to, $userId) {
            $out = fopen('php://output', 'w');

            // Report metadata header
            fputcsv($out, ['Feature Heatmap — User Activity Report']);
            fputcsv($out, ['User ID', $userId]);
            fputcsv($out, ['User Name', $user->name ?? 'N/A']);
            fputcsv($out, ['User Email', $user->email ?? 'N/A']);
            fputcsv($out, ['Date Range', "{$from} to {$to}"]);
            fputcsv($out, ['Generated At', now()->toDateTimeString()]);
            fputcsv($out, []);

            // Data header
            fputcsv($out, ['Controller Action', 'Route Name', 'HTTP Method', 'URI', 'Date', 'Hit Count']);

            foreach ($logs as $row) {
                fputcsv($out, [
                    $row->controller_action ?? '',
                    $row->route_name        ?? '',
                    $row->method            ?? '',
                    $row->uri               ?? '',
                    $row->usage_date        ?? '',
                    $row->total             ?? 0,
                ]);
            }

            fclose($out);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Authenticate dashboard user via env configured username and password.
     */
    public function login(Request $request)
    {
        if (! $this->isAuthEnabled()) {
            return response()->json(['success' => true, 'message' => 'Auth is disabled']);
        }

        $username = (string) $request->input('username');
        $password = (string) $request->input('password');

        $validUser = config('feature-heatmap.username') ?? env('FEATURE_HEATMAP_USERNAME', 'admin');
        $validPass = config('feature-heatmap.password') ?? env('FEATURE_HEATMAP_PASSWORD', 'secret');

        if ($username === (string) $validUser && $password === (string) $validPass) {
            $request->session()->put('feature_heatmap_auth', true);
            return response()->json(['success' => true, 'message' => 'Authenticated successfully']);
        }

        return response()->json(['success' => false, 'message' => 'Invalid username or password'], 401);
    }

    /**
     * Logout dashboard user.
     */
    public function logout(Request $request)
    {
        $request->session()->forget('feature_heatmap_auth');
        return response()->json(['success' => true, 'message' => 'Logged out successfully']);
    }
}

