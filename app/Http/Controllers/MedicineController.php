<?php

namespace App\Http\Controllers;

use App\Models\Medicine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MedicineController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of the user's medicines.
     */
    public function index()
    {
        $medicines = Medicine::where('user_id', Auth::id())
            ->with('activeSchedule')
            ->orderBy('created_at', 'desc')
            ->get();
            
        return view('medicine.my-medicines', compact('medicines'));
    }

    /**
     * Show the form for creating a new medicine.
     */
    public function create()
    {
        return view('medicine.add');
    }

    /**
     * Store a newly created medicine in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'medicine_name' => 'required|string|max:255',
            'type' => 'nullable|in:tablet,capsule,syrup,injection,drops,cream,inhaler,other',
            'value_per_dose' => 'nullable|numeric|min:0',
            'unit' => 'nullable|in:mg,ml,mcg,g,IU,tablet,capsule,drop,puff',
            'rule' => 'nullable|in:before_food,after_food,with_food,before_sleep,anytime',
            'dose_limit' => 'nullable|integer|min:1'
        ]);

        $medicine = Medicine::create([
            'user_id' => Auth::id(),
            ...$validated
        ]);

        return redirect()->route('medicine.my-medicines')
            ->with('success', 'Medicine added successfully. Now you can set up a schedule.');
    }

    /**
     * Show the form for editing the specified medicine.
     */
    public function edit($id)
    {
        $medicine = Medicine::where('user_id', Auth::id())->findOrFail($id);
        return view('medicine.edit', compact('medicine'));
    }

    /**
     * Update the specified medicine in storage.
     */
    public function update(Request $request, $id)
    {
        $medicine = Medicine::where('user_id', Auth::id())->findOrFail($id);
        
        $validated = $request->validate([
            'medicine_name' => 'required|string|max:255',
            'type' => 'nullable|in:tablet,capsule,syrup,injection,drops,cream,inhaler,other',
            'value_per_dose' => 'nullable|numeric|min:0',
            'unit' => 'nullable|in:mg,ml,mcg,g,IU,tablet,capsule,drop,puff',
            'rule' => 'nullable|in:before_food,after_food,with_food,before_sleep,anytime',
            'dose_limit' => 'nullable|integer|min:1'
        ]);

        $medicine->update($validated);

        return redirect()->route('medicine.my-medicines')
            ->with('success', 'Medicine updated successfully.');
    }

    /**
     * Remove the specified medicine from storage.
     */
    public function destroy($id)
    {
        $medicine = Medicine::where('user_id', Auth::id())->findOrFail($id);
        $medicine->delete();

        return redirect()->route('medicine.my-medicines')
            ->with('success', 'Medicine deleted successfully.');
    }
}