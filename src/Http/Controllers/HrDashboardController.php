<?php

namespace ME\Hr\Http\Controllers;

use Illuminate\Routing\Controller;

class HrDashboardController extends Controller
{
    public function index()
    {
        $entities = config('hr.entities', []);
        $legacyLinks = config('hr.legacy_links', []);
        $reports = config('hr.reports', []);

        return view('hr::dashboard', compact('entities', 'legacyLinks', 'reports'));
    }
}
