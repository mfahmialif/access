<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Setting;
use App\Models\Unit;
use Illuminate\Support\Facades\Storage;

class SettingController extends Controller
{
    public function index()
    {
        return response()->json(Setting::pluck('value', 'key'));
    }

    public function show($key)
    {
        $setting = Setting::where('key', $key)->first();
        return response()->json(['value' => $setting ? $setting->value : null]);
    }

    public function update(Request $request)
    {
        $data = $request->all();
        foreach ($data as $key => $value) {
            Setting::updateOrCreate(['key' => $key], ['value' => $value]);
        }
        return response()->json(['message' => 'Settings updated successfully']);
    }

    /**
     * Upload logo or logo-full for a specific unit.
     */
    public function uploadLogo(Request $request)
    {
        $request->validate([
            'unit_id' => 'required|exists:units,id',
            'type'    => 'required|in:logo,logo_full',
            'file'    => 'required|image|max:2048',
        ]);

        $user = $request->user();
        $unit = Unit::findOrFail($request->unit_id);

        // Check access: superadmin can upload for any unit, others only their own
        if (!$user->isSuperadmin() && !$user->hasUnitAccess($unit->id)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $column = $request->type === 'logo_full' ? 'logo_full_path' : 'logo_path';

        // Delete old file if exists
        $oldPath = $unit->$column;
        if ($oldPath && Storage::disk('public')->exists($oldPath)) {
            Storage::disk('public')->delete($oldPath);
        }

        // Store new file
        $path = $request->file('file')->store("units/{$unit->id}", 'public');

        $unit->update([$column => $path]);

        return response()->json([
            'message' => 'Logo berhasil diupload.',
            'path'    => $path,
            'url'     => Storage::disk('public')->url($path),
        ]);
    }

    /**
     * Delete logo or logo-full for a specific unit (reset to default).
     */
    public function deleteLogo(Request $request)
    {
        $request->validate([
            'unit_id' => 'required|exists:units,id',
            'type'    => 'required|in:logo,logo_full',
        ]);

        $user = $request->user();
        $unit = Unit::findOrFail($request->unit_id);

        if (!$user->isSuperadmin() && !$user->hasUnitAccess($unit->id)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $column = $request->type === 'logo_full' ? 'logo_full_path' : 'logo_path';

        // Delete file
        $oldPath = $unit->$column;
        if ($oldPath && Storage::disk('public')->exists($oldPath)) {
            Storage::disk('public')->delete($oldPath);
        }

        $unit->update([$column => null]);

        return response()->json(['message' => 'Logo berhasil dihapus.']);
    }

    /**
     * Get logo paths for a specific unit (public — for TV display).
     */
    public function getUnitLogos(Unit $unit)
    {
        return response()->json([
            'logo_path'      => $unit->logo_path,
            'logo_full_path' => $unit->logo_full_path,
            'logo_url'       => $unit->logo_path ? Storage::disk('public')->url($unit->logo_path) : null,
            'logo_full_url'  => $unit->logo_full_path ? Storage::disk('public')->url($unit->logo_full_path) : null,
        ]);
    }
}
