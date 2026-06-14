<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Setting;
use App\Models\Kamar;
use Illuminate\Support\Facades\Hash;

class SettingController extends Controller
{
    public function index()
    {
        $masterPin = Setting::where('key', 'master_pin')->first();
        $masterPinRoomsSetting = Setting::where('key', 'master_pin_rooms')->first();
        
        $allowedRooms = $masterPinRoomsSetting && $masterPinRoomsSetting->value ? json_decode($masterPinRoomsSetting->value, true) : [];
        $kamars = Kamar::orderBy('nomor_kamar')->get();

        return view('admin.setting.index', compact('masterPin', 'allowedRooms', 'kamars'));
    }

    public function updateMasterPin(Request $request)
    {
        $request->validate([
            'master_pin' => 'nullable|digits:6',
            'kamar_ids'  => 'nullable|array',
            'kamar_ids.*'=> 'nullable|exists:kamars,id',
        ]);

        if ($request->filled('master_pin')) {
            Setting::updateOrCreate(
                ['key' => 'master_pin'],
                ['value' => Hash::make($request->master_pin)]
            );
        }

        // Save allowed rooms
        $allowedRooms = $request->kamar_ids ?? [];
        Setting::updateOrCreate(
            ['key' => 'master_pin_rooms'],
            ['value' => json_encode($allowedRooms)]
        );

        return back()->with('success', 'Pengaturan PIN Khusus Pemilik Kos berhasil disimpan!');
    }
}
