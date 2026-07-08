    $masterData = \App\Services\HrOptionsService::getOptions();
    $employeeData = \App\Services\HrOptionsService::getOptionsForEmployee();
    $employeeOthers = $employee->otherInfo();
    $jobType = $masterData['classifications']->where('id', $employee->employee_type)->first();
    $otRate = $basicForOt > 0 ? round(($basicForOt / 208) * 2, 2) : 0;

    logo = {{asset(general()->logo())}}


ama Shift e 
Start Time
End Time
Start Allow Time 
Late Allow Time
Out Time Start

ei option gula ase 

dhore nilam -
Start Time = 08:00 AM
End Time = 05:00 PM
Start Allow Time = 07:45 AM
Late Allow Time = 08:10 AM
Out Time Start = 04:45 PM 

er mane hocche 
07:45 AM to 08:10 AM er moddhe punch hole seta intime e porbe
04:45 Pm to Next Day 07:44 AM porjonto punch korle out time hisebe count hobe

but 8:11 AM to 04:44 PM porjonto punch korle oita attendance e add hobe na intime/outtime konotatei dekhabe na  

bises kore machine log er khetre eita valo kore check korbe 