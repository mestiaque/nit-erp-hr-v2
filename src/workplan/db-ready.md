// ============================================================
// HR Management System — DBML Schema
// For: https://dbdiagram.io
// Note: bn_ prefix = Bangla text fields
// ============================================================

// ------ LOOKUP / MASTER TABLES ------

Table hr_geo_locations {
  id         int       [pk, increment]
  name       varchar   [not null, note: "max 150"]
  bn_name    varchar   [note: "max 150"]
  parent_id  int       [ref: > hr_geo_locations.id, note: "null = top level (Country)"]
  type       varchar   [not null, note: "country | division | district | police_station | post_office"]
  status     tinyint   [not null, default: 1]
  created_at timestamp [not null, default: `now()`]
  created_by int
  updated_at timestamp
  updated_by int
}

Table religions {
  id         int       [pk, increment]
  name       varchar   [not null, note: "max 100"]
  bn_name    varchar   [note: "max 100"]
  code       varchar   [note: "max 20"]
  status     tinyint   [not null, default: 1]
  created_at timestamp [not null, default: `now()`]
  created_by int
  updated_at timestamp
  updated_by int
}

Table marital_statuses {
  id         int       [pk, increment]
  name       varchar   [not null, note: "max 100"]
  bn_name    varchar   [note: "max 100"]
  code       varchar   [note: "max 20"]
  status     tinyint   [not null, default: 1]
  created_at timestamp [not null, default: `now()`]
  created_by int
  updated_at timestamp
  updated_by int
}

Table sexes {
  id         int       [pk, increment]
  name       varchar   [not null, note: "max 50"]
  bn_name    varchar   [note: "max 50"]
  code       varchar   [note: "max 10"]
  status     tinyint   [not null, default: 1]
  created_at timestamp [not null, default: `now()`]
  created_by int
  updated_at timestamp
  updated_by int
}

Table payment_methods {
  id         int       [pk, increment]
  name       varchar   [not null, note: "max 100"]
  bn_name    varchar   [note: "max 100"]
  code       varchar   [note: "max 20"]
  status     tinyint   [not null, default: 1]
  created_at timestamp [not null, default: `now()`]
  created_by int
  updated_at timestamp
  updated_by int
}

Table working_places {
  id         int       [pk, increment]
  name       varchar   [not null, note: "max 150"]
  bn_name    varchar   [note: "max 150"]
  code       varchar   [note: "max 30"]
  status     tinyint   [not null, default: 1]
  created_at timestamp [not null, default: `now()`]
  created_by int
  updated_at timestamp
  updated_by int
}

Table classifications {
  id          int       [pk, increment]
  name        varchar   [not null, note: "max 150"]
  bn_name     varchar   [note: "max 150"]
  description text
  status      tinyint   [not null, default: 1]
  created_at  timestamp [not null, default: `now()`]
  created_by  int
  updated_at  timestamp
  updated_by  int
}

Table leave_infos {
  id          int       [pk, increment]
  name        varchar   [not null, note: "max 150"]
  bn_name     varchar   [note: "max 150"]
  days        int       [not null]
  description text
  status      tinyint   [not null, default: 1]
  created_at  timestamp [not null, default: `now()`]
  created_by  int
  updated_at  timestamp
  updated_by  int
}

Table salary_keys {
  id         int       [pk, increment]
  medical    decimal   [note: "10,2"]
  lunch      decimal   [note: "10,2"]
  transport  decimal   [note: "10,2"]
  status     tinyint   [not null, default: 1]
  created_at timestamp [not null, default: `now()`]
  created_by int
  updated_at timestamp
  updated_by int
}

// ------ FACTORY & SHIFT ------

Table factories {
  id               int       [pk, increment]
  name             varchar   [not null, note: "max 200"]
  bn_name          varchar   [note: "max 200"]
  address          text
  bn_address       text
  contact_number   varchar   [note: "max 30"]
  authority_sign   varchar   [note: "file path, max 255"]
  allow_ot_hour    decimal   [note: "5,2"]
  stamp_amount     decimal   [note: "10,2"]
  status           tinyint   [not null, default: 1]
  created_at       timestamp [not null, default: `now()`]
  created_by       int
  updated_at       timestamp
  updated_by       int
}

Table shifts {
  id               int       [pk, increment]
  name             varchar   [not null, note: "max 100"]
  bn_name          varchar   [note: "max 100"]
  start_time       time      [not null]
  end_time         time      [not null]
  start_allow_time time
  late_allow_time  time
  out_time_start   time
  status           tinyint   [not null, default: 1]
  created_at       timestamp [not null, default: `now()`]
  created_by       int
  updated_at       timestamp
  updated_by       int
}

// ------ ORG STRUCTURE ------

Table departments {
  id                  int       [pk, increment]
  name                varchar   [not null, note: "max 150"]
  bn_name             varchar   [note: "max 150"]
  description         text
  head_of_department  int       [ref: > employees.id, note: "nullable"]
  status              tinyint   [not null, default: 1]
  created_at          timestamp [not null, default: `now()`]
  created_by          int
  updated_at          timestamp
  updated_by          int
}

Table sections {
  id            int       [pk, increment]
  name          varchar   [not null, note: "max 150"]
  bn_name       varchar   [note: "max 150"]
  department_id int       [not null, ref: > departments.id]
  description   text
  status        tinyint   [not null, default: 1]
  created_at    timestamp [not null, default: `now()`]
  created_by    int
  updated_at    timestamp
  updated_by    int
}

Table sub_sections {
  id                   int       [pk, increment]
  name                 varchar   [not null, note: "max 150"]
  bn_name              varchar   [note: "max 150"]
  department_id        int       [not null, ref: > departments.id]
  section_id           int       [not null, ref: > sections.id]
  salary_type          varchar   [note: "max 50"]
  approve_man_power    int
  roster_shift_id      int       [ref: > shifts.id]
  is_individual_roster tinyint   [default: 0]
  status               tinyint   [not null, default: 1]
  created_at           timestamp [not null, default: `now()`]
  created_by           int
  updated_at           timestamp
  updated_by           int
}

Table floor_lines {
  id            int       [pk, increment]
  floor_name    varchar   [not null, note: "max 150"]
  bn_floor_name varchar   [note: "max 150"]
  line_name     varchar   [not null, note: "max 150"]
  bn_line_name  varchar   [note: "max 150"]
  line_capacity int
  status        tinyint   [not null, default: 1]
  created_at    timestamp [not null, default: `now()`]
  created_by    int
  updated_at    timestamp
  updated_by    int
}

// ------ DESIGNATION ------

Table designations {
  id                       int       [pk, increment]
  name                     varchar   [not null, note: "max 150"]
  bn_name                  varchar   [note: "max 150"]
  grade                    varchar   [note: "max 20"]
  approved_manpower        int
  department_id            int       [ref: > departments.id]
  section_id               int       [ref: > sections.id]
  attendance_bonus         decimal   [note: "10,2"]
  attendance_bonus_com     decimal   [note: "10,2"]
  tiffin_allowance         decimal   [note: "10,2"]
  min_tiffin_hour          decimal   [note: "5,2"]
  night_allowance          decimal   [note: "10,2"]
  min_night_hour           decimal   [note: "5,2"]
  dinner_allowance         decimal   [note: "10,2"]
  min_dinner_hour          decimal   [note: "5,2"]
  payment_way              varchar   [note: "max 50"]
  weekend_allowance_count  int
  holiday_allowance        decimal   [note: "10,2"]
  gross_salary             decimal   [note: "12,2"]
  car_fuel_allowance       decimal   [note: "10,2"]
  phone_internet_allowance decimal   [note: "10,2"]
  extra_facility           text
  ot_one_rate              decimal   [note: "6,4"]
  ot_two_rate              decimal   [note: "6,4"]
  report_to                int       [ref: > designations.id, note: "self-ref, nullable"]
  responsibilities         text
  follow_up_team           text
  status                   tinyint   [not null, default: 1]
  created_at               timestamp [not null, default: `now()`]
  created_by               int
  updated_at               timestamp
  updated_by               int
}

// ------ BONUS ------

Table bonus_titles {
  id          int       [pk, increment]
  title       varchar   [not null, note: "max 150"]
  bn_title    varchar   [note: "max 150"]
  code        varchar   [note: "max 30"]
  description text
  status      tinyint   [not null, default: 1]
  created_at  timestamp [not null, default: `now()`]
  created_by  int
  updated_at  timestamp
  updated_by  int
}

Table bonus_policies {
  id               int       [pk, increment]
  bonus_title_id   int       [not null, ref: > bonus_titles.id]
  policy_name      varchar   [not null, note: "max 200"]
  bn_policy_name   varchar   [note: "max 200"]
  department_id    int       [ref: > departments.id]
  section_id       int       [ref: > sections.id]
  sub_section_id   int       [ref: > sub_sections.id]
  month_range_from int       [note: "1–12"]
  month_range_to   int       [note: "1–12"]
  apply_on         varchar   [note: "Basic / Gross / Production etc., max 50"]
  type             varchar   [not null, note: "fixed | percent"]
  amount           decimal   [not null, note: "12,2"]
  status           tinyint   [not null, default: 1]
  created_at       timestamp [not null, default: `now()`]
  created_by       int
  updated_at       timestamp
  updated_by       int
}

// ------ EMPLOYEE (stub) ------

// ============================================================
// EMPLOYEE — Full Schema
// All bn_ fields = Bangla input
// Common cols: status, created_at/by, updated_at/by
// ============================================================

Table employees {
  id                int       [pk, increment]
  name              varchar   [not null, note: "max 150"]
  bn_name           varchar   [note: "max 150"]
  employee_id       varchar   [not null, unique, note: "HR employee code, max 30"]
  join_date         date      [not null]
  classification_id int       [ref: > classifications.id]
  department_id     int       [ref: > departments.id]
  section_id        int       [ref: > sections.id]
  sub_section_id    int       [ref: > sub_sections.id]
  floor_line_id     int       [ref: > floor_lines.id]
  designation_id    int       [ref: > designations.id]
  working_place_id  int       [ref: > working_places.id]
  shift_id          int       [ref: > shifts.id]
  weekend           varchar   [note: "e.g. Friday, Friday+Saturday"]
  personal_contact  varchar   [note: "max 20"]
  emergency_contact varchar   [note: "max 20"]
  comp_one          tinyint   [not null, default: 0, note: "0/1"]
  comp_two          tinyint   [not null, default: 0, note: "0/1"]
  status            tinyint   [not null, default: 1]
  created_at        timestamp [not null, default: `now()`]
  created_by        int
  updated_at        timestamp
  updated_by        int
}

// ------ BASIC INFO ------

Table employee_basic_infos {
  id                          int       [pk, increment]
  employee_id                 int       [not null, ref: > employees.id]
  father_name                 varchar   [note: "max 150"]
  bn_father_name              varchar   [note: "max 150"]
  mother_name                 varchar   [note: "max 150"]
  bn_mother_name              varchar   [note: "max 150"]
  marital_status_id           int       [ref: > marital_statuses.id]
  spouse_name                 varchar   [note: "max 150"]
  bn_spouse_name              varchar   [note: "max 150"]
  sex_id                      int       [ref: > sexes.id]
  children_boys               int       [default: 0]
  children_girls              int       [default: 0]
  payment_method_id           int       [ref: > payment_methods.id]
  religion_id                 int       [ref: > religions.id]
  birth_date                  date
  blood_group                 varchar   [note: "A+/A-/B+/B-/AB+/AB-/O+/O-"]
  nationality_country_id      int       [ref: > geo_locations.id]
  national_id_no              varchar   [note: "max 30"]
  birth_registration_no       varchar   [note: "max 30"]
  passport_no                 varchar   [note: "max 30"]
  driving_license_no          varchar   [note: "max 30"]
  special_id_sign             text
  bn_special_id_sign          text
  educational_experience      text
  bn_educational_experience   text
  job_experience              text
  bn_job_experience           text
  previous_organization       varchar   [note: "max 200"]
  bn_previous_organization    varchar   [note: "max 200"]
  reference_name              varchar   [note: "max 150"]
  bn_reference_name           varchar   [note: "max 150"]
  reference_designation       varchar   [note: "max 150"]
  bn_reference_designation    varchar   [note: "max 150"]
  reference_card_no           varchar   [note: "max 50"]
  bn_reference_card_no        varchar   [note: "max 50"]
  reference_mobile_no         varchar   [note: "max 20"]
  bn_reference_mobile_no      varchar   [note: "max 20"]
  status                      tinyint   [not null, default: 1]
  created_at                  timestamp [not null, default: `now()`]
  created_by                  int
  updated_at                  timestamp
  updated_by                  int
}

// ------ SALARY INFO ------

Table employee_salary_infos {
  id                  int       [pk, increment]
  employee_id         int       [not null, ref: > employees.id]
  gross_salary        decimal   [note: "12,2"]
  gross_salary_comp1  decimal   [note: "12,2 — for comp_one"]
  gross_salary_comp2  decimal   [note: "12,2 — for comp_two"]
  payment_method_id   int       [ref: > payment_methods.id]
  bank_ac_or_phone    varchar   [note: "bank account or mobile number, max 50"]
  car_fuel            decimal   [note: "10,2"]
  phone_internet      decimal   [note: "10,2"]
  extra_facility      text
  tax                 decimal   [note: "10,2"]
  tax_calculate_by    varchar   [note: "manual | auto, max 20"]
  effective_date      date
  status              tinyint   [not null, default: 1]
  created_at          timestamp [not null, default: `now()`]
  created_by          int
  updated_at          timestamp
  updated_by          int
}

// ------ ADDRESS ------

Table employee_addresses {
  id                      int       [pk, increment]
  employee_id             int       [not null, ref: > employees.id]
  type                    varchar   [not null, note: "permanent | present"]

  // Geo refs (district → police_station → post_office all in geo_locations)
  district_id             int       [ref: > geo_locations.id]
  police_station_id       int       [ref: > geo_locations.id]
  post_office_id          int       [ref: > geo_locations.id]
  post_office             varchar   [note: "custom override if not in geo, max 100"]
  bn_post_office          varchar   [note: "max 100"]
  village                 varchar   [note: "max 150"]
  bn_village              varchar   [note: "max 150"]

  status                  tinyint   [not null, default: 1]
  created_at              timestamp [not null, default: `now()`]
  created_by              int
  updated_at              timestamp
  updated_by              int
}

// ------ NOMINEE ------

Table employee_nominees {
  id                int       [pk, increment]
  employee_id       int       [not null, ref: > employees.id]
  photo             varchar   [note: "file path, max 255"]
  name              varchar   [not null, note: "max 150"]
  bn_name           varchar   [note: "max 150"]
  district_id       int       [ref: > geo_locations.id]
  police_station_id int       [ref: > geo_locations.id]
  post_office_id    int       [ref: > geo_locations.id]
  post_office       varchar   [note: "max 100"]
  bn_post_office    varchar   [note: "max 100"]
  village           varchar   [note: "max 150"]
  bn_village        varchar   [note: "max 150"]
  nid_no            varchar   [note: "max 30"]
  mobile_no         varchar   [note: "max 20"]
  relation          varchar   [note: "max 100"]
  bn_relation       varchar   [note: "max 100"]
  age               int
  net_payment       decimal   [note: "5,2 — percentage"]
  provident_fund    decimal   [note: "5,2 — percentage"]
  insurance         decimal   [note: "5,2 — percentage"]
  accident_fine     decimal   [note: "5,2 — percentage"]
  profit            decimal   [note: "5,2 — percentage"]
  others            decimal   [note: "5,2 — percentage"]
  status            tinyint   [not null, default: 1]
  created_at        timestamp [not null, default: `now()`]
  created_by        int
  updated_at        timestamp
  updated_by        int
}

// ------ AGE VERIFICATION ------

Table employee_age_verifications {
  id                  int       [pk, increment]
  employee_id         int       [not null, ref: > employees.id]
  physical_ability    text
  identification_mark text
  age_years           int
  verified_date       date
  status              tinyint   [not null, default: 1]
  created_at          timestamp [not null, default: `now()`]
  created_by          int
  updated_at          timestamp
  updated_by          int
}

// ------ LEFTY & RESIGN ------

Table employee_separations {
  id               int       [pk, increment]
  employee_id      int       [not null, ref: > employees.id]
  status           varchar   [not null, note: "regular | lefty | resign | transfer"]
  remarks          text
  effective_date   date
  final_settlement varchar   [note: "earn_leave_only | earn_leave_with_service | earn_leave_without_service"]
  with_paid        tinyint   [default: 0, note: "0/1"]
  created_at       timestamp [not null, default: `now()`]
  created_by       int
  updated_at       timestamp
  updated_by       int
}

// ------ FINAL SETTLEMENT ------

Table employee_final_settlements {
  id                   int       [pk, increment]
  employee_id          int       [not null, ref: > employees.id]
  absent_date          date
  first_letter_date    date
  second_letter_date   date
  third_letter_date    date
  selected_letter_print varchar  [note: "1st | 2nd | 3rd"]
  status               tinyint   [not null, default: 1]
  created_at           timestamp [not null, default: `now()`]
  created_by           int
  updated_at           timestamp
  updated_by           int
}

// ------ SALARY INCREMENT ------

Table employee_salary_increments {
  id               int       [pk, increment]
  employee_id      int       [not null, ref: > employees.id]
  classification_id int      [ref: > classifications.id]
  department_id    int       [ref: > departments.id]
  section_id       int       [ref: > sections.id]
  designation_id   int       [ref: > designations.id]
  previous_salary  decimal   [not null, note: "12,2"]
  increment_amount decimal   [not null, note: "12,2"]
  new_salary       decimal   [not null, note: "12,2"]
  increment_date   date      [not null]
  status           tinyint   [not null, default: 1]
  created_at       timestamp [not null, default: `now()`]
  created_by       int
  updated_at       timestamp
  updated_by       int
}

// ------ OTHER EARNINGS & DEDUCTIONS ------

Table employee_other_transactions {
  id           int       [pk, increment]
  employee_id  int       [not null, ref: > employees.id]
  txn_date     date      [not null]
  advance_iou  decimal   [note: "10,2 — positive=advance, negative=recovery"]
  ot_adjust    decimal   [note: "10,2 — +/-"]
  day_adjust   decimal   [note: "5,2 — +/-"]
  earnings     decimal   [note: "10,2"]
  deductions   decimal   [note: "10,2"]
  remarks      text
  status       tinyint   [not null, default: 1]
  created_at   timestamp [not null, default: `now()`]
  created_by   int
  updated_at   timestamp
  updated_by   int
}

// ------ LEAVE ------

Table employee_leaves {
  id               int       [pk, increment]
  employee_id      int       [not null, ref: > employees.id]
  application_date date      [not null]
  application_no   varchar   [unique, note: "auto-generated, max 30"]
  leave_type_id    int       [not null, ref: > leave_infos.id]
  leave_from       date      [not null]
  leave_to         date      [not null]
  reason           text
  remarks          text
  status           tinyint   [not null, default: 1, note: "0=pending,1=approved,2=rejected"]
  created_at       timestamp [not null, default: `now()`]
  created_by       int
  updated_at       timestamp
  updated_by       int
}

Table attendances {
  id                    bigint    [pk, increment]
  employee_id           int       [not null, ref: > employees.id]
  date                  date      [not null]
  in_time               time
  out_time              time
  total_working_minute  int       [note: "calculated — in minutes"]
  total_ot_minute       int       [note: "calculated — in minutes"]

  // Device punch location (from biometric/app at punch time)
  latitude              decimal   [note: "10,7 — device GPS at punch"]
  longitude             decimal   [note: "10,7 — device GPS at punch"]

  // Assigned/verified location (factory or zone)
  location_lat          decimal   [note: "10,7 — assigned location lat"]
  location_long         decimal   [note: "10,7 — assigned location long"]

  status                varchar   [note: "present | absent | late | half_day | holiday | leave"]
  via                   varchar   [note: "device | app | manual"]
  verify_type           varchar   [note: "fingerprint | face | card | manual"]
  device_sn             varchar   [note: "biometric device serial number"]
  remarks               text

  created_at            timestamp [not null, default: `now()`]
  created_by            int
  updated_at            timestamp
  updated_by            int

  indexes {
    (employee_id, date) [unique, name: "uq_emp_date"]
    date                [name: "idx_date"]
    employee_id         [name: "idx_employee"]
  }
}

// ============================================================
// locks — Period/Module based lock control
// One row per (module + period). If locked, no edit allowed.
// ============================================================

Table locks {
  id           int       [pk, increment]

  // What is being locked
  module       varchar   [not null, note: "attendance | job_card | increment | salary | bonus | leave | settlement | other_transaction"]

  // Lock scope — month+year for monthly modules
  // For non-periodic modules (e.g. increment) use lock_date only
  lock_year    smallint  [note: "e.g. 2024"]
  lock_month   tinyint   [note: "1–12, null if not monthly"]
  lock_date    date      [note: "exact date lock, null if monthly"]

  // Optional: lock for a specific factory/department only
  factory_id   int       [ref: > factories.id, note: "null = all factories"]
  department_id int      [ref: > departments.id, note: "null = all departments"]

  is_locked    tinyint   [not null, default: 0, note: "0=unlocked, 1=locked"]
  locked_at    timestamp [note: "when it was locked"]
  locked_by    int       [note: "user id who locked"]
  unlocked_at  timestamp [note: "when it was last unlocked"]
  unlocked_by  int       [note: "user id who unlocked"]
  remarks      text

  created_at   timestamp [not null, default: `now()`]
  created_by   int
  updated_at   timestamp
  updated_by   int

  indexes {
    (module, lock_year, lock_month, factory_id, department_id) [unique, name: "uq_lock_period"]
    module       [name: "idx_module"]
    is_locked    [name: "idx_locked"]
  }
}

// ============================================================
// HOLIDAY
// ============================================================

Table holidays {
  id          int       [pk, increment]
  purpose     varchar   [not null, note: "e.g. Eid-ul-Fitr, National Day"]
  bn_purpose  varchar   [note: "max 200"]
  type        varchar   [not null, note: "public | optional | factory | compensatory"]
  from_date   date      [not null]
  to_date     date      [not null, note: "same as from_date for single-day"]
  remarks     text
  status      tinyint   [not null, default: 1]
  created_at  timestamp [not null, default: `now()`]
  created_by  int
  updated_at  timestamp
  updated_by  int

  indexes {
    (from_date, to_date) [name: "idx_holiday_range"]
  }
}

// ============================================================
// REGULAR DAY → WEEKEND CONVERSION
// Marks a normally working day as a weekend for a section
// ============================================================

Table regular_to_weekends {
  id          int     [pk, increment]
  section_id  int     [not null, ref: > sections.id]
  date        date    [not null]
  type        varchar [not null, note: "weekend | half_day"]
  status      tinyint [not null, default: 1]
  created_at  timestamp [not null, default: `now()`]
  created_by  int
  updated_at  timestamp
  updated_by  int

  indexes {
    (section_id, date) [unique, name: "uq_section_date"]
  }
}

// ============================================================
// SHIFT ROSTER — Section level (group roster)
// Applied to all employees of a sub_section for a date range
// ============================================================

Table shift_rosters {
  id             int     [pk, increment]
  roster_type    varchar [not null, note: "section | individual"]
  department_id  int     [ref: > departments.id]
  section_id     int     [ref: > sections.id]
  sub_section_id int     [ref: > sub_sections.id]
  shift_id       int     [not null, ref: > shifts.id]
  from_date      date    [not null]
  to_date        date    [not null]
  remarks        text
  status         tinyint [not null, default: 1]
  created_at     timestamp [not null, default: `now()`]
  created_by     int
  updated_at     timestamp
  updated_by     int

  indexes {
    (sub_section_id, from_date, to_date) [name: "idx_roster_section_period"]
  }
}

// ============================================================
// SHIFT ROSTER — Individual employee override
// Used when sub_section.is_individual_roster = 1
// or for specific employee exceptions
// ============================================================

Table shift_roster_employees {
  id           int     [pk, increment]
  employee_id  int     [not null, ref: > employees.id]
  shift_id     int     [not null, ref: > shifts.id]
  roster_date  date    [not null, note: "one row per employee per day"]
  remarks      text
  status       tinyint [not null, default: 1]
  created_at   timestamp [not null, default: `now()`]
  created_by   int
  updated_at   timestamp
  updated_by   int

  indexes {
    (employee_id, roster_date) [unique, name: "uq_emp_roster_date"]
  }
}

Ref: "regular_to_weekends"."id" < "regular_to_weekends"."date"

//////////////////////////////////////////////////////////////////////////////////////////////////


// ============================================================
// HR Management System — Full DBML Schema
// All tables prefixed with hr_
// bn_ prefix = Bangla text fields
// ============================================================

// ────────────────────────────────────────────────
// LOOKUP / MASTER TABLES
// ────────────────────────────────────────────────

Table hr_geo_locations {
  id         int       [pk, increment]
  name       varchar   [not null, note: "max 150"]
  bn_name    varchar   [note: "max 150"]
  parent_id  int       [ref: > hr_geo_locations.id, note: "null = Country (top level)"]
  type       varchar   [not null, note: "country | division | district | police_station | post_office"]
  status     tinyint   [not null, default: 1]
  created_at timestamp [not null, default: `now()`]
  created_by int
  updated_at timestamp
  updated_by int
}

Table hr_religions {
  id         int       [pk, increment]
  name       varchar   [not null, note: "max 100"]
  bn_name    varchar   [note: "max 100"]
  code       varchar   [note: "max 20"]
  status     tinyint   [not null, default: 1]
  created_at timestamp [not null, default: `now()`]
  created_by int
  updated_at timestamp
  updated_by int
}

Table hr_marital_statuses {
  id         int       [pk, increment]
  name       varchar   [not null, note: "max 100"]
  bn_name    varchar   [note: "max 100"]
  code       varchar   [note: "max 20"]
  status     tinyint   [not null, default: 1]
  created_at timestamp [not null, default: `now()`]
  created_by int
  updated_at timestamp
  updated_by int
}

Table hr_sexes {
  id         int       [pk, increment]
  name       varchar   [not null, note: "max 50"]
  bn_name    varchar   [note: "max 50"]
  code       varchar   [note: "max 10"]
  status     tinyint   [not null, default: 1]
  created_at timestamp [not null, default: `now()`]
  created_by int
  updated_at timestamp
  updated_by int
}

Table hr_payment_methods {
  id         int       [pk, increment]
  name       varchar   [not null, note: "max 100"]
  bn_name    varchar   [note: "max 100"]
  code       varchar   [note: "max 20"]
  status     tinyint   [not null, default: 1]
  created_at timestamp [not null, default: `now()`]
  created_by int
  updated_at timestamp
  updated_by int
}

Table hr_working_places {
  id         int       [pk, increment]
  name       varchar   [not null, note: "max 150"]
  bn_name    varchar   [note: "max 150"]
  code       varchar   [note: "max 30"]
  status     tinyint   [not null, default: 1]
  created_at timestamp [not null, default: `now()`]
  created_by int
  updated_at timestamp
  updated_by int
}

Table hr_classifications {
  id          int       [pk, increment]
  name        varchar   [not null, note: "max 150"]
  bn_name     varchar   [note: "max 150"]
  description text
  status      tinyint   [not null, default: 1]
  created_at  timestamp [not null, default: `now()`]
  created_by  int
  updated_at  timestamp
  updated_by  int
}

Table hr_leave_infos {
  id          int       [pk, increment]
  name        varchar   [not null, note: "max 150"]
  bn_name     varchar   [note: "max 150"]
  days        int       [not null]
  description text
  status      tinyint   [not null, default: 1]
  created_at  timestamp [not null, default: `now()`]
  created_by  int
  updated_at  timestamp
  updated_by  int
}

Table hr_salary_keys {
  id         int       [pk, increment]
  medical    decimal   [note: "10,2"]
  lunch      decimal   [note: "10,2"]
  transport  decimal   [note: "10,2"]
  status     tinyint   [not null, default: 1]
  created_at timestamp [not null, default: `now()`]
  created_by int
  updated_at timestamp
  updated_by int
}

// ────────────────────────────────────────────────
// FACTORY & SHIFT
// ────────────────────────────────────────────────

Table hr_factories {
  id               int       [pk, increment]
  name             varchar   [not null, note: "max 200"]
  bn_name          varchar   [note: "max 200"]
  address          text
  bn_address       text
  contact_number   varchar   [note: "max 30"]
  authority_sign   varchar   [note: "file path, max 255"]
  allow_ot_hour    decimal   [note: "5,2"]
  stamp_amount     decimal   [note: "10,2"]
  status           tinyint   [not null, default: 1]
  created_at       timestamp [not null, default: `now()`]
  created_by       int
  updated_at       timestamp
  updated_by       int
}

Table hr_shifts {
  id               int       [pk, increment]
  name             varchar   [not null, note: "max 100"]
  bn_name          varchar   [note: "max 100"]
  start_time       time      [not null]
  end_time         time      [not null]
  start_allow_time time
  late_allow_time  time
  out_time_start   time
  status           tinyint   [not null, default: 1]
  created_at       timestamp [not null, default: `now()`]
  created_by       int
  updated_at       timestamp
  updated_by       int
}

// ────────────────────────────────────────────────
// ORG STRUCTURE
// ────────────────────────────────────────────────

Table hr_departments {
  id                  int       [pk, increment]
  name                varchar   [not null, note: "max 150"]
  bn_name             varchar   [note: "max 150"]
  description         text
  head_of_department  int       [ref: > hr_employees.id, note: "nullable"]
  status              tinyint   [not null, default: 1]
  created_at          timestamp [not null, default: `now()`]
  created_by          int
  updated_at          timestamp
  updated_by          int
}

Table hr_sections {
  id            int       [pk, increment]
  name          varchar   [not null, note: "max 150"]
  bn_name       varchar   [note: "max 150"]
  department_id int       [not null, ref: > hr_departments.id]
  description   text
  status        tinyint   [not null, default: 1]
  created_at    timestamp [not null, default: `now()`]
  created_by    int
  updated_at    timestamp
  updated_by    int
}

Table hr_sub_sections {
  id                   int       [pk, increment]
  name                 varchar   [not null, note: "max 150"]
  bn_name              varchar   [note: "max 150"]
  department_id        int       [not null, ref: > hr_departments.id]
  section_id           int       [not null, ref: > hr_sections.id]
  salary_type          varchar   [note: "max 50"]
  approve_man_power    int
  roster_shift_id      int       [ref: > hr_shifts.id]
  is_individual_roster tinyint   [default: 0]
  status               tinyint   [not null, default: 1]
  created_at           timestamp [not null, default: `now()`]
  created_by           int
  updated_at           timestamp
  updated_by           int
}

Table hr_floor_lines {
  id            int       [pk, increment]
  floor_name    varchar   [not null, note: "max 150"]
  bn_floor_name varchar   [note: "max 150"]
  line_name     varchar   [not null, note: "max 150"]
  bn_line_name  varchar   [note: "max 150"]
  line_capacity int
  status        tinyint   [not null, default: 1]
  created_at    timestamp [not null, default: `now()`]
  created_by    int
  updated_at    timestamp
  updated_by    int
}

// ────────────────────────────────────────────────
// DESIGNATION
// ────────────────────────────────────────────────

Table hr_designations {
  id                       int       [pk, increment]
  name                     varchar   [not null, note: "max 150"]
  bn_name                  varchar   [note: "max 150"]
  grade                    varchar   [note: "max 20"]
  approved_manpower        int
  department_id            int       [ref: > hr_departments.id]
  section_id               int       [ref: > hr_sections.id]
  attendance_bonus         decimal   [note: "10,2"]
  attendance_bonus_com     decimal   [note: "10,2"]
  tiffin_allowance         decimal   [note: "10,2"]
  min_tiffin_hour          decimal   [note: "5,2"]
  night_allowance          decimal   [note: "10,2"]
  min_night_hour           decimal   [note: "5,2"]
  dinner_allowance         decimal   [note: "10,2"]
  min_dinner_hour          decimal   [note: "5,2"]
  payment_way              varchar   [note: "max 50"]
  weekend_allowance_count  int
  holiday_allowance        decimal   [note: "10,2"]
  gross_salary             decimal   [note: "12,2"]
  car_fuel_allowance       decimal   [note: "10,2"]
  phone_internet_allowance decimal   [note: "10,2"]
  extra_facility           text
  ot_one_rate              decimal   [note: "6,4"]
  ot_two_rate              decimal   [note: "6,4"]
  report_to                int       [ref: > hr_designations.id, note: "self-ref, nullable"]
  responsibilities         text
  follow_up_team           text
  status                   tinyint   [not null, default: 1]
  created_at               timestamp [not null, default: `now()`]
  created_by               int
  updated_at               timestamp
  updated_by               int
}

// ────────────────────────────────────────────────
// BONUS
// ────────────────────────────────────────────────

Table hr_bonus_titles {
  id          int       [pk, increment]
  title       varchar   [not null, note: "max 150"]
  bn_title    varchar   [note: "max 150"]
  code        varchar   [note: "max 30"]
  description text
  status      tinyint   [not null, default: 1]
  created_at  timestamp [not null, default: `now()`]
  created_by  int
  updated_at  timestamp
  updated_by  int
}

Table hr_bonus_policies {
  id               int       [pk, increment]
  bonus_title_id   int       [not null, ref: > hr_bonus_titles.id]
  policy_name      varchar   [not null, note: "max 200"]
  bn_policy_name   varchar   [note: "max 200"]
  department_id    int       [ref: > hr_departments.id]
  section_id       int       [ref: > hr_sections.id]
  sub_section_id   int       [ref: > hr_sub_sections.id]
  month_range_from int       [note: "1-12"]
  month_range_to   int       [note: "1-12"]
  apply_on         varchar   [note: "Basic | Gross | Production, max 50"]
  type             varchar   [not null, note: "fixed | percent"]
  amount           decimal   [not null, note: "12,2"]
  status           tinyint   [not null, default: 1]
  created_at       timestamp [not null, default: `now()`]
  created_by       int
  updated_at       timestamp
  updated_by       int
}

// ────────────────────────────────────────────────
// EMPLOYEE
// ────────────────────────────────────────────────

Table hr_employees {
  id                int       [pk, increment]
  name              varchar   [not null, note: "max 150"]
  bn_name           varchar   [note: "max 150"]
  employee_id       varchar   [not null, unique, note: "HR code, max 30"]
  join_date         date      [not null]
  classification_id int       [ref: > hr_classifications.id]
  department_id     int       [ref: > hr_departments.id]
  section_id        int       [ref: > hr_sections.id]
  sub_section_id    int       [ref: > hr_sub_sections.id]
  floor_line_id     int       [ref: > hr_floor_lines.id]
  designation_id    int       [ref: > hr_designations.id]
  working_place_id  int       [ref: > hr_working_places.id]
  shift_id          int       [ref: > hr_shifts.id]
  weekend           varchar   [note: "Friday | Friday+Saturday"]
  personal_contact  varchar   [note: "max 20"]
  emergency_contact varchar   [note: "max 20"]
  comp_one          tinyint   [not null, default: 0, note: "0/1"]
  comp_two          tinyint   [not null, default: 0, note: "0/1"]
  status            tinyint   [not null, default: 1]
  created_at        timestamp [not null, default: `now()`]
  created_by        int
  updated_at        timestamp
  updated_by        int
}

Table hr_employee_basic_infos {
  id                        int       [pk, increment]
  employee_id               int       [not null, unique, ref: > hr_employees.id]
  father_name               varchar   [note: "max 150"]
  bn_father_name            varchar   [note: "max 150"]
  mother_name               varchar   [note: "max 150"]
  bn_mother_name            varchar   [note: "max 150"]
  marital_status_id         int       [ref: > hr_marital_statuses.id]
  spouse_name               varchar   [note: "max 150"]
  bn_spouse_name            varchar   [note: "max 150"]
  sex_id                    int       [ref: > hr_sexes.id]
  children_boys             int       [default: 0]
  children_girls            int       [default: 0]
  payment_method_id         int       [ref: > hr_payment_methods.id]
  religion_id               int       [ref: > hr_religions.id]
  birth_date                date
  blood_group               varchar   [note: "A+/A-/B+/B-/AB+/AB-/O+/O-"]
  nationality_country_id    int       [ref: > hr_geo_locations.id]
  national_id_no            varchar   [note: "max 30"]
  birth_registration_no     varchar   [note: "max 30"]
  passport_no               varchar   [note: "max 30"]
  driving_license_no        varchar   [note: "max 30"]
  special_id_sign           text
  bn_special_id_sign        text
  educational_experience    text
  bn_educational_experience text
  job_experience            text
  bn_job_experience         text
  previous_organization     varchar   [note: "max 200"]
  bn_previous_organization  varchar   [note: "max 200"]
  reference_name            varchar   [note: "max 150"]
  bn_reference_name         varchar   [note: "max 150"]
  reference_designation     varchar   [note: "max 150"]
  bn_reference_designation  varchar   [note: "max 150"]
  reference_card_no         varchar   [note: "max 50"]
  bn_reference_card_no      varchar   [note: "max 50"]
  reference_mobile_no       varchar   [note: "max 20"]
  bn_reference_mobile_no    varchar   [note: "max 20"]
  status                    tinyint   [not null, default: 1]
  created_at                timestamp [not null, default: `now()`]
  created_by                int
  updated_at                timestamp
  updated_by                int
}

Table hr_employee_salary_infos {
  id                  int       [pk, increment]
  employee_id         int       [not null, ref: > hr_employees.id]
  gross_salary        decimal   [note: "12,2"]
  gross_salary_comp1  decimal   [note: "12,2"]
  gross_salary_comp2  decimal   [note: "12,2"]
  payment_method_id   int       [ref: > hr_payment_methods.id]
  bank_ac_or_phone    varchar   [note: "max 50"]
  car_fuel            decimal   [note: "10,2"]
  phone_internet      decimal   [note: "10,2"]
  extra_facility      text
  tax                 decimal   [note: "10,2"]
  tax_calculate_by    varchar   [note: "manual | auto"]
  effective_date      date
  status              tinyint   [not null, default: 1]
  created_at          timestamp [not null, default: `now()`]
  created_by          int
  updated_at          timestamp
  updated_by          int
}

Table hr_employee_addresses {
  id                int       [pk, increment]
  employee_id       int       [not null, ref: > hr_employees.id]
  type              varchar   [not null, note: "permanent | present"]
  district_id       int       [ref: > hr_geo_locations.id]
  police_station_id int       [ref: > hr_geo_locations.id]
  post_office_id    int       [ref: > hr_geo_locations.id]
  post_office       varchar   [note: "custom override, max 100"]
  bn_post_office    varchar   [note: "max 100"]
  village           varchar   [note: "max 150"]
  bn_village        varchar   [note: "max 150"]
  status            tinyint   [not null, default: 1]
  created_at        timestamp [not null, default: `now()`]
  created_by        int
  updated_at        timestamp
  updated_by        int

  indexes {
    (employee_id, type) [unique, name: "uq_emp_address_type"]
  }
}

Table hr_employee_nominees {
  id                int       [pk, increment]
  employee_id       int       [not null, ref: > hr_employees.id]
  photo             varchar   [note: "file path, max 255"]
  name              varchar   [not null, note: "max 150"]
  bn_name           varchar   [note: "max 150"]
  district_id       int       [ref: > hr_geo_locations.id]
  police_station_id int       [ref: > hr_geo_locations.id]
  post_office_id    int       [ref: > hr_geo_locations.id]
  post_office       varchar   [note: "max 100"]
  bn_post_office    varchar   [note: "max 100"]
  village           varchar   [note: "max 150"]
  bn_village        varchar   [note: "max 150"]
  nid_no            varchar   [note: "max 30"]
  mobile_no         varchar   [note: "max 20"]
  relation          varchar   [note: "max 100"]
  bn_relation       varchar   [note: "max 100"]
  age               int
  net_payment       decimal   [note: "5,2 — %"]
  provident_fund    decimal   [note: "5,2 — %"]
  insurance         decimal   [note: "5,2 — %"]
  accident_fine     decimal   [note: "5,2 — %"]
  profit            decimal   [note: "5,2 — %"]
  others            decimal   [note: "5,2 — %"]
  status            tinyint   [not null, default: 1]
  created_at        timestamp [not null, default: `now()`]
  created_by        int
  updated_at        timestamp
  updated_by        int
}

Table hr_employee_age_verifications {
  id                  int       [pk, increment]
  employee_id         int       [not null, unique, ref: > hr_employees.id]
  physical_ability    text
  identification_mark text
  age_years           int
  verified_date       date
  status              tinyint   [not null, default: 1]
  created_at          timestamp [not null, default: `now()`]
  created_by          int
  updated_at          timestamp
  updated_by          int
}

Table hr_employee_separations {
  id               int       [pk, increment]
  employee_id      int       [not null, ref: > hr_employees.id]
  status           varchar   [not null, note: "regular | lefty | resign | transfer"]
  remarks          text
  effective_date   date
  final_settlement varchar   [note: "earn_leave_only | earn_leave_with_service | earn_leave_without_service"]
  with_paid        tinyint   [default: 0, note: "0/1"]
  created_at       timestamp [not null, default: `now()`]
  created_by       int
  updated_at       timestamp
  updated_by       int
}

Table hr_employee_final_settlements {
  id                    int       [pk, increment]
  employee_id           int       [not null, ref: > hr_employees.id]
  absent_date           date
  first_letter_date     date
  second_letter_date    date
  third_letter_date     date
  selected_letter_print varchar   [note: "1st | 2nd | 3rd"]
  status                tinyint   [not null, default: 1]
  created_at            timestamp [not null, default: `now()`]
  created_by            int
  updated_at            timestamp
  updated_by            int
}

Table hr_employee_salary_increments {
  id                int       [pk, increment]
  employee_id       int       [not null, ref: > hr_employees.id]
  classification_id int       [ref: > hr_classifications.id]
  department_id     int       [ref: > hr_departments.id]
  section_id        int       [ref: > hr_sections.id]
  designation_id    int       [ref: > hr_designations.id]
  previous_salary   decimal   [not null, note: "12,2"]
  increment_amount  decimal   [not null, note: "12,2"]
  new_salary        decimal   [not null, note: "12,2"]
  increment_date    date      [not null]
  status            tinyint   [not null, default: 1]
  created_at        timestamp [not null, default: `now()`]
  created_by        int
  updated_at        timestamp
  updated_by        int
}

Table hr_employee_other_transactions {
  id           int       [pk, increment]
  employee_id  int       [not null, ref: > hr_employees.id]
  txn_date     date      [not null]
  advance_iou  decimal   [note: "10,2 — positive=advance, negative=recovery"]
  ot_adjust    decimal   [note: "10,2 — +/-"]
  day_adjust   decimal   [note: "5,2 — +/-"]
  earnings     decimal   [note: "10,2"]
  deductions   decimal   [note: "10,2"]
  remarks      text
  status       tinyint   [not null, default: 1]
  created_at   timestamp [not null, default: `now()`]
  created_by   int
  updated_at   timestamp
  updated_by   int
}

Table hr_employee_leaves {
  id               int       [pk, increment]
  employee_id      int       [not null, ref: > hr_employees.id]
  application_date date      [not null]
  application_no   varchar   [unique, note: "auto-generated, max 30"]
  leave_type_id    int       [not null, ref: > hr_leave_infos.id]
  leave_from       date      [not null]
  leave_to         date      [not null]
  reason           text
  remarks          text
  status           tinyint   [not null, default: 1, note: "0=pending | 1=approved | 2=rejected"]
  created_at       timestamp [not null, default: `now()`]
  created_by       int
  updated_at       timestamp
  updated_by       int
}

// ────────────────────────────────────────────────
// ATTENDANCE
// ────────────────────────────────────────────────

Table hr_attendances {
  id                   bigint    [pk, increment]
  employee_id          int       [not null, ref: > hr_employees.id]
  date                 date      [not null]
  in_time              time
  out_time             time
  total_working_minute int       [note: "in minutes"]
  total_ot_minute      int       [note: "in minutes"]
  latitude             decimal   [note: "10,7 — device GPS at punch"]
  longitude            decimal   [note: "10,7 — device GPS at punch"]
  location_lat         decimal   [note: "10,7 — assigned location"]
  location_long        decimal   [note: "10,7 — assigned location"]
  status               varchar   [note: "present | absent | late | half_day | holiday | leave"]
  via                  varchar   [note: "device | app | manual"]
  verify_type          varchar   [note: "fingerprint | face | card | manual"]
  device_sn            varchar   [note: "biometric device serial"]
  remarks              text
  created_at           timestamp [not null, default: `now()`]
  created_by           int
  updated_at           timestamp
  updated_by           int

  indexes {
    (employee_id, date) [unique, name: "uq_hr_emp_date"]
    date                [name: "idx_hr_att_date"]
    employee_id         [name: "idx_hr_att_emp"]
  }
}

// ────────────────────────────────────────────────
// LOCKS
// ────────────────────────────────────────────────

Table hr_locks {
  id            int       [pk, increment]
  module        varchar   [not null, note: "attendance | job_card | increment | salary | bonus | leave | settlement | other_transaction"]
  lock_year     smallint  [note: "e.g. 2024"]
  lock_month    tinyint   [note: "1-12, null if not monthly"]
  lock_date     date      [note: "exact date, null if monthly"]
  factory_id    int       [ref: > hr_factories.id, note: "null = all factories"]
  department_id int       [ref: > hr_departments.id, note: "null = all departments"]
  is_locked     tinyint   [not null, default: 0, note: "0=unlocked | 1=locked"]
  locked_at     timestamp
  locked_by     int
  unlocked_at   timestamp
  unlocked_by   int
  remarks       text
  created_at    timestamp [not null, default: `now()`]
  created_by    int
  updated_at    timestamp
  updated_by    int

  indexes {
    (module, lock_year, lock_month, factory_id, department_id) [unique, name: "uq_hr_lock_period"]
    module    [name: "idx_hr_lock_module"]
    is_locked [name: "idx_hr_lock_status"]
  }
}

// ────────────────────────────────────────────────
// HOLIDAY
// ────────────────────────────────────────────────

Table hr_holidays {
  id          int       [pk, increment]
  purpose     varchar   [not null, note: "e.g. Eid-ul-Fitr, max 200"]
  bn_purpose  varchar   [note: "max 200"]
  type        varchar   [not null, note: "public | optional | factory | compensatory"]
  from_date   date      [not null]
  to_date     date      [not null, note: "same as from_date for single-day"]
  remarks     text
  status      tinyint   [not null, default: 1]
  created_at  timestamp [not null, default: `now()`]
  created_by  int
  updated_at  timestamp
  updated_by  int

  indexes {
    (from_date, to_date) [name: "idx_hr_holiday_range"]
  }
}

// ────────────────────────────────────────────────
// REGULAR → WEEKEND CONVERSION
// ────────────────────────────────────────────────

Table hr_regular_to_weekends {
  id          int       [pk, increment]
  section_id  int       [not null, ref: > hr_sections.id]
  date        date      [not null]
  type        varchar   [not null, note: "weekend | half_day"]
  status      tinyint   [not null, default: 1]
  created_at  timestamp [not null, default: `now()`]
  created_by  int
  updated_at  timestamp
  updated_by  int

  indexes {
    (section_id, date) [unique, name: "uq_hr_section_weekend_date"]
  }
}

// ────────────────────────────────────────────────
// SHIFT ROSTER
// ────────────────────────────────────────────────

Table hr_shift_rosters {
  id             int       [pk, increment]
  roster_type    varchar   [not null, note: "section | individual"]
  department_id  int       [ref: > hr_departments.id]
  section_id     int       [ref: > hr_sections.id]
  sub_section_id int       [ref: > hr_sub_sections.id]
  shift_id       int       [not null, ref: > hr_shifts.id]
  from_date      date      [not null]
  to_date        date      [not null]
  remarks        text
  status         tinyint   [not null, default: 1]
  created_at     timestamp [not null, default: `now()`]
  created_by     int
  updated_at     timestamp
  updated_by     int

  indexes {
    (sub_section_id, from_date, to_date) [name: "idx_hr_roster_period"]
  }
}

Table hr_shift_roster_employees {
  id          int       [pk, increment]
  employee_id int       [not null, ref: > hr_employees.id]
  shift_id    int       [not null, ref: > hr_shifts.id]
  roster_date date      [not null]
  remarks     text
  status      tinyint   [not null, default: 1]
  created_at  timestamp [not null, default: `now()`]
  created_by  int
  updated_at  timestamp
  updated_by  int

  indexes {
    (employee_id, roster_date) [unique, name: "uq_hr_emp_roster_date"]
  }
}

// ============================================================
// ALL EXPLICIT Refs (for dbdiagram.io relation lines)
// ============================================================

// Geo self-ref
ref">Ref: hr_geo_locations.parent_id > hr_geo_locations.id

// Org hierarchy
ref">Ref: hr_sections.department_id > hr_departments.id
ref">Ref: hr_sub_sections.department_id > hr_departments.id
ref">Ref: hr_sub_sections.section_id > hr_sections.id
ref">Ref: hr_sub_sections.roster_shift_id > hr_shifts.id
ref">Ref: hr_departments.head_of_department > hr_employees.id

// Designation
ref">Ref: hr_designations.department_id > hr_departments.id
ref">Ref: hr_designations.section_id > hr_sections.id
ref">Ref: hr_designations.report_to > hr_designations.id

// Bonus
ref">Ref: hr_bonus_policies.bonus_title_id > hr_bonus_titles.id
ref">Ref: hr_bonus_policies.department_id > hr_departments.id
ref">Ref: hr_bonus_policies.section_id > hr_sections.id
ref">Ref: hr_bonus_policies.sub_section_id > hr_sub_sections.id

// Employee core
ref">Ref: hr_employees.classification_id > hr_classifications.id
ref">Ref: hr_employees.department_id > hr_departments.id
ref">Ref: hr_employees.section_id > hr_sections.id
ref">Ref: hr_employees.sub_section_id > hr_sub_sections.id
ref">Ref: hr_employees.floor_line_id > hr_floor_lines.id
ref">Ref: hr_employees.designation_id > hr_designations.id
ref">Ref: hr_employees.working_place_id > hr_working_places.id
ref">Ref: hr_employees.shift_id > hr_shifts.id

// Employee sub-tables
ref">Ref: hr_employee_basic_infos.employee_id > hr_employees.id
ref">Ref: hr_employee_basic_infos.marital_status_id > hr_marital_statuses.id
ref">Ref: hr_employee_basic_infos.sex_id > hr_sexes.id
ref">Ref: hr_employee_basic_infos.payment_method_id > hr_payment_methods.id
ref">Ref: hr_employee_basic_infos.religion_id > hr_religions.id
ref">Ref: hr_employee_basic_infos.nationality_country_id > hr_geo_locations.id

ref">Ref: hr_employee_salary_infos.employee_id > hr_employees.id
ref">Ref: hr_employee_salary_infos.payment_method_id > hr_payment_methods.id

ref">Ref: hr_employee_addresses.employee_id > hr_employees.id
ref">Ref: hr_employee_addresses.district_id > hr_geo_locations.id
ref">Ref: hr_employee_addresses.police_station_id > hr_geo_locations.id
ref">Ref: hr_employee_addresses.post_office_id > hr_geo_locations.id

ref">Ref: hr_employee_nominees.employee_id > hr_employees.id
ref">Ref: hr_employee_nominees.district_id > hr_geo_locations.id
ref">Ref: hr_employee_nominees.police_station_id > hr_geo_locations.id
ref">Ref: hr_employee_nominees.post_office_id > hr_geo_locations.id

ref">Ref: hr_employee_age_verifications.employee_id > hr_employees.id
ref">Ref: hr_employee_separations.employee_id > hr_employees.id
ref">Ref: hr_employee_final_settlements.employee_id > hr_employees.id

ref">Ref: hr_employee_salary_increments.employee_id > hr_employees.id
ref">Ref: hr_employee_salary_increments.classification_id > hr_classifications.id
ref">Ref: hr_employee_salary_increments.department_id > hr_departments.id
ref">Ref: hr_employee_salary_increments.section_id > hr_sections.id
ref">Ref: hr_employee_salary_increments.designation_id > hr_designations.id

ref">Ref: hr_employee_other_transactions.employee_id > hr_employees.id

ref">Ref: hr_employee_leaves.employee_id > hr_employees.id
ref">Ref: hr_employee_leaves.leave_type_id > hr_leave_infos.id

// Attendance
ref">Ref: hr_attendances.employee_id > hr_employees.id

// Locks
ref">Ref: hr_locks.factory_id > hr_factories.id
ref">Ref: hr_locks.department_id > hr_departments.id

// Roster
ref">Ref: hr_regular_to_weekends.section_id > hr_sections.id
ref">Ref: hr_shift_rosters.department_id > hr_departments.id
ref">Ref: hr_shift_rosters.section_id > hr_sections.id
ref">Ref: hr_shift_rosters.sub_section_id > hr_sub_sections.id
ref">Ref: hr_shift_rosters.shift_id > hr_shifts.id
ref">Ref: hr_shift_roster_employees.employee_id > hr_employees.id
ref">Ref: hr_shift_roster_employees.shift_id > hr_shifts.id