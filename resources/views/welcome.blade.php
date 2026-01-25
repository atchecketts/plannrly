<x-layouts.guest title="Plannrly - Shift Management">
    <div class="min-h-full">
        <!-- Header -->
        <header class="border-b border-gray-800">
            <nav class="max-w-7xl mx-auto px-6 py-4 flex items-center justify-between">
                <x-logo class="h-10" />
                <div class="flex items-center gap-4">
                    @auth
                        <a href="{{ route('dashboard') }}" class="px-4 py-2 text-sm font-medium text-white bg-brand-900 rounded-lg hover:bg-brand-800 transition-colors">
                            Dashboard
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="px-4 py-2 text-sm font-medium text-gray-300 hover:text-white transition-colors">
                            Sign in
                        </a>
                        <a href="{{ route('register') }}" class="px-4 py-2 text-sm font-medium text-white bg-brand-900 rounded-lg hover:bg-brand-800 transition-colors">
                            Get Started
                        </a>
                    @endauth
                </div>
            </nav>
        </header>

        <!-- Hero Section -->
        <section class="relative overflow-hidden">
            <div class="absolute inset-0 opacity-20">
                <div class="absolute top-20 left-1/4 w-96 h-96 bg-brand-500 rounded-full blur-3xl"></div>
                <div class="absolute bottom-20 right-1/4 w-96 h-96 bg-brand-700 rounded-full blur-3xl"></div>
            </div>

            <div class="relative max-w-7xl mx-auto px-6 py-24 lg:py-32">
                <div class="text-center max-w-3xl mx-auto">
                    <h1 class="text-5xl lg:text-6xl font-bold text-white leading-tight">
                        Shift scheduling<br>
                        <span class="text-brand-400">made simple</span>
                    </h1>
                    <p class="mt-6 text-xl text-gray-400 max-w-2xl mx-auto">
                        Manage your team's schedules, track time, handle leave requests, and streamline shift swaps - all in one powerful platform.
                    </p>
                    <div class="mt-10 flex items-center justify-center gap-4">
                        <a href="{{ route('register') }}" class="px-6 py-3 text-base font-medium text-white bg-brand-900 rounded-lg hover:bg-brand-800 transition-colors">
                            Start free trial
                        </a>
                        <a href="#features" class="px-6 py-3 text-base font-medium text-gray-300 border border-gray-700 rounded-lg hover:bg-gray-800 transition-colors">
                            Learn more
                        </a>
                    </div>
                </div>

                <!-- Stats -->
                <div class="mt-20 grid grid-cols-3 gap-8 max-w-2xl mx-auto">
                    <div class="text-center">
                        <div class="text-4xl font-bold text-white">10k+</div>
                        <div class="mt-1 text-sm text-gray-500">Active Users</div>
                    </div>
                    <div class="text-center">
                        <div class="text-4xl font-bold text-white">500+</div>
                        <div class="mt-1 text-sm text-gray-500">Companies</div>
                    </div>
                    <div class="text-center">
                        <div class="text-4xl font-bold text-white">99.9%</div>
                        <div class="mt-1 text-sm text-gray-500">Uptime</div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Features Section -->
        <section id="features" class="border-t border-gray-800 bg-gray-900/50">
            <div class="max-w-7xl mx-auto px-6 py-24">
                <div class="text-center mb-16">
                    <h2 class="text-3xl font-bold text-white">Everything you need</h2>
                    <p class="mt-4 text-lg text-gray-400">Powerful workforce management that scales with your business</p>
                </div>

                <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <!-- Feature 1 -->
                    <div class="bg-gray-900 rounded-2xl border border-gray-800 p-6 hover:border-brand-700 transition-colors">
                        <div class="w-12 h-12 bg-brand-900/50 rounded-xl flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-white">Shift Scheduling</h3>
                        <p class="mt-2 text-gray-400">Create and manage rotas with an intuitive drag-and-drop interface across locations and departments.</p>
                    </div>

                    <!-- Feature 2 -->
                    <div class="bg-gray-900 rounded-2xl border border-gray-800 p-6 hover:border-brand-700 transition-colors">
                        <div class="w-12 h-12 bg-brand-900/50 rounded-xl flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-white">Time Tracking</h3>
                        <p class="mt-2 text-gray-400">Employees clock in and out with ease. Track actual hours worked against scheduled shifts.</p>
                    </div>

                    <!-- Feature 3 -->
                    <div class="bg-gray-900 rounded-2xl border border-gray-800 p-6 hover:border-brand-700 transition-colors">
                        <div class="w-12 h-12 bg-brand-900/50 rounded-xl flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-white">Leave Management</h3>
                        <p class="mt-2 text-gray-400">Handle holiday requests, sick leave, and absences with automatic allowance tracking.</p>
                    </div>

                    <!-- Feature 4 -->
                    <div class="bg-gray-900 rounded-2xl border border-gray-800 p-6 hover:border-brand-700 transition-colors">
                        <div class="w-12 h-12 bg-brand-900/50 rounded-xl flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-white">Shift Swaps</h3>
                        <p class="mt-2 text-gray-400">Empower employees to request shift swaps with colleagues. Managers approve with a single click.</p>
                    </div>

                    <!-- Feature 5 -->
                    <div class="bg-gray-900 rounded-2xl border border-gray-800 p-6 hover:border-brand-700 transition-colors">
                        <div class="w-12 h-12 bg-brand-900/50 rounded-xl flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-white">Multi-Location</h3>
                        <p class="mt-2 text-gray-400">Manage multiple locations with dedicated departments and roles for distributed teams.</p>
                    </div>

                    <!-- Feature 6 -->
                    <div class="bg-gray-900 rounded-2xl border border-gray-800 p-6 hover:border-brand-700 transition-colors">
                        <div class="w-12 h-12 bg-brand-900/50 rounded-xl flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-white">Role-Based Access</h3>
                        <p class="mt-2 text-gray-400">Granular permissions for admins, location managers, department heads, and employees.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- CTA Section -->
        <section class="border-t border-gray-800 bg-brand-900 relative overflow-hidden">
            <div class="absolute inset-0 opacity-20">
                <div class="absolute top-10 left-1/4 w-64 h-64 bg-white rounded-full blur-3xl"></div>
                <div class="absolute bottom-10 right-1/4 w-64 h-64 bg-brand-400 rounded-full blur-3xl"></div>
            </div>

            <div class="relative max-w-4xl mx-auto px-6 py-24 text-center">
                <h2 class="text-3xl lg:text-4xl font-bold text-white">
                    Ready to streamline your scheduling?
                </h2>
                <p class="mt-4 text-lg text-brand-200 max-w-2xl mx-auto">
                    Join hundreds of businesses already using Plannrly to manage their workforce efficiently.
                </p>
                <div class="mt-10 flex items-center justify-center gap-4">
                    <a href="{{ route('register') }}" class="px-6 py-3 text-base font-medium text-brand-900 bg-white rounded-lg hover:bg-gray-100 transition-colors">
                        Get started for free
                    </a>
                    <a href="{{ route('login') }}" class="px-6 py-3 text-base font-medium text-white border border-white/30 rounded-lg hover:bg-white/10 transition-colors">
                        Sign in
                    </a>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="border-t border-gray-800">
            <div class="max-w-7xl mx-auto px-6 py-8 flex flex-col md:flex-row items-center justify-between gap-4">
                <p class="text-sm text-gray-500">
                    &copy; {{ date('Y') }} Plannrly. All rights reserved.
                </p>
                <p class="text-sm text-gray-600">
                    Multi-tenant SaaS shift management
                </p>
            </div>
        </footer>
    </div>
</x-layouts.guest>
