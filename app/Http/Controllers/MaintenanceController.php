<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class MaintenanceController extends Controller
{
    /**
     * Display the maintenance page
     */
    public function index()
    {
        // You can customize these values from config, database, or env variables
        $data = [
            'status' => config('app.maintenance_status', 'Maintenance in progress'),
            'estimated_time' => config('app.maintenance_time', 'Expected to be complete soon'),
            'work_description' => config('app.maintenance_description', 'System repairs and improvements'),
        ];

        return view('maintenance', $data);
    }

    /**
     * Show maintenance page with custom data
     * This method can be used to pass custom parameters
     */
    public function show(Request $request)
    {
        $status = $request->query('status', 'Maintenance in progress');
        $time = $request->query('time', 'Expected to be complete soon');
        $description = $request->query('description', 'System repairs and improvements');

        return view('maintenance', [
            'status' => $status,
            'estimated_time' => $time,
            'work_description' => $description,
        ]);
    }

    /**
     * Toggle maintenance mode (admin only)
     */
    public function toggle(Request $request)
    {
        if (! auth()->check() || ! auth()->user()->isAdmin()) {
            abort(403);
        }

        $current = \Illuminate\Support\Facades\Cache::get('maintenance_mode', config('app.maintenance_mode', false));
        $new = ! (bool) $current;
        \Illuminate\Support\Facades\Cache::put('maintenance_mode', $new, now()->addDays(7));

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['maintenance' => $new]);
        }

        return back()->with('status', 'Maintenance mode ' . ($new ? 'enabled' : 'disabled'));
    }
}
