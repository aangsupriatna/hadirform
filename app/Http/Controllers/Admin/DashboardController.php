<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Event;
use App\Models\Form;
use App\Models\Response;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $orgId = $user->organization_id;

        $eventsCount = Event::where('organization_id', $orgId)->count();
        $formsCount = Form::where('organization_id', $orgId)->count();
        $responsesCount = Response::where('organization_id', $orgId)->count();
        $verifiedCount = Response::where('organization_id', $orgId)->where('status', 'verified')->count();

        $recentResponses = Response::where('organization_id', $orgId)
            ->with(['form', 'event', 'participantSignature'])
            ->latest('submitted_at')
            ->take(8)
            ->get();

        $recentEvents = Event::where('organization_id', $orgId)
            ->withCount(['forms', 'responses'])
            ->latest()
            ->take(5)
            ->get();

        $recentAudits = AuditLog::where('organization_id', $orgId)
            ->with('actor')
            ->latest('created_at')
            ->take(6)
            ->get();

        return view('admin.dashboard', compact(
            'eventsCount',
            'formsCount',
            'responsesCount',
            'verifiedCount',
            'recentResponses',
            'recentEvents',
            'recentAudits'
        ));
    }
}
