<?php

namespace App\Http\Controllers;

use App\Services\InterviewResultService;
use App\Models\InterviewResult;
use Illuminate\Http\Request;

class InterviewResultController extends Controller
{
    protected $resultService;

    public function __construct(InterviewResultService $resultService)
    {
        $this->resultService = $resultService;
    }

    public function index(Request $request)
    {
        $userId = $request->user()->id;

        $results = InterviewResult::whereHas('application', function ($q) use ($userId) {
            $q->where('user_id', $userId);
        })
        ->with(['application.recruitmentPost.club', 'interviewSlot'])
        ->orderBy('created_at', 'desc')
        ->paginate(10);

        return response()->json($results);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'application_id' => 'required|exists:applications,id',
            'interview_slot_id' => 'nullable|exists:interview_slots,id',
            'score' => 'nullable|integer|min:0|max:100',
            'comment' => 'nullable|string',
            'result' => 'required|in:pending,passed,rejected,waitlist',
            'round' => 'sometimes|integer|min:1',
            'feedback' => 'nullable|string',
        ]);

        try {
            $result = $this->resultService->recordResult(
                $validated['application_id'],
                $request->user()->id,
                $validated
            );

            return response()->json($result->load('application.user', 'interviewSlot'), 201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    public function update(Request $request, $id)
    {
        $result = InterviewResult::findOrFail($id);

        $validated = $request->validate([
            'score' => 'nullable|integer|min:0|max:100',
            'comment' => 'nullable|string',
            'result' => 'sometimes|in:pending,passed,rejected,waitlist',
            'feedback' => 'nullable|string',
        ]);

        try {
            $result = $this->resultService->recordResult(
                $result->application_id,
                $request->user()->id,
                array_merge($validated, ['round' => $result->round, 'interview_slot_id' => $result->interview_slot_id])
            );

            return response()->json($result->load('application.user', 'interviewSlot'));
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    public function statistics(Request $request)
    {
        $user = $request->user();
        $clubId = $request->input('club_id');

        if (!$clubId && $user->ledClubs->isNotEmpty()) {
            $clubId = $user->ledClubs->first()->id;
        }

        if (!$clubId) {
            return response()->json(['message' => '未找到社团信息'], 400);
        }

        $stats = $this->resultService->getStatistics($clubId, $request->input('round'));

        return response()->json($stats);
    }
}
