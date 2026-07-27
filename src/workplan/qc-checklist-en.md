# HRM Payroll/Attendance — Centralized QC/QA Checklist (English)

This document is written so that **someone with zero prior knowledge of this software** can QA/QC the entire system just by reading this checklist. For every entity (Factory / Designation / Employee / Shift / Holiday / Weekend-to-Regular), it explains what each column does, how it affects Attendance/Job Card, and finally how it shows up in Reports (Salary Sheet, Job Card, OT Summary, etc.) — all in one place.

Source: these rules were given at different times in `workplan/checklist.md` and `workplan/complince.md`. This document simply centralizes and cleans that up into one reference.

---

## 0. Two Core Concepts First

### 0.1 Factory Compliance Mode (`factory_no`)
The system uses a single "active" Factory row (via the `hr_factory()` helper), and that row's **`factory_no`** column acts as a global switch:

| `factory_no` value | Mode | Meaning |
|---|---|---|
| `0` or `null` | **Actual** | The real/actual attendance-salary — real punch time, real OT |
| `1` | **Comp 1** (Compliance) | Buyer-audit-safe version 1 — compressed/capped hours |
| `2` | **Comp 2** (Compliance) | Buyer-audit-safe version 2 — compressed hours + a separate "Extra OT" column |

**Where this has effect** (this is the single most important thing to remember — almost everything else depends on it):
- In/Out time & OT display in Job Card / Attendance
- Deduction base in Salary Sheet (Gross vs Basic)
- Which Designation field set applies — "(Main)" vs "(Comp)"
- Whether attendance shows at all on weekend days

### 0.2 "Weekend to Regular" Swap (`HrRegularToWeekend` table)
This table is used to declare a **section-wise, date-wise** override:
- `type = 'weekend'` → a normally regular working day is company-wide declared as "off" (e.g. an extra day off before Eid)
- `type = 'regular'` → a normally weekly-holiday day is company-wide declared as a "working day" (e.g. a compensatory working Friday)

This is **not** an individual employee attendance flag — if there's no entry in this table, an employee who attends on a weekend simply doesn't count as "worked weekend"; it just shows status "Weekend" (details in Section 7).

---

## 1. FACTORY — Column by Column

| Column | What it does | Effect on Attendance/Job Card | Effect on Reports |
|---|---|---|---|
| `factory_no` (Actual/Comp1/Comp2) | Compliance mode switch | Completely changes Job Card In/Out/OT display rules (see the matrix in Section 7) | Salary Sheet's deduction base (Gross vs Basic), which Designation Main/Comp field set is used |
| `allow_ot_hour` (default 2, "assume 3" in the original example) | Max OT "allowed"/capped per day in compliance mode | Comp1: time beyond the cap is hidden (shows a shortened out-time); Comp2: up to the cap counts as "OT", the rest shows separately as "Extra OT" | Job Card OT/Extra OT columns |
| `ot_grace_minutes` ("OT Count After Shift End (min)") | How many minutes after shift-end OT starts counting | Shift end 5:00 PM + 30-min grace: exiting at 5:20 PM → OT = 0, exiting at 5:40 PM → OT = 10 min | Job Card, Attendance, Salary Sheet OT column, OT Summary — **wherever OT is shown/calculated** |
| `minimum_ot_minutes` (new, currently applied only to ANR, has zero effect if 0/null) | Buckets OT into whole-hour blocks: each hour block only counts if at least this many minutes were worked in it | For each 60-minute block past the grace cutoff (e.g. minimum=50): working 50+ minutes of that block counts the full 60 min as OT; less than that counts 0 for that block | Same as `ot_grace_minutes` — wherever OT is calculated |
| `weekend` (e.g. "Friday") | Which day(s) are the weekly holiday | Base for weekend detection | Salary Sheet "Weekly holiday's" count, Attendance status |

---

## 2. DESIGNATION — Column by Column

Designation settings apply **globally to every employee under that designation**.

| Column | What it does | Effect |
|---|---|---|
| `grade` | Auto-filled when creating an employee, shown in every report | Employee list, Salary Sheet "Grade" column |
| `approved_manpower` | Max number of employees that can be created under this designation | Employee-create validation |
| `attendance_bonus` | Attendance bonus amount in **Actual mode** (factory_no 0/null) | Paid on a full-attendance month. Doesn't apply for factory-holiday days, does apply for weekend-to-regular days |
| `attendance_bonus_com` | Attendance bonus amount in **Compliance mode** (factory_no 1/2) | Same rule, using the Comp field instead of Actual |
| `tiffin_allowance` / `min_tiffin_hour` | How much tiffin allowance is paid once minimum hours are worked | Job Card, Salary — daily/monthly per `payment_way` |
| `night_allowance` / `min_night_hour` | Same, for night allowance | ” |
| `dinner_allowance` / `min_dinner_hour` | Same, for dinner allowance | ” |
| `payment_way` (daily/monthly) | Whether Tiffin/Night/Dinner is paid per-day or as a monthly lump sum | Salary Sheet meal total |
| **`weekend_allowance_count` (Main)** | How pay is calculated for a day worked on a weekend/holiday, for **Actual mode (factory_no 0/null)** | Policy options: `gross/monthDay`, `basic/workingDay`, `basic/104*2.5`, `fixed Amount (Holiday Allowance)`, `OT by worked hour` |
| **`weekend_allowance_count_comp` (Comp)** ⭐ NEW | Same policy options, but a separate policy can be chosen for **Compliance mode (factory_no 1/2)** | This field is used in compliance mode instead of Main |
| **`holiday_allowance` (Main)** | If policy is `fixed_amount`, the fixed per-day rate for Actual mode | WP & HP amount calculation |
| **`holiday_allowance_comp` (Comp)** ⭐ NEW | Same, but for Compliance mode | ” |
| `gross_salary` | Default value filled into the employee's gross salary field on creation | Employee profile |
| `car_fuel_allowance` / `phone_internet_allowance` | Default values filled on employee creation | Employee profile, Salary Sheet |
| `ot_one_rate` / `ot_two_rate` | OT rate multiplier (by type) | Salary Sheet OT Rate column |
| `is_ot_basis_wphp` | When on: if the employee works on a weekend/holiday, that time **also counts as OT (in addition to the WP&HP amount)**. On/off doesn't affect whether WP&HP amount is paid — it only controls whether extra OT is also paid | Job Card OT column, Salary Sheet OT column |
| `is_ot_basis_main` | When on, OT is shown in **Actual mode (factory_no 0/null)** | Job Card, Salary OT |
| `is_ot_basis_others_1` | When on, OT is shown in **Comp 1 (factory_no 1)** | ” |
| `is_ot_basis_others_2` | When on, OT is shown in **Comp 2 (factory_no 2)** | ” |
| **`ot_grace_minutes`** ⭐ NEW (Designation-level) | Overrides the Factory's `ot_grace_minutes` — **if the designation's value is > 0, it wins; the factory's value is not used.** If the designation's value is 0/null, the factory's value is used automatically | Job Card, Attendance, Salary, OT Summary — everywhere |

> **QA Test:** Set `ot_grace_minutes = 20` on a Designation while the Factory's `ot_grace_minutes` is (e.g.) 10 — confirm that 20 is used, not 10, across the Attendance edit page, Job Card, and Salary Sheet OT column, all three consistently.

---

## 3. EMPLOYEE — Relevant Fields (calculation-related)

| Field | Effect |
|---|---|
| `shift_id` | The employee's base shift — OT, In/Out time, and Late calculation all depend on this shift |
| `designation_id` | All the Designation rules in Section 2 apply to this employee |
| `employment_status` (regular/lefty/resign) + `exited_at` | After the exit date, every subsequent day is **"not_employed"** — nothing counts as Absent or Present, and no deduction applies either |
| `section_id` | The Weekend-to-Regular swap applies **per section** — if the employee's section is wrong, the swap won't apply |
| `otherInfo()['salary_info']` (weekend_allowance_count/holiday_allowance override) | An employee-level override takes priority over the Designation's value (in other Attendance calculations — not in the WP&HP calculation) |

---

## 4. SHIFT — Column by Column

| Column | Example | Meaning |
|---|---|---|
| `start_time` | 08:00 AM | Official shift start |
| `end_time` | 05:00 PM | Official shift end — OT starts counting from here (plus grace) |
| `start_allow_time` (Card Accept From) | 07:45 AM | Punches are accepted as "In" starting from this time |
| `late_allow_time` (Red Marking On) | 08:10 AM | Punching in after this time is marked "Late" |
| `out_time_start` (Card Accept To) | 04:45 PM | Punches from this time onward are treated as "Out"; also the reference point for the OT cap |

**Example (from the original checklist):**
Shift: Start 8:00 AM, End 5:00 PM, Start Allow 7:45 AM, Late Allow 8:10 AM, Out Time Start 4:45 PM
- Punching between 7:45 AM – 8:10 AM → In Time
- Punching between 4:45 PM – 7:44 AM next day → Out Time
- **Punching between 8:11 AM – 4:44 PM** doesn't count as either In or Out in attendance (this needs special attention when checking machine log sync)

---

## 5. HOLIDAY (Factory Holiday) — Fields

| Field | Effect |
|---|---|
| `from_date` / `to_date` | Every employee's status is "Holiday" across this date range (regardless of attendance) |
| `type` (`Festival` / `General`) | Salary Sheet shows **two separate columns**: `FL` (Festival holiday count) and `GL` (General holiday count) |

**Effect on Attendance/Job Card:** On a Factory Holiday day, status always shows "Holiday" — unlike weekend, worked or not doesn't matter, status stays "Holiday" (this takes priority over Leave, Weekend, and Absent — see the matrix in Section 7). **In Compliance mode (factory_no 1/2), a genuine holiday worked also has its In/Out/OT completely hidden** — exactly like weekend (see the "Holiday" row in Section 7.2).

**Effect on Reports:**
- Salary Sheet: WP & HP's "Days" count is based on whether the employee **actually attended/worked** that WH/FL/GL day (not simply how many such calendar dates existed) — see Section 8.1.

---

## 6. WEEKEND TO REGULAR (`HrRegularToWeekend`) — Fields

| Field | Effect |
|---|---|
| `section_id` + `date` | Which section, which date the swap applies to |
| `type = 'regular'` | On this date, even though it's normally the weekly-off day, it's been declared a "working day" for everyone |
| `type = 'weekend'` | On this date, even though it's normally a working day, it's been declared an "off day" for everyone |
| `status` | Active(1)/Inactive(0) — when inactive, has no effect |

**Effect on Job Card/Attendance (the "14/7/2026" scenario in Section 7):** On a `type='regular'` day, if the employee attends — In/Out time shows capped within the shift (e.g. even if the real out time was 10:00 PM, it's shown capped at shift-end 5:00 PM), OT = 0 in every mode (it behaves exactly like an ordinary/general shift day).

> ⚠️ **`HrRegularToWeekend` has no relationship with WP & HP (`is_ot_basis_wphp`).** A swap-converted (`type='regular'`) day is no longer an "off day" — it's paid as a normal working day and does **not** count toward WP & HP. WP & HP applies only to a **genuine (non-swapped) weekend or factory holiday**, and only when the employee **actually attended** that day (see Section 8.1).

---

## 7. ATTENDANCE / JOB CARD — Status & OT Behavior Matrix

### 7.1 Status resolution order (top to bottom = priority)
1. **Not Employed** — after the exit date, or a future date
2. **Leave** — if a leave application exists
3. **Weekend (compliance-only display)** — in Compliance mode, a weekend-to-regular day shows the real attendance status (Present/Absent), not "Weekend"
4. **Holiday** — within a Factory Holiday date range
5. **Weekend** — the employee's weekly off day (or a `HrRegularToWeekend type='weekend'` swap)
6. **Present / Late / Absent** — based on the normal attendance record

### 7.2 In/Out Time and OT — Scenario Table (original checklist examples)

Employee: Designation "Manager", Shift (In 8:00 AM, Out 5:00 PM)

| Date/Scenario | Mode | In | Out | OT | Extra OT |
|---|---|---|---|---|---|
| **Regular day**, real out 10:00 PM | Actual | 8:00 AM | 10:00 PM | 5 Hour | (Hide) |
| ” | Comp 1 | 8:00 AM | 8:00 PM | 3 Hour | (Hide) |
| ” | Comp 2 | 8:00 AM | 10:00 PM | 3 Hour | 2 Hour |
| **Regular day**, real out 7:00 PM | Actual | 8:00 AM | 7:00 PM | 2 Hour | (Hide) |
| ” | Comp 1 | 8:00 AM | 7:00 PM | 2 Hour | (Hide) |
| ” | Comp 2 | 8:00 AM | 7:00 PM | 2 Hour | 0 Hour |
| **Weekend Day** (genuine, non-swapped) + designation `is_ot_basis_wphp`=on, real out 10:00 PM | Actual | 8:00 AM | 10:00 PM | 14 Hour (entire worked span as OT) | (Hide) |
| ” | Comp 1 | — | — | — | (Hide) — **compliance mode never shows attendance on a weekend** |
| ” | Comp 2 | — | — | — | (Hide) — same |
| **Factory Holiday** (genuine) + designation `is_ot_basis_wphp`=on, real out 10:00 PM | Actual | 8:00 AM | 10:00 PM | 14 Hour (entire worked span as OT) — **same as weekend** | (Hide) |
| ” | Comp 1 | — | — | — | (Hide) — **compliance mode never shows attendance on a holiday either** |
| ” | Comp 2 | — | — | — | (Hide) — same |
| **Weekend→Regular swap** (`type='regular'`), real out 10:00 PM | Actual | 8:00 AM | 5:00 PM (capped) | 0 Hour | (Hide) |
| ” | Comp 1 | 8:00 AM | 5:00 PM | 0 Hour | (Hide) |
| ” | Comp 2 | 8:00 AM | 5:00 PM | 0 Hour | 0 Hour |

**QA checkpoint:** In Compliance mode (1/2), attendance on a genuine weekend or holiday should **never** show, regardless of mode.

> ⚠️ The In/Out/OT cells in the Weekend/Holiday rows above describe **Job Card/Attendance display only**. The Salary Sheet's WP & HP amount (Section 8.1) is independent of this display — it checks the attendance record directly, regardless of factory mode.

### 7.3 OT Calculation Pipeline (step by step)
```
1. Compute raw minutes from Shift end time to Out time
2. Subtract the grace period: grace = Designation.ot_grace_minutes (if > 0), otherwise Factory.ot_grace_minutes
   → minutes_past_grace = max(0, raw_minutes - grace)
3. minimum_ot_minutes bucketing (only if Factory.minimum_ot_minutes is set):
   for each 60-minute block, if the minimum minutes weren't worked, that block counts as 0
4. In Compliance mode (factory_no 1/2): apply the Factory.allow_ot_hour cap + Extra OT split (Comp2 only)
```

### 7.4 Meal Allowance (Tiffin/Night/Dinner) eligibility
- Not eligible on a leave or holiday day
- Eligible once `worked_hours >= min_*_hour`
- If `payment_way = monthly`: being eligible on even one day in the month pays the full fixed monthly amount; if `daily`: pays per eligible day × rate

---

## 8. SALARY SHEET — Column by Column (state after the 22-Jul-2026 fix)

### 8.1 WP & HP (Weekend/Holiday Pay) — Days and Amount

**Relationship table — what depends on what:**

| Factor | What it determines |
|---|---|
| **Attendance** — whether a real attendance record (in_time/out_time) exists on that WH/FL/GL day | Whether WP & HP is paid at all for that day — i.e. whether it counts toward Days — **no attendance = no pay, attended = paid** |
| **Factory compliance mode** (`factory_no`) | Which Designation field set applies: 0/null → **Main**, 1/2 → **Comp** |
| **Designation's Weekend/Holiday Allowance policy** (Main or Comp) | How the Amount is calculated (fixed rate / gross-based / basic-based / OT-based — see below) |
| **Designation's `is_ot_basis_wphp` flag** | Whether that day's entire worked span is **also** counted as extra OT, on top of the WP&HP amount (if the flag is off, WP&HP is still paid — the flag only controls the extra OT, not the base amount) |

```
Days   = number of genuine (non-swap) weekend/holiday days the employee ACTUALLY ATTENDED
         (has an in_time or out_time)
         *** not attending means it doesn't count — merely being a WH/FL/GL calendar
             date isn't enough ***
         *** a HrRegularToWeekend swap ('type=regular') day doesn't count here — that's
             paid as a normal working day instead ***
Amount = Based on the Designation's policy (paid in Compliance mode too, using the Comp field):
         factory_no 0/null → weekend_allowance_count (Main) + holiday_allowance (Main)
         factory_no 1/2    → weekend_allowance_count_comp (Comp) + holiday_allowance_comp (Comp)

         Policy options:
           fixed_amount      → Holiday Allowance rate × Days
           gross_month_day   → (Gross/30) × Days
           basic_working_day → (Basic/Working Days) × Days
           basic_104_2_5     → (Basic/104×2.5) × Days
           ot_by_worked_hour → real worked hours on those attended off-days × OT Rate
```
This Amount is added into **Payable Salary** and **Net Salary**. It is a **separate, additive payment** from the "extra OT" the Job Card shows because of `is_ot_basis_wphp` — both can apply at the same time (see the Weekend/Holiday rows in Section 7.2).

> ⚠️ **In Compliance mode, Job Card/Attendance hides the off-day/holiday's In/Out/OT (Section 7.2), but the Salary Sheet's WP & HP amount is still calculated** (using the Comp field) — these are two separate things: one is display suppression, the other is salary calculation.

### 8.2 Earn Days / Absent
```
Absent    = Total Absent (raw attendance-status "Absent" count; not_employed days are not counted here)
Earn Days = Total Month Days - Absent
```
> ⚠️ **QA risk note:** This formula only excludes days marked "Absent." "Not Employed" days (post-resignation days, future days) are already a separate bucket, so they're automatically not caught by this formula. But any other "gap" (e.g. days before a joining date, if that ever occurs) would still count as "Absent" and reduce Earn Days — that's expected, since that day genuinely is "Absent" too.

### 8.3 Per-Group Header (Print)
Each Group (by Department/Section) gets its own table in print, each containing:
1. Factory info header (Logo + Company Name + Address)
2. Column header row (S.N, Emp ID, Name ... every column repeated)
3. Then that group's employee rows + group total

The Grand Total also sits in its own table with headers at the end. When printed, each group starts on a new page.

### 8.4 Compliance Base (Deduction)
```
factory_no 0/null → deduction base = Gross Salary
factory_no 1/2    → deduction base = Basic Salary
```
This base applies to Absent deduction, Tax % — everywhere a percentage-based deduction is used.

---

## 9. FILTER — Shift (Multiple) — All Reports

Every report's filter form now has a **"Shift"** multi-select (previously missing from some reports, now added to all): Employee Report, Gate Pass Report, Asset Report, Bonus Sheet, Salary Report (Fixed/Production/Bonus/Wages), Monthly Report (Recruitment/Migration/Long-Absent/Increment). Multiple shifts can be selected together as a filter — based on the employee's `shift_id`.

> Exception: the **Employee Basic Info** report still has no filter at all (a separate, simpler controller — future work).

---

## 10. QA/QC — Step-by-Step Test Scenarios

Go through each of these as a checklist:

- [ ] **Factory mode switch:** Switch the Factory's `factory_no` between 0 → 1 → 2 and check the same employee's Job Card — do In/Out/OT/Extra OT change per the table in Section 7.2?
- [ ] **Grace override:** Set `ot_grace_minutes` on a Designation and confirm the result differs from the Factory's value (check both Job Card and Salary Sheet OT column)
- [ ] **Minimum OT bucketing (ANR only):** Set Factory `minimum_ot_minutes = 50`, confirm an employee working 5:11-6:00 (49 min) gets OT = 0 min, and working 5:11-6:05 (54 min) gets OT = 60 min
- [ ] **Designation Main vs Comp:** Set different values for the Main and Comp fields on the same designation — confirm factory_no 0 uses Main's amount, and factory_no 1/2 uses Comp's amount (in the Salary Sheet WP&HP column)
- [ ] **WP&HP — no attendance, no pay:** For an employee with several WH/FL/GL days in the period but no attendance record on any of them, confirm Salary Sheet shows WP&HP Days = 0 and Amount = 0
- [ ] **WP&HP — attended, gets paid:** For that same employee, have them attend 2 genuine off days (weekend/holiday, in/out recorded) — confirm WP&HP Days = 2, and the Amount matches the designation's Main/Comp policy
- [ ] **WP&HP + extra OT combo:** With a designation's `is_ot_basis_wphp` on, have an employee attend a genuine off day — confirm the WP&HP base amount and the Job Card's full-span extra OT both show separately (additive, not double-counted as one payment)
- [ ] **Weekend-to-Regular swap doesn't count toward WP&HP:** Add a `type='regular'` entry in `HrRegularToWeekend`, have the employee attend that date — confirm it is NOT counted in WP&HP Days (it's paid as ordinary salary instead)
- [ ] **Earn Days:** For an employee with 2 Absent days in a 30-day month, confirm Earn Days = 28
- [ ] **Not Employed exclusion:** Confirm a mid-month resigned employee's post-exit days are not counted in Absent/Earn Days
- [ ] **Genuine weekend, compliance mode:** Confirm any genuine (non-swap) weekend Job Card attendance stays hidden in Comp1/Comp2
- [ ] **Genuine holiday, compliance mode:** Confirm a genuine factory holiday's Job Card attendance is also hidden in Comp1/Comp2 (same as weekend), but the Salary Sheet's WP&HP (via the Comp field) still computes correctly
- [ ] **Salary Sheet print — group header:** When printing, confirm each department/section group shows a fresh Factory info + Column header, and each group starts on a new page
- [ ] **Shift filter:** On each report (Employee, Gate Pass, Asset, Bonus Sheet, Salary Report, Monthly Report), select multiple shifts in the Shift filter and confirm only employees on those shifts appear
- [ ] **Holiday FL/GL split:** Create one Festival-type Holiday and one General-type Holiday, confirm the Salary Sheet's FL and GL columns show separate counts
- [ ] **Live OT/Salary recalculation:** Without resaving an old attendance row (saved before a settings change), confirm Attendance Report/Salary Sheet/Job Card/Employee Profile all reflect the current `ot_grace_minutes`/`minimum_ot_minutes` — not a stale value
