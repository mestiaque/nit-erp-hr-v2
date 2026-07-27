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

    
