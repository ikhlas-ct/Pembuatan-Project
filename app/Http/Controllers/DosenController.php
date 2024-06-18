<?php

namespace App\Http\Controllers;

use App\Helpers\AlertHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Dosen;
use App\Models\DosenPembimbing;
use App\Models\JudulTugasAkhir;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;



class DosenController extends Controller
{
    public function __construct()
    {
        $this->middleware('role:dosen');
    }
    public function Dashboard()
    {
        return view('Dosen.dashboard.Dashboard');
    }
    public function profile()
    {
        // dd(Auth::user()->dosen);

        return view('Dosen.Biodata.biodata');
    }

    public function updateProfile(Request $request)
    {
        // Validasi data
        $request->validate([
            'nidn' => 'required|string|max:255',
            'nama' => 'required|string|max:255',
            'department' => 'required|string|max:255',
            'no_hp' => 'required|string|max:255',
            'alamat' => 'required|string',
            'deskripsi' => 'nullable|string',
            'gambar' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048', // Validasi gambar
        ]);
    
        // Ambil data dosen yang sedang login
        $dosen = auth()->user()->dosen;
    
        // Update atribut dosen
        $dosen->nidn = $request->nidn;
        $dosen->nama = $request->nama;
        $dosen->department = $request->department;
        $dosen->no_hp = $request->no_hp;
        $dosen->alamat = $request->alamat;
        $dosen->deskripsi = $request->deskripsi;
    
        // Perbarui gambar profil jika ada
        if ($request->hasFile('gambar')) {
            Log::info('Gambar ditemukan dalam request.');
            $profileImage = $request->file('gambar');
            $profileImageSaveAsName = time() . Auth::id() . "-profile." . $profileImage->getClientOriginalExtension();
            $upload_path = 'dosen_images/';
            $profile_image_url = $upload_path . $profileImageSaveAsName;
            $profileImage->move(public_path($upload_path), $profileImageSaveAsName);
            Log::info('Gambar berhasil diunggah ke: ' . $profile_image_url);
            $dosen->gambar = $profile_image_url;
        } else {
            Log::info('Gambar tidak ditemukan dalam request.');
        }
    
        // Simpan perubahan
        $dosen->save();
    
        AlertHelper::alertSuccess('Anda telah berhasil mengupdate profil', 'Selamat!', 2000);
        return redirect()->route('profile');
    }

    
    public function konsultasi_show()
    {
        return view('Mahasiswa.Konsultasi.konsultasi');
    }

public function updatePassword(Request $request)
{
    $request->validate([
        'username' => ['required', 'string', 'max:255', Rule::unique('users')->ignore(Auth::user()->id)],
        'password_lama' => ['required'],
        'password' => 'required|confirmed', // Password confirmation
    ], [
        'username.required' => 'Username harus diisi.',
        'username.max' => 'Username maksimal 255 karakter.',
        'username.unique' => 'Username sudah digunakan oleh pengguna lain.',
        'password_lama.required' => 'Password lama harus diisi.',
        'password.required' => 'Password baru harus diisi.',
        'password.confirmed' => 'Konfirmasi password baru tidak cocok.',
    ]);

    $user = Auth::user();

    // Validasi password lama
    if (!Hash::check($request->password_lama, $user->password)) {
        return back()->withErrors(['password_lama' => 'Password lama tidak cocok']);
    }

    // Update username
    $user->username = $request->username;
    $user->save();

    // Update password
    $user->password = Hash::make($request->password);
    $user->save();

    // Hapus gambar lama jika ada
    if ($user->profile_image) {
        $gambarProfilPath = 'dosen_images/' . $user->profile_image;

        // Hapus gambar dari storage
        if (Storage::disk('public')->exists($gambarProfilPath)) {
            Storage::disk('public')->delete($gambarProfilPath);
            // Set kolom gambar_profil ke null (jika ada)
            $user->profile_image = null;
            $user->save();
        }
    }
    AlertHelper::alertSuccess('Anda telah berhasil mengupdate username dan passowrd', 'Selamat!', 2000);
    return redirect()->back();
}


public function daftar_judul()
{
    $user = Auth::user(); // Ambil user yang sedang login (diasumsikan menggunakan authentication Laravel)
    $dosenPembimbing = DosenPembimbing::where('dosen_id', $user->id)->first(); // Ambil data dosen pembimbing yang login
    $judulTugasAkhirs = JudulTugasAkhir::whereHas('mahasiswaBimbingan', function ($query) use ($dosenPembimbing) {
        $query->where('dosen_pembimbing_id', $dosenPembimbing->id);
    })->get();

    return view('pages.dosen.pengajuanjudul', compact('judulTugasAkhirs'));
}

public function updateStatus(Request $request, $id)
{
    $request->validate([
        'status' => 'required|in:diterima,ditolak',
    ]);

    $judul = JudulTugasAkhir::findOrFail($id);
    $judul->status = $request->status;
    $judul->save();

    return back()->with('success', 'Status judul tugas akhir berhasil diperbarui.');
}
    
    

    }
