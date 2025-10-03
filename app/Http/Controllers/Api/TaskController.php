<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class TaskController extends Controller
{
    /**
     * Create a new TaskController instance.
     */
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Task::byUser(auth()->id());

        // Filter by status if provided
        if ($request->has('status') && in_array($request->status, ['todo', 'progress', 'done'])) {
            $query->byStatus($request->status);
        }

        $tasks = $query->orderBy('created_at', 'desc')->get();

        // Add color hex to each task
        $tasks->each(function ($task) {
            $task->color_hex = $task->color;
        });

        return response()->json([
            'status' => 'success',
            'message' => 'Tasks retrieved successfully',
            'data' => $tasks,
            'count' => $tasks->count()
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'status' => 'sometimes|in:todo,progress,done',
            'color_index' => 'sometimes|integer|between:0,4',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $task = Task::create([
                'user_id' => auth()->id(),
                'title' => $request->title,
                'description' => $request->description,
                'start_time' => $request->start_time,
                'end_time' => $request->end_time,
                'status' => $request->status ?? Task::STATUS_TODO,
                'color_index' => $request->color_index ?? 0,
            ]);

            // Add color hex to response
            $task->color_hex = $task->color;

            Log::info('Task created successfully', [
                'user_id' => auth()->id(),
                'task_id' => $task->id,
                'title' => $task->title
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Task created successfully',
                'data' => $task
            ], 201);

        } catch (\Exception $e) {
            Log::error('Failed to create task', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create task. Please try again.'
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $task = Task::byUser(auth()->id())->find($id);

        if (!$task) {
            return response()->json([
                'status' => 'error',
                'message' => 'Task not found'
            ], 404);
        }

        // Add color hex to response
        $task->color_hex = $task->color;

        return response()->json([
            'status' => 'success',
            'message' => 'Task retrieved successfully',
            'data' => $task
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $task = Task::byUser(auth()->id())->find($id);

        if (!$task) {
            return response()->json([
                'status' => 'error',
                'message' => 'Task not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|nullable|string',
            'start_time' => 'sometimes|required|date_format:H:i',
            'end_time' => 'sometimes|required|date_format:H:i|after:start_time',
            'status' => 'sometimes|in:todo,progress,done',
            'color_index' => 'sometimes|integer|between:0,4',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $task->update($request->only([
                'title', 'description', 'start_time', 'end_time', 'status', 'color_index'
            ]));

            // Add color hex to response
            $task->color_hex = $task->color;

            Log::info('Task updated successfully', [
                'user_id' => auth()->id(),
                'task_id' => $task->id,
                'title' => $task->title
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Task updated successfully',
                'data' => $task
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to update task', [
                'user_id' => auth()->id(),
                'task_id' => $id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update task. Please try again.'
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $task = Task::byUser(auth()->id())->find($id);

        if (!$task) {
            return response()->json([
                'status' => 'error',
                'message' => 'Task not found'
            ], 404);
        }

        try {
            $task->delete();

            Log::info('Task deleted successfully', [
                'user_id' => auth()->id(),
                'task_id' => $id
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Task deleted successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to delete task', [
                'user_id' => auth()->id(),
                'task_id' => $id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete task. Please try again.'
            ], 500);
        }
    }
}
