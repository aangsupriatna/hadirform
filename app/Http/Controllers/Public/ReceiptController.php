<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class ReceiptController extends Controller
{
    public function show(string $receiptCode)
    {
        $response = Response::where('receipt_code', $receiptCode)
            ->with(['form', 'event', 'organization', 'answers', 'participantSignature'])
            ->firstOrFail();

        $verifyUrl = route('receipt.show', $receiptCode);
        $qrCodeSvg = QrCode::size(160)->generate($verifyUrl);

        $signatureDataUrl = null;
        if ($response->participantSignature && Storage::disk('local')->exists($response->participantSignature->file_path)) {
            $raw = Storage::disk('local')->get($response->participantSignature->file_path);
            $mime = $response->participantSignature->mime_type ?? 'image/png';
            $signatureDataUrl = "data:{$mime};base64," . base64_encode($raw);
        }

        $photoDataUrl = null;
        $photoAnswer = $response->answers->first(function ($a) {
            return is_array($a->value_json) && ($a->value_json['type'] ?? '') === 'photo';
        });
        if ($photoAnswer && !empty($photoAnswer->value_json['file_path']) && Storage::disk('local')->exists($photoAnswer->value_json['file_path'])) {
            $photoRaw = Storage::disk('local')->get($photoAnswer->value_json['file_path']);
            $photoMime = $photoAnswer->value_json['mime_type'] ?? 'image/jpeg';
            $photoDataUrl = "data:{$photoMime};base64," . base64_encode($photoRaw);
        }

        return view('public.receipt', compact('response', 'qrCodeSvg', 'signatureDataUrl', 'photoDataUrl'));
    }
}
