<?php

namespace App\Services;

use App\Models\Application;
use App\Models\InterviewSlot;
use App\Models\InterviewResult;
use App\Models\RecruitmentPost;
use Illuminate\Support\Facades\DB;
use App\Services\InterviewSlotService;

class ApplicationService
{
    protected $slotService;

    public function __construct(InterviewSlotService $slotService)
    {
        $this->slotService = $slotService;
    }

    /**
     * 学生提交报名
     *
     * @param int $userId
     * @param int $postId
     * @param array $data
     * @return Application
     */
    public function submitApplication(int $userId, int $postId, array $data): Application
    {
        $post = RecruitmentPost::findOrFail($postId);

        if (!$post->isOpen()) {
            throw new \Exception('该招新岗位已关闭');
        }

        $existing = Application::where('user_id', $userId)
            ->where('recruitment_post_id', $postId)
            ->first();

        if ($existing) {
            throw new \Exception('您已报名过该岗位');
        }

        return DB::transaction(function () use ($userId, $postId, $data) {
            return Application::create([
                'user_id' => $userId,
                'recruitment_post_id' => $postId,
                'motivation' => $data['motivation'] ?? null,
                'experience' => $data['experience'] ?? null,
                'skills' => $data['skills'] ?? null,
                'status' => 'pending',
            ]);
        });
    }

    /**
     * 为报名安排面试时段
     *
     * @param int $applicationId
     * @param int $slotId
     * @param int $userId 学生ID
     * @return InterviewResult
     */
    public function scheduleInterview(int $applicationId, int $slotId, int $userId): InterviewResult
    {
        $application = Application::where('id', $applicationId)
            ->where('user_id', $userId)
            ->firstOrFail();

        if ($application->status === 'accepted' || $application->status === 'rejected') {
            throw new \Exception('该报名已结束，无法选择面试时段');
        }

        if (!$this->slotService->canStudentSelectSlot($userId, $slotId)) {
            throw new \Exception('该时段不可选择（可能已满或与其他面试冲突）');
        }

        return DB::transaction(function () use ($application, $slotId) {
            $slot = InterviewSlot::findOrFail($slotId);
            $slot->increment('booked_count');

            if ($slot->booked_count >= $slot->capacity) {
                $slot->status = 'full';
                $slot->save();
            }

            $result = InterviewResult::create([
                'application_id' => $application->id,
                'interview_slot_id' => $slotId,
                'result' => 'pending',
                'round' => 1,
            ]);

            $application->status = 'interview_scheduled';
            $application->save();

            return $result;
        });
    }

    /**
     * 更新报名状态
     *
     * @param int $applicationId
     * @param string $status
     * @param string|null $remark
     * @return Application
     */
    public function updateStatus(int $applicationId, string $status, ?string $remark = null): Application
    {
        $application = Application::findOrFail($applicationId);
        $application->status = $status;
        if ($remark) {
            $application->remark = $remark;
        }
        $application->save();

        return $application;
    }

    /**
     * 获取学生的报名列表（带面试信息）
     *
     * @param int $userId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getUserApplications(int $userId)
    {
        return Application::where('user_id', $userId)
            ->with(['recruitmentPost.club', 'interviewResults.interviewSlot'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * 获取社团的报名列表
     *
     * @param int $clubId
     * @param array $filters
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getClubApplications(int $clubId, array $filters = [])
    {
        $query = Application::whereHas('recruitmentPost', function ($q) use ($clubId) {
            $q->where('club_id', $clubId);
        })
        ->with(['user', 'recruitmentPost', 'interviewResults.interviewSlot']);

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['post_id'])) {
            $query->where('recruitment_post_id', $filters['post_id']);
        }

        return $query->orderBy('created_at', 'desc')->paginate(20);
    }
}
