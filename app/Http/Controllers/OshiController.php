<?php

namespace App\Http\Controllers;

use App\Http\Requests\OshiStoreRequest;
use App\Models\Oshi;
use Illuminate\Http\RedirectResponse;

class OshiController extends Controller
{
    public function store(OshiStoreRequest $request): RedirectResponse
    {
        $oshi = Oshi::create([
            'user_id' => $request->user()->id,
            'name'    => $request->string('name'),
            'school'  => $request->input('school'),
            'faculty' => $request->input('faculty'),
            'grade'   => $request->input('grade'),
            'gender'  => $request->input('gender'),
        ]);

        return back(303)->with('success', '推しを追加しました。');
    }

    public function destroy(int $id)
    {
        $oshi = Oshi::where('user_id', auth()->id())->findOrFail($id);
        $oshi->delete();

        // ★ 303 で返す（Inertiaの定番）
        return back(303)->with('success', '推しを削除しました。');
        // もしくは
        // return redirect()->route('dashboard', [], 303)->with('success', '推しを削除しました。');
    }
}
