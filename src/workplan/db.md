Classification
    ID	Name	BnName	Description	Status(0/1)
Floor Line
    ID FloorName BnFloorName LineName BnLineName LineCapacity Status(0/1)
Bonus Title
    ID Title BnTitle Code Description Status(0/1)
Bonus Policy
    ID BonusTitleID PolicyName PolicyNameBn DepartmentId SectionId SubSectionId MonthRangeForm MonthRangeTo ApplyOn(Ex. Basic Gross Production etc.) Type(Fixed, Percent), Amount Status
Geo Location (Country + Division + District + Police Station + Post Office)
    ID Name BnName ParentId Type Status
Department 
    ID Name BnName Description, HeadOfDepartment(EmployeeId)
Designation
    ID Name Bangla Name Grade Approved Manpower Department Section AttendanceBonus AttendanceBonusCom. TiffinAllowance MinimumTiffinHour NightAllowance MinimumNightHour DinnerAllowance MinimumDinnerHour (Tiffin, Night & Dinner) PaymentWay WeekendAllowanceCount HolidayAllowance GrossSalary (Car & Fuel Phone & Internet) ExtraFacility OT_One OT_Two Report To Responsibilities Follow Up Team Status
Factory
    ID Name BnName Address BnAddress ContactNumber AuthoritySign Status AllowOTHour StampAmount
Leave Info
    ID Name BnName Days Description status
Marital Status
    ID	Name	BnName	Code	Status
Religion
    ID	Name	BnName	Code	Status
Sex 
    ID	Name	BnName	Code	Status
Salary Key
    ID	Medical	Lunch	Transport	Status
Payment Method
    ID	Name	BnName Code	Status
Shift
    Name BnName StartTime EndTime StartAllowTime LateAllowTime OutTimeStart Status
Section
    Name BanglaName Department Description Status
Sub Section
    Name BanglaName Department Section SalaryType ApproveManPower RosterShift IsIndividualRoster Status
Working Place 
    Name	BnName	Code	Status

Employee
    Name
    BnName
    Employee ID
    Join Date
    Classification
    Department
    Section
    Sub-Section
    Floor/Line
    Designation
    Working Place
    Shift
    Weekend
    Personal Contact
    Emergency Contact
    status
    comp_one(0/1)
    comp_two(0/1)
    ->employee_basic_info
        Basic Info
        Father's Name
        Father's Name (Bangla)
        Mother's Name
        Mother's Name (Bangla) 
        Marital Status
        Spouse Name
        Spouse Name (Bangla)
        Sex
        Boys(child)
        Girls(child)
        Payment Mode
        Religion
        Birth Date
        Blood Group
        Nationality (country Id)
        National ID No.
        Birth Registration No.
        Passport No.
        Driving License No.
        Special Identification Sign
        Special Identification Sign (Bangla)
        Educational Experience
        Educational Experience (Bangla)
        Job Experience
        Job Experience (Bangla)
        Previous Organization
        Previous Organization (Bangla)
        Reference Name
        Reference Name (Bangla)
        Reference Designation
        Reference Designation (Bangla)
        Reference Card No.
        Reference Card No. (Bangla)
        Reference Mobile No.
        Reference Mobile No. (Bangla)
    ->Salary Info
        Gross Salary
        Gross Salary(Comp_One)
        Gross Salary(Comp_Two)
        Payment Mode
        Bank AC. Or Phone No.
        Car & Fuel
        Phone & Internet
        Extra Facility
        Tax
        Tax Calculate By
        Date
        Status
    ->Address
        Permanent Address
            District id
            Po. Station id
            Post Office Id
            Post Office
            Post Office (Bangla)
            Village
            Village (Bangla)
        Present Address
            District id
            Po. Station id
            Post Office Id
            Post Office
            Post Office (Bangla)
            Village
            Village (Bangla)
    ->Nominee
        Photo 
        Name 
        Name(Bangla) 
        District id
        Po. Station id
        Post Office Id
        Post Office
        Post Office (Bangla)
        Village
        Village (Bangla)
        NID No.
        Mobile No.
        Relation
        Relation (Bangla)
        Age
        Net Payment
        Provident Fund
        Insurance
        Accident Fine
        Profit
        Others
    ->Age Verification 
        Physical Ability
        Identification Mark
        Age(Years)
        Date
    ->Lefty & Resign Information
        Status( regular, lefty, resign, transfer) Remarks Date Final Settlement(earn leave only, earn leave with service benifit, earn leave without service benifit) With Paid
    ->Final Settlement Information
        Absent Date
        1st Letter Date
        2nd Letter Date
        3rd Letter Date
        Select Letter to Print
    Salary Increment
        Emp ID	Name	Classification	Department	Section	Designation	Previous Salary	Increment Amount	New Salary	Increment Date
    Others Earnings & Deductions
        Date
        Advance/IOU
        OT(+/-)
        Day(+/-)
        Earnings
        Deductions
        Remarks
    Leave
        Application Date
        Application No.
        Leave Type
        Leave From
        Leave To
        Reason
        Remarks


Holiday
    Purpose
    Type
    From
    To
    Remarks
    Status
Regular to Weekend
    Section
    Date
    Type
    Status(0/1)