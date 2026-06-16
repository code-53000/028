<?php

namespace App\Services;

use App\Models\InterviewSlot;
use App\Models\Application;
use Carbon\Carbon;
use InvalidArgumentException;

class InterviewSlotService
{
    /**
     * 检查新时段是否与现有时段冲突（同一社团的时段不能重叠）
     *
     * @param int $clubId 社团ID
     * @param Carbon $startTime 开始时间
     * @param Carbon $endTime 结束时间
     * @param int|null $excludeSlotId 排除的时段ID（编辑时用）
     * @return bool 是否冲突
     */
    public function hasTimeConflict(int $clubId, Carbon $startTime, Carbon $endTime, ?int $excludeSlotId = null): bool
    {
        if ($startTime->greaterThanOrEqualTo($endTime)) {
            throw new InvalidArgumentException('结束时间必须晚于开始时间');
        }

        $query = InterviewSlot::where('club_id', $clubId)
            ->where(function ($q) use ($startTime, $endTime) {
                $q->where(function ($q2) use ($startTime, $endTime) {
                    $q2->where('start_time', '<', $endTime)
                       ->where('end_time', '>', $startTime);
                });
            });

        if ($excludeSlotId) {
            $query->where('id', '!=', $excludeSlotId);
        }

        return $query->exists();
    }

    /**
     * 获取与给定时段冲突的所有时段
     *
     * @param int $clubId
     * @param Carbon $startTime
     * @param Carbon $endTime
     * @param int|null $excludeSlotId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getConflictingSlots(int $clubId, Carbon $startTime, Carbon $endTime, ?int $excludeSlotId = null)
    {
        $query = InterviewSlot::where('club_id', $clubId)
            ->where(function ($q) use ($startTime, $endTime) {
                $q->where('start_time', '<', $endTime)
                  ->where('end_time', '>', $startTime);
            });

        if ($excludeSlotId) {
            $query->where('id', '!=', $excludeSlotId);
        }

        return $query->get();
    }

    /**
     * 检查学生是否可以选择该时段（不能有冲突的已选时段）
     *
     * @param int $userId 学生ID
     * @param int $slotId 时段ID
     * @return bool
     */
    public function canStudentSelectSlot(int $userId, int $slotId): bool
    {
        $targetSlot = InterviewSlot::findOrFail($slotId);

        if (!$targetSlot->hasCapacity()) {
            return false;
        }

        if ($targetSlot->status !== 'open') {
            return false;
        }

        $userApplications = Application::where('user_id', $userId)
            ->with('interviewResults.interviewSlot')
            ->get();

        foreach ($userApplications as $application) {
            foreach ($application->interviewResults as $result) {
                $slot = $result->interviewSlot;
                if (!$slot) {
                    continue;
                }

                if ($slot->start_time < $targetSlot->end_time &&
                    $slot->end_time > $targetSlot->start_time) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * 获取学生已有面试时段列表
     *
     * @param int $userId
     * @return \Illuminate\Support\Collection
     */
    public function getStudentInterviewSlots(int $userId)
    {
        return InterviewSlot::whereHas('interviewResults.application', function ($q) use ($userId) {
            $q->where('user_id', $userId);
        })
        ->orderBy('start_time')
        ->get();
    }

    /**
     * 批量创建时段时的冲突校验
     *
     * @param int $clubId
     * @param array $slots 每个元素包含 start_time, end_time
     * @return array 冲突的时段索引
     */
    public function validateBatchSlots(int $clubId, array $slots): array
    {
        $conflicts = [];

        foreach ($slots as $index => $slot) {
            $startTime = Carbon::parse($slot['start_time']);
            $endTime = Carbon::parse($slot['end_time']);

            if ($this->hasTimeConflict($clubId, $startTime, $endTime)) {
                $conflicts[] = $index;
                continue;
            }

            for ($j = 0; $j < $index; $j++) {
                if (!in_array($j, $conflicts)) {
                    $prevStart = Carbon::parse($slots[$j]['start_time']);
                    $prevEnd = Carbon::parse($slots[$j]['end_time']);

                    if ($startTime < $prevEnd && $endTime > $prevStart) {
                        $conflicts[] = $index;
                        break;
                    }
                }
            }
        }

        return $conflicts;
    }

    /**
     * 检查时段是否可以删除（有没有被预约）
     *
     * @param int $slotId
     * @return bool
     */
    public function canDeleteSlot(int $slotId): bool
    {
        $slot = InterviewSlot::findOrFail($slotId);
        return $slot->booked_count === 0;
    }

    /**
     * 更新时段容量后检查是否有效
     *
     * @param InterviewSlot $slot
     * @param int $newCapacity
     * @return bool
     */
    public function isValidCapacity(InterviewSlot $slot, int $newCapacity): bool
    {
        return $newCapacity >= $slot->booked_count;
    }
}
