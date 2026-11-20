<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Form;
use App\Models\Response;
use App\Models\ResponseAnswer;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SubmissionService
{
    public function __construct(
        protected SignatureService $signatureService,
        protected AttendeeLookupService $lookupService
    ) {}

    /**
     * Submit an attendance form with validation, atomic transaction, and idempotency protection.
     *
     * @param Form $form
     * @param array $inputData
     * @param string|null $idempotencyToken
     * @param string $source 'public' | 'operator'
     * @return Response
     * @throws Exception
     */
    public function submit(
        Form $form,
        array $inputData,
        ?string $idempotencyToken = null,
        string $source = 'public'
    ): Response {
        if (!$form->isOpen()) {
            throw new Exception('Maaf, formulir ini saat ini sedang tidak menerima respons atau sudah ditutup.');
        }

        $version = $form->currentVersion;
        if (!$version) {
            throw new Exception('Formulir belum memiliki versi yang diterbitkan.');
        }

        // Idempotency check: prevent duplicate submits if network retry occurs
        if (!empty($idempotencyToken)) {
            $existing = Response::where('form_id', $form->id)
                ->where('idempotency_token', $idempotencyToken)
                ->with(['answers', 'signatures'])
                ->first();

            if ($existing) {
                return $existing;
            }
        }

        $schema = $version->schema_json;
        $fields = $schema['fields'] ?? [];
        $settings = $schema['settings'] ?? [];

        $answersToSave = [];
        $signatureDataUrls = [];
        $cameraPhotos = [];

        // Validate each field in schema
        foreach ($fields as $field) {
            $key = $field['key'];
            $type = $field['type'];
            $label = $field['label'] ?? $key;
            $required = (bool)($field['required'] ?? false);
            $val = $inputData[$key] ?? null;

            // Skip display-only types
            if (in_array($type, ['heading', 'static_info'], true)) {
                continue;
            }

            if ($type === 'signature') {
                if ($required && (empty($val) || !is_string($val))) {
                    throw new Exception("Tanda tangan pada '{$label}' wajib dibubuhkan.");
                }
                if (!empty($val)) {
                    $signatureDataUrls[$key] = $val;
                }
                continue;
            }

            if (in_array($type, ['camera', 'selfie'], true)) {
                if ($required && (empty($val) || (is_string($val) && trim($val) === ''))) {
                    throw new Exception("Foto selfie pada '{$label}' wajib diambil.");
                }
                if (!empty($val)) {
                    $cameraPhotos[$key] = $val;
                }
                continue;
            }

            if ($type === 'consent') {
                if ($required && empty($val)) {
                    throw new Exception("Persetujuan pada '{$label}' wajib dicentang.");
                }
                $answersToSave[$key] = (bool)$val;
                continue;
            }

            // Normal values
            if ($required && ($val === null || $val === '' || (is_array($val) && empty($val)))) {
                throw new Exception("Kolom '{$label}' wajib diisi.");
            }

            if ($val !== null && $val !== '') {
                if ($type === 'email') {
                    if (!filter_var($val, FILTER_VALIDATE_EMAIL)) {
                        throw new Exception("Format email pada '{$label}' tidak valid.");
                    }
                } elseif ($type === 'number') {
                    if (!is_numeric($val)) {
                        throw new Exception("Kolom '{$label}' harus berupa angka.");
                    }
                } elseif (in_array($type, ['dropdown', 'radio'], true)) {
                    $options = $field['options'] ?? [];
                    if (!empty($options) && !in_array($val, $options, true)) {
                        throw new Exception("Pilihan pada '{$label}' tidak valid.");
                    }
                } elseif ($type === 'checkbox' && is_array($val)) {
                    $options = $field['options'] ?? [];
                    foreach ($val as $item) {
                        if (!empty($options) && !in_array($item, $options, true)) {
                            throw new Exception("Pilihan pada '{$label}' tidak valid.");
                        }
                    }
                }

                if (isset($field['max_length']) && is_string($val) && mb_strlen($val) > $field['max_length']) {
                    throw new Exception("Panjang karakter pada '{$label}' melebihi batas maksimal ({$field['max_length']} karakter).");
                }

                $answersToSave[$key] = $val;
            } else {
                $answersToSave[$key] = null;
            }
        }

        // Deduplication policy check
        $dedupePolicy = $settings['duplicate_policy'] ?? 'none';
        $dedupeKey = null;

        if ($dedupePolicy === 'email' && !empty($answersToSave['email'])) {
            $dedupeKey = 'email:' . strtolower(trim($answersToSave['email']));
        } elseif ($dedupePolicy === 'phone' && !empty($answersToSave['phone'])) {
            $cleanedPhone = preg_replace('/[^0-9]/', '', $answersToSave['phone']);
            $dedupeKey = 'phone:' . $cleanedPhone;
        }

        if ($dedupeKey) {
            $duplicateExists = Response::where('form_id', $form->id)
                ->where('dedupe_key', $dedupeKey)
                ->exists();

            if ($duplicateExists) {
                throw new Exception('Data Anda sudah tercatat sebelumnya pada formulir ini.');
            }
        }

        // Atomic DB transaction
        return DB::transaction(function () use (
            $form,
            $version,
            $answersToSave,
            $signatureDataUrls,
            $cameraPhotos,
            $dedupeKey,
            $idempotencyToken,
            $source
        ) {
            $receiptCode = 'HF-' . strtoupper(Str::random(10));

            $response = Response::create([
                'organization_id' => $form->organization_id,
                'event_id' => $form->event_id,
                'form_id' => $form->id,
                'form_version_id' => $version->id,
                'receipt_code' => $receiptCode,
                'status' => 'submitted',
                'source' => $source,
                'submitted_at' => now(),
                'dedupe_key' => $dedupeKey,
                'consent_version' => 'v1.0',
                'consented_at' => now(),
                'idempotency_token' => $idempotencyToken,
            ]);

            // Save Answers
            foreach ($answersToSave as $fieldKey => $value) {
                ResponseAnswer::create([
                    'response_id' => $response->id,
                    'field_key' => $fieldKey,
                    'value_json' => $value,
                ]);
            }

            // Save participant signatures
            foreach ($signatureDataUrls as $fieldKey => $dataUrl) {
                $sig = $this->signatureService->processAndStore(
                    $dataUrl,
                    $response,
                    'participant',
                    null,
                    'v1.0'
                );

                ResponseAnswer::create([
                    'response_id' => $response->id,
                    'field_key' => $fieldKey,
                    'value_json' => [
                        'signature_id' => $sig->id,
                        'sha256' => $sig->sha256_hash,
                    ],
                ]);
            }

            // Save camera selfie photos
            foreach ($cameraPhotos as $fieldKey => $photoInput) {
                $photoMeta = $this->processAndStorePhoto($photoInput, $response, $fieldKey);

                ResponseAnswer::create([
                    'response_id' => $response->id,
                    'field_key' => $fieldKey,
                    'value_json' => $photoMeta,
                ]);
            }

            AuditLog::log('response.submit', $response, null, [
                'receipt_code' => $receiptCode,
                'form_id' => $form->id,
                'source' => $source,
            ], $form->organization_id);

            // Mark pre-registered attendee as attended if matched
            $this->lookupService->markAttended($response, $answersToSave);

            return $response->load(['answers', 'signatures']);
        });
    }

    /**
     * Process, validate, sanitize, and store camera / selfie photo.
     */
    protected function processAndStorePhoto(mixed $val, Response $response, string $fieldKey): array
    {
        $binary = null;

        if ($val instanceof \Illuminate\Http\UploadedFile) {
            $binary = file_get_contents($val->getRealPath());
        } elseif (is_string($val) && preg_match('/^data:image\/(png|jpeg|jpg|webp);base64,(.+)$/s', $val, $matches)) {
            $binary = base64_decode($matches[2], true);
        }

        if (!$binary || strlen($binary) < 200) {
            throw new Exception("Data foto selfie tidak valid atau rusak.");
        }

        if (strlen($binary) > 5 * 1024 * 1024) {
            throw new Exception("Ukuran foto selfie melebihi batas maksimal 5MB.");
        }

        $img = @imagecreatefromstring($binary);
        if (!$img) {
            throw new Exception("Format gambar foto selfie tidak dapat diproses.");
        }

        $origW = imagesx($img);
        $origH = imagesy($img);

        // Resize if too large, max dimension 1280px
        $maxDim = 1280;
        if ($origW > $maxDim || $origH > $maxDim) {
            $ratio = min($maxDim / $origW, $maxDim / $origH);
            $newW = (int)($origW * $ratio);
            $newH = (int)($origH * $ratio);

            $resized = imagecreatetruecolor($newW, $newH);
            imagecopyresampled($resized, $img, 0, 0, 0, 0, $newW, $newH, $origW, $origH);
            imagedestroy($img);
            $img = $resized;
        }

        ob_start();
        imagejpeg($img, null, 85);
        $jpegBinary = ob_get_clean();
        imagedestroy($img);

        $year = now()->format('Y');
        $month = now()->format('m');
        $filename = Str::uuid()->toString() . '.jpg';
        $relativePath = "photos/{$year}/{$month}/{$filename}";

        Storage::disk('local')->put($relativePath, $jpegBinary);

        return [
            'type' => 'photo',
            'file_path' => $relativePath,
            'mime_type' => 'image/jpeg',
            'file_size' => strlen($jpegBinary),
            'sha256' => hash('sha256', $jpegBinary),
            'captured_at' => now()->toIso8601String(),
        ];
    }
}
