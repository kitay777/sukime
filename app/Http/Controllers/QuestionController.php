<?php

namespace App\Http\Controllers;

use App\Models\Question;
use App\Models\UserQuestionAnswer;
use Illuminate\Http\Request;
use Inertia\Inertia;

class QuestionController extends Controller
{
    // 初回10問（1問ずつ）
    public function initial(Request $request)
    {
        $user = $request->user();

        if ($user->initial_questions_completed) {
            return redirect()->route('dashboard');
        }

        $answeredIds = UserQuestionAnswer::where('user_id', $user->id)
            ->pluck('question_id')
            ->toArray();

        if (count($answeredIds) >= 10) {
            $user->update(['initial_questions_completed' => true]);
            return redirect()->route('dashboard');
        }

        $pickRarity = $this->pickRarity();
        $question = Question::where('rarity', $pickRarity)
            ->where('is_active', true)
            ->whereNotIn('id', $answeredIds)
            ->inRandomOrder()
            ->first();

        if (!$question) {
            $question = Question::where('is_active', true)
                ->whereNotIn('id', $answeredIds)
                ->inRandomOrder()
                ->first();
        }
        return Inertia::render('Questions/InitialOneByOne', [
            'question' => $question,
            'answered_count' => count($answeredIds),
        ]);
    }

    // 回答保存
    public function store(Request $request)
    {
        $validated = $request->validate([
            'question_id' => 'required|exists:questions,id',
            'answer'      => 'required|string|max:500',
        ]);

        UserQuestionAnswer::create([
            'user_id'     => $request->user()->id,
            'question_id' => $validated['question_id'],
            'answer'      => $validated['answer'],
            'answered_at' => now(),
        ]);

        if (!$request->user()->initial_questions_completed) {
            return redirect()->route('questions.initial');
        }

        return redirect()->route('dashboard');
    }

    // 日次1問
    public function daily(Request $request)
    {
        $user = $request->user();

        $answeredToday = UserQuestionAnswer::where('user_id', $user->id)
            ->whereDate('answered_at', today())
            ->exists();

        if ($answeredToday) {
            return redirect()->route('dashboard');
        }

        $answeredIds = UserQuestionAnswer::where('user_id', $user->id)
            ->pluck('question_id');

        $pickRarity = $this->pickRarity();

        $question = Question::where('rarity', $pickRarity)
            ->where('is_active', true)
            ->whereNotIn('id', $answeredIds)
            ->inRandomOrder()
            ->first();

        if (!$question) {
            $question = Question::where('is_active', true)
                ->whereNotIn('id', $answeredIds)
                ->inRandomOrder()
                ->first();
        }

        return Inertia::render('Questions/Daily', [
            'question' => $question,
        ]);
    }

    // 初回か日次かを振り分け
    public function showQuestionFlow(Request $request)
    {
        $user = $request->user();

        $answeredCount = UserQuestionAnswer::where('user_id', $user->id)->count();

        if ($answeredCount === 0) {
            return redirect()->route('questions.initial');
        }

        return redirect()->route('questions.daily');
    }

    // 確率抽選
    private function pickRarity(): string
    {
        $rarities = [
            'normal'       => 53,
            'rare'         => 30,
            'super_rare'   => 15,
            'ultra_rare'   => 2,
            'secret'       => 0.1,
        ];

        $total = array_sum($rarities);
        $rand = mt_rand(1, $total * 1000) / 1000;
        $cumulative = 0;

        foreach ($rarities as $rarity => $weight) {
            $cumulative += $weight;
            if ($rand <= $cumulative) {
                return $rarity;
            }
        }

        return 'normal';
    }
}
