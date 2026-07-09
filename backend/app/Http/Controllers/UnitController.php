<?php

namespace App\Http\Controllers;

use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class UnitController extends Controller
{
    /**
     * Get list of units.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        
        if ($user->isSuperadmin()) {
            $query = Unit::withCount('users');
        } else {
            $query = $user->units()->withCount('users');
        }
        
        return response()->json($query->orderBy('name')->get());
    }

    /**
     * Get units that the current user has access to.
     */
    public function myUnits(Request $request)
    {
        $user = $request->user();
        
        if ($user->isSuperadmin()) {
            $units = Unit::orderBy('name')->get();
        } else {
            $units = $user->units()->orderBy('name')->get();
        }
        
        return response()->json($units);
    }

    /**
     * Store a newly created unit.
     */
    public function store(Request $request)
    {
        $user = $request->user();
        if (!$user->isSuperadmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'name' => 'required|string|max:255|unique:units',
            'description' => 'nullable|string',
            'status' => 'in:Active,Inactive',
        ]);

        $data = $request->only(['name', 'description', 'status']);
        $data['slug'] = Str::slug($request->name);

        $unit = Unit::create($data);

        return response()->json($unit, 201);
    }

    /**
     * Display the specified unit.
     */
    public function show(Request $request, Unit $unit)
    {
        $user = $request->user();
        if (!$user->hasUnitAccess($unit->id)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        
        $unit->load('users:id,name,username');
        return response()->json($unit);
    }

    /**
     * Update the specified unit.
     */
    public function update(Request $request, Unit $unit)
    {
        $user = $request->user();
        if (!$user->isSuperadmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'name' => 'required|string|max:255|unique:units,name,'.$unit->id,
            'description' => 'nullable|string',
            'status' => 'in:Active,Inactive',
        ]);

        $data = $request->only(['name', 'description', 'status']);
        $data['slug'] = Str::slug($request->name);

        $unit->update($data);

        return response()->json($unit);
    }

    /**
     * Remove the specified unit.
     */
    public function destroy(Request $request, Unit $unit)
    {
        $user = $request->user();
        if (!$user->isSuperadmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $unit->delete();

        return response()->json(['message' => 'Unit deleted successfully']);
    }

    /**
     * Assign users to unit.
     */
    public function assignUsers(Request $request, Unit $unit)
    {
        $user = $request->user();
        if (!$user->isSuperadmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'user_ids' => 'array',
            'user_ids.*' => 'exists:users,id',
        ]);

        $unit->users()->sync($request->user_ids ?? []);

        return response()->json(['message' => 'Users assigned successfully']);
    }
}
