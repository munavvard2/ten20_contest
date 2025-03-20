<?php

namespace App\Http\Controllers;

use App\Models\Contest;
use App\Models\Question;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class QuestionController extends Controller
{
    public function index(Contest $contest): JsonResponse
    {
        return response()->json(['questions' => $contest->questions()->with('answers')->get()], 200);
    }

    public function store(Request $request, Contest $contest): JsonResponse
    {
        $validated = $request->validate([
            'question_text' => 'required|string',
            'question_type' => 'required|in:single-select,multi-select,true-false',
            'points' => 'integer|min:1',
            'answers' => 'required|array|min:2',
            'answers.*.answer_text' => 'required|string',
            'answers.*.is_correct' => 'required|boolean',
        ]);

        $question = $contest->questions()->create([
            'question_text' => $validated['question_text'],
            'question_type' => $validated['question_type'],
            'points' => $validated['points'] ?? 1,
        ]);

        foreach ($validated['answers'] as $answerData) {
            $question->answers()->create([
                'answer_text' => $answerData['answer_text'],
                'is_correct' => $answerData['is_correct'],
            ]);
        }

        return response()->json(['question' => $question->load('answers')], 201);
    }

    public function show(Contest $contest, Question $question): JsonResponse
    {
        if ($question->contest_id !== $contest->id) {
            return response()->json(['message' => 'Question not found in this contest'], 404);
        }

        return response()->json(['question' => $question->load('answers')], 200);
    }

    public function update(Request $request, Contest $contest, Question $question): JsonResponse
    {
        if ($question->contest_id !== $contest->id) {
            return response()->json(['message' => 'Question not found in this contest'], 404);
        }

        $validated = $request->validate([
            'question_text' => 'string',
            'question_type' => 'in:single-select,multi-select,true-false',
            'points' => 'integer|min:1',
        ]);

        $question->update($validated);

        return response()->json(['question' => $question], 200);
    }

    public function destroy(Contest $contest, Question $question): JsonResponse
    {
        if ($question->contest_id !== $contest->id) {
            return response()->json(['message' => 'Question not found in this contest'], 404);
        }

        $question->delete();

        return response()->json(['message' => 'Question deleted successfully'], 200);
    }
}
