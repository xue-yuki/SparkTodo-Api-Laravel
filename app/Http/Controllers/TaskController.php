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
            'data' => $tasks,
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
            'due_date' => 'nullable|date',
            'priority' => 'nullable|in:low,med,high',
            'notes' => 'nullable|string',
        ]);

        $task = $request->user()->tasks()->create([
            'title' => $validated['title'],
            'tag' => $validated['tag'] ?? 'Work',
            'time' => $validated['time'] ?? null,
            'due_date' => $validated['due_date'] ?? null,
            'priority' => $validated['priority'] ?? 'med',
            'done' => false,
            'notes' => $validated['notes'] ?? null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Task created successfully',
            'data' => $task,
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
            'data' => $task,
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
            'due_date' => 'sometimes|date|nullable',
            'priority' => 'sometimes|in:low,med,high',
            'done' => 'sometimes|boolean',
            'notes' => 'sometimes|string|nullable',
        ]);

        $task->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Task updated successfully',
            'data' => $task->fresh(),
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

        $newDoneStatus = !$task->done;

        $task->update([
            'done' => $newDoneStatus,
            'completed_at' => $newDoneStatus ? now() : null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Task status toggled',
            'data' => $task->fresh(),
        ], 200);
    }
}
