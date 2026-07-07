<?php

namespace App\Http\Controllers;

use App\Models\AppLink;
use Illuminate\Http\Request;

class AppLinkController extends Controller
{
    public function index(Request $request)
    {
        $query = AppLink::query();

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('title', 'like', "%{$s}%")
                  ->orWhere('subtitle', 'like', "%{$s}%")
                  ->orWhere('url', 'like', "%{$s}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Sorting
        $sortBy = $request->input('sort_by', 'sort_order');
        $sortDir = $request->input('sort_dir', 'asc');
        $allowedSort = ['sort_order', 'title', 'created_at'];
        if (in_array($sortBy, $allowedSort)) {
            $query->orderBy($sortBy, $sortDir === 'desc' ? 'desc' : 'asc');
        } else {
            $query->orderBy('sort_order', 'asc');
        }

        $perPage = $request->input('per_page', 20);

        return $query->paginate($perPage);
    }

    public function show(AppLink $appLink)
    {
        return response()->json($appLink);
    }

    public function store(Request $request)
    {
        $request->validate([
            'title'      => 'required|string|max:255',
            'subtitle'   => 'nullable|string|max:255',
            'icon'       => 'required|string|max:100',
            'url'        => 'required|string|max:500',
            'color'      => 'required|in:amber,blue,emerald,violet,rose,cyan',
            'sort_order' => 'nullable|integer|min:0',
            'status'     => 'in:Published,Draft',
        ]);

        $data = $request->only(['title', 'subtitle', 'icon', 'url', 'color', 'sort_order', 'status']);
        $data['created_by'] = $request->user()?->id;

        return response()->json(AppLink::create($data), 201);
    }

    public function update(Request $request, AppLink $appLink)
    {
        $request->validate([
            'title'      => 'required|string|max:255',
            'subtitle'   => 'nullable|string|max:255',
            'icon'       => 'required|string|max:100',
            'url'        => 'required|string|max:500',
            'color'      => 'required|in:amber,blue,emerald,violet,rose,cyan',
            'sort_order' => 'nullable|integer|min:0',
            'status'     => 'in:Published,Draft',
        ]);

        $data = $request->only(['title', 'subtitle', 'icon', 'url', 'color', 'sort_order', 'status']);
        $appLink->update($data);

        return response()->json($appLink);
    }

    public function destroy(AppLink $appLink)
    {
        $appLink->delete();
        return response()->json(['message' => 'Link berhasil dihapus.']);
    }
}
