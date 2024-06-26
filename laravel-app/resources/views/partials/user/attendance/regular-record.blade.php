<div
    class="p-5 mb-4 bg-white border border-gray-300 divide-y rounded-lg shadow dark:bg-gray-800 dark:border-gray-600 divider-gray-200 dark:divide-gray-700">
    <time
        class="text-lg font-semibold text-gray-900 dark:text-white">{{ date('l, d F Y', strtotime($record['date'])) }}</time>
    <ol class="flex flex-wrap mt-3 text-gray-900 md:gap-6 dark:text-white">
        <li class="items-center block p-3 sm:flex">
            @if (isset($record['check_in_time']))
                <svg class="mb-3 rounded-full fill-green-500 w-11 h-11 me-3 sm:mb-0" xmlns="http://www.w3.org/2000/svg"
                    width="24" height="24" viewBox="0 0 24 24">
                    <path
                        d="M12 2C6.486 2 2 6.486 2 12s4.486 10 10 10 10-4.486 10-10S17.514 2 12 2zm-1.999 14.413-3.713-3.705L7.7 11.292l2.299 2.295 5.294-5.294 1.414 1.414-6.706 6.706z">
                    </path>
                </svg>
            @else
                <svg class="mb-3 rounded-full fill-red-500 w-11 h-11 me-3 sm:mb-0" xmlns="http://www.w3.org/2000/svg"
                    width="24" height="24" viewBox="0 0 24 24">
                    <path
                        d="M12 2C6.486 2 2 6.486 2 12s4.486 10 10 10 10-4.486 10-10S17.514 2 12 2zm4.207 12.793-1.414 1.414L12 13.414l-2.793 2.793-1.414-1.414L10.586 12 7.793 9.207l1.414-1.414L12 10.586l2.793-2.793 1.414 1.414L13.414 12l2.793 2.793z">
                    </path>
                </svg>
            @endif
            <div class="flex flex-col">
                <span class="font-medium">Check-In</span>
                <span
                    class="text-xs font-medium text-blue-600 dark:text-blue-500">{{ $record['check_in_time'] ?? '-' }}</span>
            </div>
        </li>
        <li class="items-center block p-3 sm:flex">
            @if (isset($record['check_out_time']))
                <svg class="mb-3 rounded-full fill-green-500 w-11 h-11 me-3 sm:mb-0" xmlns="http://www.w3.org/2000/svg"
                    width="24" height="24" viewBox="0 0 24 24">
                    <path
                        d="M12 2C6.486 2 2 6.486 2 12s4.486 10 10 10 10-4.486 10-10S17.514 2 12 2zm-1.999 14.413-3.713-3.705L7.7 11.292l2.299 2.295 5.294-5.294 1.414 1.414-6.706 6.706z">
                    </path>
                </svg>
            @else
                <svg class="mb-3 rounded-full fill-red-500 w-11 h-11 me-3 sm:mb-0" xmlns="http://www.w3.org/2000/svg"
                    width="24" height="24" viewBox="0 0 24 24">
                    <path
                        d="M12 2C6.486 2 2 6.486 2 12s4.486 10 10 10 10-4.486 10-10S17.514 2 12 2zm4.207 12.793-1.414 1.414L12 13.414l-2.793 2.793-1.414-1.414L10.586 12 7.793 9.207l1.414-1.414L12 10.586l2.793-2.793 1.414 1.414L13.414 12l2.793 2.793z">
                    </path>
                </svg>
            @endif
            <div class="flex flex-col">
                <span class="font-medium">Check-Out</span>
                <span
                    class="text-xs font-medium text-blue-600 dark:text-blue-500">{{ $record['check_out_time'] ?? '-' }}</span>
            </div>
        </li>
    </ol>
</div>
