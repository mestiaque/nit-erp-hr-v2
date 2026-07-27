actual => factory->no_of_factory = 0/null
complince 1 => factory->no_of_factory = 1
complince 2 => factory->no_of_factory = 2

Allow OT Hour = factory->Allow OT Hour  (dhore nilam 3)

employee X0001 , 
    Designation Manager
    Shift (In 8:00AM, Out 5:00PM)

Job card behavior,

11/7/2026   In 8:00 AM  -   Out 10:00 PM    (regular day)
            In          Out         OT          EXTRA OT
Actual      8:00 AM     10:00 PM    5 Hour      (Hide)
Comp 1      8:00 AM     8:00  PM    3 Hour      (Hide)
Comp 2      8:00 AM     10:00 PM    3 Hour      2 Hour

12/7/2026   In 8:00 AM  -   Out 7:00 PM     (regular day)
            In          Out         OT          EXTRA OT
Actual      8:00 AM     7:00 PM     2 Hour      (Hide)
Comp 1      8:00 AM     7:00  PM    2 Hour      (Hide)
Comp 2      8:00 AM     7:00 PM     2 Hour      0 Hour

13/7/2026   In 8:00 AM  -   Out 10:00 PM    (designation->wphp enable + Weekend Day)
            In          Out         OT          EXTRA OT
Actual      8:00 AM     10:00 PM    14 Hour      (Hide)
Comp 1      --          --          --           (Hide)
Comp 2      --          --          --           (Hide)
    on weekend never show attendance in complince 

14/7/2026   In 8:00 AM  -   Out 10:00 PM    (weekend to regular)
            In          Out         OT          EXTRA OT
Actual      8:00 AM     5:00 PM    0 Hour      (Hide)
Comp 1      8:00 AM     5:00 PM    0 Hour      (Hide)
Comp 2      8:00 AM     5:00 PM    0 Hour      0 Hour

    


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




Basic->factory te ekta column add koro 'ot count after shift end (min)'
ei column er kaj hobe ot jekhane jekhane dekhay sekhane oi min por ot calculate hobe
dhoro
ot count after shift end (min) = 30
shift end time 5:00 PM
ekhon employee er 
    exit time jodi hoy 6:10 PM tahole tar ot dekhabe 40 min
    exit time jodi hoy 5:20 PM tahole tar ot dekhabe 00 min
    exit time jodi hoy 5:30 PM tahole tar ot dekhabe 00 min
    exit time jodi hoy 5:40 PM tahole tar ot dekhabe 10 min

    full system e eita apply koro, bises kore jekhane jekhane ot calculate hoy and show hoy 






Bug fix 22-JUL-2026


    
    1.1 Designation :: rename input field label 
            Weekend Allowance Count -> Weekend Allowance Count (Main)
            Holiday Allowance -> Holiday Allowance (Main)

    1.2 Designation :: Add new column 
            Weekend Allowance Count and input label Weekend Allowance Count (Comp) [eigula complice mode e dekhabe]
            Holiday Allowance and input label Holiday Allowance (Comp) [eigula complice mode e dekhabe]

    1.1 & 1.2 description
        eigular kaj holo weekend ba holiday te keo kaj korle tar oi diner parisromik kivabe hobe ta nirdharon kora ... example salary sheet er wp hp te eita dekhabe 

    1.3 Factory :: Add minimum_ot_minutes 
        apatoto eita anr e apply koro
        eitar bisoy ta emon je eitar value jodi 0/null na hoy tahole eita kothao effective hobe na
        OT Count After Shift End (min) eita alreay working

        doro shift time end hocche 5 tay and ot coutn after shift end 10 min, tar mane ot 5:10 theke count hobe
        and ot hour jodi hourly count kori tahole emon hobe 
        5:11 - 6:10
        6:11 - 7:10
        7:11 - 8:10 eivabe  
        eibar jodi minimum_ot_minutes er value 50 hoy tahole 5:11 teke minimum 50 min kaj korle 60 min ot hisebe dhora hobe ar jodi 50 min er kom hoy tahole 0 min count hobe

        mane proti 1 ghontay min 50 min kaj korle 1 ghontar ot pabe, 

    2.1 Add New Column 
            OT Count After Shift End (min) 
            ei same name ekta column factory table e ase, tumi designation eo add koro , jodi designation er valo 0 theke   boro hoy tahole designation er ta dhorei hisab hobe, factory er ta dhorte hobe na, ar jeta designation e 0 or null thakbe seta factory er ta dhorbe
    3.1 Salary sheet e 
        earn day / working day = total month day - total absent
        factory holiday othoba weekend e te keo kaj korle ta salary report e WP & HP e days and amount dekhabe, amount er hisab hobe designation er Weekend Allowance Count theke and designation er baki field er condition o ager motoi bohal thakbe 

    4.1 ANR salary sheet print e protita group e report header thakbe [factory info]

    5.1 filter by shift (multiple) apply on all report 

    
Shift
    Start Time 
    End Time
    Start Allow Time (Card Accept From)
    Late Allow Time (Red Marking On)
    Out Time Start (Card Accept To)

Shift Name  Start Time  End Time    Start Allow Time   Late Allow Time     Out Time Start
General     8:00 AM     5:00 PM     6:00 AM            8:10 AM             4:45:PM

er mane holo ei shift er employee 6:00 AM to 8:10 AM porjonto punch korle present dekhabe
                                  8:11 AM to 4:44 PM porjonto punch korle late dekhabe
                                  6:00 AM to 4:44 PM porjonto punch korle 1st punch ta punch in hisebe count korbe and er pore 4:44 porjonto ar punch update hobe na
                                  4:45 PM to next day 5:59 porjonto punch korle punch out hisebe count hobe last punch 


WH	SL	ML	EL	CL	GL	FL


factory holiday create hoy na
    leate type hobe [festival and general]
    eita all report e dekhabe jekhane attendance or leave niye kaj kora ase
    (salary sheet e Leave er under e FL and GL name 2 ta column korbe 
    salary sheet sfl e weekend + holiday = holiday)
    ar onno report jemon jobcard, attendance, summary etc te jevabe ase sevabe add koro othoba jevabe add korle valo hobe sevabe add koro but onno kono data change koro na  ==