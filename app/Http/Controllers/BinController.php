<?php

namespace App\Http\Controllers;

use App\Models\Bin;
use App\Models\WasteRequest;
use App\Models\User;
use App\Services\AutoAssignService;
use Illuminate\Http\Request;

class BinController extends Controller
{
    protected $autoAssignService;

    public function __construct(AutoAssignService $autoAssignService)
    {
        $this->autoAssignService = $autoAssignService;
    }

    public function index()
    {
        $bins = Bin::all();
        return view('citizen.bins', compact('bins'));
    }

    public function updateFillLevel(Request $request, Bin $bin)
    {
        $request->validate([
            'fill_level' => 'required|integer|min:0|max:100',
        ]);

        $bin->update([
            'fill_level' => $request->fill_level,
            'status' => $request->fill_level >= 90 ? 'full' : 'active'
        ]);

        if ($bin->fill_level >= 90) {
            // Auto-generate pickup request
            $wasteRequest = WasteRequest::create([
                'user_id' => auth()->id() ?? User::where('role', 'admin')->first()->id, // Default to admin if no user logged in (simulation)
                'waste_category_id' => 1, // Default General Waste
                'address' => $bin->location_name,
                'latitude' => $bin->latitude,
                'longitude' => $bin->longitude,
                'status' => 'pending'
            ]);

            // Auto-assign
            $this->autoAssignService->assign($wasteRequest);
        }

        return response()->json(['success' => true, 'bin' => $bin]);
    }

    public function adminIndex()
    {
        $bins = Bin::all();
        return view('admin.bins.index', compact('bins'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'location_name' => 'required|string|max:255',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'fill_level' => 'required|integer|min:0|max:100',
        ]);

        $validated['status'] = $validated['fill_level'] >= 90 ? 'full' : 'active';

        Bin::create($validated);

        return back()->with('success', 'Smart Bin added successfully!');
    }

    public function update(Request $request, Bin $bin)
    {
        $validated = $request->validate([
            'location_name' => 'required|string|max:255',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'fill_level' => 'required|integer|min:0|max:100',
        ]);

        $validated['status'] = $validated['fill_level'] >= 90 ? 'full' : 'active';

        $bin->update($validated);

        return back()->with('success', 'Smart Bin updated successfully!');
    }

    public function destroy(Bin $bin)
    {
        $bin->delete();
        return back()->with('success', 'Smart Bin deleted successfully!');
    }
}
