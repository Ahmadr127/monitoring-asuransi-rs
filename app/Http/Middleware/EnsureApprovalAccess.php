<?php

namespace App\Http\Middleware;

use App\Models\ApprovalStep;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Akses detail & pembatalan pengajuan: approver (permission approval dari DB),
 * admin, user yang sedang ditugaskan, atau pengaju itu sendiri.
 */
class EnsureApprovalAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if (! $user) {
            return redirect()->route('login');
        }

        $approval = $request->route('approval');
        $isRequestor = $approval && (int) $approval->requested_by === (int) $user->id;

        if ($user->isAdmin() || $user->hasPermission('approval') || $isRequestor || $this->hasPendingApproval($user)) {
            return $next($request);
        }

        abort(403, 'Unauthorized access');
    }

    private function hasPendingApproval($user): bool
    {
        if (ApprovalStep::where('approver_user_id', $user->id)->where('status', 'pending')->exists()) {
            return true;
        }

        if ($user->organization_unit_id) {
            return ApprovalStep::where('approver_type', 'department')
                ->where('approver_value', (string) $user->organization_unit_id)
                ->where('status', 'pending')
                ->exists();
        }

        return false;
    }
}