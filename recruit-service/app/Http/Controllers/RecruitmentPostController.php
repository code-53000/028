<?php

namespace App\Http\Controllers;

use App\Models\RecruitmentPost;
use App\Models\Club;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class RecruitmentPostController extends Controller
{
    public function index(Request $request)
    {
        $query = RecruitmentPost::with('club')->where('status', 'open');

        if ($request->has('club_id')) {
            $query->where('club_id', $request->club_id);
        }

        if ($request->has('keyword')) {
            $keyword = $request->keyword;
            $query->where(function ($q) use ($keyword) {
                $q->where('title', 'like', "%{$keyword}%")
                  ->orWhere('description', 'like', "%{$keyword}%");
            });
        }

        $posts = $query->orderBy('created_at', 'desc')->paginate(12);

        return response()->json($posts);
    }

    public function show($id)
    {
        $post = RecruitmentPost::with('club', 'interviewSlots')->findOrFail($id);

        return response()->json($post);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'club_id' => 'required|exists:clubs,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'requirements' => 'nullable|string',
            'benefits' => 'nullable|string',
            'quota' => 'required|integer|min:1',
            'deadline' => 'nullable|date',
        ]);

        $club = Club::findOrFail($validated['club_id']);
        $this->validateClubLeaderAccess($request, $club);

        $post = RecruitmentPost::create(array_merge($validated, [
            'status' => 'draft',
        ]));

        return response()->json($post, 201);
    }

    public function update(Request $request, $id)
    {
        $post = RecruitmentPost::findOrFail($id);
        $this->validateClubLeaderAccess($request, $post->club);

        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'requirements' => 'nullable|string',
            'benefits' => 'nullable|string',
            'quota' => 'sometimes|integer|min:1',
            'status' => 'sometimes|in:draft,open,closed',
            'deadline' => 'nullable|date',
        ]);

        $post->update($validated);

        return response()->json($post);
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
