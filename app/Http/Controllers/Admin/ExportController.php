<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Export;
use App\Models\Form;
use App\Services\ReportService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ExportController extends Controller
{
    public function __construct(
        protected ReportService $reportService
    ) {}

    public function export(Request $request, Form $form)
    {
        $this->authorize('export', $form);

        $validated = $request->validate([
            'format' => 'required|in:xlsx,pdf',
            'status' => 'nullable|string',
        ]);

        $user = $request->user();
        $format = $validated['format'];
        $filters = ['status' => $validated['status'] ?? null];

        $exportRecord = Export::create([
            'organization_id' => $user->organization_id,
            'requested_by' => $user->id,
            'form_id' => $form->id,
            'format' => $format,
            'filters_json' => $filters,
            'status' => 'completed',
            'created_at' => now(),
            'completed_at' => now(),
        ]);

        AuditLog::log('form.export', $form, null, [
            'format' => $format,
            'filters' => $filters,
        ], $form->organization_id);

        $safeTitle = Str::slug($form->title, '_');
        $dateStr = now()->format('Ymd_His');

        if ($format === 'xlsx') {
            $binary = $this->reportService->generateXlsx($form, $filters);
            $filename = "rekap_{$safeTitle}_{$dateStr}.xlsx";
            return response($binary)
                ->header('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
                ->header('Content-Disposition', "attachment; filename=\"{$filename}\"");
        } else {
            $binary = $this->reportService->generatePdf($form, $filters);
            $filename = "daftar_hadir_{$safeTitle}_{$dateStr}.pdf";
            return response($binary)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', "attachment; filename=\"{$filename}\"");
        }
    }
}
