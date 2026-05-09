<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;

class AnalyticsController extends Controller
{
    /**
     * Get analytics data for the authenticated user
     */
    public function index(Request $request)
    {
        $user = $request->user();

        // Get all user's tasks
        $allTasks = $user->tasks;
        $completedTasks = $allTasks->where('done', true);

        // Total shipped (completed) tasks
        $totalShipped = $completedTasks->count();

        // Total tasks
        $totalTasks = $allTasks->count();

        // Completion rate
        $completionRate = $totalTasks > 0 ? round(($totalShipped / $totalTasks) * 100) : 0;

        // Calculate streak (consecutive days with completed tasks)
        $streak = $this->calculateStreak($completedTasks);

        // Get tasks per day for last 14 days
        $tasksPerDay = $this->getTasksPerDay($completedTasks, 14);

        // Get heatmap data for last 15 weeks (105 days)
        $heatmapData = $this->getHeatmapData($completedTasks, 105);

        // Calculate total focus time (placeholder - could be based on task durations)
        $focusTimeHours = $this->calculateFocusTime($completedTasks);

        return response()->json([
            'success' => true,
            'data' => [
                'total_shipped' => $totalShipped,
                'total_tasks' => $totalTasks,
                'completion_rate' => $completionRate,
                'streak_days' => $streak,
                'focus_time_hours' => $focusTimeHours,
                'tasks_per_day' => $tasksPerDay,
                'heatmap_data' => $heatmapData,
            ],
        ], 200);
    }

    /**
     * Calculate consecutive days streak
     */
    private function calculateStreak($completedTasks)
    {
        if ($completedTasks->isEmpty()) {
            return 0;
        }

        $dates = $completedTasks
            ->filter(fn($task) => !empty($task->due_date))
            ->map(fn($task) => Carbon::parse($task->due_date)->format('Y-m-d'))
            ->unique()
            ->sort()
            ->values();

        if ($dates->isEmpty()) {
            return 0;
        }

        $today = Carbon::today()->format('Y-m-d');
        $yesterday = Carbon::yesterday()->format('Y-m-d');

        if (!$dates->contains($today) && !$dates->contains($yesterday)) {
            return 0;
        }

        $currentStreak = 1;
        for ($i = count($dates) - 1; $i > 0; $i--) {
            $currentDate = Carbon::parse($dates[$i]);
            $prevDate = Carbon::parse($dates[$i - 1]);

            if ($currentDate->diffInDays($prevDate) === 1) {
                $currentStreak++;
            } else {
                break;
            }
        }

        return $currentStreak;
    }

    /**
     * Get tasks completed per day for the last N days
     */
    private function getTasksPerDay($completedTasks, $days)
    {
        $result = [];

        for ($i = $days - 1; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $count = $completedTasks->filter(function ($task) use ($date) {
                return !empty($task->due_date) && Carbon::parse($task->due_date)->isSameDay($date);
            })->count();

            $result[] = $count;
        }

        return $result;
    }

    /**
     * Get heatmap data (0-4 intensity levels) for last N days
     */
    private function getHeatmapData($completedTasks, $days)
    {
        $result = [];

        for ($i = $days - 1; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $count = $completedTasks->filter(function ($task) use ($date) {
                return !empty($task->due_date) && Carbon::parse($task->due_date)->isSameDay($date);
            })->count();

            $level = match (true) {
                $count === 0 => 0,
                $count === 1 => 1,
                $count === 2 => 2,
                $count === 3 => 3,
                $count >= 4 => 4,
            };

            $result[] = $level;
        }

        return $result;
    }

    /**
     * Calculate total focus time (placeholder - based on completed tasks)
     * In real scenario, you might track actual time spent
     */
    private function calculateFocusTime($completedTasks)
    {
        // Estimate: 30 minutes per completed task
        $totalMinutes = $completedTasks->count() * 30;
        $hours = round($totalMinutes / 60, 1);

        return $hours;
    }
}
