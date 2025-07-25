<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\QuizQuestion;
use App\Models\Relationship;
use App\Models\ShutterTime;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'logto_token' => 'required|string',
        ]);

        try {
            // Verify Logto token
            $decoded = JWT::decode(
                $request->logto_token,
                new Key(config('onlyshots.logto.app_secret'), 'HS256')
            );

            // Find or create user
            $user = User::firstOrCreate(
                ['logto_id' => $decoded->sub],
                [
                    'email' => $decoded->email ?? '',
                    'nickname' => $decoded->nickname ?? $decoded->name ?? '',
                    'avatar' => $decoded->picture ?? '',
                ]
            );

            // Create personal relationship if not exists
            if (!$user->relationships()->where('type', 'personal')->exists()) {
                Relationship::create([
                    'owner_user_id' => $user->id,
                    'type' => 'personal',
                    'name' => $user->nickname . '的个人空间',
                ]);
            }

            // Create shutter time record if not exists
            if (!$user->shutterTime) {
                ShutterTime::create([
                    'user_id' => $user->id,
                    'balance' => 0,
                ]);
            }

            // Generate API token
            $token = $user->createToken('OnlyShots API')->plainTextToken;

            return response()->json([
                'user' => $user->load('shutterTime'),
                'token' => $token,
                'needs_quiz' => $user->level === 0,
            ]);

        } catch (\Exception $e) {
            throw ValidationException::withMessages([
                'logto_token' => ['Invalid token'],
            ]);
        }
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out successfully']);
    }

    public function me(Request $request)
    {
        return response()->json([
            'user' => $request->user()->load(['shutterTime', 'relationships']),
        ]);
    }

    public function quiz(Request $request)
    {
        $user = $request->user();
        
        if ($user->level > 0) {
            return response()->json(['message' => 'Quiz already completed'], 400);
        }

        $questions = QuizQuestion::active()
            ->inRandomOrder()
            ->take(config('onlyshots.quiz.questions_per_quiz'))
            ->get(['id', 'question', 'options']);

        return response()->json(['questions' => $questions]);
    }

    public function submitQuiz(Request $request)
    {
        $user = $request->user();
        
        if ($user->level > 0) {
            return response()->json(['message' => 'Quiz already completed'], 400);
        }

        $request->validate([
            'answers' => 'required|array|size:' . config('onlyshots.quiz.questions_per_quiz'),
            'answers.*.question_id' => 'required|exists:quiz_questions,id',
            'answers.*.selected_option' => 'required|integer|min:0|max:3',
        ]);

        $questions = QuizQuestion::whereIn('id', collect($request->answers)->pluck('question_id'))->get();
        $correct = 0;

        foreach ($request->answers as $answer) {
            $question = $questions->find($answer['question_id']);
            if ($question && $question->correct_option === $answer['selected_option']) {
                $correct++;
            }
        }

        $score = ($correct / config('onlyshots.quiz.questions_per_quiz')) * 100;
        $passed = $score >= config('onlyshots.quiz.passing_score');

        if ($passed) {
            $user->update(['level' => 1]);
        }

        return response()->json([
            'score' => $score,
            'passed' => $passed,
            'correct_answers' => $correct,
            'total_questions' => config('onlyshots.quiz.questions_per_quiz'),
        ]);
    }
}