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

13/7/2026   In 8:00 AM  -   Out 10:00 PM    (weekend day + designation->wphp enable)
            In          Out         OT          EXTRA OT
Actual      8:00 AM     10:00 PM    14 Hour      (Hide)
Comp 1      8:00 AM     8:00  PM    12 Hour      (Hide)
Comp 2      8:00 AM     10:00 PM    12 Hour      2 Hour

    


