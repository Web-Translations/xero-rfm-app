<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\XeroConnection;
use App\Models\RfmReport;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->get('q', ''));
        // High-level stats
        $totalUsers = User::count();
        // Paying subscribers (clarify vs total users): non-free plans with active/pending status
        $payingSubscribers = User::whereIn('subscription_plan', ['pro', 'pro_plus'])
            ->whereIn('subscription_status', ['active', 'pending'])
            ->where(function ($q) {
                $q->whereNull('subscription_ends_at')->orWhere('subscription_ends_at', '>', now());
            })
            ->count();
        $proUsers = User::where('subscription_plan', 'pro')->count();
        $proPlusUsers = User::where('subscription_plan', 'pro_plus')->count();
        $freeUsers = User::where('subscription_plan', 'free')->count();

        $linkedXeroConnections = XeroConnection::count();
        $activeXeroConnections = XeroConnection::where('is_active', true)->count();

        // Latest snapshot date overall for reference
        $latestSnapshotDate = RfmReport::max('snapshot_date');

        // Customers overview (searchable)
        $customersQuery = User::query()
            ->select(
                'users.id', 'users.name', 'users.email',
                'users.subscription_plan', 'users.subscription_status', 'users.subscription_ends_at'
            )
            ->withCount([
                'xeroConnections as xero_connections_count',
            ])
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($inner) use ($search) {
                    $inner->where('users.name', 'like', "%{$search}%")
                          ->orWhere('users.email', 'like', "%{$search}%");
                    if (is_numeric($search)) {
                        $inner->orWhere('users.id', (int) $search);
                    }
                });
            })
            ->orderBy('users.created_at', 'desc')
            ->paginate(25);

        $customers = $customersQuery;

        // Per-user aggregates (in a single query each to avoid N+1 on large pages)
        $userIds = collect($customers->items())->pluck('id');

        $lastSyncByUser = XeroConnection::select('user_id', DB::raw('MAX(last_sync_at) as last_sync_at'))
            ->whereIn('user_id', $userIds)
            ->groupBy('user_id')
            ->pluck('last_sync_at', 'user_id');

        $latestRfmByUser = RfmReport::select('user_id', DB::raw('MAX(snapshot_date) as latest_snapshot'))
            ->whereIn('user_id', $userIds)
            ->groupBy('user_id')
            ->pluck('latest_snapshot', 'user_id');

        return view('admin.index', [
            'stats' => [
                'total_users' => $totalUsers,
                'paying_subscribers' => $payingSubscribers,
                'free_users' => $freeUsers,
                'pro_users' => $proUsers,
                'pro_plus_users' => $proPlusUsers,
                'xero_connections' => $linkedXeroConnections,
                'xero_active_connections' => $activeXeroConnections,
                'latest_snapshot_date' => $latestSnapshotDate,
            ],
            'customers' => $customers,
            'lastSyncByUser' => $lastSyncByUser,
            'latestRfmByUser' => $latestRfmByUser,
            'search' => $search,
        ]);
    }

    public function startImpersonation(User $user)
    {
        // Only admins can reach here due to middleware
        if ($user->id === auth()->id()) {
            return redirect()->route('admin.index')->withErrors('You cannot view as yourself.');
        }
        session([
            'impersonated_user_id' => $user->id,
            'impersonated_by_admin_id' => auth()->id(),
            'impersonation_mode' => 'read_only',
        ]);

        // Redirect to user's landing page
        return redirect()->route('dashboard')->with('status', 'Viewing as '.$user->name.' (read-only).');
    }

    public function stopImpersonation()
    {
        session()->forget([
            'impersonated_user_id',
            'impersonated_by_admin_id',
            'impersonation_mode',
        ]);

        return redirect()->route('admin.index')->with('status', 'Stopped viewing as user.');
    }
}


