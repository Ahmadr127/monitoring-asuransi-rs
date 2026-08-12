<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OrganizationType;
use App\Models\OrganizationUnit;
use App\Models\User;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    public function index(Request $request)
    {
        $query = OrganizationUnit::with(['type', 'parent', 'head', 'members'])->withCount('members');

        if ($request->filled('type_id')) {
            $query->where('type_id', $request->integer('type_id'));
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        if ($request->filled('search')) {
            $query->where(fn ($q) => $q->where('name', 'ilike', "%{$request->search}%")
                ->orWhere('code', 'ilike', "%{$request->search}%"));
        }

        $units = $query->orderBy('name')->get();
        $types = OrganizationType::orderBy('level')->orderBy('name')->get();
        $typeCount = $types->count();

        return view('admin.departments.index', compact('units', 'types', 'typeCount'));
    }

    public function create()
    {
        $types = OrganizationType::orderBy('level')->orderBy('name')->get();
        $unitOptions = OrganizationUnit::orderBy('name')->get();
        $users = User::with('role')->orderBy('name')->get();

        return view('admin.departments.create', compact('types', 'unitOptions', 'users'));
    }

    public function store(Request $request)
    {
        $validated = $this->validateUnit($request);

        OrganizationUnit::create(array_merge($validated, [
            'is_active' => $request->boolean('is_active'),
        ]));

        return redirect()->route('admin.departments.index')->with('success', 'Departemen berhasil ditambahkan.');
    }

    public function edit(OrganizationUnit $unit)
    {
        $unit->load('type', 'parent', 'head');

        $types = OrganizationType::orderBy('level')->orderBy('name')->get();
        $unitOptions = OrganizationUnit::orderBy('name')->get();
        $users = User::with('role')->orderBy('name')->get();

        return view('admin.departments.edit', compact('unit', 'types', 'unitOptions', 'users'));
    }

    public function update(Request $request, OrganizationUnit $unit)
    {
        $validated = $this->validateUnit($request, $unit);

        $unit->update(array_merge($validated, [
            'is_active' => $request->boolean('is_active'),
        ]));

        return redirect()->route('admin.departments.show', $unit)->with('success', 'Departemen berhasil diperbarui.');
    }

    public function show(OrganizationUnit $unit)
    {
        $unit->load([
            'type', 'parent.type', 'head', 'children.type', 'children.head',
            'members.role',
        ]);

        $allUsers = User::with('role')->orderBy('name')->get();
        $availableUsers = User::with('role')
            ->whereNull('organization_unit_id')
            ->orderBy('name')
            ->get();

        return view('admin.departments.show', compact('unit', 'allUsers', 'availableUsers'));
    }

    public function destroy(OrganizationUnit $unit)
    {
        if ($unit->members()->count() > 0) {
            return redirect()->route('admin.departments.index')
                ->with('error', 'Departemen masih memiliki anggota, tidak dapat dihapus.');
        }

        if ($unit->children()->count() > 0) {
            return redirect()->route('admin.departments.index')
                ->with('error', 'Departemen masih memiliki sub-unit, tidak dapat dihapus.');
        }

        $unit->delete();

        return redirect()->route('admin.departments.index')->with('success', 'Departemen berhasil dihapus.');
    }

    // ---- Anggota & Kepala ----

    public function addMember(Request $request, OrganizationUnit $unit)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $user = User::findOrFail($validated['user_id']);

        if ((int) $user->organization_unit_id === (int) $unit->id) {
            return redirect()->route('admin.departments.show', $unit)
                ->with('info', 'User tersebut sudah menjadi anggota departemen ini.');
        }

        $user->update(['organization_unit_id' => $unit->id]);

        return redirect()->route('admin.departments.show', $unit)
            ->with('success', "{$user->name} berhasil ditambahkan sebagai anggota.");
    }

    public function removeMember(Request $request, OrganizationUnit $unit, User $user)
    {
        if ((int) $unit->head_id === (int) $user->id) {
            return redirect()->route('admin.departments.show', $unit)
                ->with('error', 'Kepala departemen tidak dapat dihapus sebagai anggota. Ubah kepala terlebih dahulu.');
        }

        if ((int) $user->organization_unit_id === (int) $unit->id) {
            $user->update(['organization_unit_id' => null]);
        }

        return redirect()->route('admin.departments.show', $unit)
            ->with('success', "{$user->name} berhasil dihapus dari anggota.");
    }

    public function updateHead(Request $request, OrganizationUnit $unit)
    {
        $validated = $request->validate([
            'head_id' => 'nullable|exists:users,id',
        ]);

        $unit->update(['head_id' => $validated['head_id'] ?? null]);

        return redirect()->route('admin.departments.show', $unit)
            ->with('success', 'Kepala departemen berhasil diperbarui.');
    }

    // ---- Tipe Departemen ----

    public function types(Request $request)
    {
        $types = OrganizationType::withCount('organizationUnits')->orderBy('level')->orderBy('name')->get();

        return view('admin.departments.types', compact('types'));
    }

    public function storeType(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:organization_types,name',
            'display_name' => 'required|string|max:255',
            'level' => 'required|integer|min:1',
            'description' => 'nullable|string|max:500',
        ]);

        OrganizationType::create($validated);

        return redirect()->route('admin.departments.types.index')
            ->with('success', 'Tipe departemen berhasil ditambahkan.');
    }

    public function updateType(Request $request, OrganizationType $type)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:organization_types,name,'.$type->id,
            'display_name' => 'required|string|max:255',
            'level' => 'required|integer|min:1',
            'description' => 'nullable|string|max:500',
        ]);

        $type->update($validated);

        return redirect()->route('admin.departments.types.index')
            ->with('success', 'Tipe departemen berhasil diperbarui.');
    }

    public function destroyType(OrganizationType $type)
    {
        if ($type->organizationUnits()->count() > 0) {
            return redirect()->route('admin.departments.types.index')
                ->with('error', 'Tipe masih dipakai departemen, tidak dapat dihapus.');
        }

        $type->delete();

        return redirect()->route('admin.departments.types.index')
            ->with('success', 'Tipe departemen berhasil dihapus.');
    }

    private function validateUnit(Request $request, ?OrganizationUnit $unit = null): array
    {
        $codeRule = 'required|string|max:20|unique:organization_units,code';
        if ($unit) {
            $codeRule .= ','.$unit->id;
        }

        return $request->validate([
            'name' => 'required|string|max:255',
            'code' => $codeRule,
            'type_id' => 'required|exists:organization_types,id',
            'parent_id' => 'nullable|exists:organization_units,id',
            'head_id' => 'nullable|exists:users,id',
            'description' => 'nullable|string|max:500',
            'is_active' => 'nullable|boolean',
        ]);
    }
}