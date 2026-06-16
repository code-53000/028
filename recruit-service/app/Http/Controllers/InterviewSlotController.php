<?php

namespace App\Http\Controllers;

use App\Services\InterviewSlotService;
use App\Services\ApplicationService;
use App\Models\InterviewSlot;
use App\Models\RecruitmentPost;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class InterviewSlotController extends Controller
{
    protected $slotService;
    protected $applicationService;

    public function __construct(InterviewSlotService $slotService, ApplicationService $applicationService)
    {
        $this->slotService = $slotService;
        $this->applicationService = $applicationService;
    }

    public function index(Request $request)
    {
        $query = InterviewSlot::with('recruitmentPost.club')->where('status', 'open');

        if ($request->has('recruitment_post_id')) {
            $query->where('recruitment_post_id', $request->recruitment_post_id);
        }

        if ($request->has('club_id')) {
            $query->where('club_id', $request->club_id);
        }

        if ($request->has('date')) {
            $date = Carbon::parse($request->date);
            $query->whereDate('start_time', $date);
        }

        $slots = $query->orderBy('start_time')->get();

        return response()->json($slots);
    }

    public function clubSlots(Request $request)
    {
        $user = $request->user();
        $clubId = $request->input('club_id');

        if (!$clubId && $user->ledClubs->isNotEmpty()) {
            $clubId = $user->ledClubs->first()->id;
        }

        if (!$clubId) {
            return response()->json(['message' => '未找到社团信息'], 400);
        }

        $query = InterviewSlot::where('club_id', $clubId)
            ->withCount('interviewResults')
            ->orderBy('start_time');

        if ($request->has('recruitment_post_id')) {
            $query->where('recruitment_post_id', $request->recruitment_post_id);
        }

        $slots = $query->paginate(20);

        return response()->json($slots);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'recruitment_post_id' => 'required|exists:recruitment_posts,id',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
            'location' => 'nullable|string|max:255',
            'capacity' => 'required|integer|min:1',
            'notes' => 'nullable|string',
        ]);

        $post = RecruitmentPost::findOrFail($validated['recruitment_post_id']);
        $club = $post->club;
        $this->validateClubLeaderAccess($request, $club);

        $startTime = Carbon::parse($validated['start_time']);
        $endTime = Carbon::parse($validated['end_time']);

        if ($this->slotService->hasTimeConflict($club->id, $startTime, $endTime)) {
            $conflicts = $this->slotService->getConflictingSlots($club->id, $startTime, $endTime);
            return response()->json([
                'message' => '时段冲突',
                'conflicts' => $conflicts,
            ], 400);
        }

        $slot = InterviewSlot::create(array_merge($validated, [
            'club_id' => $club->id,
            'booked_count' => 0,
            'status' => 'open',
        ]));

        return response()->json($slot, 201);
    }

    public function update(Request $request, $id)
    {
        $slot = InterviewSlot::findOrFail($id);
        $this->validateClubLeaderAccess($request, $slot->club);

        $validated = $request->validate([
            'start_time' => 'sometimes|date',
            'end_time' => 'sometimes|date|after:start_time',
            'location' => 'nullable|string|max:255',
            'capacity' => 'sometimes|integer|min:1',
            'status' => 'sometimes|in:open,full,closed',
            'notes' => 'nullable|string',
        ]);

        $startTime = isset($validated['start_time']) ? Carbon::parse($validated['start_time']) : $slot->start_time;
        $endTime = isset($validated['end_time']) ? Carbon::parse($validated['end_time']) : $slot->end_time;

        if (isset($validated['start_time']) || isset($validated['end_time'])) {
            if ($this->slotService->hasTimeConflict($slot->club_id, $startTime, $endTime, $slot->id)) {
                return response()->json(['message' => '时段冲突'], 400);
            }
        }

        if (isset($validated['capacity']) && !$this->slotService->isValidCapacity($slot, $validated['capacity'])) {
            return response()->json(['message' => '容量不能小于已预约人数'], 400);
        }

        $slot->update($validated);

        return response()->json($slot);
    }

    public function destroy(Request $request, $id)
    {
        $slot = InterviewSlot::findOrFail($id);
        $this->validateClubLeaderAccess($request, $slot->club);

        if (!$this->slotService->canDeleteSlot($slot->id)) {
            return response()->json(['message' => '该时段已有学生预约，无法删除'], 400);
        }

        $slot->delete();

        return response()->json(['message' => '删除成功']);
    }

    public function selectSlot(Request $request, $slotId)
    {
        $validated = $request->validate([
            'application_id' => 'required|exists:applications,id',
        ]);

        try {
            $result = $this->applicationService->scheduleInterview(
                $validated['application_id'],
                $slotId,
                $request->user()->id
            );

            return response()->json($result->load('interviewSlot'), 201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    protected function validateClubLeaderAccess(Request $request, $club)
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
