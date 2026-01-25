<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Plannrly - Employee</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'Inter', sans-serif; }
        .safe-area-bottom { padding-bottom: env(safe-area-inset-bottom, 0); }
    </style>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            50: '#f0f0ff',
                            100: '#e0e0ff',
                            200: '#c4c0ff',
                            300: '#a090ff',
                            400: '#7c5cff',
                            500: '#5a30f0',
                            600: '#4a20d0',
                            700: '#3a15b0',
                            800: '#2a0fa0',
                            900: '#160092',
                        }
                    }
                }
            }
        }
    </script>
</head>
<body class="h-full bg-gray-950">
    <div class="flex flex-col h-full max-w-md mx-auto bg-gray-950">
        <!-- Status Bar Spacer -->
        <div class="bg-brand-900 h-6"></div>

        <!-- Header -->
        <header class="bg-brand-900 text-white px-4 pb-6 pt-2">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-white/20 rounded-full flex items-center justify-center font-medium">
                        JD
                    </div>
                    <div>
                        <p class="font-semibold">Hello, John!</p>
                        <p class="text-sm text-brand-200">Front Desk • Cashier</p>
                    </div>
                </div>
                <button class="relative p-2 bg-white/10 rounded-lg">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                    </svg>
                    <span class="absolute top-1 right-1 w-2.5 h-2.5 bg-red-500 rounded-full border-2 border-brand-900"></span>
                </button>
            </div>
        </header>

        <!-- Main Content -->
        <main class="flex-1 overflow-y-auto">
            <!-- Today's Shift Card -->
            <div class="px-4 -mt-4">
                <div class="bg-gray-900 rounded-2xl border border-gray-800 overflow-hidden">
                    <div class="p-4 border-b border-gray-800">
                        <div class="flex items-center justify-between mb-3">
                            <div>
                                <p class="text-sm text-gray-500">Today's Shift</p>
                                <p class="font-semibold text-white">Monday, Jan 15</p>
                            </div>
                            <span class="px-3 py-1 bg-green-500/20 text-green-400 text-sm font-medium rounded-full">Active</span>
                        </div>
                        <div class="flex items-center gap-4">
                            <div class="flex-1">
                                <div class="text-2xl font-bold text-white">9:00 AM - 5:00 PM</div>
                                <div class="text-sm text-gray-500 mt-1">8 hours • 30 min break</div>
                            </div>
                        </div>
                    </div>

                    <!-- Clock In/Out Section -->
                    <div class="p-4 bg-gray-800/50">
                        <div class="grid grid-cols-2 gap-3 mb-4">
                            <div class="text-center p-3 bg-gray-900 rounded-xl border border-gray-800">
                                <p class="text-xs text-gray-500 mb-1">Clocked In</p>
                                <p class="text-lg font-bold text-green-400">8:58 AM</p>
                            </div>
                            <div class="text-center p-3 bg-gray-900 rounded-xl border border-gray-800">
                                <p class="text-xs text-gray-500 mb-1">Working</p>
                                <p class="text-lg font-bold text-white">4h 32m</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <button class="flex items-center justify-center gap-2 py-3 bg-amber-500 text-white rounded-xl font-medium hover:bg-amber-400 transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Start Break
                            </button>
                            <button class="flex items-center justify-center gap-2 py-3 bg-red-500 text-white rounded-xl font-medium hover:bg-red-400 transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                </svg>
                                Clock Out
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- This Week Summary -->
            <div class="px-4 mt-6">
                <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-3">This Week</h2>
                <div class="grid grid-cols-3 gap-3">
                    <div class="bg-gray-900 rounded-xl p-4 border border-gray-800 text-center">
                        <p class="text-2xl font-bold text-brand-400">32h</p>
                        <p class="text-xs text-gray-500 mt-1">Scheduled</p>
                    </div>
                    <div class="bg-gray-900 rounded-xl p-4 border border-gray-800 text-center">
                        <p class="text-2xl font-bold text-white">20h</p>
                        <p class="text-xs text-gray-500 mt-1">Worked</p>
                    </div>
                    <div class="bg-gray-900 rounded-xl p-4 border border-gray-800 text-center">
                        <p class="text-2xl font-bold text-white">3</p>
                        <p class="text-xs text-gray-500 mt-1">Shifts Left</p>
                    </div>
                </div>
            </div>

            <!-- Upcoming Shifts -->
            <div class="px-4 mt-6">
                <div class="flex items-center justify-between mb-3">
                    <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wider">Upcoming Shifts</h2>
                    <a href="#" class="text-sm text-brand-400 font-medium">View All</a>
                </div>
                <div class="space-y-3">
                    <div class="bg-gray-900 rounded-xl p-4 border border-gray-800 flex items-center gap-4">
                        <div class="w-12 h-12 bg-brand-900/50 rounded-xl flex flex-col items-center justify-center border border-brand-700/50">
                            <span class="text-xs font-medium text-brand-300">TUE</span>
                            <span class="text-lg font-bold text-brand-400">16</span>
                        </div>
                        <div class="flex-1">
                            <p class="font-medium text-white">9:00 AM - 5:00 PM</p>
                            <p class="text-sm text-gray-500">Front Desk • Cashier</p>
                        </div>
                        <span class="text-sm text-gray-500">8h</span>
                    </div>
                    <div class="bg-gray-900 rounded-xl p-4 border border-gray-800 flex items-center gap-4">
                        <div class="w-12 h-12 bg-brand-900/50 rounded-xl flex flex-col items-center justify-center border border-brand-700/50">
                            <span class="text-xs font-medium text-brand-300">THU</span>
                            <span class="text-lg font-bold text-brand-400">18</span>
                        </div>
                        <div class="flex-1">
                            <p class="font-medium text-white">12:00 PM - 8:00 PM</p>
                            <p class="text-sm text-gray-500">Front Desk • Cashier</p>
                        </div>
                        <span class="text-sm text-gray-500">8h</span>
                    </div>
                    <div class="bg-gray-900 rounded-xl p-4 border border-gray-800 flex items-center gap-4">
                        <div class="w-12 h-12 bg-brand-900/50 rounded-xl flex flex-col items-center justify-center border border-brand-700/50">
                            <span class="text-xs font-medium text-brand-300">FRI</span>
                            <span class="text-lg font-bold text-brand-400">19</span>
                        </div>
                        <div class="flex-1">
                            <p class="font-medium text-white">9:00 AM - 5:00 PM</p>
                            <p class="text-sm text-gray-500">Front Desk • Cashier</p>
                        </div>
                        <span class="text-sm text-gray-500">8h</span>
                    </div>
                </div>
            </div>

            <!-- Leave Balance -->
            <div class="px-4 mt-6 mb-6">
                <div class="flex items-center justify-between mb-3">
                    <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wider">Leave Balance</h2>
                    <a href="#" class="text-sm text-brand-400 font-medium">Request Leave</a>
                </div>
                <div class="bg-gray-900 rounded-xl p-4 border border-gray-800">
                    <div class="flex items-center justify-between mb-3">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-green-500/10 rounded-lg flex items-center justify-center">
                                <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <div>
                                <p class="font-medium text-white">Annual Leave</p>
                                <p class="text-sm text-gray-500">15 days remaining</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-lg font-bold text-white">15</p>
                            <p class="text-xs text-gray-500">of 25 days</p>
                        </div>
                    </div>
                    <div class="w-full bg-gray-800 rounded-full h-2">
                        <div class="bg-green-500 h-2 rounded-full" style="width: 60%"></div>
                    </div>
                </div>
            </div>

            <!-- Pending Requests -->
            <div class="px-4 mb-24">
                <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-3">Pending Requests</h2>
                <div class="bg-amber-500/10 border border-amber-500/30 rounded-xl p-4">
                    <div class="flex items-start gap-3">
                        <div class="w-10 h-10 bg-amber-500/20 rounded-lg flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="flex-1">
                            <p class="font-medium text-amber-300">Leave Request</p>
                            <p class="text-sm text-amber-400/70">Jan 20-22 (Annual Leave)</p>
                            <p class="text-xs text-amber-400/50 mt-1">Awaiting approval</p>
                        </div>
                    </div>
                </div>
            </div>
        </main>

        <!-- Bottom Navigation -->
        <nav class="bg-gray-900 border-t border-gray-800 safe-area-bottom">
            <div class="grid grid-cols-5 h-16">
                <a href="#" class="flex flex-col items-center justify-center text-brand-400">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                    <span class="text-xs mt-1 font-medium">Home</span>
                </a>
                <a href="#" class="flex flex-col items-center justify-center text-gray-500">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <span class="text-xs mt-1">Shifts</span>
                </a>
                <a href="#" class="flex flex-col items-center justify-center">
                    <div class="w-14 h-14 -mt-8 bg-brand-900 rounded-full flex items-center justify-center shadow-lg border-4 border-gray-950">
                        <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <span class="text-xs mt-1 text-brand-400 font-medium">Clock</span>
                </a>
                <a href="#" class="flex flex-col items-center justify-center text-gray-500">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                    </svg>
                    <span class="text-xs mt-1">Swap</span>
                </a>
                <a href="#" class="flex flex-col items-center justify-center text-gray-500">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    <span class="text-xs mt-1">Profile</span>
                </a>
            </div>
        </nav>
    </div>
</body>
</html>
