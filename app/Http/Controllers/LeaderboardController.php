<?php

namespace App\Http\Controllers;

use App\Models\Contest;
use App\Models\Participation;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class LeaderboardController extends Controller
{
    public function show(Contest $contest): JsonResponse
    {
        $leaderboard = Participation::where('contest_id', $contest->id)
            ->where('status', 'completed')
            ->with('user:id,name,email')
            ->orderByDesc('score')
            ->orderBy('completed_at')
            ->get(['id', 'user_id', 'score', 'completed_at']);
        return response()->json(['leaderboard' => $leaderboard], 200);
    }
}
