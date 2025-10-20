<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Question;

class QuestionSeeder extends Seeder
{
    public function run(): void
    {
        // ノーマルレア (53%)
        $normals = [
            '好きな食べ物は？',
            '好きな曲やアーティストは？',
            '将来なりたい職業は？',
            'MBTI診断結果は？',
            'タイプの芸能人は？',
            '休日は何をしている？',
            '好きなキャラクターは？',
            '好きなアニメや漫画は？',
            '最近ハマっている趣味は？',
            '好きな映画は？',
            '好きなYouTuberは？',
            'ストレス解消法は？',
            '好きなゲームは？',
            '自分を動物に例えると何？',
            '一億円あったら何に使う？',
            '他人に自慢できることは？',
            'もし無人島に一つだけ持っていくなら？',
            '一目ぼれしたことある？',
            '誕生月は？',
            '初恋の時期は？',
            '自分で思う自分のギャップは？',
            '好きな匂いは？',
            '好きな人ができたらすぐ周りにバレるタイプ？隠すタイプ？',
        ];
        foreach ($normals as $q) {
            Question::create(['content' => $q, 'rarity' => 'normal', 'is_active' => true]);
        }

        // レア (30%)
        $rares = [
            '恋人に求める条件は？',
            '異性の好きな髪型は？',
            '自分の好きなところ、魅力的なところは？',
            '恋人と行きたいところは？',
            '異性のどういう系統の服が好み？',
            '友達と恋人どちらを優先する？',
            '恋人とどれくらいの頻度で連絡取りたい？',
            '恋人とどれくらいの頻度でデートしたい？',
            '恋愛は追いたい派？追われたい派？',
            '恋人にされたら冷める行動は？',
            '失恋した事ある？',
            '理想の告白シチュエーションは？',
            '異性にされてキュンとしたことは？',
        ];
        foreach ($rares as $q) {
            Question::create(['content' => $q, 'rarity' => 'rare', 'is_active' => true]);
        }

        // スーパーレア (15%)
        $supers = [
            '異性の好きな仕草は？',
            'タイプじゃないけど好きになった経験がある？',
            '恋人に言われて一番うれしい一言は？',
            '顔と性格、どちらが大事？',
            '自分はS？M？',
            '好きな人に何て呼ばれたい？',
            '恋人には甘えたい？甘えられたい？',
        ];
        foreach ($supers as $q) {
            Question::create(['content' => $q, 'rarity' => 'super_rare', 'is_active' => true]);
        }

        // ウルトラレア (2%)
        $ultras = [
            '付き合ったことがある人数は？',
            '相手に見せる「好きのサイン」は？',
        ];
        foreach ($ultras as $q) {
            Question::create(['content' => $q, 'rarity' => 'ultra_rare', 'is_active' => true]);
        }

        // シークレットレア (0.1%)
        $secrets = [
            '好きな人がいるかどうか',
        ];
        foreach ($secrets as $q) {
            Question::create(['content' => $q, 'rarity' => 'secret', 'is_active' => true]);
        }
    }
}
