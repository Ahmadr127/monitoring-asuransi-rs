<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\WorkflowException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ApprovalApproveRequest;
use App\Http\Requests\Admin\ApprovalRejectRequest;
use App\Models\ApprovalRequest;
use App\Services\WorkflowService;
use Illuminate\Http\Request;

class ApprovalController extends Controller
{
    public function index(WorkflowService $service)
    {
        $user = auth()->user();

        $pending = $service->pendingForMe($user);

        $pending->each(function (ApprovalRequest $req) use ($user, $service) {
            $req->my_step_status = $service->myStepStatus($req, $user);
        });

        return view('admin.approvals.index', compact('pending'));
    }

    public function myRequests(WorkflowService $service)
    {
        $requestedByMe = $service->requestedByMe(auth()->user());

        return view('admin.approvals.my', compact('requestedByMe'));
    }

    public function show(ApprovalRequest $approval, WorkflowService $service)
    {
        return view('admin.approvals.show', ['request' => $service->detail($approval)]);
    }

    public function approve(ApprovalRequest $approval, ApprovalApproveRequest $request, WorkflowService $service)
    {
        try {
            $service->approve(
                $approval,
                $request->user(),
                $request->validated('catatan_keputusan'),
                $request->file('attachment'),
            );

            return redirect()->back()->with('success', 'Langkah disetujui.');
        } catch (WorkflowException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function reject(ApprovalRequest $approval, ApprovalRejectRequest $request, WorkflowService $service)
    {
        try {
            $service->reject($approval, $request->user(), $request->validated('catatan_keputusan'));

            return redirect()->back()->with('success', 'Pengajuan ditolak.');
        } catch (WorkflowException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function cancel(ApprovalRequest $approval, Request $request, WorkflowService $service)
    {
        try {
            $service->cancel($approval, $request->user());

            return redirect()->back()->with('success', 'Pengajuan dibatalkan.');
        } catch (WorkflowException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}
