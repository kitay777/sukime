<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Question;
use App\Models\UserQuestionAnswer;

class UserQuestionAnswerSeeder extends Seeder
{
    public function run(): void
    {
        $questions = Question::all();
        $users = User::all();

        foreach ($users as $user) {
            foreach ($questions as $question) {
                // すでに回答がある場合はスキップ（重複防止）
                if (UserQuestionAnswer::where('user_id', $user->id)
                        ->where('question_id', $question->id)
                        ->exists()) {
                    continue;
                }

                UserQuestionAnswer::create([
                    'user_id' => $user->id,
                    'question_id' => $question->id,
                    'answer' => "ダミー回答: {$user->name} - {$question->id}",
                    'answered_at' => now(),
                ]);
            }
        }
    }
}
