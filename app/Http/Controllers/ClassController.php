<?php

namespace App\Http\Controllers;

use App\Models\ClassSession;
use App\Models\Batch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ClassController extends Controller
{
    public function schedule(Request $request)
    {
        $request->validate([
            'batch_id' => 'required|exists:batches,id',
            'topic' => 'required|string|max:255',
            'start_time' => 'required|date',
            'duration' => 'required|integer|min:1', // in minutes
        ]);

        // Check if the instructor belongs to this batch
        $batch = Batch::findOrFail($request->batch_id);
        if (!$batch->instructors()->where('users.id', Auth::id())->exists()) {
            return response()->json(['message' => 'You are not an instructor of this batch'], 403);
        }

        // Calculate end time based on duration
        $startTime = Carbon::parse($request->start_time);
        $endTime = $startTime->copy()->addMinutes($request->duration);

        $class = ClassSession::create([
            'batch_id' => $request->batch_id,
            'instructor_id' => Auth::id(),
            'topic' => $request->topic,
            'description' => $request->description,
            'start_time' => $startTime,
            'end_time' => $endTime, // Ensure end_time is always set
            'status' => 'scheduled',
            'room_number' => $request->room_number ?? 'TBA',
        ]);

        return response()->json([
            'message' => 'Class scheduled successfully',
            'data' => $class
        ], 201);
    }

    public function upcomingClasses(Request $request)
    {
        $user = Auth::user();
        $query = ClassSession::with(['batch', 'instructor'])
            ->where('start_time', '>', now())
            ->orderBy('start_time', 'asc');

        if ($user->role === 'student') {
            // Get batch IDs student belongs to, specify table to avoid ambiguous id
            $batchIds = $user->batches()->pluck('batches.id')->toArray();
            $query->whereIn('batch_id', $batchIds);
        } elseif ($user->role === 'instructor') {
            // Instructors can see classes they're teaching
            $query->where('instructor_id', $user->id);
        }

        $classes = $query->get();

        return response()->json([
            'data' => $classes
        ]);
    }

    public function show(ClassSession $class)
    {
        $user = Auth::user();
        
        // Check if user has access to this class
        if ($user->role === 'student' && !$user->batches()->where('batches.id', $class->batch_id)->exists()) {
            return response()->json(['message' => 'You do not have access to this class'], 403);
        }

        if ($user->role === 'instructor' && $class->instructor_id !== $user->id) {
            return response()->json(['message' => 'You do not have access to this class'], 403);
        }

        return response()->json([
            'data' => $class->load(['batch', 'instructor'])
        ]);
    }
}
