<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Signature;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SignatureController extends Controller
{
    public function show(Request $request, Signature $signature)
    {
        $user = $request->user();
        if ($user->organization_id !== $signature->organization_id && !$user->isSuperAdmin()) {
            abort(403, 'Akses ke tanda tangan ini ditolak.');
        }

        if (!Storage::disk('local')->exists($signature->file_path)) {
            abort(404, 'File tanda tangan tidak ditemukan.');
        }

        $file = Storage::disk('local')->get($signature->file_path);

        return response($file, 200, [
            'Content-Type' => $signature->mime_type ?? 'image/png',
            'Content-Disposition' => 'inline; filename="signature.png"',
            'Cache-Control' => 'private, max-age=86400',
        ]);
    }
}
