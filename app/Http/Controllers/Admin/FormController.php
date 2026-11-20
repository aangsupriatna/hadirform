<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Event;
use App\Models\Form;
use App\Services\FormSchemaService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class FormController extends Controller
{
    public function __construct(
        protected FormSchemaService $schemaService
    ) {}

    public function index(Request $request)
    {
        $orgId = $request->user()->organization_id;

        $forms = Form::where('organization_id', $orgId)
            ->with(['event', 'currentVersion'])
            ->withCount(['responses', 'attendeeLookups'])
            ->latest()
            ->paginate(15);

        return view('admin.forms.index', compact('forms'));
    }

    public function create(Request $request)
    {
        $orgId = $request->user()->organization_id;
        $events = Event::where('organization_id', $orgId)->where('status', '!=', 'archived')->get();

        $selectedEventId = $request->query('event_id');
        $themes = \App\Services\FormThemeService::all();
        $headerPresets = \App\Services\FormHeaderPresetService::all();

        return view('admin.forms.create', compact('events', 'selectedEventId', 'themes', 'headerPresets'));
    }

    public function uploadHeaderImage(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,webp,svg|max:3072',
        ]);

        $path = $request->file('image')->store('form_headers', 'public');

        return response()->json([
            'success' => true,
            'path' => '/storage/' . $path,
            'url' => asset('storage/' . $path),
        ]);
    }

    public function store(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'event_id' => 'required|exists:events,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'theme_color' => 'nullable|string|max:30',
            'header_image' => 'nullable|string|max:500',
            'public_slug' => 'nullable|string|max:100|alpha_dash|unique:forms,public_slug',
            'opens_at' => 'nullable|date',
            'closes_at' => 'nullable|date|after_or_equal:opens_at',
        ]);

        $event = Event::findOrFail($validated['event_id']);
        if ($event->organization_id !== $user->organization_id) {
            abort(403);
        }

        $slug = $validated['public_slug'] ?? Str::slug($validated['title']) . '-' . Str::random(5);
        // Ensure unique slug
        while (Form::where('public_slug', $slug)->exists()) {
            $slug = Str::slug($validated['title']) . '-' . Str::random(5);
        }

        // Default initial schema
        $defaultSchema = [
            'title' => $validated['title'],
            'description' => $validated['description'] ?? '',
            'settings' => [
                'theme_color' => $validated['theme_color'] ?? 'indigo',
                'header_image' => $validated['header_image'] ?? null,
                'require_signature' => true,
                'allow_anonymous_access' => true,
                'duplicate_policy' => 'none',
                'show_receipt' => true,
                'consent_text' => 'Saya menyatakan bahwa data yang diisi adalah benar.',
            ],
            'fields' => [
                [
                    'key' => 'full_name',
                    'type' => 'text',
                    'label' => 'Nama Lengkap',
                    'placeholder' => 'Nama Lengkap dan Gelar',
                    'required' => true,
                    'max_length' => 150,
                ],
                [
                    'key' => 'institution',
                    'type' => 'text',
                    'label' => 'Instansi / Perusahaan',
                    'placeholder' => 'Nama Kantor / Lembaga',
                    'required' => true,
                    'max_length' => 200,
                ],
                [
                    'key' => 'position',
                    'type' => 'text',
                    'label' => 'Jabatan',
                    'required' => false,
                    'max_length' => 150,
                ],
                [
                    'key' => 'participant_signature',
                    'type' => 'signature',
                    'label' => 'Tanda Tangan Peserta',
                    'help_text' => 'Tanda tangani pada kanvas berikut.',
                    'required' => true,
                ],
                [
                    'key' => 'privacy_consent',
                    'type' => 'consent',
                    'label' => 'Saya menyetujui data kehadiran ini digunakan untuk administrasi kegiatan.',
                    'required' => true,
                ],
            ],
        ];

        $form = Form::create([
            'organization_id' => $user->organization_id,
            'event_id' => $event->id,
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'public_slug' => $slug,
            'status' => 'draft',
            'opens_at' => $validated['opens_at'] ?? null,
            'closes_at' => $validated['closes_at'] ?? null,
            'settings_json' => $defaultSchema['settings'],
        ]);

        // Publish version 1 automatically or keep as draft version
        $this->schemaService->publish($form, $defaultSchema, $user);

        return redirect()->route('admin.forms.builder', $form)->with('success', 'Formulir berhasil dibuat.');
    }

    public function show(Request $request, Form $form)
    {
        $this->authorize('view', $form);

        $form->load(['event', 'currentVersion', 'versions']);
        $form->loadCount('attendeeLookups');
        $publicUrl = route('public.form.show', $form->public_slug);

        $qrCodeSvg = QrCode::size(200)->generate($publicUrl);

        $responsesStats = [
            'total' => $form->responses()->count(),
            'verified' => $form->responses()->where('status', 'verified')->count(),
            'rejected' => $form->responses()->where('status', 'rejected')->count(),
            'submitted' => $form->responses()->where('status', 'submitted')->count(),
        ];

        return view('admin.forms.show', compact('form', 'publicUrl', 'qrCodeSvg', 'responsesStats'));
    }

    public function builder(Request $request, Form $form)
    {
        $this->authorize('update', $form);

        $form->load(['event', 'currentVersion', 'versions']);
        $form->loadCount('attendeeLookups');
        $currentSchema = $form->currentVersion?->schema_json ?? [
            'title' => $form->title,
            'description' => $form->description ?? '',
            'settings' => [
                'theme_color' => $form->theme_color ?? 'indigo',
                'require_signature' => true,
                'allow_anonymous_access' => true,
                'duplicate_policy' => 'none',
                'show_receipt' => true,
                'consent_text' => 'Saya menyetujui pemrosesan data kehadiran ini.',
            ],
            'fields' => [],
        ];

        if (!isset($currentSchema['settings']['theme_color'])) {
            $currentSchema['settings']['theme_color'] = $form->theme_color ?? 'indigo';
        }

        $themes = \App\Services\FormThemeService::all();
        $headerPresets = \App\Services\FormHeaderPresetService::all();

        if (!isset($currentSchema['settings']['header_image'])) {
            $currentSchema['settings']['header_image'] = $form->header_image ?? null;
        }

        return view('admin.forms.builder', compact('form', 'currentSchema', 'themes', 'headerPresets'));
    }

    public function publish(Request $request, Form $form)
    {
        $this->authorize('publish', $form);

        $schema = $request->input('schema');
        if (is_string($schema)) {
            $schema = json_decode($schema, true);
        }

        if (!is_array($schema)) {
            return back()->withErrors(['schema' => 'Format data skema tidak valid.']);
        }

        try {
            $version = $this->schemaService->publish($form, $schema, $request->user());
            return redirect()->route('admin.forms.show', $form)->with('success', "Versi {$version->version_number} berhasil diterbitkan!");
        } catch (Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function close(Request $request, Form $form)
    {
        $this->authorize('update', $form);

        $before = ['status' => $form->status];
        $form->update(['status' => 'closed']);

        AuditLog::log('form.close', $form, $before, ['status' => 'closed'], $form->organization_id);

        return back()->with('success', 'Formulir berhasil ditutup.');
    }

    public function reopen(Request $request, Form $form)
    {
        $this->authorize('update', $form);

        $before = ['status' => $form->status];
        $form->update(['status' => 'published']);

        AuditLog::log('form.reopen', $form, $before, ['status' => 'published'], $form->organization_id);

        return back()->with('success', 'Formulir dibuka kembali.');
    }

    public function destroy(Request $request, Form $form)
    {
        $this->authorize('delete', $form);

        $before = $form->toArray();
        $form->delete();

        AuditLog::log('form.delete', $form, $before, null, $form->organization_id);

        return redirect()->route('admin.forms.index')->with('success', 'Formulir berhasil dihapus.');
    }
}
