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
        $masterPin = Setting::where('key', 'master_pin')->first();
        $ownerCardUidSetting = Setting::where('key', 'owner_card_uid')->first();
        
        $ownerCardUid = $ownerCardUidSetting ? $ownerCardUidSetting->value : '';

        return view('admin.setting.index', compact('masterPin', 'ownerCardUid'));
    }

    public function updateMasterPin(Request $request)
    {
        $request->validate([
            'master_pin'     => 'nullable|digits:6',
            'owner_card_uid' => 'nullable|string|max:50',
        ]);

        if ($request->filled('master_pin')) {
            Setting::updateOrCreate(
                ['key' => 'master_pin'],
                ['value' => Hash::make($request->master_pin)]
            );
        }

        Setting::updateOrCreate(
            ['key' => 'owner_card_uid'],
            ['value' => $request->owner_card_uid]
        );

        return back()->with('success', 'Pengaturan Master PIN & Kartu Admin berhasil disimpan!');
    }
}
