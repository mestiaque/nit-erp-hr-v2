Job Card Report:
Main page
Employee ID(s) ***Use "," for more ID
From (date select)
To (date select)

Classification (dropdown from basic)
Department (dropdown from basic)

Section (dropdown from basic)
Sub-Section (dropdown from basic)

Shift (dropdown from basic)
Working Place (dropdown from basic)

Block/Line (dropdown from basic)
Salary Type (fixed price, fixed rate)
Employee Status (regular, lefty, resign)
Language (banga, english)
Report Type (dropdown - job card, job card summary, job card lock, job card summary Lock, Attendance Summary , OT details, Ot summary, )
[lock switch btn, for job card lock with apply function]

Job Card Report:
Job Card Report (11-Apr-2026 To 11-Apr-2026)

info table
Employee ID	: B00144	Department	: Production
Name	: Sorborton Mia	Section	: Cad & Sample
Classification	: Staff	Designation	: Cad Master
Join Date	: 02-Nov-24

main table
SL	Date	Shift	Day Name	In Time	Out Time	OT Hours	Status	Remarks


Job card summary:
Job Card Summary (11-Apr-2026 To 11-Apr-2026)
section wise loop with report header
Table SI	Emp. ID	Name	Designation	DOJ	Section	Sub-Section	Block/ Line	11(td: A | 0)	P/OT(0 | 0.00) , header 11 = 11 days, 11(td: A | 0)	P/OT(0 | 0.00) ei 2 ta ektu research kore bujhte hobe, ei 11 din er moddhe employee ta present chilo kina, present thakle P, OT hours jodi thake tahole | diye separate kore dekhabe, na thakle 0.00 dekhabe, ei 11 din er moddhe employee ta present chilo kina, present thakle P, OT hours jodi thake tahole | diye separate kore dekhabe, na thakle 0.00 dekhabe

Job card (lock):
Job Card (11-Apr-2026 To 11-Apr-2026)
As your wish

Job card Summary (Lock):
As your wish


Attendance Summary
print section wise
Table : SI	ID	Name	Designation	Join Date	Section	Sub-Section	Block/ Line	Month Day	Late	Absent	Leave	Weekend	Fac. Holiday	Present	Earn Days	OT	Remarks


OT details
print section wise
SI	Emp. ID	Name	Designation	DOJ	Section	Sub-Section	Block/ Line	11	To. OT

OT Summary:
table : SI	Designation	Section	11	To. OT
Section wise td


pint hobe printMaster 2 layout e
kisu select na korle all print hobe



/////////////////////////////////////////////////////
Attendance Report

date (date picker)
Classification (dropdown : from basic)
salary type (dropdown : fixed rate , fixed price)
Department ( dropdown : from basic)
Section ( dropdown : from basic)
Sub section ( dropdown : from basic)
Shift
    today shift (checkbox : form basic)
    lastday shift (checkbox : form basic)
type (attendanse type ja ja hote pare sei hisebe)


/////////////////////////////////////////////////////
Tifin/Diner/Night Report

date (date picker)
Classification (dropdown : from basic)
salary type (dropdown : fixed rate , fixed price)
Department ( dropdown : from basic)
Section ( dropdown : from basic)
Sub section ( dropdown : from basic)
Working Plase (dropdown : form basic)
Shift (checkbox : form basic)
type (Tifin/Diner/Night)
report type (details, ... ,... ja ja thakte pare)

///////////////////////////////////////////////////
Bonus Sheet
    -fixed
        -up to date (date picker)
        -bonus title (dropdown:from basic)
        -employee ids (input text and , for multuple id)
        -block/line (dropdown:from basic)
        -Designation (dropdown:from basic)
        -Language (bangla, englsh)
        -report type (details, ... ,... ja ja lage )
        -with picture (switch btn for show employee picture in report)
        -lock bonus (bonus lock function with switch btn )

        ->> details report (department wise)
                th -> sl, photo, employee id, name,Designation, join date, job age, gross salary, basic salary,present(%), Stamp, Bonus Amount, Signature and stampt (blank column)

    -Production
        -from (date picker)
        -to (date picker)
        -bonus title (dropdown:from basic)
        -employee ids (input text and , for multuple id)
        -block/line (dropdown:from basic)
        -Designation (dropdown:from basic)
        -groupBy ( department, section, etc)
        -Language (bangla, englsh)
        -report type (details, ... ,... ja ja lage )
        -with picture (switch btn for show employee picture in report)
        -lock bonus (bonus lock function with switch btn )


salary
    fixed
        -from (date picker)
        -to (date picker)
        -employee ids (input text and , for multuple id)
        -department (dropdown:from basic)
        -Designation (dropdown:from basic)
        -Section  (dropdown:from basic)
        -sub section (dropdown:from basic)
        -Classification (dropdown:from basic)
        -Working place (dropdown:from basic)
        -block/line (dropdown:from basic)
        -Designation (dropdown:from basic)
        -employee status (dropdown:from basic)
        -Language (bangla, englsh)
        -payment mode (checkbox: from basic)
        -report type (details, ...... ja ja lage)
        -with picture (switch btn for show employee picture in report)
        -lock salary (salary lock function with switch btn )

    Production
    bonus
    wages and salary summary




report e gula add koro , first interface e input  gula thakbe ad sobgukar niche [report] btn thakbe click korle printMaster2 te print hobe new tab





>report
    >>empoloyee
    >>monthly
    >>machine id
    >>job card report
    >>personal file
    >>attendance
    >>Tifin/Night/Diner
    >>Pro. Job Card
    >>Bonus Sheet
    >>Salary Sheet


Production Rate
    >Trimming to Zipper
Index : th : Local Agent , Buyer, Style Name, Style Number, Gauge, Order Qty, Merchendiser
    >> assing progress open modal 
        in row : process, rate, select pro. process