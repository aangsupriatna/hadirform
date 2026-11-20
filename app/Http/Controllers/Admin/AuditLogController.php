<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $orgId = $request->user()->organization_id;

        $logs = AuditLog::where('organization_id', $orgId)
            ->with('actor')
            ->latest('created_at')
            ->paginate(25);

        return view('admin.audit.index', compact('logs'));
    }
}
