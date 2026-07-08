<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Setting;

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
}
