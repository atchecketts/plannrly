<x-samples.layout title="Design Samples">
    <div class="min-h-full py-12 px-4">
        <div class="max-w-4xl mx-auto">
            <div class="text-center mb-12">
                <div class="flex items-center justify-center mb-4">
                    <x-samples.logo class="h-16" />
                </div>
                <p class="text-lg text-gray-400">Design Samples for Review</p>
                <p class="text-sm text-gray-600 mt-2">Click any card below to view the design sample</p>
            </div>

            <div class="grid md:grid-cols-2 gap-6">
                <!-- Login -->
                <a href="/samples/login" class="group bg-gray-900 rounded-2xl border border-gray-800 overflow-hidden hover:border-brand-700 transition-all">
                    <div class="aspect-video bg-brand-900 flex items-center justify-center relative overflow-hidden">
                        <div class="absolute inset-0 opacity-20">
                            <div class="absolute top-10 left-10 w-32 h-32 bg-white rounded-full blur-3xl"></div>
                        </div>
                        <div class="text-center text-white relative">
                            <svg class="w-16 h-16 mx-auto mb-2 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                            </svg>
                            <span class="text-sm opacity-75">Preview</span>
                        </div>
                    </div>
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-white group-hover:text-brand-400 transition-colors">Login Page</h3>
                        <p class="text-sm text-gray-500 mt-1">Split-screen layout with branding and login form</p>
                    </div>
                </a>

                <!-- Register -->
                <a href="/samples/register" class="group bg-gray-900 rounded-2xl border border-gray-800 overflow-hidden hover:border-brand-700 transition-all">
                    <div class="aspect-video bg-brand-900 flex items-center justify-center relative overflow-hidden">
                        <div class="absolute inset-0 opacity-20">
                            <div class="absolute top-10 left-10 w-32 h-32 bg-white rounded-full blur-3xl"></div>
                        </div>
                        <div class="text-center text-white relative">
                            <svg class="w-16 h-16 mx-auto mb-2 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                            </svg>
                            <span class="text-sm opacity-75">Preview</span>
                        </div>
                    </div>
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-white group-hover:text-brand-400 transition-colors">Registration Page</h3>
                        <p class="text-sm text-gray-500 mt-1">Company signup with name, contact details, and password</p>
                    </div>
                </a>

                <!-- Admin Dashboard -->
                <a href="/samples/admin-dashboard" class="group bg-gray-900 rounded-2xl border border-gray-800 overflow-hidden hover:border-brand-700 transition-all">
                    <div class="aspect-video bg-gray-800 flex items-center justify-center">
                        <div class="text-center text-white">
                            <svg class="w-16 h-16 mx-auto mb-2 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                            </svg>
                            <span class="text-sm opacity-75">Preview</span>
                        </div>
                    </div>
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-white group-hover:text-brand-400 transition-colors">Admin Dashboard</h3>
                        <p class="text-sm text-gray-500 mt-1">Full desktop layout with sidebar, stats, and action items</p>
                    </div>
                </a>

                <!-- Schedule Calendar -->
                <a href="/samples/schedule" class="group bg-gray-900 rounded-2xl border border-gray-800 overflow-hidden hover:border-brand-700 transition-all">
                    <div class="aspect-video bg-gray-800 flex items-center justify-center">
                        <div class="text-center text-white">
                            <svg class="w-16 h-16 mx-auto mb-2 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            <span class="text-sm opacity-75">Preview</span>
                        </div>
                    </div>
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-white group-hover:text-brand-400 transition-colors">Shift Schedule</h3>
                        <p class="text-sm text-gray-500 mt-1">Weekly calendar view with drag-and-drop shifts</p>
                    </div>
                </a>

                <!-- Employee Mobile -->
                <a href="/samples/employee-mobile" class="group bg-gray-900 rounded-2xl border border-gray-800 overflow-hidden hover:border-brand-700 transition-all md:col-span-2">
                    <div class="md:flex">
                        <div class="md:w-1/3 aspect-video md:aspect-auto bg-brand-900 flex items-center justify-center p-8 relative overflow-hidden">
                            <div class="absolute inset-0 opacity-20">
                                <div class="absolute top-10 left-10 w-32 h-32 bg-white rounded-full blur-3xl"></div>
                            </div>
                            <div class="text-center text-white relative">
                                <svg class="w-16 h-16 mx-auto mb-2 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                </svg>
                                <span class="text-sm opacity-75">Mobile Preview</span>
                            </div>
                        </div>
                        <div class="p-6 md:flex-1">
                            <h3 class="text-lg font-semibold text-white group-hover:text-brand-400 transition-colors">Employee Mobile View</h3>
                            <p class="text-sm text-gray-500 mt-1">Dedicated mobile experience with clock in/out, upcoming shifts, and leave balance</p>
                            <div class="mt-4 flex flex-wrap gap-2">
                                <span class="px-2 py-1 bg-gray-800 text-gray-400 text-xs rounded-full">Clock In/Out</span>
                                <span class="px-2 py-1 bg-gray-800 text-gray-400 text-xs rounded-full">View Shifts</span>
                                <span class="px-2 py-1 bg-gray-800 text-gray-400 text-xs rounded-full">Leave Balance</span>
                                <span class="px-2 py-1 bg-gray-800 text-gray-400 text-xs rounded-full">Bottom Nav</span>
                            </div>
                        </div>
                    </div>
                </a>
            </div>

            <div class="mt-12 text-center text-sm text-gray-600">
                <p>These are static design mockups for review purposes.</p>
                <p class="mt-1">No functionality is implemented yet.</p>
            </div>
        </div>
    </div>
</x-samples.layout>
