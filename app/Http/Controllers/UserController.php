<?php

namespace App\Http\Controllers;

use App\Models\Participation;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    /**
     * Register a new user. and get token
     */
    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => ['required', 'confirmed', Password::min(8)],
            'role' => 'sometimes|in:normal,vip,admin',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'] ?? 'normal', // Default to normal role
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => ['token' => explode("|",$token)[1]],
        ], 201);
    }
    /**
     * Login the user. get token on login
     */
    public static function login(Request $request): JsonResponse
    {
        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {
            $user = Auth::user();
            if($user->tokens->count()){
                foreach ($user->tokens as $token) {
                    $token->delete(); // safeguard against multiple tokens
                }
            }
            $token = $user->createToken('auth_token')->plainTextToken;
            return response()->json(['token' => explode("|",$token)[1]], 200);
        }
        return response()->json(['message' => 'Unauthorized'], 401);
    }
    /**
     * Get all prizes won by the authenticated user.
     */
    public function prizes(Request $request): JsonResponse
    {
        $prizes = $request->user()
            ->prizes()
            ->with('contest:id,name,description')
            ->orderByDesc('created_at')
            ->get();

        return response()->json(['prizes' => $prizes], 200);
    }

    /**
     * Get the authenticated user's participation history.
     */
    public function participationHistory(Request $request): JsonResponse
    {
        $history = $request->user()
            ->participations()
            ->with(['contest:id,name,description,start_time,end_time'])
            ->orderByDesc('created_at')
            ->get();

        return response()->json(['participation_history' => $history], 200);
    }

    /**
     * Get contests that the user has joined but not completed.
     */
    public function inProgressContests(Request $request): JsonResponse
    {
        $inProgressContests = $request->user()
            ->participations()
            ->where('status', 'in-progress')
            ->with(['contest' => function($query) {
                $query->select('id', 'name', 'description', 'start_time', 'end_time');
            }])
            ->get()
            ->pluck('contest');

        return response()->json(['in_progress_contests' => $inProgressContests], 200);
    }

    /**
     * Get a summary of the user's contest activity.
     */
    public function dashboard(Request $request): JsonResponse
    {
        $user = $request->user();

        $totalParticipations = $user->participations()->count();
        $completedParticipations = $user->participations()->where('status', 'completed')->count();
        $inProgressParticipations = $user->participations()->where('status', 'in-progress')->count();
        $totalPrizes = $user->prizes()->count();

        $recentParticipations = $user->participations()
            ->with('contest:id,name')
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        $recentPrizes = $user->prizes()
            ->with('contest:id,name')
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        return response()->json([
            'stats' => [
                'total_participations' => $totalParticipations,
                'completed_participations' => $completedParticipations,
                'in_progress_participations' => $inProgressParticipations,
                'total_prizes' => $totalPrizes,
            ],
            'recent_participations' => $recentParticipations,
            'recent_prizes' => $recentPrizes,
        ], 200);
    }
}
