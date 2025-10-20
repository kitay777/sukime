<?php

namespace App\Support;

use App\Models\Crush;
use App\Models\User;
use App\Models\Oshi;   

class CrushMatcher
{
    // 全/半角・空白を正規化し、小文字化
    public static function norm(?string $s): string
    {
        $s = (string)$s;
        // 全半角・スペース・カタカナ等を統一（a=英/数, s=スペース, K=カタカナ半角→全角, V=濁点結合）
        $s = mb_convert_kana($s, 'asKV', 'UTF-8');
        // すべての空白（全角/半角/改行/タブ）を除去
        $s = preg_replace('/\s+/u', '', $s ?? '');
        return mb_strtolower(trim($s), 'UTF-8');
    }

    public static function normGender(?string $g): string
    {
        $g = self::norm($g);
        return match ($g) {
            '男性','おとこ','だんせい','male','man','m'   => 'male',
            '女性','おんな','じょせい','female','woman','f'=> 'female',
            'その他','other','o'                             => 'other',
            '', '未回答','unknown','unk'                    => 'unknown',
            default                                         => $g,
        };
    }

    public static function crushEqualsProfile(Crush|Oshi $crush, User $user): bool
    {
        // users テーブルは統一済み（school/faculty/grade/gender）を想定
        $uSchool  = $user->school  ?? '';
        $uFaculty = $user->faculty ?? '';
        $uGrade   = $user->grade   ?? '';
        $uGender  = $user->gender  ?? '';

        return self::norm($crush->name)    === self::norm($user->name)
            && self::norm($crush->school)  === self::norm($uSchool)
            && self::norm($crush->faculty) === self::norm($uFaculty)
            && self::norm($crush->grade)   === self::norm($uGrade)
            && self::normGender($crush->gender) === self::normGender($uGender);
    }
}
