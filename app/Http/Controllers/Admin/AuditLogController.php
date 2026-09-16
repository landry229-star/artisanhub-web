<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminAuditLog;

class AuditLogController extends Controller
{
    public function index()
    {
        $logs = AdminAuditLog::with('admin')
            ->latest()
            ->paginate(30);

        return view('admin.audit_logs.index', compact('logs'));
    }
}
