<x-samples.layout title="Admin Dashboard">
    <div class="min-h-full flex">
        <!-- Sidebar -->
        <aside class="hidden lg:flex lg:flex-col lg:w-64 bg-gray-900 border-r border-gray-800">
            <div class="flex items-center gap-3 px-6 py-5 border-b border-gray-800">
                <x-samples.logo class="h-8" />
            </div>

            <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto">
                <a href="#" class="flex items-center gap-3 px-3 py-2.5 text-white bg-brand-900/50 border border-brand-700/50 rounded-lg">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                    <span class="font-medium">Dashboard</span>
                </a>

                <a href="/samples/schedule" class="flex items-center gap-3 px-3 py-2.5 text-gray-400 hover:text-white hover:bg-gray-800 rounded-lg transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <span>Schedule</span>
                </a>

                <a href="#" class="flex items-center gap-3 px-3 py-2.5 text-gray-400 hover:text-white hover:bg-gray-800 rounded-lg transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                    <span>Employees</span>
                </a>

                <a href="#" class="flex items-center gap-3 px-3 py-2.5 text-gray-400 hover:text-white hover:bg-gray-800 rounded-lg transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                    <span>Locations</span>
                </a>

                <a href="#" class="flex items-center gap-3 px-3 py-2.5 text-gray-400 hover:text-white hover:bg-gray-800 rounded-lg transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                    </svg>
                    <span>Departments</span>
                </a>

                <a href="#" class="flex items-center gap-3 px-3 py-2.5 text-gray-400 hover:text-white hover:bg-gray-800 rounded-lg transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                    <span>Business Roles</span>
                </a>

                <div class="pt-4 mt-4 border-t border-gray-800">
                    <p class="px-3 text-xs font-semibold text-gray-600 uppercase tracking-wider">Management</p>
                </div>

                <a href="#" class="flex items-center justify-between px-3 py-2.5 text-gray-400 hover:text-white hover:bg-gray-800 rounded-lg transition-colors">
                    <div class="flex items-center gap-3">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        <span>Leave Requests</span>
                    </div>
                    <span class="bg-amber-500 text-white text-xs font-medium px-2 py-0.5 rounded-full">5</span>
                </a>

                <a href="#" class="flex items-center justify-between px-3 py-2.5 text-gray-400 hover:text-white hover:bg-gray-800 rounded-lg transition-colors">
                    <div class="flex items-center gap-3">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                        </svg>
                        <span>Shift Swaps</span>
                    </div>
                    <span class="bg-amber-500 text-white text-xs font-medium px-2 py-0.5 rounded-full">2</span>
                </a>

                <a href="#" class="flex items-center gap-3 px-3 py-2.5 text-gray-400 hover:text-white hover:bg-gray-800 rounded-lg transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>Time Tracking</span>
                </a>

                <a href="#" class="flex items-center gap-3 px-3 py-2.5 text-gray-400 hover:text-white hover:bg-gray-800 rounded-lg transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                    <span>Reports</span>
                </a>

                <a href="#" class="flex items-center gap-3 px-3 py-2.5 text-gray-400 hover:text-white hover:bg-gray-800 rounded-lg transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    <span>Settings</span>
                </a>
            </nav>

            <!-- User Menu -->
            <div class="p-4 border-t border-gray-800">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-brand-900 rounded-full flex items-center justify-center text-white font-medium">
                        JS
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-white truncate">John Smith</p>
                        <p class="text-xs text-gray-500 truncate">Admin</p>
                    </div>
                    <button class="p-1.5 text-gray-500 hover:text-white rounded-lg hover:bg-gray-800">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                    </button>
                </div>
            </div>
        </aside>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col min-h-screen bg-gray-950">
            <!-- Top Header -->
            <header class="bg-gray-900 border-b border-gray-800 px-6 py-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-white">Dashboard</h1>
                        <p class="text-sm text-gray-500 mt-0.5">Welcome back, John. Here's what's happening today.</p>
                    </div>
                    <div class="flex items-center gap-4">
                        <button class="relative p-2 text-gray-500 hover:text-white hover:bg-gray-800 rounded-lg transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                            </svg>
                            <span class="absolute top-1 right-1 w-2.5 h-2.5 bg-red-500 rounded-full border-2 border-gray-900"></span>
                        </button>
                        <button class="flex items-center gap-2 bg-brand-900 text-white px-4 py-2 rounded-lg font-medium hover:bg-brand-800 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                            </svg>
                            Quick Actions
                        </button>
                    </div>
                </div>
            </header>

            <!-- Dashboard Content -->
            <main class="flex-1 p-6 overflow-y-auto">
                <!-- Stats Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                    <!-- On Duty Card -->
                    <div class="bg-gray-900 rounded-xl border border-gray-800 p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-500">On Duty Today</p>
                                <p class="text-3xl font-bold text-white mt-1">24</p>
                                <p class="text-sm text-gray-500 mt-1">of 35 staff</p>
                            </div>
                            <div class="w-12 h-12 bg-green-500/10 rounded-lg flex items-center justify-center">
                                <svg class="w-6 h-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- On Leave Card -->
                    <div class="bg-gray-900 rounded-xl border border-gray-800 p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-500">On Leave</p>
                                <p class="text-3xl font-bold text-white mt-1">3</p>
                                <p class="text-sm text-gray-500 mt-1">employees</p>
                            </div>
                            <div class="w-12 h-12 bg-amber-500/10 rounded-lg flex items-center justify-center">
                                <svg class="w-6 h-6 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Hours This Week -->
                    <div class="bg-gray-900 rounded-xl border border-gray-800 p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-500">Hours This Week</p>
                                <p class="text-3xl font-bold text-white mt-1">842</p>
                                <p class="text-sm text-green-500 mt-1">+5% vs planned</p>
                            </div>
                            <div class="w-12 h-12 bg-brand-500/10 rounded-lg flex items-center justify-center">
                                <svg class="w-6 h-6 text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Unassigned Shifts -->
                    <div class="bg-gray-900 rounded-xl border border-gray-800 p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-500">Unassigned Shifts</p>
                                <p class="text-3xl font-bold text-red-500 mt-1">12</p>
                                <p class="text-sm text-gray-500 mt-1">this week</p>
                            </div>
                            <div class="w-12 h-12 bg-red-500/10 rounded-lg flex items-center justify-center">
                                <svg class="w-6 h-6 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Cards & Alerts -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
                    <!-- Pending Leave Requests -->
                    <div class="bg-gray-900 rounded-xl border border-gray-800 overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-800 flex items-center justify-between">
                            <h3 class="font-semibold text-white">Pending Leave Requests</h3>
                            <span class="bg-amber-500/20 text-amber-400 text-xs font-medium px-2.5 py-1 rounded-full">5 pending</span>
                        </div>
                        <div class="divide-y divide-gray-800">
                            <div class="px-6 py-4 hover:bg-gray-800/50 transition-colors">
                                <div class="flex items-center gap-4">
                                    <div class="w-10 h-10 bg-purple-500/20 rounded-full flex items-center justify-center text-purple-400 font-medium text-sm">
                                        SM
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="font-medium text-white">Sarah Mitchell</p>
                                        <p class="text-sm text-gray-500">Annual Leave • Jan 20-22</p>
                                    </div>
                                    <div class="flex gap-2">
                                        <button class="p-2 text-green-500 hover:bg-green-500/10 rounded-lg transition-colors">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                            </svg>
                                        </button>
                                        <button class="p-2 text-red-500 hover:bg-red-500/10 rounded-lg transition-colors">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="px-6 py-4 hover:bg-gray-800/50 transition-colors">
                                <div class="flex items-center gap-4">
                                    <div class="w-10 h-10 bg-blue-500/20 rounded-full flex items-center justify-center text-blue-400 font-medium text-sm">
                                        MJ
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="font-medium text-white">Mike Johnson</p>
                                        <p class="text-sm text-gray-500">Sick Leave • Jan 18</p>
                                    </div>
                                    <div class="flex gap-2">
                                        <button class="p-2 text-green-500 hover:bg-green-500/10 rounded-lg transition-colors">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                            </svg>
                                        </button>
                                        <button class="p-2 text-red-500 hover:bg-red-500/10 rounded-lg transition-colors">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="px-6 py-4 hover:bg-gray-800/50 transition-colors">
                                <div class="flex items-center gap-4">
                                    <div class="w-10 h-10 bg-green-500/20 rounded-full flex items-center justify-center text-green-400 font-medium text-sm">
                                        LW
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="font-medium text-white">Lisa Wong</p>
                                        <p class="text-sm text-gray-500">Annual Leave • Feb 1-5</p>
                                    </div>
                                    <div class="flex gap-2">
                                        <button class="p-2 text-green-500 hover:bg-green-500/10 rounded-lg transition-colors">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                            </svg>
                                        </button>
                                        <button class="p-2 text-red-500 hover:bg-red-500/10 rounded-lg transition-colors">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="px-6 py-3 bg-gray-800/50 border-t border-gray-800">
                            <a href="#" class="text-sm font-medium text-brand-400 hover:text-brand-300">View all requests →</a>
                        </div>
                    </div>

                    <!-- Shift Swap Requests -->
                    <div class="bg-gray-900 rounded-xl border border-gray-800 overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-800 flex items-center justify-between">
                            <h3 class="font-semibold text-white">Shift Swap Requests</h3>
                            <span class="bg-amber-500/20 text-amber-400 text-xs font-medium px-2.5 py-1 rounded-full">2 pending</span>
                        </div>
                        <div class="divide-y divide-gray-800">
                            <div class="px-6 py-4 hover:bg-gray-800/50 transition-colors">
                                <div class="flex items-start gap-4">
                                    <div class="flex -space-x-2">
                                        <div class="w-8 h-8 bg-purple-500/20 rounded-full flex items-center justify-center text-purple-400 font-medium text-xs ring-2 ring-gray-900">
                                            AB
                                        </div>
                                        <div class="w-8 h-8 bg-blue-500/20 rounded-full flex items-center justify-center text-blue-400 font-medium text-xs ring-2 ring-gray-900">
                                            CD
                                        </div>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="font-medium text-white text-sm">Alex Brown ↔ Chris Davis</p>
                                        <p class="text-xs text-gray-500 mt-0.5">Mon 9-5 ↔ Tue 9-5</p>
                                        <p class="text-xs text-gray-600 mt-0.5">Front Desk • Cashier</p>
                                    </div>
                                    <div class="flex gap-1">
                                        <button class="p-1.5 text-green-500 hover:bg-green-500/10 rounded-lg transition-colors">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                            </svg>
                                        </button>
                                        <button class="p-1.5 text-red-500 hover:bg-red-500/10 rounded-lg transition-colors">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="px-6 py-4 hover:bg-gray-800/50 transition-colors">
                                <div class="flex items-start gap-4">
                                    <div class="flex -space-x-2">
                                        <div class="w-8 h-8 bg-green-500/20 rounded-full flex items-center justify-center text-green-400 font-medium text-xs ring-2 ring-gray-900">
                                            EF
                                        </div>
                                        <div class="w-8 h-8 bg-amber-500/20 rounded-full flex items-center justify-center text-amber-400 font-medium text-xs ring-2 ring-gray-900">
                                            GH
                                        </div>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="font-medium text-white text-sm">Emma Foster ↔ Grace Hill</p>
                                        <p class="text-xs text-gray-500 mt-0.5">Wed 12-8 ↔ Thu 12-8</p>
                                        <p class="text-xs text-gray-600 mt-0.5">Warehouse • Picker</p>
                                    </div>
                                    <div class="flex gap-1">
                                        <button class="p-1.5 text-green-500 hover:bg-green-500/10 rounded-lg transition-colors">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                            </svg>
                                        </button>
                                        <button class="p-1.5 text-red-500 hover:bg-red-500/10 rounded-lg transition-colors">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="px-6 py-3 bg-gray-800/50 border-t border-gray-800">
                            <a href="#" class="text-sm font-medium text-brand-400 hover:text-brand-300">View all swaps →</a>
                        </div>
                    </div>

                    <!-- Alerts -->
                    <div class="bg-gray-900 rounded-xl border border-gray-800 overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-800">
                            <h3 class="font-semibold text-white">Attendance Alerts</h3>
                        </div>
                        <div class="divide-y divide-gray-800">
                            <div class="px-6 py-4 flex items-start gap-3">
                                <div class="w-8 h-8 bg-red-500/10 rounded-full flex items-center justify-center flex-shrink-0">
                                    <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-white">Mike Johnson hasn't clocked in</p>
                                    <p class="text-xs text-gray-500 mt-0.5">Shift started at 9:00 AM</p>
                                </div>
                            </div>
                            <div class="px-6 py-4 flex items-start gap-3">
                                <div class="w-8 h-8 bg-amber-500/10 rounded-full flex items-center justify-center flex-shrink-0">
                                    <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-white">Sarah M. approaching overtime</p>
                                    <p class="text-xs text-gray-500 mt-0.5">38 of 40 hours this week</p>
                                </div>
                            </div>
                            <div class="px-6 py-4 flex items-start gap-3">
                                <div class="w-8 h-8 bg-amber-500/10 rounded-full flex items-center justify-center flex-shrink-0">
                                    <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-white">Alex B. approaching overtime</p>
                                    <p class="text-xs text-gray-500 mt-0.5">39 of 40 hours this week</p>
                                </div>
                            </div>
                        </div>
                        <div class="px-6 py-3 bg-gray-800/50 border-t border-gray-800">
                            <a href="#" class="text-sm font-medium text-brand-400 hover:text-brand-300">View all alerts →</a>
                        </div>
                    </div>
                </div>

                <!-- Today's Schedule Preview -->
                <div class="bg-gray-900 rounded-xl border border-gray-800 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-800 flex items-center justify-between">
                        <div>
                            <h3 class="font-semibold text-white">Today's Schedule</h3>
                            <p class="text-sm text-gray-500">Monday, January 15, 2024</p>
                        </div>
                        <a href="/samples/schedule" class="flex items-center gap-2 text-sm font-medium text-brand-400 hover:text-brand-300">
                            View full schedule
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                            </svg>
                        </a>
                    </div>
                    <div class="p-6">
                        <div class="relative">
                            <!-- Time indicators -->
                            <div class="absolute left-0 top-0 bottom-0 w-16 flex flex-col justify-between text-xs text-gray-600 py-2">
                                <span>6 AM</span>
                                <span>9 AM</span>
                                <span>12 PM</span>
                                <span>3 PM</span>
                                <span>6 PM</span>
                                <span>9 PM</span>
                            </div>
                            <!-- Schedule grid -->
                            <div class="ml-20 space-y-3">
                                <div class="flex items-center gap-4">
                                    <div class="w-32 text-sm font-medium text-gray-300 truncate">Front Desk</div>
                                    <div class="flex-1 h-8 bg-gray-800 rounded relative">
                                        <div class="absolute h-full bg-brand-600 rounded" style="left: 20%; width: 53%;" title="John D. 9AM-5PM"></div>
                                        <div class="absolute h-full bg-purple-600 rounded" style="left: 40%; width: 53%;" title="Sarah M. 12PM-8PM"></div>
                                    </div>
                                </div>
                                <div class="flex items-center gap-4">
                                    <div class="w-32 text-sm font-medium text-gray-300 truncate">Warehouse</div>
                                    <div class="flex-1 h-8 bg-gray-800 rounded relative">
                                        <div class="absolute h-full bg-green-600 rounded" style="left: 0%; width: 53%;" title="Mike J. 6AM-2PM"></div>
                                        <div class="absolute h-full bg-amber-600 rounded" style="left: 40%; width: 53%;" title="Lisa W. 12PM-8PM"></div>
                                    </div>
                                </div>
                                <div class="flex items-center gap-4">
                                    <div class="w-32 text-sm font-medium text-gray-300 truncate">Kitchen</div>
                                    <div class="flex-1 h-8 bg-gray-800 rounded relative">
                                        <div class="absolute h-full bg-red-500/30 rounded border-2 border-dashed border-red-500" style="left: 20%; width: 53%;" title="Unassigned 9AM-5PM"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="mt-6 flex items-center gap-6 text-xs">
                            <div class="flex items-center gap-2">
                                <div class="w-3 h-3 bg-brand-600 rounded"></div>
                                <span class="text-gray-400">Assigned</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <div class="w-3 h-3 bg-red-500/30 rounded border border-dashed border-red-500"></div>
                                <span class="text-gray-400">Unassigned</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <div class="w-3 h-3 bg-gray-600 rounded"></div>
                                <span class="text-gray-400">On Leave</span>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
</x-samples.layout>
