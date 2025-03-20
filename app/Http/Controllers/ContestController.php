<?php

namespace App\Http\Controllers;

use App\Models\Contest;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class ContestController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Contest::query();

        // Get user if authenticated, otherwise treat as guest
        $user = $request->user();
        if(!$user) { // if not found in request, try to get from sanctum guard
            $user = (Auth::guard('sanctum')->user());
        }
        if (!$user) {
            // Guest users can only see normal contests
            $query->where('access_level', 'normal');
        } elseif ($user->role === 'normal') {
            // Normal users can only see normal contests
            $query->where('access_level', 'normal');
        }
        // Admin and VIP users can see all contests
        $contests = $query->get();
        return response()->json(['contests' => $contests], 200);
    }

    public function show(Contest $contest, Request $request): JsonResponse
    {
        $user = $request->user();
        if(!$user) { // if not found in request, try to get from sanctum guard
            $user = (Auth::guard('sanctum')->user());
        }
        // Guest users can only view normal contests
        if (!$user && $contest->isVip()) {
            return response()->json(['message' => 'You need to be logged in to view this contest'], 403);
        }

        // Normal users cannot access VIP contests
        if ($user && $user->role === 'normal' && $contest->isVip()) {
            return response()->json(['message' => 'You do not have access to this contest'], 403);
        }

        return response()->json(['contest' => $contest->load('questions.answers')], 200);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
            'prize_information' => 'required|string',
            'access_level' => 'required|in:normal,vip',
            'is_active' => 'boolean',
        ]);

        $contest = Contest::create($validated);

        return response()->json(['contest' => $contest], 201);
    }

    public function update(Request $request, Contest $contest): JsonResponse
    {
        // Check if user can access this contest
        if ($contest->isVip() && $request->user()->role === 'normal') {
            return response()->json(['message' => 'You do not have access to this contest'], 403);
        }
        $validated = $request->validate([
            'name' => 'string|max:255',
            'description' => 'string',
            'start_time' => 'date',
            'end_time' => 'date|after:start_time',
            'prize_information' => 'string',
            'access_level' => 'in:normal,vip',
            'is_active' => 'boolean',
        ]);

        $contest->update($validated);

        return response()->json(['contest' => $contest], 200);
    }

    public function destroy(Request $request,Contest $contest): JsonResponse
    {
        // Check if user can access this contest
        if ($contest->isVip() && $request->user()->role === 'normal') {
            return response()->json(['message' => 'You do not have access to this contest'], 403);
        }
        $contest->delete();

        return response()->json(['message' => 'Contest deleted successfully'], 200);
    }
}
