<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\WorkflowException;
use App\Http\Controllers\Controller;
use App\Models\ApprovalRequest;
use App\Models\RuangLingkup;
use App\Services\WorkflowService;
use Illuminate\Http\Request;

class ApprovalController extends Controller
{
    public function index(WorkflowService $service)
    {
        $user = auth()->user();

        $pending = $service->pendingFor($user)->values();

        $requestedByMe = ApprovalRequest::with(['subject', 'workflow'])
            ->where('requested_by', $user->id)
            ->orderByDesc('created_at')
            ->limit(20)
            ->get();

        $decidedByMe = \App\Models\ApprovalStep::with(['request.subject', 'request.workflow'])
            ->where('decided_by', $user->id)
            ->orderByDesc('decided_at')
            ->limit(20)
            ->get();

        return view('admin.approvals.index', compact('pending', 'requestedByMe', 'decidedByMe'));
    }

    public function show(ApprovalRequest $request)
    {
        $request->load([
            'subject',
            'requestor',
            'workflow',
            'steps.approver',
            'steps.decidedBy',
        ]);

        return view('admin.approvals.show', compact('request'));
    }

    public function approve(ApprovalRequest $approval, Request $httpRequest, WorkflowService $service)
    {
        try {
            $service->approve($approval, $httpRequest->user(), $httpRequest->input('note'));

            return redirect()->back()->with('success', 'Langkah disetujui.');
        } catch (WorkflowException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function reject(ApprovalRequest $approval, Request $httpRequest, WorkflowService $service)
    {
        $httpRequest->validate(['note' => 'nullable|string|max:500']);

        try {
            $service->reject($approval, $httpRequest->user(), $httpRequest->input('note'));

            return redirect()->back()->with('success', 'Pengajuan ditolak.');
        } catch (WorkflowException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function cancel(ApprovalRequest $approval, Request $httpRequest, WorkflowService $service)
    {
        try {
            $service->cancel($approval, $httpRequest->user());

            return redirect()->back()->with('success', 'Pengajuan dibatalkan.');
        } catch (WorkflowException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}