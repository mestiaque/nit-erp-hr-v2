Create a new report module in HR Payroll.
Add only one sidebar menu:Tiffin / Dinner / Night
Report Filters:This report should use the same filter layout and behavior as other existing reports in the system. Do not create a different filter design.Add a Report Type dropdown with the following options:
Tiffin
Dinner
Night

Report Logic

The report will calculate allowance based on the Designation configuration.

Tiffin
Read Tiffin Allowance
Read Minimum Tiffin Hour
If an employee's worked hour is greater than or equal to the configured Minimum Tiffin Hour, the employee is eligible for the configured Tiffin Allowance.
If the designation payment way is Daily, show daily eligible records.
If the payment way is Monthly, calculate the total monthly eligible amount.
Dinner
Read Dinner Allowance
Read Minimum Dinner Hour
Apply the same logic as Tiffin.
Night
Read Night Allowance
Read Minimum Night Hour
Apply the same logic as Tiffin.
Payment Way

Use the Designation field:
Tiffin, Night & Dinner Payment Way

Possible values:

Daily
Monthly

The report calculation must follow the configured payment method.

If Monthly is selected, also display:

Total Eligible Days
Total Payable Amount
Export


Follow the existing report UI and coding structure.
Do not create a separate menu for Tiffin, Dinner, and Night.
Keep only one sidebar menu named "Tiffin / Dinner / Night".
The Report Type dropdown (Tiffin / Dinner / Night) will control which report data is displayed.
Allowance eligibility must always be calculated using the corresponding Designation configuration fields (Allowance Amount, Minimum Hour, and Payment Way).