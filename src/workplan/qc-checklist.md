# HRM Payroll/Attendance — Centralized QC/QA Checklist

Ei document ta likha hoyeche eiভাবে যেন **কেউ software ta আগে theke na jene**, শুধু ei checklist dekhe pura system টা QA/QC korte pare। Prottekta entity (Factory / Designation / Employee / Shift / Holiday / Weekend-to-Regular) er kon column ki kaje lage, seta Attendance/Job Card e kivabe effect kore, ar shesh e Report e (Salary Sheet, Job Card, OT Summary etc.) kivabe show hoy — sob ekjaygay, point akare deya holo.

Source: এই সব রুলস আলাদা আলাদা সময়ে `workplan/checklist.md` আর `workplan/complince.md` e deya hoyeche। Ei document sudhu segulake ekjaygay centralize kore, ekta clean reference banaise।

---

## 0. Shurute — 2ta Central Concept

### 0.1 Factory Compliance Mode (`factory_no`)
System ekta single "active" Factory row use kore (`hr_factory()` helper diye), ar oi factory row er **`factory_no`** column ekta global switch hisebe kaj kore:

| `factory_no` value | Mode | Ki mane |
|---|---|---|
| `0` ba `null` | **Actual** | Reality/actual attendance-salary — real punch time, real OT |
| `1` | **Comp 1** (Compliance) | Buyer-audit-safe version 1 — compressed/capped hours |
| `2` | **Comp 2** (Compliance) | Buyer-audit-safe version 2 — compressed hours + "Extra OT" alada column e |

**Effect kothay kothay pore** (eita mone rakha সবচেয়ে গুরুত্বপূর্ণ, kaj kaj eitar upor depend kore):
- Job Card / Attendance-এ In/Out time & OT show howa
- Salary Sheet-এ deduction base (Gross vs Basic)
- Designation-এর "(Main)" vs "(Comp)" fields kon ta use hobe
- Weekend-এ attendance dekhabe kina

### 0.2 "Weekend to Regular" Swap (`HrRegularToWeekend` table)
Ei table diye **section-wise, date-wise** ekta declared override kora hoy:
- `type = 'weekend'` → normally je din regular working day, ei din ke company-wide "weekend/off" banano hoise (e.g. eid er age extra off day)
- `type = 'regular'` → normally je din weekly holiday, oi din ke company-wide "working day" banano hoise (e.g. compensatory working Friday)

Eita **individual employee attendance na** — ei table e entry na thakle ekjon employee weekend e attend korle seta "worked weekend" e count hoy na, sudhu "Weekend" status e dekhabe (৩.১ number section e details ache).

---

## 1. FACTORY — Column-by-Column

| Column | Ki kore | Attendance/Job Card e effect | Report e effect |
|---|---|---|---|
| `factory_no` (Actual/Comp1/Comp2) | Compliance mode switch | Job card In/Out/OT display rules completely change (see Section 7 matrix) | Salary Sheet-এর deduction base (Gross vs Basic), Designation Main vs Comp field selection |
| `allow_ot_hour` (default 2, "dhore nilam 3" example e) | Compliance mode e ekdin e max koto OT "allowed"/cap dhora hobe | Comp1: cap er upore somoy hide kora hoy (out time chhoto dekhabe); Comp2: cap porjonto "OT", baki ta "Extra OT" column e alada dekhabe | Job Card OT/Extra OT columns |
| `ot_grace_minutes` ("OT Count After Shift End (min)") | Shift-end er koto min por theke OT count shuru hobe | Shift end 5:00 PM + grace 30 min hole, 5:20 e exit korle OT = 0, 5:40 e exit korle OT = 10 min | Job Card, Attendance, Salary Sheet OT column, OT Summary — **jekhaneu OT show/calculate hoy shobkhanei** |
| `minimum_ot_minutes` (নতুন, শুধু ANR e apply, 0/null hole kono effect nai) | OT ke পুরো-ঘন্টা block e ভাগ kore: প্রতি ঘন্টায় minimum eto minute kaj na korle sei ghontar OT = 0 | Grace-এর পরের প্রতিটি ৬০-মিনিট ব্লকে (e.g. minimum=50): block e ৫০+ min kaj thakle পুরো ৬০ min OT count hobe, তার কম hole সেই ব্লকের OT = 0 | Same as `ot_grace_minutes` — jekhane OT calculate hoy shobkhane |
| `weekend` (e.g. "Friday") | Kon din(gulo) weekly holiday | Weekend detection-এর base | Salary Sheet "Weekly holiday's" count, Attendance status |

---

## 2. DESIGNATION — Column-by-Column

Designation-এর effect **সেই designation-এর সব employee-দের jonno globally** kaj kore।

| Column | Ki kore | Effect |
|---|---|---|
| `grade` | Employee create korar somoy auto fill hoy, sob report e show hoy | Employee list, salary sheet "Grade" column |
| `approved_manpower` | Ei designation-এর under-এ max koto employee create kora jabe | Employee create validation |
| `attendance_bonus` | **Actual mode**-এ (factory_no 0/null) attendance bonus koto | Monthly full-attendance hole eita pabe. Factory holiday-র jonno na, weekend-to-regular-এর jonno pabe |
| `attendance_bonus_com` | **Compliance mode**-এ (factory_no 1/2) attendance bonus koto | Same rule, Actual-এর jaygay Comp field ta use hobe |
| `tiffin_allowance` / `min_tiffin_hour` | Minimum e hour kaj korle tiffin allowance koto pabe | Job Card, Salary — payment_way onujayi daily/monthly |
| `night_allowance` / `min_night_hour` | Same, night allowance-এর jonno | ” |
| `dinner_allowance` / `min_dinner_hour` | Same, dinner allowance-এর jonno | ” |
| `payment_way` (daily/monthly) | Tiffin/Night/Dinner payment daily naki lump-sum monthly | Salary Sheet meal total |
| **`weekend_allowance_count` (Main)** | Weekend/Holiday-তে কাজ করলে সেদিনের taka kivabe calculate hobe, **Actual mode (factory_no 0/null)**-এর jonno | Policy options: `gross/monthDay`, `basic/workingDay`, `basic/104*2.5`, `fixed Amount (Holiday Allowance)`, `OT by worked hour` |
| **`weekend_allowance_count_comp` (Comp)** ⭐ NEW | Same policy options, kintu **Compliance mode (factory_no 1/2)**-এর jonno আলাদা policy বেছে নেওয়া যায় | Factory compliance mode e ei field ta use hobe, Main na |
| **`holiday_allowance` (Main)** | Policy `fixed_amount` hole, Actual mode-এ ekdin er fix rate koto | WP & HP amount calculation |
| **`holiday_allowance_comp` (Comp)** ⭐ NEW | Same, kintu Compliance mode-এর jonno | ” |
| `gross_salary` | Employee create somoy default gross salary field-এ fill hobe | Employee profile |
| `car_fuel_allowance` / `phone_internet_allowance` | Employee create somoy default fill hobe | Employee profile, Salary Sheet |
| `ot_one_rate` / `ot_two_rate` | OT rate multiplier (type onujayi) | Salary Sheet OT Rate column |
| `is_ot_basis_wphp` | On thakle: employee **weekend/holiday-তে কাজ করলে সেই সময়টা OT হিসেবেও (WP&HP amount-এর পাশাপাশি অতিরিক্ত) count হবে**। Off/On WP&HP amount pabei — eita sudhu extra OT dibe kina control kore | Job Card OT column, Salary Sheet OT column |
| `is_ot_basis_main` | On thakle **Actual mode (factory_no 0/null)**-এ OT dekhabe | Job Card, Salary OT |
| `is_ot_basis_others_1` | On thakle **Comp 1 (factory_no 1)**-এ OT dekhabe | ” |
| `is_ot_basis_others_2` | On thakle **Comp 2 (factory_no 2)**-এ OT dekhabe | ” |
| **`ot_grace_minutes`** ⭐ NEW (Designation-level) | Factory-র `ot_grace_minutes`-কে override kore — **jodi designation-এর value > 0 hoy, সেটাই ব্যবহার হবে; factory-র value ব্যবহার হবে না।** Designation e 0/null thakle factory-র value automatically চলে আসবে | Job Card, Attendance, Salary, OT Summary — sob jaygay |

> **QA Test:** Designation-এ `ot_grace_minutes = 20` set kore dekhun, Factory-এর `ot_grace_minutes` (e.g. 10) ke ignore kore 20-i use hocche kina — Attendance edit page, Job Card, ebong Salary Sheet OT column tinta jaygay-e same result asha উচিত।

---

## 3. EMPLOYEE — Relevant Fields (calculation-related)

| Field | Effect |
|---|---|
| `shift_id` | Employee-র base shift — OT, In/Out time, Late calculation shob ei shift-এর upor depend kore |
| `designation_id` | Section 2-এর shob Designation rule ei employee-র jonno apply hoy |
| `employment_status` (regular/lefty/resign) + `exited_at` | Resign/exit korle exit date-এর pore-র প্রতিটি din **"not_employed"** — Absent/Present kono কিছুই count হবে না, deduction o hobe না |
| `section_id` | Weekend-to-Regular swap **section-wise** apply hoy — tai employee-র section thik na thakle swap kaj korbe na |
| `otherInfo()['salary_info']` (weekend_allowance_count/holiday_allowance override) | Employee-level override thakle Designation-র value-র upore priority pabe (WP&HP calculation-এ na — Attendance-এর অন্য জায়গায়) |

---

## 4. SHIFT — Column-by-Column

| Column | Example | Ki mane |
|---|---|---|
| `start_time` | 08:00 AM | Official shift start |
| `end_time` | 05:00 PM | Official shift end — OT ei somoy theke count shuru hoy (grace jog kore) |
| `start_allow_time` (Card Accept From) | 07:45 AM | Ei somoy theke punch accept shuru hobe In hisebe |
| `late_allow_time` (Red Marking On) | 08:10 AM | Ei somoyer pore punch korle "Late" mark hobe |
| `out_time_start` (Card Accept To) | 04:45 PM | Ei somoy theke porer punch OUT hisebe dhora hobe; OT cap-এর reference o eta |

**Example (checklist.md theke):**
Shift: Start 8:00 AM, End 5:00 PM, Start Allow 7:45 AM, Late Allow 8:10 AM, Out Time Start 4:45 PM
- 7:45 AM – 8:10 AM punch → In Time
- 4:45 PM – porer din 7:44 AM punch → Out Time
- **8:11 AM – 4:44 PM-এর মধ্যে punch করলে** সেটা attendance-এ In/Out kোনটাতেই count hobe na (machine log sync-এর somoy eta বিশেষভাবে check korte hobe)

---

## 5. HOLIDAY (Factory Holiday) — Fields

| Field | Effect |
|---|---|
| `from_date` / `to_date` | Ei date range-এ shob employee-র status = "Holiday" (attendance na thakleo) |
| `type` (`Festival` / `General`) | Salary Sheet-এ **আলাদা 2ta column**: `FL` (Festival holiday count) ebong `GL` (General holiday count) |

**Attendance/Job Card e effect:** Factory Holiday din-e status সবসময় "Holiday" dekhabe, weekend-এর moto na — worked/not-worked যাই hok, status "Holiday"-i thakbe (leave, weekend, ba absent এদের চেয়ে priority বেশি, Section 7-এর matrix dekhun)। **Compliance mode (factory_no 1/2)-এ genuine holiday-তে কাজ করলেও In/Out/OT সম্পূর্ণ hide thakbe** — ঠিক weekend-এর moto (Section 7.2-এর "Holiday" row dekhun)।

**Report e effect:**
- Salary Sheet: employee সেই WH/FL/GL day-তে **আসলেই attend/কাজ করেছে কিনা** সেটা দেখে WP & HP-এর "Days" গণনা হয় (calendar-এ কতগুলো WH/FL/GL date আছে সেটা না) — Section 8.1 dekhun।

---

## 6. WEEKEND TO REGULAR (`HrRegularToWeekend`) — Fields

| Field | Effect |
|---|---|
| `section_id` + `date` | Kon section-এর kon date-এ swap apply hobe |
| `type = 'regular'` | Ei date-এ normally weekly-off thakleo, sobar jonno "working day" banano hoise |
| `type = 'weekend'` | Ei date-এ normally working day thakleo, sobar jonno "off day" banano hoise |
| `status` | Active(1)/Inactive(0) — inactive hole eita kono effect ferbe na |

**Job Card/Attendance e effect (Section 7-এর "14/7/2026" scenario):** `type='regular'` din-e employee attend korle — In/Out time shift-এর within capped kore dekhabe (e.g. Out time real 10:00 PM hoileo shift end 5:00 PM porjonto capped dekhabe), OT = 0 shob mode-এ (behave kore ekta general/ordinary shift day-r moto)।

> ⚠️ **`HrRegularToWeekend`-এর সাথে WP & HP (`is_ot_basis_wphp`)-এর কোনো সম্পর্ক নেই।** Swap-করা (`type='regular'`) day আর "off day" thake na — সেটা normal working day hisebe salary pay hoy, WP & HP-তে count hoy na। WP & HP sudhu **genuine (non-swapped) weekend ba factory holiday** din-er jonno, ar shudhu tokhon jokhon employee সেই din **আসলেই attend করেছে** (Section 8.1 dekhun)।

---

## 7. ATTENDANCE / JOB CARD — Status ও OT Behavior Matrix

### 7.1 Status decide korar order (priority, upor theke niche)
1. **Not Employed** — exit date-এর pore ba future date
2. **Leave** — leave application thakle
3. **Weekend (compliance-only display)** — Compliance mode-এ weekend-to-regular din-e real attendance status dekhay (Present/Absent), "Weekend" na
4. **Holiday** — Factory Holiday date range-এর moddhe
5. **Weekend** — Employee-র weekly off day (ba `HrRegularToWeekend type='weekend'` swap)
6. **Present / Late / Absent** — normal attendance record onujayi

### 7.2 In/Out Time o OT — Scenario Table (checklist.md-এর original examples)

Employee: Designation "Manager", Shift (In 8:00 AM, Out 5:00 PM)

| Date/Scenario | Mode | In | Out | OT | Extra OT |
|---|---|---|---|---|---|
| **Regular day**, real out 10:00 PM | Actual | 8:00 AM | 10:00 PM | 5 Hour | (Hide) |
| ” | Comp 1 | 8:00 AM | 8:00 PM | 3 Hour | (Hide) |
| ” | Comp 2 | 8:00 AM | 10:00 PM | 3 Hour | 2 Hour |
| **Regular day**, real out 7:00 PM | Actual | 8:00 AM | 7:00 PM | 2 Hour | (Hide) |
| ” | Comp 1 | 8:00 AM | 7:00 PM | 2 Hour | (Hide) |
| ” | Comp 2 | 8:00 AM | 7:00 PM | 2 Hour | 0 Hour |
| **Weekend Day** (genuine, non-swapped) + designation `is_ot_basis_wphp`=on, real out 10:00 PM | Actual | 8:00 AM | 10:00 PM | 14 Hour (পুরো worked span OT) | (Hide) |
| ” | Comp 1 | — | — | — | (Hide) — **weekend-এ compliance mode কখনো attendance দেখাবে না** |
| ” | Comp 2 | — | — | — | (Hide) — same |
| **Factory Holiday** (genuine) + designation `is_ot_basis_wphp`=on, real out 10:00 PM | Actual | 8:00 AM | 10:00 PM | 14 Hour (পুরো worked span OT) — **weekend-এর মতোই** | (Hide) |
| ” | Comp 1 | — | — | — | (Hide) — **holiday-তেও compliance mode attendance দেখাবে না** |
| ” | Comp 2 | — | — | — | (Hide) — same |
| **Weekend→Regular swap** (`type='regular'`), real out 10:00 PM | Actual | 8:00 AM | 5:00 PM (capped) | 0 Hour | (Hide) |
| ” | Comp 1 | 8:00 AM | 5:00 PM | 0 Hour | (Hide) |
| ” | Comp 2 | 8:00 AM | 5:00 PM | 0 Hour | 0 Hour |

> ⚠️ উপরের Weekend/Holiday row-এ In/Out/OT-এর ঘরগুলো শুধু **Job Card/Attendance-এর display** বোঝাচ্ছে। Salary Sheet-এর WP & HP amount (Section 8.1) এই display থেকে independent — সেটা attendance record সরাসরি check kore, factory mode যাই hok।

**QA checkpoint:** Compliance mode (1/2)-এ genuine weekend-এ attendance **কখনোই** show hobe না, mode jai hok।

### 7.3 OT Calculation Pipeline (step by step)
```
1. Shift end time + Out time নিয়ে raw minutes বের করা হয়
2. Grace period বিয়োগ: grace = Designation.ot_grace_minutes (যদি >0) নাহলে Factory.ot_grace_minutes
   → minutes_past_grace = max(0, raw_minutes - grace)
3. minimum_ot_minutes bucketing (Factory.minimum_ot_minutes সেট থাকলেই শুধু):
   প্রতি ৬০ মিনিট ব্লকে minimum eta মিনিট কাজ না থাকলে সেই ব্লক 0 গণনা হবে
4. Compliance mode হলে (factory_no 1/2): Factory.allow_ot_hour দিয়ে cap + Extra OT split (শুধু Comp2)
```

### 7.4 Meal Allowance (Tiffin/Night/Dinner) eligibility
- Kono din-e leave ba holiday thakle eligible na
- `worked_hours >= min_*_hour` hole eligible
- `payment_way = monthly` hole: mash-e ekdin o eligible hole পুরো মাসের fixed amount pabe; `daily` hole eligible din-এর count × rate

---

## 8. SALARY SHEET — Column-by-Column (22-Jul-2026 fix-এর পরের অবস্থা)

### 8.1 WP & HP (Weekend/Holiday Pay) — Days ও Amount

**Kon jinis kon jinis-er upor depend kore (relationship table):**

| Factor | Ki determine kore |
|---|---|
| **Attendance** — sei WH/FL/GL din-e employee-র real attendance record ache kina (in_time/out_time) | WP & HP পাবে কিনা, mane Days count-এ oi din dhora hobe kina — **attend na thakle 0 taka, attend thakle taka pabe** |
| **Factory compliance mode** (`factory_no`) | Designation-এর kon field-set use hobe: 0/null → **Main**, 1/2 → **Comp** |
| **Designation-এর Weekend/Holiday Allowance policy** (Main ba Comp) | Amount-টা কীভাবে গণনা হবে (fixed rate/gross-based/basic-based/OT-based — niche dekhun) |
| **Designation-এর `is_ot_basis_wphp` flag** | WP&HP amount-এর পাশাপাশি সেই দিনের পুরো worked span **অতিরিক্ত OT** হিসেবেও গণনা হবে কিনা (flag off thakleo WP&HP pabei, শুধু extra OT-টা পাবে না) |

```
Days   = koto ta genuine (non-swap) weekend/holiday din-e employee ATTEND koreche (in_time ba out_time ache)
         *** attend na korle count hobe na — calendar-এ WH/FL/GL date thaklei hobe na ***
         *** HrRegularToWeekend swap ('type=regular') din eta count hoy na — oita normal working day ***
Amount = Designation-এর policy অনুযায়ী (Compliance mode হলেও পাবে, শুধু Comp field ব্যবহার হবে):
         factory_no 0/null → weekend_allowance_count (Main) + holiday_allowance (Main)
         factory_no 1/2    → weekend_allowance_count_comp (Comp) + holiday_allowance_comp (Comp)

         Policy options:
           fixed_amount      → Holiday Allowance rate × Days
           gross_month_day   → (Gross/30) × Days
           basic_working_day → (Basic/Working Days) × Days
           basic_104_2_5     → (Basic/104×2.5) × Days
           ot_by_worked_hour → sei attended off-day gulor real worked hours × OT Rate
```
Ei Amount **Payable Salary** ebong **Net Salary**-তে add hoy। Eta **`is_ot_basis_wphp`-এর কারণে job card-এ যে "extra OT" দেখায় সেটার থেকে সম্পূর্ণ আলাদা, additive payment** — দুইটা একসাথেই হতে পারে (Section 7.2-এর Weekend/Holiday row dekhun)।

> ⚠️ **Compliance mode-এ Job Card/Attendance-এ off-day/holiday-র In/Out/OT hide thake (Section 7.2), কিন্তু Salary Sheet-এর WP & HP amount তাও গণনা হয়** (Comp field দিয়ে) — এই দুইটা আলাদা জিনিস, একটা display suppression, আরেকটা salary calculation।

### 8.2 Earn Days / Absent
```
Absent    = Total Absent (raw attendance-status "Absent" count; not_employed দিন এতে ধরা হয় না)
Earn Days = Total Month Days - Absent
```
> ⚠️ **QA risk note:** Ei formula-তে শুধু "Absent"-marked din বাদ jay। "Not Employed" din (resign-er পরের din, future din) already alada bucket-এ, tai eigula automatically Earn Days-এ ধরা পড়ে না। Kintu অন্য কোনো "gap" (যেমন joining date-এর আগের din, jodi thake) থাকলে সেটা এখনো "Absent"-এই count হবে এবং Earn Days কমাবে — eita normal, karon oi din-tao "Absent"-ই।

### 8.3 Per-Group Header (Print)
প্রতিটা Group (Department/Section wise)-এর জন্য print-এ আলাদা টেবিল, প্রত্যেকটায়:
1. Factory info header (Logo + Company Name + Address)
2. Column header row (S.N, Emp ID, Name ... সব কলাম আবার নতুন করে)
3. তারপর সেই group-এর employee rows + group total

Grand Total-ও নিজের আলাদা header-সহ table-এ শেষে থাকবে। Print korle protyek group notun page-e শুরু hobe।

### 8.4 Compliance Base (Deduction)
```
factory_no 0/null → deduction base = Gross Salary
factory_no 1/2    → deduction base = Basic Salary
```
Ei base Absent deduction, Tax % — jekhaneu percentage-based deduction ache, shobkhane apply hoy।

---

## 9. FILTER — Shift (Multiple) — All Reports

Sob report-এর filter form-e ekhon **"Shift"** multi-select ase (age kichu report-e chhilo na, sob-e add kora hoise): Employee Report, Gate Pass Report, Asset Report, Bonus Sheet, Salary Report (Fixed/Production/Bonus/Wages), Monthly Report (Recruitment/Migration/Long-Absent/Increment). Multiple shift ekshathe select kore filter kora jabe — employee-র `shift_id` onujayi.

> ব্যতিক্রম: **Employee Basic Info** report-e ekhono kono filter নেই (আলাদা, ছোট controller — future kaj)।

---

## 10. QA/QC — Step-by-Step Test Scenarios

Checklist hisebe niche-r protita scenario test kore dekhun:

- [ ] **Factory mode switch:** Factory-র `factory_no` = 0 → 1 → 2 kore ekই employee-র Job Card dekhun — In/Out/OT/Extra OT Section 7.2-এর table onujayi change hocche kina
- [ ] **Grace override:** Designation-এ `ot_grace_minutes` set kore, Factory-র value-r cheye আলাদা result ashche kina check koro (Job Card + Salary Sheet OT column duitatei)
- [ ] **Minimum OT bucketing (ANR only):** Factory-এ `minimum_ot_minutes = 50` set kore, kono employee 5:11-6:00 (49 min) kaj korle OT = 0 min, ar 5:11-6:05 (54 min) kaj korle OT = 60 min dekhacche kina
- [ ] **Designation Main vs Comp:** Ekই designation-এ Main o Comp field আলাদা set kore, factory_no 0 e Main-এর amount, factory_no 1/2 e Comp-এর amount ashche kina (Salary Sheet WP&HP column-e)
- [ ] **WP&HP — attend na korle 0:** Ekjon employee-র period-e WH/FL/GL-এ 4-5ta din thakleo, sei din-gulote employee attend na korle (in/out time nai), WP&HP Days = 0, Amount = 0 ashche kina
- [ ] **WP&HP — attend korle taka pabe:** Sei employee genuine weekend/holiday-r 2din attend korle (in/out time ache), WP&HP Days = 2 dekhacche kina, ebong Amount designation-র Main/Comp policy onujayi thik ashche kina
- [ ] **WP&HP + Extra OT combo:** Ekta designation-e `is_ot_basis_wphp` on kore, ekjon employee genuine off-day-te attend korle — WP&HP Amount (base allowance) ebong Job Card-এর full-span OT (extra) duitai alada-alada ashche kina (double-pay na, ekta additive payment)
- [ ] **Weekend-to-Regular swap taka pabe na WP&HP-te:** `HrRegularToWeekend`-e `type='regular'` swap-করা din-e employee attend korle, sei din WP&HP Days-e count hocche na eta confirm koro (normal salary hisebei pay hobe)
- [ ] **Earn Days:** Ekjon employee-র 30 diner mash-e 2din Absent thakle, Earn Days = 28 ashche kina
- [ ] **Not Employed exclusion:** Mid-month resign kora employee-র exit-er porer din Absent/Earn Days-e count hocche na eta confirm koro
- [ ] **Genuine weekend, compliance mode:** Comp1/Comp2-e kono genuine (non-swap) weekend-e Job Card attendance thakleo, seta hide thakche kina
- [ ] **Genuine holiday, compliance mode:** Comp1/Comp2-e kono genuine factory holiday-te Job Card attendance thakleo, seta o hide thakche kina (weekend-er moto), kintu Salary Sheet-e WP&HP (Comp field diye) tao thik ashche kina
- [ ] **Salary Sheet print — group header:** Print korle protita department/section group-e notun kore Factory info + Column header dekhacche kina, ebong print-e protita group notun page-e shuru hocche kina
- [ ] **Shift filter:** Prottekta report-e (Employee, Gate Pass, Asset, Bonus Sheet, Salary Report, Monthly Report) Shift filter-e ekadhik shift select kore, sudhu oi shift-er employee-ra ashche kina
- [ ] **Holiday FL/GL split:** Ekta Festival type Holiday ebong ekta General type Holiday create kore, Salary Sheet-e FL ebong GL column-e alada alada count ashche kina
- [ ] **Live OT/Salary recalculation:** Kono purono attendance row (settings change howar age save kora) resave na kore-o, Attendance Report/Salary Sheet/Job Card/Employee Profile — sob jaygay notun `ot_grace_minutes`/`minimum_ot_minutes` onujayi OT dekhacche kina (stale/purono value na)
