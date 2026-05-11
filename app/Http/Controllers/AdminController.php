<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\WasteRequest;
use App\Models\User;
use App\Models\Assignment;

class AdminController extends Controller
{
    public function index()
    {
        $totalRequests = WasteRequest::count();
        $pendingRequests = WasteRequest::where('status', 'pending')->count();
        $completedRequests = WasteRequest::whereIn('status', ['collected', 'disposed'])->count();
        
        $requests = WasteRequest::with(['user', 'wasteCategory', 'assignment', 'schedule'])->latest()->get();
        $drivers = User::where('role', 'driver')->with('location')->get();

        // Analytics: Waste Category Distribution
        $categoryStats = \App\Models\WasteCategory::withCount('wasteRequests')->get();
        
        // Driver locations for live map
        $driverLocations = \App\Models\DriverLocation::with('driver')->get();

        return view('admin.dashboard', compact(
            'totalRequests', 'pendingRequests', 'completedRequests', 
            'requests', 'drivers', 'categoryStats', 'driverLocations'
        ));
    }

    public function requests()
    {
        $requests = WasteRequest::with(['user', 'wasteCategory', 'assignment', 'schedule'])->latest()->paginate(15);
        $drivers = User::where('role', 'driver')->get();
        return view('admin.requests', compact('requests', 'drivers'));
    }

    public function assign(Request $request)
    {
        $request->validate([
            'waste_request_id' => 'required|exists:waste_requests,id',
            'driver_id' => 'required|exists:users,id',
        ]);

        Assignment::create([
            'driver_id' => $request->driver_id,
            'waste_request_id' => $request->waste_request_id,
            'status' => 'pending',
        ]);

        $wasteRequest = WasteRequest::find($request->waste_request_id);
        $wasteRequest->status = 'assigned';
        $wasteRequest->save();

        return back()->with('success', 'Request assigned to driver successfully!');
    }

    public function users(Request $request)
    {
        $query = User::query();

        // Search by name or email
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filter by role
        if ($request->filled('role') && in_array($request->role, ['admin', 'driver', 'citizen'])) {
            $query->where('role', $request->role);
        }

        $users = $query->orderBy('created_at', 'desc')->paginate(15)->withQueryString();

        $roleCounts = [
            'total' => User::count(),
            'admin' => User::where('role', 'admin')->count(),
            'driver' => User::where('role', 'driver')->count(),
            'citizen' => User::where('role', 'citizen')->count(),
        ];

        return view('admin.users', compact('users', 'roleCounts'));
    }

    public function updateRole(Request $request, User $user)
    {
        $request->validate([
            'role' => ['required', 'in:admin,driver,citizen'],
        ]);

        // Prevent admin from demoting themselves
        if ($user->id === auth()->id() && $request->role !== 'admin') {
            return back()->with('error', 'You cannot change your own role.');
        }

        $oldRole = $user->role;
        $user->role = $request->role;
        $user->save();

        return back()->with('success', "{$user->name}'s role changed from {$oldRole} to {$request->role}.");
    }

    public function exportReports()
    {
        $headers = [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=waste_report_" . date('Y-m-d') . ".csv",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];

        $requests = WasteRequest::with(['user', 'wasteCategory', 'schedule'])->get();

        $callback = function() use ($requests) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['ID', 'Citizen', 'Category', 'Address', 'Status', 'Date', 'Time Slot']);

            foreach ($requests as $req) {
                $dateValue = $req->schedule ? $req->schedule->pickup_date : $req->created_at;
                $formattedDate = $dateValue instanceof \Carbon\Carbon ? $dateValue->format('d-M-Y') : \Carbon\Carbon::parse($dateValue)->format('d-M-Y');
                
                fputcsv($file, [
                    $req->id,
                    $req->user->name,
                    $req->wasteCategory->name,
                    $req->address,
                    $req->status,
                    $formattedDate,
                    $req->schedule ? $req->schedule->time_slot : 'N/A'
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
