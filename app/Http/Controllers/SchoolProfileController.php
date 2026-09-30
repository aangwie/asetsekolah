<?php

namespace App\Http\Controllers;

use App\Models\SchoolProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class SchoolProfileController extends Controller
{
    public function edit(): View
    {
        return view('school-profile.form', ['profile' => SchoolProfile::firstOrCreate([], ['school_name' => 'Sekolah'])]);
    }

    public function update(Request $request): RedirectResponse
    {
        $profile = SchoolProfile::firstOrCreate([], ['school_name' => 'Sekolah']);
        $data = $request->validate([
            'school_name' => 'required|string|max:255',
            'npsn' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'logo_pemkab' => 'nullable|image|max:500|mimes:jpg,jpeg,png,webp',
            'logo_sekolah' => 'nullable|image|max:500|mimes:jpg,jpeg,png,webp',
        ]);

        $oldPemkab = $profile->logo_pemkab_path;
        $oldSekolah = $profile->logo_sekolah_path;
        $newPemkab = $request->hasFile('logo_pemkab') ? self::storeLogo($request->file('logo_pemkab')) : null;
        $newSekolah = $request->hasFile('logo_sekolah') ? self::storeLogo($request->file('logo_sekolah')) : null;

        try {
            $profile->update([
                'school_name' => $data['school_name'],
                'npsn' => $data['npsn'] ?? null,
                'address' => $data['address'] ?? null,
                'logo_pemkab_path' => $newPemkab ?? $oldPemkab,
                'logo_sekolah_path' => $newSekolah ?? $oldSekolah,
            ]);
        } catch (\Throwable $e) {
            if ($newPemkab) Storage::disk('public')->delete($newPemkab);
            if ($newSekolah) Storage::disk('public')->delete($newSekolah);
            throw $e;
        }

        if ($newPemkab && $oldPemkab) Storage::disk('public')->delete($oldPemkab);
        if ($newSekolah && $oldSekolah) Storage::disk('public')->delete($oldSekolah);

        return redirect()->route('school-profile.edit')->with('ok', 'Data sekolah update.');
    }

    private static function storeLogo(UploadedFile $file): string
    {
        $img = imagecreatefromstring(file_get_contents($file->getRealPath()));
        $path = 'logos/'.Str::uuid()->toString().'.webp';
        $tmp = tempnam(sys_get_temp_dir(), 'webp');
        imagewebp($img, $tmp, 80);
        imagedestroy($img);
        Storage::disk('public')->put($path, file_get_contents($tmp));
        @unlink($tmp);

        return $path;
    }
}
