<?php

namespace App\Services;

use App\Models\Form;
use App\Models\Response;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class ReportService
{
    /**
     * Sanitize cell value to prevent CSV/Excel Formula Injection.
     */
    protected function sanitizeCellValue(mixed $val): string
    {
        if (is_array($val)) {
            $val = implode(', ', $val);
        }
        $str = (string)($val ?? '');
        if ($str !== '' && in_array($str[0], ['=', '+', '-', '@'], true)) {
            return "'" . $str;
        }
        return $str;
    }

    /**
     * Create base64 thumbnail for PDF rendering.
     */
    protected function getPhotoThumbnailBase64(?string $filePath, int $maxDim = 120): ?string
    {
        if (!$filePath || !Storage::disk('local')->exists($filePath)) {
            return null;
        }

        $raw = Storage::disk('local')->get($filePath);
        if (!$raw) {
            return null;
        }

        $img = @imagecreatefromstring($raw);
        if (!$img) {
            return null;
        }

        $w = imagesx($img);
        $h = imagesy($img);

        if ($w > $maxDim || $h > $maxDim) {
            $ratio = min($maxDim / $w, $maxDim / $h);
            $newW = max(1, (int)($w * $ratio));
            $newH = max(1, (int)($h * $ratio));

            $resized = imagecreatetruecolor($newW, $newH);
            imagecopyresampled($resized, $img, 0, 0, 0, 0, $newW, $newH, $w, $h);
            imagedestroy($img);
            $img = $resized;
        }

        ob_start();
        imagejpeg($img, null, 75);
        $jpegData = ob_get_clean();
        imagedestroy($img);

        return 'data:image/jpeg;base64,' . base64_encode($jpegData);
    }

    /**
     * Generate XLSX file binary using PhpSpreadsheet.
     */
    public function generateXlsx(Form $form, ?array $filters = null): string
    {
        $query = $form->responses()->with(['answers', 'participantSignature', 'verifier'])->latest('submitted_at');
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        $responses = $query->get();

        $version = $form->currentVersion;
        $fields = $version?->schema_json['fields'] ?? [];

        // Dynamic fields split
        $textFields = array_values(array_filter($fields, fn($f) => !in_array($f['type'], ['camera', 'selfie', 'signature', 'consent', 'heading', 'static_info'], true)));
        $photoFields = array_values(array_filter($fields, fn($f) => in_array($f['type'], ['camera', 'selfie'], true)));

        $hasSignatureField = !empty(array_filter($fields, fn($f) => $f['type'] === 'signature'));
        $hasAnySignature = $hasSignatureField || $responses->contains(fn($r) => $r->participantSignature !== null);

        $spreadsheet = new Spreadsheet();

        // Sheet 1: Data Kehadiran
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Daftar Kehadiran');

        // Headers
        $headers = ['No', 'Kode Bukti', 'Waktu Hadir', 'Status'];
        foreach ($textFields as $tf) {
            $headers[] = $tf['label'];
        }
        foreach ($photoFields as $pf) {
            $headers[] = $pf['label'] . ' (Foto)';
        }
        if ($hasAnySignature) {
            $headers[] = 'Tanda Tangan';
        }
        $headers[] = 'Diverifikasi Oleh';

        $col = 1;
        foreach ($headers as $header) {
            $cell = $sheet->getCell([$col, 1]);
            $cell->setValue($header);
            $sheet->getStyle([$col, 1])->getFont()->setBold(true);
            $sheet->getStyle([$col, 1])->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFE2E8F0');
            $sheet->getColumnDimensionByColumn($col)->setAutoSize(true);
            $col++;
        }

        $rowNum = 2;
        foreach ($responses as $idx => $resp) {
            $col = 1;
            $sheet->setCellValue([$col++, $rowNum], $idx + 1);
            $sheet->setCellValue([$col++, $rowNum], $this->sanitizeCellValue($resp->receipt_code));
            $sheet->setCellValue([$col++, $rowNum], $resp->submitted_at ? $resp->submitted_at->format('d/m/Y H:i') : '-');
            $sheet->setCellValue([$col++, $rowNum], $this->sanitizeCellValue(ucfirst($resp->status)));

            foreach ($textFields as $tf) {
                $val = $resp->getAnswerValue($tf['key']);
                if (is_array($val)) {
                    $val = implode(', ', $val);
                } elseif (is_bool($val)) {
                    $val = $val ? 'Ya' : 'Tidak';
                }
                $sheet->setCellValue([$col++, $rowNum], $this->sanitizeCellValue($val));
            }

            foreach ($photoFields as $pf) {
                $photoAns = $resp->answers->firstWhere('field_key', $pf['key']);
                $hasPhoto = is_array($photoAns?->value_json) && ($photoAns->value_json['type'] ?? '') === 'photo';
                $sheet->setCellValue([$col++, $rowNum], $hasPhoto ? 'Ada (Tersimpan)' : 'Tidak Ada');
            }

            if ($hasAnySignature) {
                $hasSig = $resp->participantSignature !== null;
                $sheet->setCellValue([$col++, $rowNum], $hasSig ? 'Ada (Tersimpan)' : 'Tidak Ada');
            }

            $sheet->setCellValue([$col++, $rowNum], $this->sanitizeCellValue($resp->verifier?->name ?? '-'));

            $rowNum++;
        }

        // Sheet 2: Metadata
        $metaSheet = $spreadsheet->createSheet();
        $metaSheet->setTitle('Informasi Kegiatan');
        $metaSheet->setCellValue('A1', 'Kegiatan:');
        $metaSheet->setCellValue('B1', $this->sanitizeCellValue($form->event->title));
        $metaSheet->setCellValue('A2', 'Formulir:');
        $metaSheet->setCellValue('B2', $this->sanitizeCellValue($form->title));
        $metaSheet->setCellValue('A3', 'Total Hadir:');
        $metaSheet->setCellValue('B3', $responses->count());
        $metaSheet->setCellValue('A4', 'Waktu Ekspor:');
        $metaSheet->setCellValue('B4', now()->format('d M Y H:i:s T'));

        $metaSheet->getColumnDimension('A')->setAutoSize(true);
        $metaSheet->getColumnDimension('B')->setAutoSize(true);

        $writer = new Xlsx($spreadsheet);
        ob_start();
        $writer->save('php://output');
        return ob_get_clean();
    }

    /**
     * Generate PDF binary using Dompdf.
     */
    public function generatePdf(Form $form, ?array $filters = null): string
    {
        $query = $form->responses()->with(['answers', 'participantSignature', 'operatorSignature', 'verifier'])->latest('submitted_at');
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        $responses = $query->get();

        $version = $form->currentVersion;
        $fields = $version?->schema_json['fields'] ?? [];

        // Dynamic fields split based on active form schema
        $textFields = array_values(array_filter($fields, fn($f) => !in_array($f['type'], ['camera', 'selfie', 'signature', 'consent', 'heading', 'static_info'], true)));
        $photoFields = array_values(array_filter($fields, fn($f) => in_array($f['type'], ['camera', 'selfie'], true)));

        $hasSignatureField = !empty(array_filter($fields, fn($f) => $f['type'] === 'signature'));
        $hasAnySignature = $hasSignatureField || $responses->contains(fn($r) => $r->participantSignature !== null);

        $rows = [];
        foreach ($responses as $idx => $resp) {
            $sigBase64 = null;
            if ($hasAnySignature && $resp->participantSignature && Storage::disk('local')->exists($resp->participantSignature->file_path)) {
                $raw = Storage::disk('local')->get($resp->participantSignature->file_path);
                $sigBase64 = 'data:image/png;base64,' . base64_encode($raw);
            }

            $answers = [];
            foreach ($textFields as $tf) {
                $val = $resp->getAnswerValue($tf['key']) ?? '-';
                if (is_array($val)) {
                    $val = implode(', ', $val);
                } elseif (is_bool($val)) {
                    $val = $val ? 'Ya' : 'Tidak';
                }
                $answers[$tf['key']] = $val;
            }

            $photos = [];
            foreach ($photoFields as $pf) {
                $photoAns = $resp->answers->firstWhere('field_key', $pf['key']);
                $photoPath = (is_array($photoAns?->value_json) && ($photoAns->value_json['type'] ?? '') === 'photo')
                    ? ($photoAns->value_json['file_path'] ?? null)
                    : null;
                $photos[$pf['key']] = $this->getPhotoThumbnailBase64($photoPath, 120);
            }

            $rows[] = [
                'no' => $idx + 1,
                'receipt_code' => $resp->receipt_code,
                'submitted_at' => $resp->submitted_at ? $resp->submitted_at->format('d/m/Y H:i') : '-',
                'status' => $resp->status,
                'answers' => $answers,
                'photos' => $photos,
                'signature_base64' => $sigBase64,
                'verifier' => $resp->verifier?->name,
            ];
        }

        $data = [
            'form' => $form,
            'event' => $form->event,
            'organization' => $form->organization,
            'rows' => $rows,
            'textFields' => $textFields,
            'photoFields' => $photoFields,
            'hasAnySignature' => $hasAnySignature,
            'generated_at' => now()->format('d F Y, H:i'),
        ];

        $pdf = Pdf::loadView('reports.attendance_pdf', $data)
            ->setPaper('a4', 'landscape')
            ->setOption('isRemoteEnabled', false);

        return $pdf->output();
    }
}
