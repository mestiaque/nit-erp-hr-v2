Sl-NO	Card no	Name	Photo	Join Date	Designation	Section	Grade	Basic	H.R	M/A	F/A	T/A	Gross Salary	Month Day	Work Day	Holy Day	E/L	S/L	C/L	 Pay Day	Absent Day	Absent TK	Att: Bonus	Other Alowance	Total Salary	OT. Hour	Rate	OT-Amount	Net Salary	Advance Paid 	Revenue	Payable 	Stamp









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


amar [OT summary, Salary Summary, Attendance With OT] report lagbe, clear, professional and accurate ==

-> salary report sfl e stamp er pore arekta blank column add koro [signature]
-> dhoro kono employee 16-July-2026 e resign dilo, tar data oi 16-july-2026 porjontoi dekhabe attendance, report, job card , salarysheet soho joto report ase sob report e, and emoloyee list e oitar column light-red add kore dao == 
->daily attendance report e summary dekhao sundor and details e =
->attendance with ot eita correct koro and eita monthly processing er moddhe dao, sidebar e alada korar dorkar nai =
->monthly late report ta attendance report er moddhe dhukiye dao, sidebar e alada kore dekhanor dorkar nai =



Create a complete Employee Gate Pass module for an HR Management System.

Database Table:
hr_employee_gate_pass

The module will be managed only by HR Officers. Employees do not submit requests.

Features:
- Gate Pass List Page
- Create Gate Pass Modal
- Save & Print functionality
- Print page opens automatically in a new browser tab after successful save.

List Page:
- Page Title: Employee Gate Pass
- Top-right button: Create Gate Pass
- Search box
- Responsive Bootstrap table

Table Columns:
- Pass No
- Employee
- Department
- Out Time
- In Time
- Duration (Minutes)
- Reason
- Status
- Action (View, Print, Edit)

Create Modal Fields:
- Employee (Searchable Select)
- Out Time (Datetime Picker, default current date & time)
- Duration (Minutes)
- In Time (Datetime Picker)
- Reason (Dropdown)
- Remarks (Textarea)

JavaScript Logic:
- If Out Time and Duration are entered, automatically calculate In Time.
- If Out Time and In Time are changed, automatically calculate Duration.
- Keep Duration and In Time synchronized.

Workflow:
1. HR Officer clicks Create Gate Pass.
2. Fills the form.
3. Clicks Save & Print.
4. Data is saved into the `hr_employee_gate_pass` table.
5. The system opens the printable gate pass in a new browser tab.
6. The modal closes.
7. The table refreshes automatically.

Suggested Database Columns (hr_employee_gate_pass):
- id
- pass_no
- employee_id
- out_time
- in_time
- duration_minutes
- reason
- remarks
- status
- created_by
- created_at
- updated_at

Status Values:
- Active
- Returned

UI Style:
- Bootstrap 5
- Clean and modern HRMS dashboard
- White cards
- Rounded corners
- Responsive
- Professional admin interface
- Blue primary buttons
- Soft status badges


