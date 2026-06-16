<?php

namespace App\Http\Controllers;

use App\Models\Club;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ClubController extends Controller
{
    public function index(Request $request)
    {
        $query = Club::where('is_active', true)->withCount('recruitmentPosts');

        if ($request->has('category')) {
            $query->where('category', $request->category);
        }

        if ($request->has('keyword')) {
            $keyword = $request->keyword;
            $query->where(function ($q) use ($keyword) {
                $q->where('name', 'like', "%{$keyword}%")
                  ->orWhere('description', 'like', "%{$keyword}%");
            });
        }

        $clubs = $query->orderBy('member_count', 'desc')->paginate(12);

        return response()->json($clubs);
    }

    public function show($id)
    {
        $club = Club::with(['recruitmentPosts' => function ($q) {
            $q->where('status', 'open');
        }, 'leaders'])->findOrFail($id);

        return response()->json($club);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'logo' => 'nullable|string|max:500',
            'description' => 'nullable|string',
            'category' => 'nullable|string|max:100',
        ]);

        $club = Club::create(array_merge($validated, [
            'is_active' => true,
            'member_count' => 0,
        ]));

        $club->members()->attach($request->user()->id, ['role' => 'leader']);

        return response()->json($club, 201);
    }

    public function update(Request $request, $id)
    {
        $club = Club::findOrFail($id);

        $this->validateClubLeaderAccess($request, $club);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'logo' => 'nullable|string|max:500',
            'description' => 'nullable|string',
            'category' => 'nullable|string|max:100',
            'is_active' => 'sometimes|boolean',
        ]);

        $club->update($validated);

        return response()->json($club);
    }

    protected function validateClubLeaderAccess(Request $request, Club $club)
    {
        $user = $request->user();

        if ($user->role === 'admin') {
            return;
        }

        $isLeader = $club->leaders()->where('user_id', $user->id)->exists();

        if (!$isLeader) {
            throw ValidationException::withMessages([
                'club' => ['您不是该社团的负责人，无权操作'],
            ]);
        }
    }
}
