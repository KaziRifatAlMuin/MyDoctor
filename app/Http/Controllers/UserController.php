<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\User;
use Carbon\Carbon;

class UserController extends Controller
{
    /**
     * Display a listing of users (public view).
     */
    public function index(Request $request)
    {
        $query = User::query();
        
        // Apply search filter
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            });
        }
        
        // Apply role filter
        if ($request->filled('role')) {
            $query->where('role', $request->get('role'));
        }
        
        // Apply sorting
        $sort = $request->get('sort', 'latest');
        switch ($sort) {
            case 'oldest':
                $query->oldest();
                break;
            case 'name':
                $query->orderBy('name', 'asc');
                break;
            default:
                $query->latest();
                break;
        }
        
        $users = $query->paginate(20);
        
        // Get statistics
        $adminCount = User::where('role', 'admin')->count();
        $memberCount = User::where('role', 'member')->count();
        $totalUsers = User::count();
        $recentUsers = User::whereDate('created_at', '>=', Carbon::now()->subWeek())->count();
        
        return view('users.index', compact(
            'users', 
            'adminCount',
            'memberCount', 
            'totalUsers',
            'recentUsers'
        ));
    }

    /**
     * Display the specified user profile (admin only).
     */
    public function show(User $user)
    {
        // Check if user is admin
        if (!auth()->check() || !auth()->user()->isAdmin()) {
            abort(403, 'Access denied. Admin privileges required.');
        }
        
        return view('users.show', compact('user'));
    }
}