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

        $pending = ApprovalRequest::with(['subject', 'requestor', 'workflow'])
            ->whereHas('steps', function ($q) use ($user) {
                $q->where('approver_user_id', $user->id);
                if ($user->organization_unit_id) {
                    $q->orWhere(function ($q2) use ($user) {
                        $q2->where('approver_type', 'department')
                            ->where('approver_value', (string) $user->organization_unit_id);
                    });
                }
            })
            ->orderByDesc('created_at')
            ->get();

        $requestedByMe = ApprovalRequest::with(['subject', 'requestor', 'workflow'])
            ->where('requested_by', $user->id)
            ->orderByDesc('created_at')
            ->get();

        return view('admin.approvals.index', compact('pending', 'requestedByMe'));
    }

    public function show(ApprovalRequest $approval)
    {
        $approval->load([
            'subject',
            'requestor',
            'workflow',
            'steps.approver',
            'steps.decidedBy',
        ]);

        return view('admin.approvals.show', ['request' => $approval]);
    }

    public function approve(ApprovalRequest $approval, Request $httpRequest, WorkflowService $service)
    {
        $currentStep = $approval->currentStepRecord()->first();

        $rules = ['catatan_keputusan' => 'nullable|string|max:500'];
        if ($currentStep?->requires_attachment) {
            $rules['attachment'] = ['required', 'file', 'mimes:pdf', 'max:5120'];
        } else {
            $rules['attachment'] = ['nullable', 'file', 'mimes:pdf', 'max:5120'];
        }

        $validated = $httpRequest->validate($rules);

        $attachmentPath = null;
        $attachmentName = null;
        if ($httpRequest->hasFile('attachment')) {
            $file = $httpRequest->file('attachment');
            $attachmentName = $file->getClientOriginalName();
            $attachmentPath = $file->store('approval-attachments/' . $approval->id, 'public');
        }

        try {
            $service->approve($approval, $httpRequest->user(), $validated['catatan_keputusan'] ?? null, $attachmentPath, $attachmentName);

            return redirect()->back()->with('success', 'Langkah disetujui.');
        } catch (WorkflowException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function reject(ApprovalRequest $approval, Request $httpRequest, WorkflowService $service)
    {
        $validated = $httpRequest->validate([
            'catatan_keputusan' => 'required|string|max:500',
        ]);

        try {
            $service->reject($approval, $httpRequest->user(), $validated['catatan_keputusan']);

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