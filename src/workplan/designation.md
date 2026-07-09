>>Designation Effectiveness
Grade
Approved Manpower
Attendance Bonus
Attendance Bonus Com.
Tiffin Allowance
Minimum Tiffin Hour
Night Allowance
Minimum Night Hour
Dinner Allowance
Minimum Dinner Hour
Tiffin, Night & Dinner Payment Way [daily, monthly]
Weekend Allowance Count [gross/monthDay, Basic/workingDay, basic/104*2.5, fixed Amount(Holiday Allowance), OT by work hour]
Holiday Allowance, 
Gross Salary, 
Car & Fuel Allowance, 
Phone & Internet Allowance, 
Is OT Basis (WPHP), 
Is OT Basis (Main)
Is OT Basis (Others-1) 
Is OT Basis (Others-2)



>>Designation Effectiveness

Grade 
    -> employee create er somoy auto ei grade add hobe designation er grade onujayi and sobjaygay sob report e show korbe
Approved Manpower
    -> ei designation er under e koto jon worker/employee create kora jabe ta ekhane ullekh thakbe
Attendance Bonus 
    -> Actual mode (Basic->factory->no_of_factory == 0 or null ) e attendance bonus koto hobe ta ekhan theke hobe, kono employee monthly all day attend thakle ei bonus pabe, factory holiday er ayotay porbe na, weekend to regular er ayotay porbe
Attendance Bonus Com.
    -> Complince mode (Basic->factory->no_of_factory == 1 or 2) e attendance bonus koto hobe ta ekhan theke hobe, kono employee monthly all day attend thakle ei bonus pabe, factory holiday er ayotay porbe na, weekend to regular er ayotay porbe
Tiffin Allowance
    ->ei designation er ayotay je employee gula ase tader tiffin allowance ekhan theke calculate hobe
Minimum Tiffin Hour
    ->employee koto hour kaj korle tiffin allownce er jonno able hobe ta ekhane theke calculate hove
Night Allowance
    -> same as tiffin
Minimum Night Hour
    -> same as tiffin
Dinner Allowance
    -> same as tiffin
Minimum Dinner Hour
    -> same as tiffin
Tiffin, Night & Dinner Payment Way [daily, monthly]
    ->paymanetgula ki daily calculate hobe naki monthly ta ekhan theke define hobe
Weekend Allowance Count [gross/monthDay, Basic/workingDay, basic/104*2.5, fixed Amount(Holiday Allowance), OT by work hour]
    ->weekend allowance ekhane jeta select thakbe sei onujayi ei designation er sobar allowance calculate hobe
Holiday Allowance, 
    ->jodi weekend allowance count 'fixed Amount(Holiday Allowance)' hoy tahole 
Gross Salary,
    ->employee create er somoy employee er gross salary field gulate ekhaner amount fill hobe
Car & Fuel Allowance, 
    ->employee create er somoy employee er ar & Fuel Allowance field gulate ekhaner amount fill hobe
Phone & Internet Allowance, 
    ->employee create er somoy employee er Phone & Internet Allowance field gulate ekhaner amount fill hobe
Is OT Basis (WPHP), 
    -> eita on thakle friday/weekend er full attendance ot hisebe count hobe
Is OT Basis (Main)
    -> eita on thakle Actual(Basic->factory->no_of_factory == 0 or null) e ot dekhabe
Is OT Basis (Others-1) 
    -> eita on thakle Actual(Basic->factory->no_of_factory == 1) e ot dekhabe
Is OT Basis (Others-2)
    -> eita on thakle Actual(Basic->factory->no_of_factory == 2) e ot dekhabe


ei calculation ta sob jaygay effect korbe , all report e bises kore salary, job card, payslip





>>Earnings & Deductions effect 
admin/hr-center/employees/315/earnings-deductions
Date -> ei date er month te sob employee er earnings ebong deductions calculate hobe
Advance/IOU
    -> ei amount advance/iou hisebe deduction hobe
OT(+/-)
    -> ekhane + mane overtime er taka Earnings e add hobe and - mane overtime er taka deduction hobe
        example  + 1 mane 1 ghontar overtime er taka add hobe , - 1 mane 1 ghontar overtime er taka deduction hobe
Day(+/-)
    -> ekhane + mane day er taka Earnings e add hobe and - mane day er taka deduction hobe
        example  + 1 mane 1 day er taka add hobe , - 1 mane 1 day er taka deduction hobe
Earnings
    -> ei amount earnings hobe
Deductions
    -> ei amount deductions hobe

ei calculation ta sob jaygay effect korbe , all report e bises kore salary, job card, payslip
