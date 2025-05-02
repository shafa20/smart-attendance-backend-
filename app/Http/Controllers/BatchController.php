<?php

namespace App\Http\Controllers;

use App\Models\Batch;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BatchController extends Controller
{
    // GET /api/batches
    public function index(Request $request)
    {
        $user = Auth::user();
        if ($user->role === 'admin') {
            // Admin: get all batches
            $batches = Batch::all();
        } elseif ($user->role === 'instructor') {
            // Instructor: get only assigned batches
            $batches = $user->batches()->get();
        } else {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json($batches);
    }

    // GET /api/batches/export-attendance
    public function exportAttendance(Request $request)
    {
        $user = Auth::user();
        if ($user->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Example: Export all attendance records as CSV
        $filename = 'attendance_export_' . now()->format('Ymd_His') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=$filename",
        ];

        $callback = function() {
            $handle = fopen('php://output', 'w');

            // Header row
            fputcsv($handle, ['Batch Name', 'Student Name', 'Date', 'Status']);

            // You may need to adjust this query based on your Attendance model structure
            $attendances = \DB::table('attendances')
                ->join('batches', 'attendances.batch_id', '=', 'batches.id')
                ->join('users', 'attendances.user_id', '=', 'users.id')
                ->select('batches.name as batch_name', 'users.name as student_name', 'attendances.date', 'attendances.status')
                ->get();

            foreach ($attendances as $row) {
                fputcsv($handle, [
                    $row->batch_name,
                    $row->student_name,
                    $row->date,
                    $row->status
                ]);
            }
            fclose($handle);
        };

        return new StreamedResponse($callback, 200, $headers);
    }
}
