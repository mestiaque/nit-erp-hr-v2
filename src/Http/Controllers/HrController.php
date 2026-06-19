<?php

namespace ME\Hr\Http\Controllers;

use Illuminate\Routing\Controller;
use ME\Hr\Models\HrGeoLocation;

class HrController extends Controller
{
    public function index()
    {
        return redirect()->route('hr-center.dashboard');
    }

    public function getThanasByDistrict($id)
    {
        $thanas = HrGeoLocation::where('parent_id', $id)
            ->where('type', 'police_station')
            ->orderBy('name')
            ->get(['id', 'name', 'bn_name']);

        return response()->json($thanas);
    }
}
