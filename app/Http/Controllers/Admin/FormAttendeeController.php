<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Form;
use App\Models\FormAttendeeLookup;
use App\Services\AttendeeLookupService;
use Exception;
use Illuminate\Http\Request;

class FormAttendeeController extends Controller
{
    public function __construct(
        protected AttendeeLookupService $lookupService
    ) {}

    public function index(Request $request, Form $form)
    {
        $this->authorize('view', $form);

        $query = $form->attendeeLookups()->latest();

        if ($search = $request->query('q')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('identifier', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('institution', 'like', "%{$search}%");
            });
        }

        if ($status = $request->query('status')) {
            if ($status === 'attended') {
                $query->where('is_attended', true);
            } elseif ($status === 'pending') {
                $query->where('is_attended', false);
            }
        }

        $attendees = $query->paginate(20)->withQueryString();
        $totalCount = $form->attendeeLookups()->count();
        $attendedCount = $form->attendeeLookups()->where('is_attended', true)->count();

        return view('admin.forms.attendees', compact('form', 'attendees', 'totalCount', 'attendedCount'));
    }

    public function import(Request $request, Form $form)
    {
        $this->authorize('update', $form);

        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv,txt|max:5120', // max 5MB
            'replace' => 'nullable|boolean',
        ]);

        try {
            $count = $this->lookupService->import(
                $form,
                $request->file('file'),
                $request->boolean('replace')
            );

            AuditLog::log('form.attendees_import', $form, null, [
                'imported_count' => $count,
                'replace' => $request->boolean('replace'),
            ], $form->organization_id);

            return back()->with('success', "Berhasil mengimpor {$count} data peserta undangan.");
        } catch (Exception $e) {
            return back()->withErrors(['file' => 'Gagal mengimpor file: ' . $e->getMessage()]);
        }
    }

    public function template(Request $request, Form $form)
    {
        $this->authorize('view', $form);

        $binary = $this->lookupService->generateTemplate();
        $filename = "template_peserta_" . \Illuminate\Support\Str::slug($form->title) . ".xlsx";

        return response($binary)
            ->header('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
            ->header('Content-Disposition', "attachment; filename=\"{$filename}\"");
    }

    public function destroy(Request $request, Form $form)
    {
        $this->authorize('update', $form);

        $count = $form->attendeeLookups()->count();
        $form->attendeeLookups()->delete();

        AuditLog::log('form.attendees_clear', $form, ['count' => $count], null, $form->organization_id);

        return back()->with('success', 'Semua data peserta terdaftar berhasil dibersihkan.');
    }

    public function store(Request $request, Form $form)
    {
        $this->authorize('update', $form);

        $validated = $request->validate([
            'name' => 'required|string|max:200',
            'institution' => 'nullable|string|max:200',
            'position' => 'nullable|string|max:150',
            'email' => 'nullable|email|max:150',
            'phone' => 'nullable|string|max:50',
            'identifier' => 'nullable|string|max:100',
        ]);

        $attendee = $form->attendeeLookups()->create($validated);

        AuditLog::log('form.attendee_create', $form, null, $attendee->toArray(), $form->organization_id);

        return back()->with('success', "Peserta '{$attendee->name}' berhasil ditambahkan.");
    }

    public function update(Request $request, Form $form, FormAttendeeLookup $attendee)
    {
        $this->authorize('update', $form);
        if ($attendee->form_id !== $form->id) {
            abort(404);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:200',
            'institution' => 'nullable|string|max:200',
            'position' => 'nullable|string|max:150',
            'email' => 'nullable|email|max:150',
            'phone' => 'nullable|string|max:50',
            'identifier' => 'nullable|string|max:100',
        ]);

        $before = $attendee->toArray();
        $attendee->update($validated);

        AuditLog::log('form.attendee_update', $form, $before, $attendee->toArray(), $form->organization_id);

        return back()->with('success', "Data peserta '{$attendee->name}' berhasil diperbarui.");
    }

    public function destroySingle(Request $request, Form $form, FormAttendeeLookup $attendee)
    {
        $this->authorize('update', $form);
        if ($attendee->form_id !== $form->id) {
            abort(404);
        }

        $name = $attendee->name;
        $attendee->delete();

        AuditLog::log('form.attendee_delete', $form, ['name' => $name], null, $form->organization_id);

        return back()->with('success', "Peserta '{$name}' berhasil dihapus.");
    }
}
