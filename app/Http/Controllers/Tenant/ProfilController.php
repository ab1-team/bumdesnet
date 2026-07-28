<?php

namespace App\Http\Controllers\Tenant;

use App\Models\Tenant\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Contracts\Session\Session;
use App\Http\Controllers\Controller;

class ProfilController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        $title = 'Profil User';
        return view('profil.index')->with(compact('title', 'user'));
    }

    public function update(Request $request)
    {
        $data = $request->only([
            'nama',
            'alamat',
            'telpon',
            'jenis_kelamin',
        ]);

        $validator = Validator::make($data, [
            'nama' => ['required', 'string', 'max:255'],
            'alamat' => ['required', 'string', 'max:255'],
            'telpon' => ['required', 'string', 'max:255'],
            'jenis_kelamin' => ['required', 'string', 'max:255'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Masih ada data yang kosong',
                'errors' => $validator->errors()
            ]);
        }

        $user = auth()->user();
        User::where('id', $user->id)->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Profil berhasil diupdate',
            'nama' => $data['nama'],
            'alamat' => $data['alamat']
        ]);
    }

    public function upload(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'profil-image' => 'file|image|mimes:png,jpg,jpeg|max:2048'
        ]);

        if ($validation->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Masih ada data yang kosong',
                'errors' => $validation->errors()
            ]);
        }

        $user = auth()->user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User tidak ditemukan atau belum login'
            ]);
        }

        if ($request->file('profil-image') && $request->file('profil-image')->isValid()) {
            // Hapus foto lama jika ada dan bukan default
            if ($user->foto && $user->foto != 'default.png') {
                Storage::delete('profil/' . $user->foto);
            }

            $file = $request->file('profil-image');
            $nama_file = time() . '_' . $file->getClientOriginalName();
            $file->storeAs('profil', $nama_file, 'public');

            // Update database menggunakan query builder seperti kamu mau
            $upload = User::where('id', $user->id)
                ->update([
                    'foto' => $nama_file,
                ]);

            if ($upload) {
                return response()->json([
                    'success' => true,
                    'msg' => 'Gambar berhasil diupload'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'msg' => 'Gagal update data user'
                ]);
            }
        }

        return response()->json([
            'success' => false,
            'msg' => 'Upload gambar gagal'
        ]);
    }

    public function data_login(Request $request)
    {
        $user = auth()->user();
        $data = $request->only([
            'username',
            'password',
        ]);

        $validasi['username'] = ['required', 'string', 'max:255'];
        if ($data['username'] != $user->username) {
            $validasi['username'] = ['unique:users'];
        }

        $validator = Validator::make($data, $validasi);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Username sudah terdaftar',
                'errors' => $validator->errors()
            ]);
        }

        $update['auth_token'] = md5(strtolower($data['username'] . '|' . $data['password']));
        $update['username'] = $data['username'];
        if ($data['password']) {
            $update['password'] = Hash::make($data['password']);
        }
        User::where('id', $user->id)->update($update);

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json([
            'success' => true,
            'message' => 'Profil berhasil diupdate',
            'username' => $data['username'],
        ]);
    }
}
