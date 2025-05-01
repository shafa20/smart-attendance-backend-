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
        echo "$class->id";
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
}
