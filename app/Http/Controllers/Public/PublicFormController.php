<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Form;
use App\Services\SubmissionService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PublicFormController extends Controller
{
    public function __construct(
        protected SubmissionService $submissionService
    ) {}

    public function show(string $slug)
    {
        $form = Form::where('public_slug', $slug)
            ->with(['event', 'organization', 'currentVersion'])
            ->firstOrFail();

        if (!$form->isOpen()) {
            return view('public.closed', compact('form'));
        }

        $version = $form->currentVersion;
        if (!$version) {
            return view('public.closed', [
                'form' => $form,
                'customMessage' => 'Formulir ini belum memiliki versi yang siap dipublikasikan.',
            ]);
        }

        $schema = $version->schema_json;
        $idempotencyToken = Str::random(40);

        return view('public.form', compact('form', 'version', 'schema', 'idempotencyToken'));
    }

    public function submit(Request $request, string $slug)
    {
        $form = Form::where('public_slug', $slug)
            ->with(['currentVersion', 'event'])
            ->firstOrFail();

        try {
            $response = $this->submissionService->submit(
                $form,
                $request->all(),
                $request->input('_idempotency_token'),
                'public'
            );

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'receipt_code' => $response->receipt_code,
                    'redirect' => route('receipt.show', $response->receipt_code),
                ]);
            }

            return redirect()->route('receipt.show', $response->receipt_code)->with('success', 'Kehadiran berhasil dikirim!');
        } catch (Exception $e) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 422);
            }

            return back()->withInput()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function lookup(Request $request, string $slug, \App\Services\AttendeeLookupService $lookupService)
    {
        $form = Form::where('public_slug', $slug)->firstOrFail();

        if (!$form->isOpen()) {
            return response()->json([], 403);
        }

        $q = (string)$request->query('q', '');
        $results = $lookupService->search($form, $q, 10);

        return response()->json($results);
    }
}
