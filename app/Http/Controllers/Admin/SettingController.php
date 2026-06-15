<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Setting;
use Illuminate\Support\Facades\Hash;

class SettingController extends Controller
{
    public function index()
    {
        $masterPinSetting = Setting::where('key', 'master_pin')->first();
        $masterPin = $masterPinSetting ? $masterPinSetting->value : '';

        return view('admin.setting.index', compact('masterPin'));
    }

    public function updateMasterPin(Request $request)
    {
        $request->validate([
            'master_pin' => 'nullable|string|max:50',
        ]);

        if ($request->filled('master_pin')) {
            Setting::updateOrCreate(
                ['key' => 'master_pin'],
                ['value' => strtoupper(trim($request->master_pin))]
            );
        }

        return back()->with('success', 'Pengaturan Master PIN berhasil disimpan!');
    }
}
