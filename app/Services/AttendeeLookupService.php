<?php

namespace App\Services;

use App\Models\Form;
use App\Models\FormAttendeeLookup;
use App\Models\Response;
use Exception;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class AttendeeLookupService
{
    /**
     * Import attendees from uploaded CSV or Excel file.
     *
     * @param Form $form
     * @param UploadedFile $file
     * @param bool $replaceExisting
     * @return int Number of imported rows
     * @throws Exception
     */
    public function import(Form $form, UploadedFile $file, bool $replaceExisting = false): int
    {
        $spreadsheet = IOFactory::load($file->getRealPath());
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray(null, true, true, true);

        if (count($rows) < 2) {
            throw new Exception('File kosong atau tidak memiliki baris data.');
        }

        // Header mapping (row 1)
        $headerRow = array_shift($rows);
        $colMap = [];

        foreach ($headerRow as $colLetter => $rawHeader) {
            $header = strtolower(trim((string)$rawHeader));
            $header = str_replace([' ', '-', '.'], '_', $header);

            if (in_array($header, ['nama', 'name', 'nama_lengkap', 'full_name'], true)) {
                $colMap['name'] = $colLetter;
            } elseif (in_array($header, ['instansi', 'perusahaan', 'lembaga', 'institution', 'company', 'kantor'], true)) {
                $colMap['institution'] = $colLetter;
            } elseif (in_array($header, ['jabatan', 'posisi', 'position', 'role'], true)) {
                $colMap['position'] = $colLetter;
            } elseif (in_array($header, ['email', 'surel', 'e_mail'], true)) {
                $colMap['email'] = $colLetter;
            } elseif (in_array($header, ['no_hp', 'hp', 'telepon', 'whatsapp', 'phone', 'telp', 'nohp'], true)) {
                $colMap['phone'] = $colLetter;
            } elseif (in_array($header, ['nip', 'nik', 'no_peserta', 'tiket', 'nomor_tiket', 'identifier', 'id_peserta'], true)) {
                $colMap['identifier'] = $colLetter;
            }
        }

        if (empty($colMap['name'])) {
            throw new Exception("Kolom 'Nama' atau 'Nama Lengkap' wajib ada pada baris pertama (header).");
        }

        if ($replaceExisting) {
            FormAttendeeLookup::where('form_id', $form->id)->delete();
        }

        $imported = 0;
        $now = now();

        foreach ($rows as $row) {
            $name = trim((string)($row[$colMap['name']] ?? ''));
            if ($name === '') {
                continue;
            }

            $institution = isset($colMap['institution']) ? trim((string)($row[$colMap['institution']] ?? '')) : null;
            $position = isset($colMap['position']) ? trim((string)($row[$colMap['position']] ?? '')) : null;
            $email = isset($colMap['email']) ? trim((string)($row[$colMap['email']] ?? '')) : null;
            $phone = isset($colMap['phone']) ? trim((string)($row[$colMap['phone']] ?? '')) : null;
            $identifier = isset($colMap['identifier']) ? trim((string)($row[$colMap['identifier']] ?? '')) : null;

            FormAttendeeLookup::create([
                'form_id' => $form->id,
                'name' => $name,
                'institution' => $institution ?: null,
                'position' => $position ?: null,
                'email' => $email ?: null,
                'phone' => $phone ?: null,
                'identifier' => $identifier ?: null,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $imported++;
        }

        return $imported;
    }

    /**
     * Generate an Excel template for pre-registered attendees.
     */
    public function generateTemplate(): string
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Daftar Peserta');

        $headers = ['Nama Lengkap', 'Instansi / Perusahaan', 'Jabatan', 'Email', 'Nomor HP', 'NIP / No Peserta'];
        foreach ($headers as $colIdx => $header) {
            $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIdx + 1);
            $sheet->setCellValue("{$colLetter}1", $header);
            $sheet->getStyle("{$colLetter}1")->getFont()->setBold(true);
            $sheet->getColumnDimension($colLetter)->setAutoSize(true);
        }

        // Example rows
        $sampleData = [
            ['Dr. Budi Santoso, M.Kom', 'Kementerian Komunikasi dan Informatika', 'Kepala Seksi', 'budi.santoso@example.com', '081234567890', '198501012010011001'],
            ['Siti Rahmawati, S.T.', 'PT Sukses Digital Pratama', 'Project Manager', 'siti.rahma@example.com', '085678901234', 'EMP-0294'],
            ['Andi Pratama, M.Sc', 'Universitas Indonesia', 'Dosen / Peneliti', 'andi.p@example.com', '087812345678', 'UI-88392'],
        ];

        foreach ($sampleData as $rowIdx => $data) {
            $rowNum = $rowIdx + 2;
            foreach ($data as $colIdx => $val) {
                $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIdx + 1);
                $sheet->setCellValue("{$colLetter}{$rowNum}", $val);
            }
        }

        $writer = new Xlsx($spreadsheet);
        ob_start();
        $writer->save('php://output');
        return ob_get_clean();
    }

    /**
     * Search attendees in a form for autocomplete suggestions.
     *
     * @param Form $form
     * @param string $query
     * @param int $limit
     * @return array
     */
    public function search(Form $form, string $query, int $limit = 10): array
    {
        $q = trim($query);
        if (mb_strlen($q) < 2) {
            return [];
        }

        return FormAttendeeLookup::where('form_id', $form->id)
            ->where(function ($sub) use ($q) {
                $sub->where('name', 'like', "%{$q}%")
                    ->orWhere('identifier', 'like', "%{$q}%")
                    ->orWhere('phone', 'like', "%{$q}%")
                    ->orWhere('email', 'like', "%{$q}%")
                    ->orWhere('institution', 'like', "%{$q}%");
            })
            ->select(['id', 'name', 'identifier', 'institution', 'position', 'email', 'phone', 'is_attended'])
            ->take($limit)
            ->get()
            ->toArray();
    }

    /**
     * Mark lookup attendee as attended if matched by submission.
     */
    public function markAttended(Response $response, array $submittedAnswers): ?FormAttendeeLookup
    {
        $name = trim($submittedAnswers['full_name'] ?? ($submittedAnswers['name'] ?? ''));
        $email = trim($submittedAnswers['email'] ?? '');
        $phone = trim($submittedAnswers['phone'] ?? '');

        if (empty($name) && empty($email) && empty($phone)) {
            return null;
        }

        $lookup = FormAttendeeLookup::where('form_id', $response->form_id)
            ->where('is_attended', false)
            ->where(function ($q) use ($name, $email, $phone) {
                if (!empty($name)) {
                    $q->where('name', $name);
                }
                if (!empty($email)) {
                    $q->orWhere('email', $email);
                }
                if (!empty($phone)) {
                    $q->orWhere('phone', $phone);
                }
            })
            ->first();

        if ($lookup) {
            $lookup->update([
                'is_attended' => true,
                'attended_at' => now(),
                'response_id' => $response->id,
            ]);
        }

        return $lookup;
    }
}
