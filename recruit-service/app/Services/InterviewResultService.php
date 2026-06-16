<?php

namespace App\Services;

use App\Models\InterviewResult;
use App\Models\Application;
use Illuminate\Support\Facades\DB;

class InterviewResultService
{
    /**
     * 记录面试结果
     *
     * @param int $applicationId
     * @param int $interviewerId
     * @param array $data
     * @return InterviewResult
     */
    public function recordResult(int $applicationId, int $interviewerId, array $data): InterviewResult
    {
        $application = Application::findOrFail($applicationId);

        return DB::transaction(function () use ($application, $interviewerId, $data) {
            $round = $data['round'] ?? 1;

            $result = InterviewResult::updateOrCreate(
                [
                    'application_id' => $application->id,
                    'round' => $round,
                ],
                [
                    'interview_slot_id' => $data['interview_slot_id'] ?? null,
                    'interviewer_id' => $interviewerId,
                    'score' => $data['score'] ?? null,
                    'comment' => $data['comment'] ?? null,
                    'result' => $data['result'] ?? 'pending',
                    'feedback' => $data['feedback'] ?? null,
                ]
            );

            $this->updateApplicationStatus($application, $result);

            return $result;
        });
    }

    /**
     * 根据面试结果更新报名状态
     *
     * @param Application $application
     * @param InterviewResult $result
     * @return void
     */
    protected function updateApplicationStatus(Application $application, InterviewResult $result): void
    {
        switch ($result->result) {
            case 'passed':
                $application->status = 'interview_completed';
                break;
            case 'rejected':
                $application->status = 'rejected';
                break;
            case 'waitlist':
                $application->status = 'interview_completed';
                break;
            default:
                $application->status = 'interview_scheduled';
        }
        $application->save();
    }

    /**
     * 批量通过/拒绝
     *
     * @param array $applicationIds
     * @param string $result
     * @param int $interviewerId
     * @return int
     */
    public function batchUpdate(array $applicationIds, string $result, int $interviewerId): int
    {
        $count = 0;

        foreach ($applicationIds as $appId) {
            $this->recordResult($appId, $interviewerId, ['result' => $result]);
            $count++;
        }

        return $count;
    }

    /**
     * 获取某轮次的面试结果统计
     *
     * @param int $clubId
     * @param int|null $round
     * @return array
     */
    public function getStatistics(int $clubId, ?int $round = null): array
    {
        $query = InterviewResult::whereHas('application.recruitmentPost', function ($q) use ($clubId) {
            $q->where('club_id', $clubId);
        });

        if ($round) {
            $query->where('round', $round);
        }

        $results = $query->get();

        return [
            'total' => $results->count(),
            'passed' => $results->where('result', 'passed')->count(),
            'rejected' => $results->where('result', 'rejected')->count(),
            'waitlist' => $results->where('result', 'waitlist')->count(),
            'pending' => $results->where('result', 'pending')->count(),
            'avg_score' => $results->whereNotNull('score')->avg('score'),
        ];
    }
}
