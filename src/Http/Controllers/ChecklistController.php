<?php

namespace ME\Hr\Http\Controllers;

use Illuminate\Routing\Controller;

class ChecklistController extends Controller
{
    public function index()
    {
        $categories = config('checklist');

        $totalItems = 0;
        $countReady = 0;
        $countDev   = 0;
        $countNone  = 0;

        foreach ($categories as $cat) {
            foreach ($cat['items'] as $item) {
                $totalItems++;
                match ((int) $item['status']) {
                    2       => $countReady++,
                    1       => $countDev++,
                    default => $countNone++,
                };
            }
        }

        $stats = [
            'total' => $totalItems,
            'ready' => $countReady,
            'dev'   => $countDev,
            'none'  => $countNone,
            'pctReady' => $totalItems > 0 ? round($countReady / $totalItems * 100, 1) : 0,
            'pctDev'   => $totalItems > 0 ? round($countDev   / $totalItems * 100, 1) : 0,
            'pctNone'  => $totalItems > 0 ? round($countNone  / $totalItems * 100, 1) : 0,
        ];

        return view('hr::checklist.index', compact('categories', 'stats'));
    }
}
