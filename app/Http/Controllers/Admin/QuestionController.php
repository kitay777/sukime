<?php

// app/Http/Controllers/Admin/QuestionController.php
namespace App\Http\Controllers\Admin;

use App\Models\UserQuestionAnswer;
use App\Http\Controllers\Controller;
use App\Models\Question;
use Illuminate\Http\Request;
use Inertia\Inertia;

class QuestionController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Questions/Index', [
            'questions' => Question::orderBy('sort_order')->paginate(20),
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Questions/Create');
    }

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

        // 初回の場合 → initial ルートへ戻す
        if (!$request->user()->initial_questions_completed) {
            return redirect()->route('questions.initial');
        }

        // それ以降は日次なので dashboard へ
        return redirect()->route('dashboard');
    }


    public function edit(Question $question)
    {
        $question->is_active = (bool) $question->is_active;
        return Inertia::render('Admin/Questions/Edit', [
            'question' => $question,
        ]);
    }

    public function update(Request $request, Question $question)
    {
        $validated = $request->validate([
            'content' => 'required|string|max:500',
            'rarity' => 'required|in:normal,rare,super_rare,ultra_rare,secret',
            'sort_order' => 'nullable|integer',
            'is_active' => 'boolean',
        ]);


        $question->update($validated);

        return redirect()->route('admin.questions.index');
    }

    public function destroy(Question $question)
    {
        $question->delete();
        return redirect()->route('admin.questions.index');
    }
    public function initial(Request $request)
    {
        $user = $request->user();

        // すでに10問完了済みならダッシュボードへ
        if ($user->initial_questions_completed) {
            return redirect()->route('dashboard');
        }

        $answeredIds = UserQuestionAnswer::where('user_id', $user->id)
            ->pluck('question_id')
            ->toArray();

        // 10問終わっていたらフラグ更新
        if (count($answeredIds) >= 10) {
            $user->update(['initial_questions_completed' => true]);
            return redirect()->route('dashboard');
        }

        // 次の1問を選ぶ
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
        $rand = mt_rand(1, $total * 1000) / 1000; // 小数対応
        $cumulative = 0;

        foreach ($rarities as $rarity => $weight) {
            $cumulative += $weight;
            if ($rand <= $cumulative) {
                return $rarity;
            }
        }

        return 'normal';
    }

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

    public function showQuestionFlow(Request $request)
    {
        $user = $request->user();

        $answeredCount = UserQuestionAnswer::where('user_id', $user->id)->count();

        if ($answeredCount === 0) {
            // まだ一度も答えていない → 初回 10 問
            return redirect()->route('questions.initial');
        }

        // すでに答えている → 日次1問へ
        return redirect()->route('questions.daily');
    }


}
