<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminExportLog;
use Illuminate\Http\Request;

class ExportController extends Controller
{
    public function index(Request $request)
    {
        $logs = AdminExportLog::with('user')->latest()->paginate(20);

        return view('admin.exports.index', compact('logs'));
    }
}
