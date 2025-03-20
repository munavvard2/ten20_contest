<?php

namespace App\Http\Controllers;

use App\Models\Contest;
use App\Models\Participation;
use App\Models\Prize;
use App\Models\Question;
use App\Models\Answer;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ParticipationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $participations = $request->user()->participations()->with('contest')->get();

        return response()->json(['participations' => $participations], 200);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'contest_id' => 'required|exists:contests,id',
        ]);

        $user = $request->user();
        $contest = Contest::findOrFail($validated['contest_id']);

        // Check if contest is accessible to the user
        if ($contest->isVip() && $user->role === 'normal') {
            return response()->json(['message' => 'You do not have access to this contest'], 403);
        }

        // Check if user already has an active participation in this contest
        $existingParticipation = Participation::where('user_id', $user->id)
            ->where('contest_id', $contest->id)
            ->where('status', 'in-progress')
            ->first();

        if ($existingParticipation) {
            return response()->json(['participation' => $existingParticipation], 200);
        }

        $participation = Participation::create([
            'user_id' => $user->id,
            'contest_id' => $contest->id,
            'score' => 0,
            'status' => 'in-progress',
        ]);

        return response()->json(['participation' => $participation], 201);
    }

    public function show(Participation $participation, Request $request): JsonResponse
    {
        if ($participation->user_id !== $request->user()->id && !in_array($request->user()->role, ['admin', 'vip'])) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json([
            'participation' => $participation->load(['contest', 'userAnswers.question', 'userAnswers.answer'])
        ], 200);
    }

    public function update(Request $request, Participation $participation): JsonResponse
    {
        if ($participation->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($participation->status === 'completed') {
            return response()->json(['message' => 'This participation is already completed'], 400);
        }

        $validated = $request->validate([
            'answers' => 'required|array',
            'answers.*.question_id' => 'required|exists:questions,id',
            'answers.*.answer_ids' => 'required|array',
            'answers.*.answer_ids.*' => 'required|exists:answers,id',
        ]);

        // Process and save user answers
        foreach ($validated['answers'] as $answer) {
            $question = Question::findOrFail($answer['question_id']);

            // Check if question belongs to the participation's contest
            if ($question->contest_id !== $participation->contest_id) {
                continue;
            }

            // For single-select and true-false questions, take only the first answer
            if (in_array($question->question_type, ['single-select', 'true-false'])) {
                $answerIds = [array_shift($answer['answer_ids'])];
            } else {
                $answerIds = $answer['answer_ids'];
            }

            // Remove existing answers for this question
            $participation->userAnswers()
                ->where('question_id', $question->id)
                ->delete();

            // Save new answers
            foreach ($answerIds as $answerId) {
                $participation->userAnswers()->create([
                    'question_id' => $question->id,
                    'answer_id' => $answerId,
                ]);
            }
        }

        // If submitting the participation
        if ($request->input('submit', false)) {
            $this->calculateScore($participation);
            $participation->update([
                'status' => 'completed',
                'completed_at' => now(),
            ]);


            $firstPrizeUser = Participation::where('contest_id', $participation->contest_id)
                ->where('status', 'completed')
                ->with('user:id,name,email')
                ->orderByDesc('score')
                ->orderBy('completed_at')
                ->get(['id', 'user_id', 'score', 'completed_at'])
                ->first();

            $contest = $participation->contest;
            if ($contest->prize) {
                $prize = $contest->prize;
                $prize->user_id = $firstPrizeUser->user_id;
                $prize->save();
            } else {
                Prize::create([
                    'user_id' => $firstPrizeUser->user_id,
                    'contest_id' => $participation->contest_id,
                    'prize_name' => $contest->prize_information
                ]);
            }
        }

        return response()->json(['participation' => $participation->fresh()], 200);
    }

    protected function calculateScore(Participation $participation): void
    {
        $score = 0;
        $userAnswers = $participation->userAnswers()->with(['question', 'answer'])->get();
        $questionScores = [];

        foreach ($userAnswers as $userAnswer) {
            $questionId = $userAnswer->question_id;

            if (!isset($questionScores[$questionId])) {
                $questionScores[$questionId] = [
                    'correct' => true,
                    'points' => $userAnswer->question->points,
                ];
            }

            // If any answer is incorrect, mark question as incorrect
            if (!$userAnswer->answer->is_correct) {
                $questionScores[$questionId]['correct'] = false;
            }
        }

        foreach ($questionScores as $questionScore) {
            if ($questionScore['correct']) {
                $score += $questionScore['points'];
            }
        }
        $participation->update(['score' => $score]);
    }
}
