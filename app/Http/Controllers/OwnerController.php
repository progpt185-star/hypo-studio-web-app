<?php

namespace App\Http\Controllers;

use App\Models\Owner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class OwnerController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function dashboard()
    {
        // authorize using gate defined in AuthServiceProvider
        if (!Gate::allows('view-reports')) {
            abort(403);
        }

        // Owner dashboard can reuse existing OwnerReportController logic or views
        // For now, load owners and pass basic counts
        $owners = Owner::with('user')->get();
        return view('owner.dashboard', compact('owners'));
    }
}
