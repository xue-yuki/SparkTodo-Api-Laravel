<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    /**
     * Display a listing of the user's tasks
     */
    public function index(Request $request)
    {
        $tasks = $request->user()
            ->tasks()
            ->orderBy('done', 'asc')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'tasks' => $tasks,
            ],
        ], 200);
    }

    /**
     * Store a newly created task
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'tag' => 'nullable|string|max:50',
            'time' => 'nullable|string',
            'priority' => 'nullable|in:low,med,high',
        ]);

        $task = $request->user()->tasks()->create([
            'title' => $validated['title'],
            'tag' => $validated['tag'] ?? 'Work',
            'time' => $validated['time'] ?? null,
            'priority' => $validated['priority'] ?? 'med',
            'done' => false,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Task created successfully',
            'data' => [
                'task' => $task,
            ],
        ], 201);
    }

    /**
     * Display the specified task
     */
    public function show(Request $request, string $id)
    {
        $task = $request->user()->tasks()->find($id);

        if (!$task) {
            return response()->json([
                'success' => false,
                'message' => 'Task not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'task' => $task,
            ],
        ], 200);
    }

    /**
     * Update the specified task
     */
    public function update(Request $request, string $id)
    {
        $task = $request->user()->tasks()->find($id);

        if (!$task) {
            return response()->json([
                'success' => false,
                'message' => 'Task not found',
            ], 404);
        }

        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'tag' => 'sometimes|string|max:50',
            'time' => 'sometimes|string|nullable',
            'priority' => 'sometimes|in:low,med,high',
            'done' => 'sometimes|boolean',
        ]);

        $task->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Task updated successfully',
            'data' => [
                'task' => $task->fresh(),
            ],
        ], 200);
    }

    /**
     * Remove the specified task
     */
    public function destroy(Request $request, string $id)
    {
        $task = $request->user()->tasks()->find($id);

        if (!$task) {
            return response()->json([
                'success' => false,
                'message' => 'Task not found',
            ], 404);
        }

        $task->delete();

        return response()->json([
            'success' => true,
            'message' => 'Task deleted successfully',
        ], 200);
    }

    /**
     * Toggle task done status
     */
    public function toggle(Request $request, string $id)
    {
        $task = $request->user()->tasks()->find($id);

        if (!$task) {
            return response()->json([
                'success' => false,
                'message' => 'Task not found',
            ], 404);
        }

        $task->update(['done' => !$task->done]);

        return response()->json([
            'success' => true,
            'message' => 'Task status toggled',
            'data' => [
                'task' => $task->fresh(),
            ],
        ], 200);
    }
}
