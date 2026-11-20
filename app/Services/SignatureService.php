<?php

namespace App\Services;

use App\Models\Organization;
use App\Models\Response;
use App\Models\Signature;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SignatureService
{
    /**
     * Process, validate, sanitize, and store a signature.
     *
     * @param string $dataUrl Base64 data URL (e.g., data:image/png;base64,...)
     * @param Response $response
     * @param string $signerType 'participant' | 'operator' | 'admin'
     * @param User|null $user
     * @param string|null $consentVersion
     * @return Signature
     * @throws Exception
     */
    public function processAndStore(
        string $dataUrl,
        Response $response,
        string $signerType = 'participant',
        ?User $user = null,
        ?string $consentVersion = 'v1.0'
    ): Signature {
        if (!preg_match('/^data:image\/(png|jpeg|webp);base64,(.+)$/s', $dataUrl, $matches)) {
            throw new Exception('Format tanda tangan tidak valid.');
        }

        $base64Data = $matches[2];
        $binary = base64_decode($base64Data, true);

        if ($binary === false || strlen($binary) < 100) {
            throw new Exception('Data tanda tangan rusak atau kosong.');
        }

        if (strlen($binary) > 2 * 1024 * 1024) { // 2MB limit
            throw new Exception('Ukuran data tanda tangan melebihi batas maksimal 2MB.');
        }

        // Validate using GD
        $img = @imagecreatefromstring($binary);
        if (!$img) {
            throw new Exception('File gambar tanda tangan tidak dapat diproses.');
        }

        $width = imagesx($img);
        $height = imagesy($img);

        if ($width < 50 || $height < 20 || $width > 2500 || $height > 1500) {
            imagedestroy($img);
            throw new Exception('Dimensi kanvas tanda tangan tidak memenuhi ketentuan.');
        }

        // Check if canvas is essentially blank
        // Sample pixels to count drawn / non-transparent strokes
        $drawnPixels = 0;
        $totalChecked = 0;
        $stepX = max(1, (int)($width / 150));
        $stepY = max(1, (int)($height / 150));

        for ($x = 0; $x < $width; $x += $stepX) {
            for ($y = 0; $y < $height; $y += $stepY) {
                $totalChecked++;
                $rgba = imagecolorat($img, $x, $y);
                $colors = imagecolorsforindex($img, $rgba);
                
                // Alpha is 0 (fully opaque) to 127 (fully transparent)
                // If opacity is substantial and not pure white
                if ($colors['alpha'] < 100) {
                    $brightness = ($colors['red'] + $colors['green'] + $colors['blue']) / 3;
                    if ($brightness < 240) {
                        $drawnPixels++;
                    }
                }
            }
        }

        // Require at least a minimal stroke density
        if ($drawnPixels < 15) {
            imagedestroy($img);
            throw new Exception('Tanda tangan kosong atau goresan tidak terdeteksi.');
        }

        // Create clean sanitized transparent PNG
        $cleanImg = imagecreatetruecolor($width, $height);
        imagealphablending($cleanImg, false);
        imagesavealpha($cleanImg, true);
        $transparent = imagecolorallocatealpha($cleanImg, 0, 0, 0, 127);
        imagefill($cleanImg, 0, 0, $transparent);
        imagecopy($cleanImg, $img, 0, 0, 0, 0, $width, $height);
        imagedestroy($img);

        // Capture PNG output
        ob_start();
        imagepng($cleanImg, null, 9);
        $cleanPngBinary = ob_get_clean();
        imagedestroy($cleanImg);

        // Storage path in private disk: signatures/YYYY/MM/{random_uuid}.png
        $year = now()->format('Y');
        $month = now()->format('m');
        $filename = Str::uuid()->toString() . '.png';
        $relativePath = "signatures/{$year}/{$month}/{$filename}";

        Storage::disk('local')->put($relativePath, $cleanPngBinary);

        $sha256 = hash('sha256', $cleanPngBinary);

        return Signature::create([
            'organization_id' => $response->organization_id,
            'response_id' => $response->id,
            'signer_type' => $signerType,
            'signed_by_user_id' => $user?->id,
            'file_path' => $relativePath,
            'mime_type' => 'image/png',
            'file_size' => strlen($cleanPngBinary),
            'sha256_hash' => $sha256,
            'signed_at' => now(),
            'consent_version' => $consentVersion,
            'metadata_json' => [
                'width' => $width,
                'height' => $height,
                'strokes_sample' => $drawnPixels,
                'user_agent' => request()->userAgent(),
            ],
        ]);
    }
}
