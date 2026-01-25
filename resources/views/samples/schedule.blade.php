<x-samples.layout title="Schedule">
    <div class="min-h-full flex">
        <!-- Sidebar -->
        <aside class="hidden lg:flex lg:flex-col lg:w-64 bg-gray-900 border-r border-gray-800">
            <div class="flex items-center gap-3 px-6 py-5 border-b border-gray-800">
                <x-samples.logo class="h-8" />
            </div>

            <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto">
                <a href="/samples/admin-dashboard" class="flex items-center gap-3 px-3 py-2.5 text-gray-400 hover:text-white hover:bg-gray-800 rounded-lg transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                    <span>Dashboard</span>
                </a>

                <a href="#" class="flex items-center gap-3 px-3 py-2.5 text-white bg-brand-900/50 border border-brand-700/50 rounded-lg">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <span class="font-medium">Schedule</span>
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
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6z" />
                    </svg>
                    <span>Departments</span>
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
                </div>
            </div>
        </aside>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col min-h-screen bg-gray-950">
            <!-- Top Header -->
            <header class="bg-gray-900 border-b border-gray-800 px-6 py-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <button class="p-2 text-gray-500 hover:text-white hover:bg-gray-800 rounded-lg transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                            </svg>
                        </button>
                        <div>
                            <h1 class="text-xl font-bold text-white">Week of January 15 - 21, 2024</h1>
                            <p class="text-sm text-gray-500">Main Street Location</p>
                        </div>
                        <button class="p-2 text-gray-500 hover:text-white hover:bg-gray-800 rounded-lg transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </button>
                    </div>
                    <div class="flex items-center gap-3">
                        <!-- View Toggle -->
                        <div class="flex bg-gray-800 rounded-lg p-1">
                            <button class="px-3 py-1.5 text-sm font-medium text-gray-400 hover:text-white rounded-md transition-colors">Day</button>
                            <button class="px-3 py-1.5 text-sm font-medium text-white bg-brand-900 rounded-md">Week</button>
                            <button class="px-3 py-1.5 text-sm font-medium text-gray-400 hover:text-white rounded-md transition-colors">Month</button>
                        </div>

                        <!-- Status Badge -->
                        <div class="flex items-center gap-2 px-3 py-1.5 bg-amber-500/10 border border-amber-500/30 rounded-lg">
                            <div class="w-2 h-2 bg-amber-500 rounded-full"></div>
                            <span class="text-sm font-medium text-amber-400">Draft</span>
                        </div>

                        <!-- Action Buttons -->
                        <button class="flex items-center gap-2 px-4 py-2 text-white bg-gray-800 border border-gray-700 rounded-lg font-medium hover:bg-gray-700 transition-colors">
                            <svg class="w-5 h-5 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                            </svg>
                            AI Assist
                        </button>
                        <button class="flex items-center gap-2 px-4 py-2 text-white bg-brand-900 rounded-lg font-medium hover:bg-brand-800 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            Publish Rota
                        </button>
                    </div>
                </div>

                <!-- Filters -->
                <div class="flex items-center gap-4 mt-4">
                    <div class="flex items-center gap-2">
                        <label class="text-sm font-medium text-gray-500">Location:</label>
                        <select class="text-sm bg-gray-800 border-gray-700 text-white rounded-lg focus:ring-brand-500 focus:border-brand-500 px-3 py-1.5">
                            <option>All Locations</option>
                            <option selected>Main Street</option>
                            <option>Downtown</option>
                            <option>Mall Outlet</option>
                        </select>
                    </div>
                    <div class="flex items-center gap-2">
                        <label class="text-sm font-medium text-gray-500">Department:</label>
                        <select class="text-sm bg-gray-800 border-gray-700 text-white rounded-lg focus:ring-brand-500 focus:border-brand-500 px-3 py-1.5">
                            <option selected>All Departments</option>
                            <option>Front Desk</option>
                            <option>Warehouse</option>
                            <option>Kitchen</option>
                        </select>
                    </div>
                    <div class="flex items-center gap-2">
                        <label class="text-sm font-medium text-gray-500">Role:</label>
                        <select class="text-sm bg-gray-800 border-gray-700 text-white rounded-lg focus:ring-brand-500 focus:border-brand-500 px-3 py-1.5">
                            <option selected>All Roles</option>
                            <option>Cashier</option>
                            <option>Supervisor</option>
                            <option>Picker</option>
                            <option>Cook</option>
                        </select>
                    </div>
                    <!-- Make Default Button -->
                    <button class="flex items-center gap-1.5 text-sm text-gray-400 hover:text-brand-400 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z" />
                        </svg>
                        Make Default
                    </button>
                    <div class="flex items-center gap-2 ml-auto">
                        <input type="text" placeholder="Search employees..." class="text-sm bg-gray-800 border-gray-700 text-white placeholder-gray-500 rounded-lg focus:ring-brand-500 focus:border-brand-500 w-48 px-3 py-1.5">
                    </div>
                </div>
            </header>

            <!-- Schedule Grid -->
            <main class="flex-1 overflow-auto">
                <style>
                    .schedule-cell:hover .add-shift-btn { opacity: 1; }
                    .add-shift-btn { opacity: 0; transition: opacity 0.15s; }
                </style>
                <div class="min-w-[1000px]">
                    <!-- Day Headers -->
                    <div class="grid grid-cols-8 bg-gray-900 border-b border-gray-800 sticky top-0 z-10">
                        <div class="p-4 border-r border-gray-800">
                            <span class="text-sm font-medium text-gray-500">Employee</span>
                        </div>
                        <div class="p-4 border-r border-gray-800 text-center">
                            <div class="text-sm font-medium text-gray-400">Mon</div>
                            <div class="text-lg font-bold text-white">15</div>
                        </div>
                        <div class="p-4 border-r border-gray-800 text-center">
                            <div class="text-sm font-medium text-gray-400">Tue</div>
                            <div class="text-lg font-bold text-white">16</div>
                        </div>
                        <div class="p-4 border-r border-gray-800 text-center">
                            <div class="text-sm font-medium text-gray-400">Wed</div>
                            <div class="text-lg font-bold text-white">17</div>
                        </div>
                        <div class="p-4 border-r border-gray-800 text-center">
                            <div class="text-sm font-medium text-gray-400">Thu</div>
                            <div class="text-lg font-bold text-white">18</div>
                        </div>
                        <div class="p-4 border-r border-gray-800 text-center">
                            <div class="text-sm font-medium text-gray-400">Fri</div>
                            <div class="text-lg font-bold text-white">19</div>
                        </div>
                        <div class="p-4 border-r border-gray-800 text-center bg-gray-800/50">
                            <div class="text-sm font-medium text-gray-500">Sat</div>
                            <div class="text-lg font-bold text-gray-400">20</div>
                        </div>
                        <div class="p-4 text-center bg-gray-800/50">
                            <div class="text-sm font-medium text-gray-500">Sun</div>
                            <div class="text-lg font-bold text-gray-400">21</div>
                        </div>
                    </div>

                    <!-- Department: Front Desk -->
                    <div class="bg-brand-900/30 border-b border-brand-700/30 px-4 py-2">
                        <span class="text-sm font-semibold text-brand-300">Front Desk</span>
                        <span class="text-xs text-brand-400/60 ml-2">3 employees</span>
                    </div>

                    <!-- Employee Row 1 - John Doe -->
                    <div class="grid grid-cols-8 bg-gray-900 border-b border-gray-800 hover:bg-gray-800/50 transition-colors">
                        <div class="p-3 border-r border-gray-800 flex items-center gap-3">
                            <div class="w-8 h-8 bg-brand-500/20 rounded-full flex items-center justify-center text-brand-400 font-medium text-xs">
                                JD
                            </div>
                            <div>
                                <div class="text-sm font-medium text-white">John Doe</div>
                                <div class="text-xs text-gray-500">Cashier</div>
                            </div>
                        </div>
                        <div class="p-2 border-r border-gray-800">
                            <div class="bg-brand-600 text-white rounded-lg p-2 text-xs cursor-move hover:bg-brand-500 transition-colors">
                                <div class="font-medium">9:00 - 17:00</div>
                                <div class="text-brand-200">8h</div>
                            </div>
                        </div>
                        <div class="p-2 border-r border-gray-800">
                            <div class="bg-brand-600 text-white rounded-lg p-2 text-xs cursor-move hover:bg-brand-500 transition-colors">
                                <div class="font-medium">9:00 - 17:00</div>
                                <div class="text-brand-200">8h</div>
                            </div>
                        </div>
                        <div class="p-2 border-r border-gray-800 schedule-cell cursor-pointer hover:bg-gray-800 transition-colors" title="Click to add shift">
                            <div class="add-shift-btn h-full min-h-[44px] border-2 border-dashed border-gray-700 rounded-lg flex items-center justify-center">
                                <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                </svg>
                            </div>
                        </div>
                        <div class="p-2 border-r border-gray-800">
                            <div class="bg-brand-600 text-white rounded-lg p-2 text-xs cursor-move hover:bg-brand-500 transition-colors">
                                <div class="font-medium">12:00 - 20:00</div>
                                <div class="text-brand-200">8h</div>
                            </div>
                        </div>
                        <div class="p-2 border-r border-gray-800">
                            <div class="bg-brand-600 text-white rounded-lg p-2 text-xs cursor-move hover:bg-brand-500 transition-colors">
                                <div class="font-medium">9:00 - 17:00</div>
                                <div class="text-brand-200">8h</div>
                            </div>
                        </div>
                        <div class="p-2 border-r border-gray-800 bg-gray-800/30 schedule-cell cursor-pointer hover:bg-gray-700/50 transition-colors" title="Click to add shift">
                            <div class="add-shift-btn h-full min-h-[44px] border-2 border-dashed border-gray-700 rounded-lg flex items-center justify-center">
                                <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                </svg>
                            </div>
                        </div>
                        <div class="p-2 bg-gray-800/30 schedule-cell cursor-pointer hover:bg-gray-700/50 transition-colors" title="Click to add shift">
                            <div class="add-shift-btn h-full min-h-[44px] border-2 border-dashed border-gray-700 rounded-lg flex items-center justify-center">
                                <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Employee Row 2 - Sarah Mitchell -->
                    <div class="grid grid-cols-8 bg-gray-900 border-b border-gray-800 hover:bg-gray-800/50 transition-colors">
                        <div class="p-3 border-r border-gray-800 flex items-center gap-3">
                            <div class="w-8 h-8 bg-purple-500/20 rounded-full flex items-center justify-center text-purple-400 font-medium text-xs">
                                SM
                            </div>
                            <div>
                                <div class="text-sm font-medium text-white">Sarah Mitchell</div>
                                <div class="text-xs text-gray-500">Cashier</div>
                            </div>
                        </div>
                        <div class="p-2 border-r border-gray-800 schedule-cell cursor-pointer hover:bg-gray-800 transition-colors" title="Click to add shift">
                            <div class="add-shift-btn h-full min-h-[44px] border-2 border-dashed border-gray-700 rounded-lg flex items-center justify-center">
                                <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                </svg>
                            </div>
                        </div>
                        <div class="p-2 border-r border-gray-800">
                            <div class="bg-purple-600 text-white rounded-lg p-2 text-xs cursor-move hover:bg-purple-500 transition-colors">
                                <div class="font-medium">9:00 - 17:00</div>
                                <div class="text-purple-200">8h</div>
                            </div>
                        </div>
                        <div class="p-2 border-r border-gray-800">
                            <div class="bg-purple-600 text-white rounded-lg p-2 text-xs cursor-move hover:bg-purple-500 transition-colors">
                                <div class="font-medium">9:00 - 17:00</div>
                                <div class="text-purple-200">8h</div>
                            </div>
                        </div>
                        <div class="p-2 border-r border-gray-800">
                            <div class="bg-amber-500/20 border border-amber-500/50 rounded-lg p-2 text-xs">
                                <div class="font-medium text-amber-400">Annual Leave</div>
                            </div>
                        </div>
                        <div class="p-2 border-r border-gray-800">
                            <div class="bg-purple-600 text-white rounded-lg p-2 text-xs cursor-move hover:bg-purple-500 transition-colors">
                                <div class="font-medium">9:00 - 17:00</div>
                                <div class="text-purple-200">8h</div>
                            </div>
                        </div>
                        <div class="p-2 border-r border-gray-800 bg-gray-800/30">
                            <div class="bg-purple-600 text-white rounded-lg p-2 text-xs cursor-move hover:bg-purple-500 transition-colors">
                                <div class="font-medium">10:00 - 18:00</div>
                                <div class="text-purple-200">8h</div>
                            </div>
                        </div>
                        <div class="p-2 bg-gray-800/30 schedule-cell cursor-pointer hover:bg-gray-700/50 transition-colors" title="Click to add shift">
                            <div class="add-shift-btn h-full min-h-[44px] border-2 border-dashed border-gray-700 rounded-lg flex items-center justify-center">
                                <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Employee Row 3 - Tom Harris (Supervisor) -->
                    <div class="grid grid-cols-8 bg-gray-900 border-b border-gray-800 hover:bg-gray-800/50 transition-colors">
                        <div class="p-3 border-r border-gray-800 flex items-center gap-3">
                            <div class="w-8 h-8 bg-cyan-500/20 rounded-full flex items-center justify-center text-cyan-400 font-medium text-xs">
                                TH
                            </div>
                            <div>
                                <div class="text-sm font-medium text-white">Tom Harris</div>
                                <div class="text-xs text-gray-500">Supervisor</div>
                            </div>
                        </div>
                        <div class="p-2 border-r border-gray-800">
                            <div class="bg-cyan-600 text-white rounded-lg p-2 text-xs cursor-move hover:bg-cyan-500 transition-colors">
                                <div class="font-medium">8:00 - 16:00</div>
                                <div class="text-cyan-200">8h</div>
                            </div>
                        </div>
                        <div class="p-2 border-r border-gray-800">
                            <div class="bg-cyan-600 text-white rounded-lg p-2 text-xs cursor-move hover:bg-cyan-500 transition-colors">
                                <div class="font-medium">8:00 - 16:00</div>
                                <div class="text-cyan-200">8h</div>
                            </div>
                        </div>
                        <div class="p-2 border-r border-gray-800">
                            <div class="bg-cyan-600 text-white rounded-lg p-2 text-xs cursor-move hover:bg-cyan-500 transition-colors">
                                <div class="font-medium">8:00 - 16:00</div>
                                <div class="text-cyan-200">8h</div>
                            </div>
                        </div>
                        <div class="p-2 border-r border-gray-800">
                            <div class="bg-cyan-600 text-white rounded-lg p-2 text-xs cursor-move hover:bg-cyan-500 transition-colors">
                                <div class="font-medium">8:00 - 16:00</div>
                                <div class="text-cyan-200">8h</div>
                            </div>
                        </div>
                        <div class="p-2 border-r border-gray-800">
                            <div class="bg-cyan-600 text-white rounded-lg p-2 text-xs cursor-move hover:bg-cyan-500 transition-colors">
                                <div class="font-medium">8:00 - 16:00</div>
                                <div class="text-cyan-200">8h</div>
                            </div>
                        </div>
                        <div class="p-2 border-r border-gray-800 bg-gray-800/30 schedule-cell cursor-pointer hover:bg-gray-700/50 transition-colors" title="Click to add shift">
                            <div class="add-shift-btn h-full min-h-[44px] border-2 border-dashed border-gray-700 rounded-lg flex items-center justify-center">
                                <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                </svg>
                            </div>
                        </div>
                        <div class="p-2 bg-gray-800/30 schedule-cell cursor-pointer hover:bg-gray-700/50 transition-colors" title="Click to add shift">
                            <div class="add-shift-btn h-full min-h-[44px] border-2 border-dashed border-gray-700 rounded-lg flex items-center justify-center">
                                <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Department: Warehouse -->
                    <div class="bg-green-500/10 border-b border-green-500/20 px-4 py-2">
                        <span class="text-sm font-semibold text-green-400">Warehouse</span>
                        <span class="text-xs text-green-400/60 ml-2">3 employees</span>
                    </div>

                    <!-- Employee Row 4 - Mike Johnson -->
                    <div class="grid grid-cols-8 bg-gray-900 border-b border-gray-800 hover:bg-gray-800/50 transition-colors">
                        <div class="p-3 border-r border-gray-800 flex items-center gap-3">
                            <div class="w-8 h-8 bg-green-500/20 rounded-full flex items-center justify-center text-green-400 font-medium text-xs">
                                MJ
                            </div>
                            <div>
                                <div class="text-sm font-medium text-white">Mike Johnson</div>
                                <div class="text-xs text-gray-500">Picker</div>
                            </div>
                        </div>
                        <div class="p-2 border-r border-gray-800">
                            <div class="bg-green-600 text-white rounded-lg p-2 text-xs cursor-move hover:bg-green-500 transition-colors">
                                <div class="font-medium">6:00 - 14:00</div>
                                <div class="text-green-200">8h</div>
                            </div>
                        </div>
                        <div class="p-2 border-r border-gray-800">
                            <div class="bg-green-600 text-white rounded-lg p-2 text-xs cursor-move hover:bg-green-500 transition-colors">
                                <div class="font-medium">6:00 - 14:00</div>
                                <div class="text-green-200">8h</div>
                            </div>
                        </div>
                        <div class="p-2 border-r border-gray-800">
                            <div class="bg-green-600 text-white rounded-lg p-2 text-xs cursor-move hover:bg-green-500 transition-colors">
                                <div class="font-medium">6:00 - 14:00</div>
                                <div class="text-green-200">8h</div>
                            </div>
                        </div>
                        <div class="p-2 border-r border-gray-800">
                            <div class="bg-green-600 text-white rounded-lg p-2 text-xs cursor-move hover:bg-green-500 transition-colors">
                                <div class="font-medium">6:00 - 14:00</div>
                                <div class="text-green-200">8h</div>
                            </div>
                        </div>
                        <div class="p-2 border-r border-gray-800">
                            <div class="bg-green-600 text-white rounded-lg p-2 text-xs cursor-move hover:bg-green-500 transition-colors">
                                <div class="font-medium">6:00 - 14:00</div>
                                <div class="text-green-200">8h</div>
                            </div>
                        </div>
                        <div class="p-2 border-r border-gray-800 bg-gray-800/30 schedule-cell cursor-pointer hover:bg-gray-700/50 transition-colors" title="Click to add shift">
                            <div class="add-shift-btn h-full min-h-[44px] border-2 border-dashed border-gray-700 rounded-lg flex items-center justify-center">
                                <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                </svg>
                            </div>
                        </div>
                        <div class="p-2 bg-gray-800/30 schedule-cell cursor-pointer hover:bg-gray-700/50 transition-colors" title="Click to add shift">
                            <div class="add-shift-btn h-full min-h-[44px] border-2 border-dashed border-gray-700 rounded-lg flex items-center justify-center">
                                <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Employee Row 5 - Lisa Wong -->
                    <div class="grid grid-cols-8 bg-gray-900 border-b border-gray-800 hover:bg-gray-800/50 transition-colors">
                        <div class="p-3 border-r border-gray-800 flex items-center gap-3">
                            <div class="w-8 h-8 bg-amber-500/20 rounded-full flex items-center justify-center text-amber-400 font-medium text-xs">
                                LW
                            </div>
                            <div>
                                <div class="text-sm font-medium text-white">Lisa Wong</div>
                                <div class="text-xs text-gray-500">Picker</div>
                            </div>
                        </div>
                        <div class="p-2 border-r border-gray-800">
                            <div class="bg-amber-600 text-white rounded-lg p-2 text-xs cursor-move hover:bg-amber-500 transition-colors">
                                <div class="font-medium">14:00 - 22:00</div>
                                <div class="text-amber-200">8h</div>
                            </div>
                        </div>
                        <div class="p-2 border-r border-gray-800">
                            <div class="bg-amber-600 text-white rounded-lg p-2 text-xs cursor-move hover:bg-amber-500 transition-colors">
                                <div class="font-medium">14:00 - 22:00</div>
                                <div class="text-amber-200">8h</div>
                            </div>
                        </div>
                        <div class="p-2 border-r border-gray-800">
                            <div class="bg-amber-600 text-white rounded-lg p-2 text-xs cursor-move hover:bg-amber-500 transition-colors">
                                <div class="font-medium">14:00 - 22:00</div>
                                <div class="text-amber-200">8h</div>
                            </div>
                        </div>
                        <div class="p-2 border-r border-gray-800 schedule-cell cursor-pointer hover:bg-gray-800 transition-colors" title="Click to add shift">
                            <div class="add-shift-btn h-full min-h-[44px] border-2 border-dashed border-gray-700 rounded-lg flex items-center justify-center">
                                <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                </svg>
                            </div>
                        </div>
                        <div class="p-2 border-r border-gray-800 schedule-cell cursor-pointer hover:bg-gray-800 transition-colors" title="Click to add shift">
                            <div class="add-shift-btn h-full min-h-[44px] border-2 border-dashed border-gray-700 rounded-lg flex items-center justify-center">
                                <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                </svg>
                            </div>
                        </div>
                        <div class="p-2 border-r border-gray-800 bg-gray-800/30">
                            <div class="bg-amber-600 text-white rounded-lg p-2 text-xs cursor-move hover:bg-amber-500 transition-colors">
                                <div class="font-medium">10:00 - 18:00</div>
                                <div class="text-amber-200">8h</div>
                            </div>
                        </div>
                        <div class="p-2 bg-gray-800/30">
                            <div class="bg-amber-600 text-white rounded-lg p-2 text-xs cursor-move hover:bg-amber-500 transition-colors">
                                <div class="font-medium">10:00 - 18:00</div>
                                <div class="text-amber-200">8h</div>
                            </div>
                        </div>
                    </div>

                    <!-- Employee Row 6 - David Chen (Supervisor) -->
                    <div class="grid grid-cols-8 bg-gray-900 border-b border-gray-800 hover:bg-gray-800/50 transition-colors">
                        <div class="p-3 border-r border-gray-800 flex items-center gap-3">
                            <div class="w-8 h-8 bg-teal-500/20 rounded-full flex items-center justify-center text-teal-400 font-medium text-xs">
                                DC
                            </div>
                            <div>
                                <div class="text-sm font-medium text-white">David Chen</div>
                                <div class="text-xs text-gray-500">Supervisor</div>
                            </div>
                        </div>
                        <div class="p-2 border-r border-gray-800">
                            <div class="bg-teal-600 text-white rounded-lg p-2 text-xs cursor-move hover:bg-teal-500 transition-colors">
                                <div class="font-medium">6:00 - 14:00</div>
                                <div class="text-teal-200">8h</div>
                            </div>
                        </div>
                        <div class="p-2 border-r border-gray-800">
                            <div class="bg-teal-600 text-white rounded-lg p-2 text-xs cursor-move hover:bg-teal-500 transition-colors">
                                <div class="font-medium">6:00 - 14:00</div>
                                <div class="text-teal-200">8h</div>
                            </div>
                        </div>
                        <div class="p-2 border-r border-gray-800">
                            <div class="bg-teal-600 text-white rounded-lg p-2 text-xs cursor-move hover:bg-teal-500 transition-colors">
                                <div class="font-medium">6:00 - 14:00</div>
                                <div class="text-teal-200">8h</div>
                            </div>
                        </div>
                        <div class="p-2 border-r border-gray-800">
                            <div class="bg-teal-600 text-white rounded-lg p-2 text-xs cursor-move hover:bg-teal-500 transition-colors">
                                <div class="font-medium">6:00 - 14:00</div>
                                <div class="text-teal-200">8h</div>
                            </div>
                        </div>
                        <div class="p-2 border-r border-gray-800">
                            <div class="bg-teal-600 text-white rounded-lg p-2 text-xs cursor-move hover:bg-teal-500 transition-colors">
                                <div class="font-medium">6:00 - 14:00</div>
                                <div class="text-teal-200">8h</div>
                            </div>
                        </div>
                        <div class="p-2 border-r border-gray-800 bg-gray-800/30 schedule-cell cursor-pointer hover:bg-gray-700/50 transition-colors" title="Click to add shift">
                            <div class="add-shift-btn h-full min-h-[44px] border-2 border-dashed border-gray-700 rounded-lg flex items-center justify-center">
                                <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                </svg>
                            </div>
                        </div>
                        <div class="p-2 bg-gray-800/30 schedule-cell cursor-pointer hover:bg-gray-700/50 transition-colors" title="Click to add shift">
                            <div class="add-shift-btn h-full min-h-[44px] border-2 border-dashed border-gray-700 rounded-lg flex items-center justify-center">
                                <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Department: Kitchen -->
                    <div class="bg-red-500/10 border-b border-red-500/20 px-4 py-2">
                        <span class="text-sm font-semibold text-red-400">Kitchen</span>
                        <span class="text-xs text-red-400/60 ml-2">2 employees</span>
                    </div>

                    <!-- Employee Row 7 - Emma Foster -->
                    <div class="grid grid-cols-8 bg-gray-900 border-b border-gray-800 hover:bg-gray-800/50 transition-colors">
                        <div class="p-3 border-r border-gray-800 flex items-center gap-3">
                            <div class="w-8 h-8 bg-rose-500/20 rounded-full flex items-center justify-center text-rose-400 font-medium text-xs">
                                EF
                            </div>
                            <div>
                                <div class="text-sm font-medium text-white">Emma Foster</div>
                                <div class="text-xs text-gray-500">Cook</div>
                            </div>
                        </div>
                        <div class="p-2 border-r border-gray-800">
                            <div class="bg-rose-600 text-white rounded-lg p-2 text-xs cursor-move hover:bg-rose-500 transition-colors">
                                <div class="font-medium">7:00 - 15:00</div>
                                <div class="text-rose-200">8h</div>
                            </div>
                        </div>
                        <div class="p-2 border-r border-gray-800">
                            <div class="bg-rose-600 text-white rounded-lg p-2 text-xs cursor-move hover:bg-rose-500 transition-colors">
                                <div class="font-medium">7:00 - 15:00</div>
                                <div class="text-rose-200">8h</div>
                            </div>
                        </div>
                        <div class="p-2 border-r border-gray-800 schedule-cell cursor-pointer hover:bg-gray-800 transition-colors" title="Click to add shift">
                            <div class="add-shift-btn h-full min-h-[44px] border-2 border-dashed border-gray-700 rounded-lg flex items-center justify-center">
                                <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                </svg>
                            </div>
                        </div>
                        <div class="p-2 border-r border-gray-800">
                            <div class="bg-rose-600 text-white rounded-lg p-2 text-xs cursor-move hover:bg-rose-500 transition-colors">
                                <div class="font-medium">7:00 - 15:00</div>
                                <div class="text-rose-200">8h</div>
                            </div>
                        </div>
                        <div class="p-2 border-r border-gray-800">
                            <div class="bg-rose-600 text-white rounded-lg p-2 text-xs cursor-move hover:bg-rose-500 transition-colors">
                                <div class="font-medium">7:00 - 15:00</div>
                                <div class="text-rose-200">8h</div>
                            </div>
                        </div>
                        <div class="p-2 border-r border-gray-800 bg-gray-800/30">
                            <div class="bg-rose-600 text-white rounded-lg p-2 text-xs cursor-move hover:bg-rose-500 transition-colors">
                                <div class="font-medium">8:00 - 16:00</div>
                                <div class="text-rose-200">8h</div>
                            </div>
                        </div>
                        <div class="p-2 bg-gray-800/30">
                            <div class="bg-rose-600 text-white rounded-lg p-2 text-xs cursor-move hover:bg-rose-500 transition-colors">
                                <div class="font-medium">8:00 - 16:00</div>
                                <div class="text-rose-200">8h</div>
                            </div>
                        </div>
                    </div>

                    <!-- Employee Row 8 - James Wilson -->
                    <div class="grid grid-cols-8 bg-gray-900 border-b border-gray-800 hover:bg-gray-800/50 transition-colors">
                        <div class="p-3 border-r border-gray-800 flex items-center gap-3">
                            <div class="w-8 h-8 bg-orange-500/20 rounded-full flex items-center justify-center text-orange-400 font-medium text-xs">
                                JW
                            </div>
                            <div>
                                <div class="text-sm font-medium text-white">James Wilson</div>
                                <div class="text-xs text-gray-500">Cook</div>
                            </div>
                        </div>
                        <div class="p-2 border-r border-gray-800 schedule-cell cursor-pointer hover:bg-gray-800 transition-colors" title="Click to add shift">
                            <div class="add-shift-btn h-full min-h-[44px] border-2 border-dashed border-gray-700 rounded-lg flex items-center justify-center">
                                <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                </svg>
                            </div>
                        </div>
                        <div class="p-2 border-r border-gray-800 schedule-cell cursor-pointer hover:bg-gray-800 transition-colors" title="Click to add shift">
                            <div class="add-shift-btn h-full min-h-[44px] border-2 border-dashed border-gray-700 rounded-lg flex items-center justify-center">
                                <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                </svg>
                            </div>
                        </div>
                        <div class="p-2 border-r border-gray-800">
                            <div class="bg-orange-600 text-white rounded-lg p-2 text-xs cursor-move hover:bg-orange-500 transition-colors">
                                <div class="font-medium">15:00 - 23:00</div>
                                <div class="text-orange-200">8h</div>
                            </div>
                        </div>
                        <div class="p-2 border-r border-gray-800">
                            <div class="bg-orange-600 text-white rounded-lg p-2 text-xs cursor-move hover:bg-orange-500 transition-colors">
                                <div class="font-medium">15:00 - 23:00</div>
                                <div class="text-orange-200">8h</div>
                            </div>
                        </div>
                        <div class="p-2 border-r border-gray-800">
                            <div class="bg-orange-600 text-white rounded-lg p-2 text-xs cursor-move hover:bg-orange-500 transition-colors">
                                <div class="font-medium">15:00 - 23:00</div>
                                <div class="text-orange-200">8h</div>
                            </div>
                        </div>
                        <div class="p-2 border-r border-gray-800 bg-gray-800/30">
                            <div class="bg-orange-600 text-white rounded-lg p-2 text-xs cursor-move hover:bg-orange-500 transition-colors">
                                <div class="font-medium">15:00 - 23:00</div>
                                <div class="text-orange-200">8h</div>
                            </div>
                        </div>
                        <div class="p-2 bg-gray-800/30">
                            <div class="bg-orange-600 text-white rounded-lg p-2 text-xs cursor-move hover:bg-orange-500 transition-colors">
                                <div class="font-medium">15:00 - 23:00</div>
                                <div class="text-orange-200">8h</div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>

            <!-- Bottom Action Bar -->
            <footer class="bg-gray-900 border-t border-gray-800 px-6 py-3">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-6 text-sm">
                        <div>
                            <span class="text-gray-500">Total hours:</span>
                            <span class="font-semibold text-white ml-1">312h</span>
                        </div>
                        <div>
                            <span class="text-gray-500">Scheduled:</span>
                            <span class="font-semibold text-green-400 ml-1">288h</span>
                        </div>
                        <div>
                            <span class="text-gray-500">Unscheduled:</span>
                            <span class="font-semibold text-gray-400 ml-1">24h</span>
                        </div>
                        <div>
                            <span class="text-gray-500">Employees:</span>
                            <span class="font-semibold text-white ml-1">8</span>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <button class="px-4 py-2 text-gray-300 bg-gray-800 border border-gray-700 rounded-lg font-medium hover:bg-gray-700 transition-colors">
                            Copy Previous Week
                        </button>
                        <button class="px-4 py-2 text-gray-300 bg-gray-800 border border-gray-700 rounded-lg font-medium hover:bg-gray-700 transition-colors">
                            Clear All
                        </button>
                    </div>
                </div>
            </footer>
        </div>
    </div>
</x-samples.layout>
