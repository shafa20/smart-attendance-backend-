<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Attendance;
use App\Models\ClassSession;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    public function markAttendance(Request $request)
    {
        $request->validate([
            'class_session_id' => 'required|exists:classes,id',
            'status' => 'required|in:present,absent,late',
        ]);

        $user = Auth::user();
        if ($user->role !== 'student') {
            return response()->json(['message' => 'Only students can mark attendance.'], 403);
        }

        $class = ClassSession::findOrFail($request->class_session_id);
        //echo "$class->id";
        $now =  Carbon::now('Asia/Dhaka');
        $start = Carbon::parse($class->start_time);
        $windowStart = $start->copy()->subMinutes(10);
        $windowEnd = $start->copy()->addMinutes(10);

        // if ($now->lt($windowStart) || $now->gt($windowEnd)) {
        //     return response()->json(['message' => 'Attendance can only be marked 10 minutes before or after class start time.'], 403);
        // }

      $attendance = Attendance::updateOrCreate(
          [
              'student_id' => $user->id,
             // 'class_session_id' => $class->id, // match migration column
          ],
          [
              'status' => $request->status,
              'check_in_time' => $now,
          ]
      );

        return response()->json(['message' => 'Attendance marked successfully.', 'data' => $attendance]);
    }

    // Helper function to check instructor's batch access (pivot table version)
    private function checkBatchAccess($batch_id)
    {
        $user = auth()->user();
        if ($user->role === 'admin') {
            return true;
        }
        if ($user->role === 'instructor') {
            // Check batch_instructor pivot table
            $hasAccess = \DB::table('batch_instructor')
                ->where('batch_id', $batch_id)
                ->where('user_id', $user->id)
                ->exists();
            if (!$hasAccess) {
                return false;
            }
        }
        return true;
    }

    // Get total attendance stats for a batch
    public function batchAttendanceStats($batch_id)
    {
        if (!$this->checkBatchAccess($batch_id)) {
            return response()->json(['message' => 'This Batch not assigned to you'], 403);
        }
        $totalClasses = \App\Models\ClassSession::where('batch_id', $batch_id)->count();
        $totalStudents = \App\Models\Batch::find($batch_id)?->students()->count();
        $totalAttendances = \App\Models\Attendance::whereIn('class_session_id', function($q) use ($batch_id) {
            $q->select('id')->from('classes')->where('batch_id', $batch_id);
        })->count();
        return response()->json([
            'total_classes' => $totalClasses,
            'total_students' => $totalStudents,
            'total_attendances' => $totalAttendances
        ]);
    }

    // Get most present student in a batch
    public function mostPresentStudent($batch_id)
    {
        if (!$this->checkBatchAccess($batch_id)) {
            return response()->json(['message' => 'Batch not found or not assigned to you'], 403);
        }
        $student = \App\Models\Attendance::select('student_id', \DB::raw('COUNT(*) as present_count'))
            ->whereIn('class_session_id', function($q) use ($batch_id) {
                $q->select('id')->from('classes')->where('batch_id', $batch_id);
            })
            ->where('status', 'present')
            ->groupBy('student_id')
            ->orderByDesc('present_count')
            ->first();
        if (!$student) {
            return response()->json(['message' => 'No attendance data found.'], 404);
        }
        $user = \App\Models\User::find($student->student_id);
        return response()->json([
            'student' => $user,
            'present_count' => $student->present_count
        ]);
    }

    // Get attendance trend for the past 30 days for a batch
    public function attendanceTrend($batch_id)
    {
        if (!$this->checkBatchAccess($batch_id)) {
            return response()->json(['message' => 'Batch not found or not assigned to you'], 403);
        }
        $trend = \App\Models\Attendance::selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->whereIn('class_session_id', function($q) use ($batch_id) {
                $q->select('id')->from('classes')->where('batch_id', $batch_id);
            })
            ->whereBetween('created_at', [now()->subDays(30)->startOfDay(), now()->endOfDay()])
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();
        return response()->json($trend);
    }
}
