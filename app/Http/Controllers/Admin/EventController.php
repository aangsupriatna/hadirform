<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Event;
use Illuminate\Http\Request;

class EventController extends Controller
{
    public function index(Request $request)
    {
        $orgId = $request->user()->organization_id;

        $events = Event::where('organization_id', $orgId)
            ->withCount(['forms', 'responses'])
            ->latest()
            ->paginate(15);

        return view('admin.events.index', compact('events'));
    }

    public function create()
    {
        return view('admin.events.create');
    }

    public function store(Request $request)
    {
        $user = $request->user();
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'venue' => 'nullable|string|max:255',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date|after_or_equal:starts_at',
            'timezone' => 'required|string|max:50',
            'status' => 'required|in:draft,published,closed,archived',
        ]);

        $validated['organization_id'] = $user->organization_id;
        $validated['created_by'] = $user->id;

        $event = Event::create($validated);

        AuditLog::log('event.create', $event, null, $validated, $user->organization_id);

        return redirect()->route('admin.events.show', $event)->with('success', 'Kegiatan berhasil dibuat.');
    }

    public function show(Request $request, Event $event)
    {
        $this->authorize('view', $event);

        $event->load(['forms' => function ($q) {
            $q->withCount('responses');
        }]);

        return view('admin.events.show', compact('event'));
    }

    public function edit(Request $request, Event $event)
    {
        $this->authorize('update', $event);
        return view('admin.events.edit', compact('event'));
    }

    public function update(Request $request, Event $event)
    {
        $this->authorize('update', $event);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'venue' => 'nullable|string|max:255',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date|after_or_equal:starts_at',
            'timezone' => 'required|string|max:50',
            'status' => 'required|in:draft,published,closed,archived',
        ]);

        $before = $event->only(array_keys($validated));
        $event->update($validated);

        AuditLog::log('event.update', $event, $before, $validated, $event->organization_id);

        return redirect()->route('admin.events.show', $event)->with('success', 'Kegiatan berhasil diperbarui.');
    }

    public function destroy(Request $request, Event $event)
    {
        $this->authorize('delete', $event);

        $before = $event->toArray();
        $event->delete();

        AuditLog::log('event.delete', $event, $before, null, $event->organization_id);

        return redirect()->route('admin.events.index')->with('success', 'Kegiatan berhasil dihapus.');
    }
}
