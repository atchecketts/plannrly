<x-samples.layout title="Register">
    <div class="min-h-full flex">
        <!-- Left Side - Branding -->
        <div class="hidden lg:flex lg:w-1/2 bg-brand-900 p-12 flex-col justify-between relative overflow-hidden">
            <!-- Background Pattern -->
            <div class="absolute inset-0 opacity-10">
                <div class="absolute top-20 left-20 w-64 h-64 bg-white rounded-full blur-3xl"></div>
                <div class="absolute bottom-20 right-20 w-96 h-96 bg-brand-400 rounded-full blur-3xl"></div>
            </div>

            <div class="relative">
                <x-samples.logo class="h-12" />
            </div>

            <div class="relative space-y-6">
                <h1 class="text-4xl font-bold text-white leading-tight">
                    Start managing<br>your team today
                </h1>
                <p class="text-brand-200 text-lg max-w-md">
                    Join thousands of businesses using Plannrly to streamline their workforce scheduling.
                </p>

                <div class="space-y-4 pt-4">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 bg-brand-700/50 rounded-full flex items-center justify-center">
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                        </div>
                        <span class="text-white">Free 14-day trial</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 bg-brand-700/50 rounded-full flex items-center justify-center">
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                        </div>
                        <span class="text-white">No credit card required</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 bg-brand-700/50 rounded-full flex items-center justify-center">
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                        </div>
                        <span class="text-white">Cancel anytime</span>
                    </div>
                </div>
            </div>

            <div class="relative text-brand-300 text-sm">
                &copy; 2024 Plannrly. All rights reserved.
            </div>
        </div>

        <!-- Right Side - Registration Form -->
        <div class="flex-1 flex items-center justify-center p-8 overflow-y-auto bg-gray-900">
            <div class="w-full max-w-md py-8">
                <!-- Mobile Logo -->
                <div class="lg:hidden flex items-center justify-center mb-8">
                    <x-samples.logo class="h-10" />
                </div>

                <div class="text-center mb-8">
                    <h2 class="text-2xl font-bold text-white">Create your account</h2>
                    <p class="text-gray-400 mt-2">Get started with your free trial</p>
                </div>

                <form class="space-y-5">
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-1.5">Company name</label>
                        <input type="text" placeholder="Acme Corporation" class="w-full px-4 py-3 bg-gray-800 border border-gray-700 text-white placeholder-gray-500 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-brand-500 outline-none transition-colors">
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-1.5">First name</label>
                            <input type="text" placeholder="John" class="w-full px-4 py-3 bg-gray-800 border border-gray-700 text-white placeholder-gray-500 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-brand-500 outline-none transition-colors">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-1.5">Last name</label>
                            <input type="text" placeholder="Smith" class="w-full px-4 py-3 bg-gray-800 border border-gray-700 text-white placeholder-gray-500 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-brand-500 outline-none transition-colors">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-1.5">Work email</label>
                        <input type="email" placeholder="john@acme.com" class="w-full px-4 py-3 bg-gray-800 border border-gray-700 text-white placeholder-gray-500 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-brand-500 outline-none transition-colors">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-1.5">Password</label>
                        <input type="password" placeholder="Create a strong password" class="w-full px-4 py-3 bg-gray-800 border border-gray-700 text-white placeholder-gray-500 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-brand-500 outline-none transition-colors">
                        <p class="text-xs text-gray-500 mt-1.5">Must be at least 8 characters</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-1.5">Confirm password</label>
                        <input type="password" placeholder="Confirm your password" class="w-full px-4 py-3 bg-gray-800 border border-gray-700 text-white placeholder-gray-500 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-brand-500 outline-none transition-colors">
                    </div>

                    <div class="flex items-start">
                        <input type="checkbox" id="terms" class="w-4 h-4 text-brand-600 bg-gray-800 border-gray-600 rounded focus:ring-brand-500 mt-0.5">
                        <label for="terms" class="ml-2 text-sm text-gray-400">
                            I agree to the <a href="#" class="text-brand-400 hover:underline">Terms of Service</a> and <a href="#" class="text-brand-400 hover:underline">Privacy Policy</a>
                        </label>
                    </div>

                    <button type="submit" class="w-full bg-brand-900 text-white py-3 px-4 rounded-lg font-medium hover:bg-brand-800 focus:ring-4 focus:ring-brand-500/25 transition-colors">
                        Create account
                    </button>
                </form>

                <p class="mt-8 text-center text-sm text-gray-500">
                    Already have an account?
                    <a href="/samples/login" class="text-brand-400 hover:text-brand-300 font-medium">Sign in</a>
                </p>
            </div>
        </div>
    </div>
</x-samples.layout>
