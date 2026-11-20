<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Form;
use App\Models\Response;
use App\Models\ResponseVerification;
use App\Services\SignatureService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ResponseController extends Controller
{
    public function __construct(
        protected SignatureService $signatureService
    ) {}

    public function index(Request $request, ?Form $form = null)
    {
        $user = $request->user();
        $orgId = $user->organization_id;

        $query = Response::where('organization_id', $orgId)
            ->with(['form', 'event', 'participantSignature', 'verifier']);

        if ($form) {
            $this->authorize('view', $form);
            $query->where('form_id', $form->id);
        }

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        if ($search = $request->query('q')) {
            $query->where(function ($q) use ($search) {
                $q->where('receipt_code', 'like', "%{$search}%")
                  ->orWhereHas('answers', function ($aq) use ($search) {
                      $aq->where('value_json', 'like', "%{$search}%");
                  });
            });
        }

        $responses = $query->latest('submitted_at')->paginate(20)->withQueryString();

        return view('admin.responses.index', compact('responses', 'form'));
    }

    public function show(Request $request, Response $response)
    {
        $this->authorize('view', $response);

        $response->load([
            'form.currentVersion',
            'event',
            'answers',
            'signatures.user',
            'participantSignature',
            'operatorSignature',
            'verifier',
            'verifications.operator',
            'verifications.signature',
        ]);

        return view('admin.responses.show', compact('response'));
    }

    public function verify(Request $request, Response $response)
    {
        $this->authorize('verify', $response);

        $validated = $request->validate([
            'decision' => 'required|in:verified,rejected,voided',
            'reason' => 'nullable|string|max:500',
            'operator_signature' => 'nullable|string',
        ]);

        if (in_array($validated['decision'], ['rejected', 'voided'], true) && empty($validated['reason'])) {
            return back()->withErrors(['reason' => 'Alasan wajib diisi jika menolak atau membatalkan kehadiran.']);
        }

        DB::transaction(function () use ($response, $validated, $request) {
            $operator = $request->user();
            $sigId = null;

            // Process operator signature if provided
            if (!empty($validated['operator_signature'])) {
                try {
                    $sig = $this->signatureService->processAndStore(
                        $validated['operator_signature'],
                        $response,
                        'operator',
                        $operator,
                        'v1.0'
                    );
                    $sigId = $sig->id;
                } catch (Exception $e) {
                    // Log error or rethrow
                    throw $e;
                }
            }

            // Create verification record (never overwrite history!)
            ResponseVerification::create([
                'response_id' => $response->id,
                'operator_id' => $operator->id,
                'decision' => $validated['decision'],
                'reason' => $validated['reason'] ?? null,
                'signature_id' => $sigId,
                'created_at' => now(),
            ]);

            $before = [
                'status' => $response->status,
                'verified_at' => $response->verified_at,
                'verified_by' => $response->verified_by,
                'rejection_reason' => $response->rejection_reason,
            ];

            // Update response status
            $response->update([
                'status' => $validated['decision'],
                'verified_at' => now(),
                'verified_by' => $operator->id,
                'rejection_reason' => $validated['reason'] ?? null,
            ]);

            AuditLog::log('response.verify', $response, $before, [
                'status' => $validated['decision'],
                'reason' => $validated['reason'] ?? null,
                'has_operator_signature' => $sigId !== null,
            ], $response->organization_id);
        });

        return back()->with('success', 'Status verifikasi kehadiran berhasil disimpan.');
    }

    public function photo(Request $request, Response $response, string $fieldKey)
    {
        $this->authorize('view', $response);

        $answer = $response->answers()->where('field_key', $fieldKey)->firstOrFail();
        $val = $answer->value_json;

        if (!is_array($val) || empty($val['file_path']) || !Storage::disk('local')->exists($val['file_path'])) {
            abort(404, 'File foto selfie tidak ditemukan.');
        }

        $file = Storage::disk('local')->get($val['file_path']);
        $mime = $val['mime_type'] ?? 'image/jpeg';

        return response($file, 200, [
            'Content-Type' => $mime,
            'Content-Disposition' => 'inline; filename="selfie-' . $response->receipt_code . '.jpg"',
            'Cache-Control' => 'private, max-age=86400',
        ]);
    }
}
