@extends('layouts.app')

@section('content')
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
        @include('partials.user.dashboard.attendance-checker')
        <div
            class="bg-white w-full h-full rounded-lg col-span-full p-4 md:px-6 md:py-12 shadow dark:shadow-lg dark:border-gray-600">
            <figure class="max-w-screen-md mx-auto text-center">
                <svg class="w-10 h-10 mx-auto mb-3 text-gray-400 dark:text-gray-600" aria-hidden="true"
                    xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 18 14">
                    <path
                        d="M6 0H2a2 2 0 0 0-2 2v4a2 2 0 0 0 2 2h4v1a3 3 0 0 1-3 3H2a1 1 0 0 0 0 2h1a5.006 5.006 0 0 0 5-5V2a2 2 0 0 0-2-2Zm10 0h-4a2 2 0 0 0-2 2v4a2 2 0 0 0 2 2h4v1a3 3 0 0 1-3 3h-1a1 1 0 0 0 0 2h1a5.006 5.006 0 0 0 5-5V2a2 2 0 0 0-2-2Z" />
                </svg>
                <blockquote>
                    @if (!empty($motivation))
                        <p class="text-2xl italic font-semibold text-gray-900 dark:text-white">
                            {{ $motivation }}
                        </p>
                    @else
                        <p class="text-2xl italic font-semibold text-gray-700 dark:text-white">
                            Too many request, please try again a few moments to generate motivation.
                        </p>
                    @endif
                </blockquote>
                <figcaption class="flex items-center justify-center mt-6 space-x-2">
                    <cite class="font-medium text-gray-900 dark:text-white">Powered by</cite>
                    <cite class="text-sm text-gray-500 dark:text-gray-400">Gemini</cite>
                    <svg class="w-4 h-4 text-blue-500" fill="none" xmlns="http://www.w3.org/2000/svg"
                        viewBox="0 0 16 16">
                        <path
                            d="M16 8.016A8.522 8.522 0 008.016 16h-.032A8.521 8.521 0 000 8.016v-.032A8.521 8.521 0 007.984 0h.032A8.522 8.522 0 0016 7.984v.032z"
                            fill="url(#prefix__paint0_radial_980_20147)" />
                        <defs>
                            <radialGradient id="prefix__paint0_radial_980_20147" cx="0" cy="0" r="1"
                                gradientUnits="userSpaceOnUse"
                                gradientTransform="matrix(16.1326 5.4553 -43.70045 129.2322 1.588 6.503)">
                                <stop offset=".067" stop-color="#9168C0" />
                                <stop offset=".343" stop-color="#5684D1" />
                                <stop offset=".672" stop-color="#1BA1E3" />
                            </radialGradient>
                        </defs>
                    </svg>
                </figcaption>
            </figure>
        </div>
        @if ($is_manager)
            <div class="rounded-lg dark:border-gray-600 h-full md:h-full">
                <div class="w-full bg-white rounded-lg shadow dark:bg-gray-800 p-4 md:p-6">
                    <div class="flex justify-between items-start w-full">
                        <div class="flex-col items-center">
                            <div class="flex items-center mb-1">
                                <h5 class="text-xl font-bold leading-none text-gray-900 dark:text-white me-1">Employee
                                    Attendance
                                </h5>
                            </div>
                            <form class="flex justify-between items-center" id="period-form" method="GET" action="">
                                <select name="period" id="period-selector"
                                    class="cursor-pointer outline-none border-none focus:outline-none">
                                    <option class="cursor-pointer" value="1" {{ $old_period === 1 ? 'selected' : '' }}>
                                        Yesterday</option>
                                    <option class="cursor-pointer" value="2" {{ $old_period === 2 ? 'selected' : '' }}>
                                        Today</option>
                                    <option class="cursor-pointer" value="3" {{ $old_period === 3 ? 'selected' : '' }}>
                                        Last
                                        7 days</option>
                                    <option class="cursor-pointer" value="4" {{ $old_period === 4 ? 'selected' : '' }}>
                                        Last
                                        30 days</option>
                                    <option class="cursor-pointer" value="5" {{ $old_period === 5 ? 'selected' : '' }}>
                                        Last
                                        90 days</option>
                                </select>
                            </form>
                            <div id="dateRangeDropdown"
                                class="z-10 hidden bg-white divide-y divide-gray-100 rounded-lg shadow w-80 lg:w-96 dark:bg-gray-700 dark:divide-gray-600">
                                <div class="p-3" aria-labelledby="dateRangeButton">
                                    <div date-rangepicker datepicker-autohide class="flex items-center">
                                        <div class="relative">
                                            <div
                                                class="absolute inset-y-0 start-0 flex items-center ps-3 pointer-events-none">
                                                <svg class="w-4 h-4 text-gray-500 dark:text-gray-400" aria-hidden="true"
                                                    xmlns="http://www.w3.org/2000/svg" fill="currentColor"
                                                    viewBox="0 0 20 20">
                                                    <path
                                                        d="M20 4a2 2 0 0 0-2-2h-2V1a1 1 0 0 0-2 0v1h-3V1a1 1 0 0 0-2 0v1H6V1a1 1 0 0 0-2 0v1H2a2 2 0 0 0-2 2v2h20V4ZM0 18a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V8H0v10Zm5-8h10a1 1 0 0 1 0 2H5a1 1 0 0 1 0-2Z" />
                                                </svg>
                                            </div>
                                            <input name="start" type="text"
                                                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full ps-10 p-2.5  dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                                                placeholder="Start date">
                                        </div>
                                        <span class="mx-2 text-gray-500 dark:text-gray-400">to</span>
                                        <div class="relative">
                                            <div
                                                class="absolute inset-y-0 start-0 flex items-center ps-3 pointer-events-none">
                                                <svg class="w-4 h-4 text-gray-500 dark:text-gray-400" aria-hidden="true"
                                                    xmlns="http://www.w3.org/2000/svg" fill="currentColor"
                                                    viewBox="0 0 20 20">
                                                    <path
                                                        d="M20 4a2 2 0 0 0-2-2h-2V1a1 1 0 0 0-2 0v1h-3V1a1 1 0 0 0-2 0v1H6V1a1 1 0 0 0-2 0v1H2a2 2 0 0 0-2 2v2h20V4ZM0 18a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V8H0v10Zm5-8h10a1 1 0 0 1 0 2H5a1 1 0 0 1 0-2Z" />
                                                </svg>
                                            </div>
                                            <input name="end" type="text"
                                                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full ps-10 p-2.5  dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                                                placeholder="End date">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Line Chart -->
                    <div class="py-6" id="pie-chart" class="rounded-full"></div>

                    <div
                        class="grid grid-cols-2 items-center border-gray-200 border-t dark:border-gray-700 justify-between pt-4">
                        <div>
                            <h1 class="font-semibold text-gray-500 mb-2">Total Attendance</h1>
                            <p class="text-2xl">{{ $employee_attendance_chart['attend_count'] }}</p>
                        </div>

                        <div>
                            <h1 class="font-semibold text-gray-500 mb-2">Total Absence</h1>
                            <p class="text-2xl">{{ $employee_attendance_chart['absence_count'] }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="border bg-white rounded-lg shadow-sm p-4 md:p-6 text-gray-900">
                <h1 class="font-bold text-xl mb-8">Employee Performance</h1>

                {{-- Headline Top Attendance --}}
                <div class="flex gap-3 mb-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                        fill="none">
                        <circle cx="12" cy="12" r="12" fill="#6DB653" />
                        <path
                            d="M17.7459 15.3512L13.0824 7.27389C12.4871 6.24204 11.5129 6.24204 10.9176 7.27389L6.25407 15.3512C5.65877 16.3831 6.14583 17.2259 7.33644 17.2259H16.6636C17.8542 17.2259 18.3412 16.3817 17.7459 15.3512Z"
                            fill="white" />
                    </svg>
                    <h2 class="font-semibold opacity-80 text-base">Top Attendance</h2>
                </div>

                {{-- Table Top Attendance --}}
                <div class="w-full overflow-x-auto">
                    <table class="w-full table-fixed text-sm text-left">
                        <thead class="opacity-50 border-b-[1.5px]">
                            <tr>
                                <th scope="col" class="w-[12%] py-3 font-medium">
                                    No
                                </th>
                                <th scope="col" class="w-[48%] py-3 font-medium">
                                    Name
                                </th>
                                <th scope="col" class="w-[20%] py-3 font-medium">
                                    Att/Abs
                                </th>
                                <th scope="col" class="w-[20%] py-3 font-medium">
                                    Percentage
                                </th>
                            </tr>
                        </thead>
                        <tr class="h-4"></tr>
                        <tbody>
                            @forelse ($employee_peformance['best_users_attendance'] ?? [] as $index => $employee)
                                @include('partials.user.dashboard.employee_peformance_record')
                            @empty
                                <tr class="h-5"></tr>
                                <tr class= "dark:bg-gray-800 dark:border-none">
                                    <td class="text-center" colspan="4">No employees</td>
                                </tr>
                                <tr class="h-5"></tr>
                            @endforelse
                        </tbody>
                    </table>

                    {{-- Headline Worst Attendance --}}
                    <div class="flex gap-3 mb-2 mt-8">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                            fill="none">
                            <circle cx="12" cy="12" r="12" transform="rotate(180 12 12)" fill="#C64747" />
                            <path
                                d="M6.25407 9.64876L10.9176 17.7261C11.5129 18.758 12.4871 18.758 13.0824 17.7261L17.7459 9.64876C18.3412 8.6169 17.8542 7.7741 16.6636 7.7741L7.33644 7.7741C6.14583 7.7741 5.65877 8.61835 6.25407 9.64876Z"
                                fill="white" />
                        </svg>
                        <h2 class="font-semibold opacity-80 text-base">Worst Attendance</h2>
                    </div>

                    {{-- Table Worst Attendance --}}
                    <table class="w-full table-fixed text-sm text-left">
                        <thead class="opacity-50 border-b-[1.5px]">
                            <tr>
                                <th scope="col" class="w-[12%] py-3 font-medium">
                                    No
                                </th>
                                <th scope="col" class="w-[48%] py-3 font-medium">
                                    Name
                                </th>
                                <th scope="col" class="w-[20%] py-3 font-medium">
                                    Att/Abs
                                </th>
                                <th scope="col" class="w-[20%] py-3 font-medium">
                                    Percentage
                                </th>
                            </tr>
                        </thead>
                        <tr class="h-4"></tr>
                        <tbody>
                            @forelse ($employee_peformance['worst_users_attendance'] ?? [] as $index => $employee)
                                @include('partials.user.dashboard.employee_peformance_record')
                            @empty
                                <tr class="h-5"></tr>
                                <tr class= "dark:bg-gray-800 dark:border-none">
                                    <td class="text-center" colspan="4">No employees</td>
                                </tr>
                                <tr class="h-5"></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>
    @if ($is_manager)
        {{-- Script form submit handler & pie chart --}}
        <script type="module">
            addEventListener('DOMContentLoaded', () => {
                const chart = new ApexCharts(document.querySelector("#pie-chart"), {
                    series: [0, 0],
                    chart: {
                        type: 'pie',
                        animations: {
                            enabled: true,
                            easing: 'easeinout',
                            speed: 800,
                            animateGradually: {
                                enabled: true,
                                delay: 150
                            },
                            dynamicAnimation: {
                                enabled: true,
                                speed: 350
                            }
                        },
                    },
                    labels: ['Attend', 'Absence'],
                    legend: {
                        position: 'bottom',
                        horizontalAlign: 'center',
                        floating: false,
                        fontSize: '14px',
                        offsetX: 0,
                        offsetY: 0
                    },
                    tooltip: {
                        enabled: true,
                        theme: 'dark',
                        fillSeriesColor: false
                    }
                });

                chart.render();

                chart.updateSeries([{{ $employee_attendance_chart['attend_count'] }},
                    {{ $employee_attendance_chart['absence_count'] }}
                ]);
            });

            const periodForm = document.querySelector('#period-form');
            const periodSelector = document.querySelector('#period-selector');

            periodSelector.addEventListener('change', (e) => {
                periodForm.submit();
            });
        </script>
    @endif
@endsection
