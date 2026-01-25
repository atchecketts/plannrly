<x-layouts.guest title="Login">
    <div class="min-h-full flex">
        <!-- Left Side - Branding -->
        <div class="hidden lg:flex lg:w-1/2 bg-brand-900 p-12 flex-col justify-between relative overflow-hidden">
            <!-- Background Pattern -->
            <div class="absolute inset-0 opacity-10">
                <div class="absolute top-20 left-20 w-64 h-64 bg-white rounded-full blur-3xl"></div>
                <div class="absolute bottom-20 right-20 w-96 h-96 bg-brand-400 rounded-full blur-3xl"></div>
            </div>

            <div class="relative">
                <x-logo class="h-12" />
            </div>

            <div class="relative space-y-6">
                <h1 class="text-4xl font-bold text-white leading-tight">
                    Simplify your<br>shift scheduling
                </h1>
                <p class="text-brand-200 text-lg max-w-md">
                    Manage rotas, track time, and keep your team organized with our intuitive workforce management platform.
                </p>

                <div class="flex gap-8 pt-4">
                    <div>
                        <div class="text-3xl font-bold text-white">10k+</div>
                        <div class="text-brand-300 text-sm">Active Users</div>
                    </div>
                    <div>
                        <div class="text-3xl font-bold text-white">500+</div>
                        <div class="text-brand-300 text-sm">Companies</div>
                    </div>
                    <div>
                        <div class="text-3xl font-bold text-white">99.9%</div>
                        <div class="text-brand-300 text-sm">Uptime</div>
                    </div>
                </div>
            </div>

            <div class="relative text-brand-300 text-sm">
                &copy; {{ date('Y') }} Plannrly. All rights reserved.
            </div>
        </div>

        <!-- Right Side - Login Form -->
        <div class="flex-1 flex items-center justify-center p-8 bg-gray-900">
            <div class="w-full max-w-md">
                <!-- Mobile Logo -->
                <div class="lg:hidden flex items-center justify-center mb-8">
                    <x-logo class="h-10" />
                </div>

                <div class="text-center mb-8">
                    <h2 class="text-2xl font-bold text-white">Welcome back</h2>
                    <p class="text-gray-400 mt-2">Sign in to your account to continue</p>
                </div>

                <form class="space-y-5" action="{{ route('login') }}" method="POST">
                    @csrf

                    @if ($errors->any())
                        <div class="rounded-lg bg-red-500/10 border border-red-500/20 p-4">
                            <div class="flex items-start gap-3">
                                <svg class="w-5 h-5 text-red-400 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <div class="text-sm text-red-400">
                                    @foreach ($errors->all() as $error)
                                        <p>{{ $error }}</p>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif

                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-300 mb-1.5">Email address</label>
                        <input type="email" name="email" id="email" value="{{ old('email') }}" placeholder="you@company.com" class="w-full px-4 py-3 bg-gray-800 border border-gray-700 text-white placeholder-gray-500 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-brand-500 outline-none transition-colors">
                        @error('email')
                            <p class="mt-1.5 text-sm text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label for="password" class="block text-sm font-medium text-gray-300">Password</label>
                            <a href="#" class="text-sm text-brand-400 hover:text-brand-300">Forgot password?</a>
                        </div>
                        <input type="password" name="password" id="password" placeholder="Enter your password" class="w-full px-4 py-3 bg-gray-800 border border-gray-700 text-white placeholder-gray-500 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-brand-500 outline-none transition-colors">
                        @error('password')
                            <p class="mt-1.5 text-sm text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex items-center">
                        <input type="checkbox" name="remember" id="remember" value="1" class="w-4 h-4 text-brand-600 bg-gray-800 border-gray-600 rounded focus:ring-brand-500">
                        <label for="remember" class="ml-2 text-sm text-gray-400">Remember me for 30 days</label>
                    </div>

                    <button type="submit" class="w-full bg-brand-900 text-white py-3 px-4 rounded-lg font-medium hover:bg-brand-800 focus:ring-4 focus:ring-brand-500/25 transition-colors">
                        Sign in
                    </button>
                </form>

                <div class="mt-8">
                    <div class="relative">
                        <div class="absolute inset-0 flex items-center">
                            <div class="w-full border-t border-gray-700"></div>
                        </div>
                        <div class="relative flex justify-center text-sm">
                            <span class="px-4 bg-gray-900 text-gray-500">New to Plannrly?</span>
                        </div>
                    </div>

                    <a href="{{ route('register') }}" class="mt-4 w-full flex items-center justify-center gap-2 border border-gray-700 text-gray-300 py-3 px-4 rounded-lg font-medium hover:bg-gray-800 transition-colors">
                        Create an account
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                        </svg>
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-layouts.guest>
