<?php

namespace App\Http\Controllers;

use App\Services\ApplicationService;
use App\Models\Application;
use Illuminate\Http\Request;

class ApplicationController extends Controller
{
    protected $applicationService;

    public function __construct(ApplicationService $applicationService)
    {
        $this->applicationService = $applicationService;
    }

    public function index(Request $request)
    {
        $applications = $this->applicationService->getUserApplications($request->user()->id);

        return response()->json($applications);
    }

    public function show(Request $request, $id)
    {
        $application = Application::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->with(['recruitmentPost.club', 'interviewResults.interviewSlot'])
            ->firstOrFail();

        return response()->json($application);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'recruitment_post_id' => 'required|exists:recruitment_posts,id',
            'motivation' => 'nullable|string',
            'experience' => 'nullable|string',
            'skills' => 'nullable|string',
        ]);

        try {
            $application = $this->applicationService->submitApplication(
                $request->user()->id,
                $validated['recruitment_post_id'],
                $validated
            );

            return response()->json($application->load('recruitmentPost.club'), 201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    public function clubApplications(Request $request)
    {
        $user = $request->user();
        $clubId = $request->input('club_id');

        if (!$clubId && $user->ledClubs->isNotEmpty()) {
            $clubId = $user->ledClubs->first()->id;
        }

        if (!$clubId) {
            return response()->json(['message' => '未找到社团信息'], 400);
        }

        $applications = $this->applicationService->getClubApplications($clubId, $request->all());

        return response()->json($applications);
    }

    public function updateStatus(Request $request, $id)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,reviewing,interview_scheduled,interview_completed,accepted,rejected',
            'remark' => 'nullable|string',
        ]);

        try {
            $application = $this->applicationService->updateStatus(
                $id,
                $validated['status'],
                $validated['remark'] ?? null
            );

            return response()->json($application->load('user', 'recruitmentPost'));
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }
}
