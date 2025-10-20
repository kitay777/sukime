<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Question;
use App\Models\UserQuestionAnswer;
use Illuminate\Support\Facades\Hash;

class DummyUserSeeder extends Seeder
{
    public function run(): void
    {
        for ($i = 1; $i <= 50; $i++) {
            $user = User::create([
                'name' => "user{$i}",
                'real_name' => "テストユーザー{$i}",
                'school_name' => "千葉大学",
                'class_or_department' => "経済学部",
                'grade' => rand(1,4) . "年",
                'email' => "user{$i}@example.com",
                'password' => Hash::make('password'),
                'gender' => rand(0,1) ? 'male' : 'female',
                'profile_completed' => true,
            ]);

            // 質問にダミー回答を入れる
            $questions = Question::inRandomOrder()->take(5)->get();
            foreach ($questions as $q) {
                UserQuestionAnswer::create([
                    'user_id' => $user->id,
                    'question_id' => $q->id,
                    'answer' => "ダミー回答 {$i}-{$q->id}",
                    'answered_at' => now(),
                ]);
            }
        }
    }
}

