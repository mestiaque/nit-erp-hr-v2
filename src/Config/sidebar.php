<?php

return [

    // HR Center
    [
        'group_title' => '',
        [
            'title'      => 'HR Management',
            'icon'       => 'fa-solid fa-people-group',
            'icon_color' => 'text-primary',
            'permission' => '',
            'order'      => 10,
            'children'   => [
                [
                    'title'      => 'HR Dashboard',
                    'icon'       => 'fa-solid fa-gauge-high',
                    'icon_color' => 'text-primary',
                    'permission' => '',
                    'route'      => '/admin/hr-center',
                ],
                [
                    'title'      => 'Basic Info.',
                    'icon'       => 'fa-solid fa-gear',
                    'icon_color' => 'text-info',
                    'permission' => 'hr_factory',
                    'children'   => [
                        [
                            'title'      => 'Classification',
                            'icon'       => 'fa-solid fa-arrow-right',
                            'route'      => '/admin/hr-center/masters/classifications',
                            'icon_color' => 'text-warning',
                            'permission' => 'hr_classification',
                        ],
                        [
                            'title'      => 'Asset Category',
                            'icon'       => 'fa-solid fa-arrow-right',
                            'route'      => '/admin/hr-center/masters/asset-categories',
                            'icon_color' => 'text-warning',
                            'permission' => 'hr_asset_category',
                        ],
                        [
                            'title'      => 'Block/Line',
                            'icon'       => 'fa-solid fa-arrow-right',
                            'route'      => '/admin/hr-center/masters/floor-lines',
                            'icon_color' => 'text-warning',
                            'permission' => 'hr_block',
                        ],

                        [
                            'title'      => 'Bonus Policy',
                            'icon'       => 'fa-solid fa-arrow-right',
                            'route'      => '/admin/hr-center/masters/bonus-policies',
                            'icon_color' => 'text-warning',
                            'permission' => 'hr_bonus_policy',
                        ],
                        [
                            'title'      => 'Bonus Title',
                            'icon'       => 'fa-solid fa-arrow-right',
                            'route'      => '/admin/hr-center/masters/bonus-titles',
                            'icon_color' => 'text-warning',
                            'permission' => 'hr_bonus_title',
                        ],
                        [
                            'title'      => 'Country',
                            'icon'       => 'fa-solid fa-arrow-right',
                            'route'      => '/admin/hr-center/masters/countries',
                            'icon_color' => 'text-warning',
                            'permission' => 'hr_country',
                        ],
                        [
                            'title'      => 'Division',
                            'icon'       => 'fa-solid fa-arrow-right',
                            'route'      => '/admin/hr-center/masters/divisions',
                            'icon_color' => 'text-warning',
                            'permission' => 'hr_division',
                        ],

                        [
                            'title'      => 'District',
                            'icon'       => 'fa-solid fa-arrow-right',
                            'route'      => '/admin/hr-center/masters/districts',
                            'icon_color' => 'text-warning',
                            'permission' => 'hr_district',
                        ],
                        [
                            'title'      => 'Po. Station',
                            'icon'       => 'fa-solid fa-arrow-right',
                            'route'      => '/admin/hr-center/masters/police-stations',
                            'icon_color' => 'text-warning',
                            'permission' => 'hr_police_station',
                        ],
                        [
                            'title'      => 'Department',
                            'icon'       => 'fa-solid fa-arrow-right',
                            'route'      => '/admin/hr-center/masters/departments',
                            'icon_color' => 'text-warning',
                            'permission' => 'hr_department',
                        ],
                        [
                            'title'      => 'Designation',
                            'icon'       => 'fa-solid fa-arrow-right',
                            'route'      => '/admin/hr-center/masters/designations',
                            'icon_color' => 'text-warning',
                            'permission' => 'hr_designation',
                        ],
                        [
                            'title'      => 'Factory',
                            'icon'       => 'fa-solid fa-arrow-right',
                            'route'      => '/admin/hr-center/masters/factories',
                            'icon_color' => 'text-warning',
                            'permission' => 'hr_factory',
                        ],
                        [
                            'title'      => 'Leave Info.',
                            'icon'       => 'fa-solid fa-arrow-right',
                            'route'      => '/admin/hr-center/masters/leave-infos',
                            'icon_color' => 'text-warning',
                            'permission' => 'hr_leave_info',
                        ],
                        [
                            'title'      => 'Marital Status',
                            'icon'       => 'fa-solid fa-arrow-right',
                            'route'      => '/admin/hr-center/masters/marital-statuses',
                            'icon_color' => 'text-warning',
                            'permission' => 'hr_marital_status',
                        ],

                        [
                            'title'      => 'Production Bonus(%)',
                            'icon'       => 'fa-solid fa-arrow-right',
                            'route'      => '/admin/hr-center/masters/production-bonuses',
                            'icon_color' => 'text-warning',
                            'permission' => 'hr_production_bonus',
                        ],
                        [
                            'title'      => 'Religion',
                            'icon'       => 'fa-solid fa-arrow-right',
                            'route'      => '/admin/hr-center/masters/religions',
                            'icon_color' => 'text-warning',
                            'permission' => 'hr_religion',
                        ],
                        [
                            'title'      => 'Sex',
                            'icon'       => 'fa-solid fa-arrow-right',
                            'route'      => '/admin/hr-center/masters/sexes',
                            'icon_color' => 'text-warning',
                            'permission' => 'hr_sex',
                        ],
                        [
                            'title'      => 'Salary Keys',
                            'icon'       => 'fa-solid fa-arrow-right',
                            'route'      => '/admin/hr-center/masters/salary-keys',
                            'icon_color' => 'text-warning',
                            'permission' => 'hr_salary_keys',
                        ],
                        [
                            'title'      => 'Salary Payment Mode',
                            'icon'       => 'fa-solid fa-arrow-right',
                            'route'      => '/admin/hr-center/masters/payment-methods',
                            'icon_color' => 'text-warning',
                            'permission' => 'hr_salary_payment_mode',
                        ],
                        [
                            'title'      => 'Shift',
                            'icon'       => 'fa-solid fa-arrow-right',
                            'route'      => '/admin/hr-center/masters/shifts',
                            'icon_color' => 'text-warning',
                            'permission' => 'hr_shift',
                        ],
                        [
                            'title'      => 'Section',
                            'icon'       => 'fa-solid fa-arrow-right',
                            'route'      => '/admin/hr-center/masters/sections',
                            'icon_color' => 'text-warning',
                            'permission' => 'hr_section',
                        ],
                        [
                            'title'      => 'Sub Section',
                            'icon'       => 'fa-solid fa-arrow-right',
                            'route'      => '/admin/hr-center/masters/sub-sections',
                            'icon_color' => 'text-warning',
                            'permission' => 'hr_sub_section',
                        ],
                        [
                            'title'      => 'Weeks',
                            'icon'       => 'fa-solid fa-arrow-right',
                            'route'      => '/admin/hr-center/masters/weeks',
                            'icon_color' => 'text-warning',
                            'permission' => 'hr_week',
                        ],
                        [
                            'title'      => 'Working Place',
                            'icon'       => 'fa-solid fa-arrow-right',
                            'route'      => '/admin/hr-center/masters/working-places',
                            'icon_color' => 'text-warning',
                            'permission' => 'hr_working_place',
                        ],
                    ]
                ],
                [
                    'title'      => 'Employee',
                    'icon'       => 'fa-solid fa-arrow-right',
                    'route'      => '/admin/hr-center/employees',
                    'icon_color' => 'text-warning',
                    'permission' => 'hr_employee'
                ],
                [
                    'title'      => 'Attendance',
                    'icon'       => 'fa-solid fa-calendar-check',
                    'route'      => '/admin/hr-center/attendances',
                    'icon_color' => 'text-success',
                    'permission' => 'hr_attendance',
                ],
                [
                    'title'      => 'Factory Holiday',
                    'icon'       => 'fa-solid fa-arrow-right',
                    'route'      => '/admin/hr-center/holidays',
                    'icon_color' => 'text-warning',
                    'permission' => 'hr_factory_holiday',
                ],
                [
                    'title'      => 'Employee Gate Pass',
                    'icon'       => 'fa-solid fa-right-to-bracket',
                    'route'      => '/admin/hr-center/gate-passes',
                    'icon_color' => 'text-warning',
                    'permission' => 'hr_employee_gate_pass',
                ],
                [
                    'title'      => 'Asset Management',
                    'icon'       => 'fa-solid fa-laptop',
                    'route'      => '/admin/hr-center/employee-assets',
                    'icon_color' => 'text-warning',
                    'permission' => 'hr_employee_asset',
                ],
                [
                    'title'      => 'Weekend to Regular',
                    'icon'       => 'fa-solid fa-arrow-right',
                    'route'      => '/admin/hr-center/regular-to-weekend',
                    'icon_color' => 'text-warning',
                    'permission' => 'hr_regular_to_weekend',
                ],
                [
                    'title'      => 'Shift Rostering',
                    'icon'       => 'fa-solid fa-calendar-days',
                    'route'      => '/admin/hr-center/rosters',
                    'icon_color' => 'text-info',
                    'permission' => 'hr_shift_roastering',
                ],
                [
                    'title'      => 'Reports',
                    'icon'       => 'fa-solid fa-chart-bar',
                    'icon_color' => 'text-info',
                    'permission' => '',
                    'children'   => [

                        // ── Employee ──────────────────────────────────────
                        [
                            'title'      => 'Employee',
                            'icon'       => 'fa-solid fa-users',
                            'icon_color' => 'text-primary',
                            'permission' => '',
                            'children'   => [
                                [
                                    'title'      => 'Employee List',
                                    'icon'       => 'fa-solid fa-arrow-right',
                                    'route'      => '/admin/hr-center/reports/employee',
                                    'icon_color' => 'text-warning',
                                    'permission' => 'hr_employee_report',
                                ],
                                [
                                    'title'      => 'Personal File',
                                    'icon'       => 'fa-solid fa-arrow-right',
                                    'route'      => '/admin/hr-center/reports/personal-file',
                                    'icon_color' => 'text-warning',
                                    'permission' => 'hr_personal_file_report',
                                ],
                                [
                                    'title'      => 'Daily Manpower',
                                    'icon'       => 'fa-solid fa-arrow-right',
                                    'route'      => '/admin/hr-center/reports/daily-manpower-report',
                                    'icon_color' => 'text-warning',
                                    'permission' => 'hr_daily_manpower_report',
                                ],
                            ],
                        ],

                        // ── Attendance ────────────────────────────────────
                        [
                            'title'      => 'Attendance',
                            'icon'       => 'fa-solid fa-calendar-check',
                            'icon_color' => 'text-success',
                            'permission' => '',
                            'children'   => [
                                [
                                    'title'      => 'Attendance Report',
                                    'icon'       => 'fa-solid fa-arrow-right',
                                    'route'      => '/admin/hr-center/reports/attendance-report',
                                    'icon_color' => 'text-warning',
                                    'permission' => 'hr_attendance_report',
                                ],
                                [
                                    'title'      => 'Daily report',
                                    'icon'       => 'fa-solid fa-arrow-right',
                                    'route'      => '/admin/hr-center/reports/daily-attendance-report',
                                    'icon_color' => 'text-warning',
                                    'permission' => 'hr_daily_attendance_report',
                                ],
                                [
                                    'title'      => 'Tiffin / Diner / Night',
                                    'icon'       => 'fa-solid fa-arrow-right',
                                    'route'      => '/admin/hr-center/reports/meal-report',
                                    'icon_color' => 'text-warning',
                                    'permission' => 'hr_meal_report',
                                ],
                                [
                                    'title'      => 'Gate Pass Report',
                                    'icon'       => 'fa-solid fa-arrow-right',
                                    'route'      => '/admin/hr-center/reports/gate-pass-report',
                                    'icon_color' => 'text-warning',
                                    'permission' => 'hr_gate_pass_report',
                                ],
                            ],
                        ],

                        // ── Salary & Payroll ──────────────────────────────
                        [
                            'title'      => 'Salary & Payroll',
                            'icon'       => 'fa-solid fa-money-bill-wave',
                            'icon_color' => 'text-success',
                            'permission' => '',
                            'children'   => [
                                [
                                    'title'      => 'Monthly Processing',
                                    'icon'       => 'fa-solid fa-arrow-right',
                                    'route'      => '/admin/hr-center/reports/monthly',
                                    'icon_color' => 'text-warning',
                                    'permission' => 'hr_monthly_report',
                                ],
                                [
                                    'title'      => 'Fixed Salary',
                                    'icon'       => 'fa-solid fa-arrow-right',
                                    'route'      => '/admin/hr-center/reports/fixed-salary',
                                    'icon_color' => 'text-warning',
                                    'permission' => 'hr_salary_report_fixed',
                                ],
                                [
                                    'title'      => 'Bonus Salary',
                                    'icon'       => 'fa-solid fa-arrow-right',
                                    'route'      => '/admin/hr-center/reports/bonus-salary',
                                    'icon_color' => 'text-warning',
                                    'permission' => 'hr_salary_report_bonus',
                                ],
                                [
                                    'title'      => 'Production Salary',
                                    'icon'       => 'fa-solid fa-arrow-right',
                                    'route'      => '/admin/hr-center/reports/production-salary',
                                    'icon_color' => 'text-warning',
                                    'permission' => 'hr_salary_report_production',
                                ],
                                [
                                    'title'      => 'Wages & Salary Summary',
                                    'icon'       => 'fa-solid fa-arrow-right',
                                    'route'      => '/admin/hr-center/reports/wages-salary-summary',
                                    'icon_color' => 'text-warning',
                                    'permission' => 'hr_salary_report_wage',
                                ],
                                [
                                    'title'      => 'OT Summary',
                                    'icon'       => 'fa-solid fa-arrow-right',
                                    'route'      => '/admin/hr-center/reports/ot-summary',
                                    'icon_color' => 'text-warning',
                                    'permission' => 'hr_ot_summary_report',
                                ],
                                [
                                    'title'      => 'Pay Slip',
                                    'icon'       => 'fa-solid fa-file-invoice-dollar',
                                    'route'      => '/admin/hr-center/reports/pay-slip',
                                    'icon_color' => 'text-warning',
                                    'permission' => 'pay_slip_report',
                                ],
                            ],
                        ],

                        // ── Job Card ──────────────────────────────────────
                        [
                            'title'      => 'Job Card',
                            'icon'       => 'fa-solid fa-id-card',
                            'icon_color' => 'text-warning',
                            'permission' => '',
                            'children'   => [
                                [
                                    'title'      => 'Job Card Report',
                                    'icon'       => 'fa-solid fa-arrow-right',
                                    'route'      => '/admin/hr-center/reports/job-card-report',
                                    'icon_color' => 'text-warning',
                                    'permission' => 'hr_job_card_report',
                                ],
                                [
                                    'title'      => 'Pro. Job Card',
                                    'icon'       => 'fa-solid fa-arrow-right',
                                    'route'      => '/admin/hr-center/reports/pro-job-card',
                                    'icon_color' => 'text-warning',
                                    'permission' => 'hr_pro_job_card',
                                ],
                            ],
                        ],

                        // ── Bonus ─────────────────────────────────────────
                        [
                            'title'      => 'Bonus Sheet',
                            'icon'       => 'fa-solid fa-gift',
                            'icon_color' => 'text-danger',
                            'permission' => '',
                            'children'   => [
                                [
                                    'title'      => 'Fixed Bonus',
                                    'icon'       => 'fa-solid fa-arrow-right',
                                    'route'      => '/admin/hr-center/reports/bonus-sheet/fixed',
                                    'icon_color' => 'text-warning',
                                    'permission' => 'hr_bonus_sheet_fixed',
                                ],
                                [
                                    'title'      => 'Production Bonus',
                                    'icon'       => 'fa-solid fa-arrow-right',
                                    'route'      => '/admin/hr-center/reports/bonus-sheet/production',
                                    'icon_color' => 'text-warning',
                                    'permission' => 'hr_bonus_sheet_production',
                                ],
                            ],
                        ],

                        // ── Others ────────────────────────────────────────
                        [
                            'title'      => 'Others',
                            'icon'       => 'fa-solid fa-ellipsis',
                            'icon_color' => 'text-secondary',
                            'permission' => '',
                            'children'   => [
                                [
                                    'title'      => 'Asset Report',
                                    'icon'       => 'fa-solid fa-arrow-right',
                                    'route'      => '/admin/hr-center/reports/asset-report',
                                    'icon_color' => 'text-warning',
                                    'permission' => 'hr_asset_report',
                                ],
                            ],
                        ],

                    ],
                ],


                // [
                //     'title'      => 'Production Rate',
                //     'icon'       => 'fa-solid fa-arrow-right',
                //     'icon_color' => 'text-info',
                //     'permission' => '',
                //     'children'   => [
                //         [
                //             'title'      => 'Linking',
                //             'icon'       => 'fa-solid fa-arrow-right',
                //             'route'      => '/admin/hr-center/production-rate?process=linking',
                //             'icon_color' => 'text-warning',
                //             'permission' => 'hr_production_rate_linking',
                //         ],
                //         [
                //             'title'      => 'Triming',
                //             'icon'       => 'fa-solid fa-arrow-right',
                //             'route'      => '/admin/hr-center/production-rate?process=triming',
                //             'icon_color' => 'text-warning',
                //             'permission' => 'hr_production_rate_triming',
                //         ],

                //         [
                //             'title'      => 'Mending',
                //             'icon'       => 'fa-solid fa-arrow-right',
                //             'route'      => '/admin/hr-center/production-rate?process=mending',
                //             'icon_color' => 'text-warning',
                //             'permission' => 'hr_production_rate_mending',
                //         ],
                //         [
                //             'title'      => 'Hole/Button/BarTack',
                //             'icon'       => 'fa-solid fa-arrow-right',
                //             'route'      => '/admin/hr-center/production-rate?process=hole-button-bartack',
                //             'icon_color' => 'text-warning',
                //             'permission' => 'hr_production_rate_hole_button_bartack',
                //         ],
                //         [
                //             'title'      => 'Sewing',
                //             'icon'       => 'fa-solid fa-arrow-right',
                //             'route'      => '/admin/hr-center/production-rate?process=sewing',
                //             'icon_color' => 'text-warning',
                //             'permission' => 'hr_production_rate_sewing',
                //         ],
                //         [
                //             'title'      => 'Ironing',
                //             'icon'       => 'fa-solid fa-arrow-right',
                //             'route'      => '/admin/hr-center/production-rate?process=ironing',
                //             'icon_color' => 'text-warning',
                //             'permission' => 'hr_production_rate_ironing',
                //         ],
                //         [
                //             'title'      => 'Zipper',
                //             'icon'       => 'fa-solid fa-arrow-right',
                //             'route'      => '/admin/hr-center/production-rate?process=zipper',
                //             'icon_color' => 'text-warning',
                //             'permission' => 'hr_production_rate_zipper',
                //         ],

                //     ]
                // ],


                // [
                //     'title' => 'Attendance Import',
                //     'icon' => 'fa-brands fa-accusoft',
                //     'permission' => '',
                //     'children' => [
                //         [
                //             'title' => 'Data Import',
                //             'icon' => 'fa fa-file-import',
                //             'route' => 'admin/hr-center/zkteco-data-import',
                //             'permission' => '',
                //         ],
                //     ]
                // ]



            ]
        ],
    ],

];

