<?php
namespace ME\Hr\Http\Controllers;

use App\Models\Country;
use Illuminate\Routing\Controller;

class HrController extends Controller
{
    public function index()
    {
        return redirect()->route('hr-center.dashboard');
    }

    public function getThanasByDistrict($id)
    {
        $thanas = Country::where('parent_id', $id)->get();

        return response()->json($thanas);
    }
}
