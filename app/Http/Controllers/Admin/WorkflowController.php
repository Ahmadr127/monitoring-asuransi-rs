<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ApproverType;
use App\Http\Controllers\Controller;
use App\Models\OrganizationUnit;
use App\Models\Role;
use App\Models\User;
use App\Models\WorkflowDefinition;
use App\Models\WorkflowStep;
use Illuminate\Http\Request;

class WorkflowController extends Controller
{
    public function index()
    {
        $workflows = WorkflowDefinition::withCount('steps')->orderBy('name')->get();

        return view('admin.workflows.index', compact('workflows'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:workflow_definitions,code',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'is_active' => 'nullable|boolean',
        ]);

        WorkflowDefinition::create(array_merge($validated, [
            'is_active' => $request->boolean('is_active'),
        ]));

        return redirect()->route('admin.workflows.index')->with('success', 'Workflow berhasil dibuat.');
    }

    public function edit(WorkflowDefinition $workflow)
    {
        $workflow->load('steps');

        $approverTypes = ApproverType::cases();
        $roles = Role::orderBy('name')->get();
        $users = User::with('role')->orderBy('name')->get();
        $departments = OrganizationUnit::orderBy('name')->get();

        return view('admin.workflows.edit', compact('workflow', 'approverTypes', 'roles', 'users', 'departments'));
    }

    public function update(Request $request, WorkflowDefinition $workflow)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'is_active' => 'nullable|boolean',
        ]);

        $workflow->update(array_merge($validated, [
            'is_active' => $request->boolean('is_active'),
        ]));

        return redirect()->back()->with('success', 'Workflow berhasil diperbarui.');
    }

    public function destroy(WorkflowDefinition $workflow)
    {
        if ($workflow->requests()->count() > 0) {
            return redirect()->route('admin.workflows.index')
                ->with('error', 'Workflow sudah pernah dipakai, tidak dapat dihapus. Nonaktifkan saja.');
        }

        $workflow->delete();

        return redirect()->route('admin.workflows.index')->with('success', 'Workflow berhasil dihapus.');
    }

    public function storeStep(Request $request, WorkflowDefinition $workflow)
    {
        $validated = $this->validateStep($request);

        $maxOrder = $workflow->steps()->max('step_order') ?? 0;

        $workflow->steps()->create(array_merge($validated, [
            'step_order' => $maxOrder + 1,
            'can_reject' => $request->boolean('can_reject'),
            'is_active' => true,
        ]));

        return redirect()->back()->with('success', 'Langkah approval berhasil ditambahkan.');
    }

    public function updateStep(Request $request, WorkflowStep $step)
    {
        $validated = $this->validateStep($request);

        $step->update(array_merge($validated, [
            'can_reject' => $request->boolean('can_reject'),
            'is_active' => $request->boolean('is_active'),
        ]));

        return redirect()->back()->with('success', 'Langkah approval berhasil diperbarui.');
    }

    public function destroyStep(WorkflowStep $step)
    {
        if ($step->approvalSteps()->count() > 0) {
            return redirect()->back()->with('error', 'Step sudah pernah dipakai pada pengajuan, tidak dapat dihapus.');
        }

        $step->delete();

        return redirect()->back()->with('success', 'Langkah approval berhasil dihapus.');
    }

    private function validateStep(Request $request): array
    {
        return $request->validate([
            'name' => 'required|string|max:255',
            'approver_type' => 'required|in:role,user,department_head,department',
            'approver_value' => 'required|string|max:255',
            'can_reject' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
            'skip_if' => 'nullable|string',
        ]) + ['skip_if' => $request->filled('skip_if') ? json_decode($request->skip_if, true) : null];
    }
}