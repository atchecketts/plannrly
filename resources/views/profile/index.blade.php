<x-layouts.app title="My Profile">
    <div class="max-w-4xl">
        <div class="bg-gray-900 rounded-lg border border-gray-800 mb-6">
            <div class="px-6 py-4 flex flex-col lg:flex-row lg:items-center lg:justify-between">
                <div class="flex items-center gap-4">
                    <div class="relative">
                        @if($user->avatar_path)
                            <img src="{{ Storage::url($user->avatar_path) }}" alt="{{ $user->full_name }}" class="w-16 h-16 rounded-full object-cover">
                        @else
                            <div class="w-16 h-16 rounded-full bg-brand-900 flex items-center justify-center text-white text-xl font-semibold">
                                {{ $user->initials }}
                            </div>
                        @endif
                    </div>
                    <div class="min-w-0 flex-1">
                        <h2 class="text-lg font-semibold text-white">{{ $user->full_name }}</h2>
                        <div class="mt-1 flex flex-col sm:flex-row sm:flex-wrap sm:gap-x-6">
                            <p class="text-sm text-gray-400">{{ $user->email }}</p>
                            @if($user->phone)
                                <p class="text-sm text-gray-400">{{ $user->phone }}</p>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="mt-4 flex gap-3 lg:ml-4 lg:mt-0">
                    <a href="{{ route('profile.edit') }}" class="bg-brand-900 text-white py-2.5 px-4 rounded-lg font-medium hover:bg-brand-800 transition-colors">
                        Edit Profile
                    </a>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
            {{-- Contact Information --}}
            <div class="bg-gray-900 rounded-lg border border-gray-800">
                <div class="px-6 py-4 border-b border-gray-800 flex justify-between items-center">
                    <h3 class="text-base font-semibold text-white">Contact Information</h3>
                    <a href="{{ route('profile.edit') }}" class="text-sm text-brand-400 hover:text-brand-300">Edit</a>
                </div>
                <div class="p-6 space-y-4">
                    <div>
                        <dt class="text-sm text-gray-400">Full Name</dt>
                        <dd class="mt-1 text-sm text-white">{{ $user->full_name }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-gray-400">Email Address</dt>
                        <dd class="mt-1 text-sm text-white">{{ $user->email }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-gray-400">Phone Number</dt>
                        <dd class="mt-1 text-sm text-white">{{ $user->phone ?? 'Not provided' }}</dd>
                    </div>
                </div>
            </div>

            {{-- Profile Photo --}}
            <div class="bg-gray-900 rounded-lg border border-gray-800">
                <div class="px-6 py-4 border-b border-gray-800">
                    <h3 class="text-base font-semibold text-white">Profile Photo</h3>
                </div>
                <div class="p-6">
                    <div class="flex items-center gap-4">
                        @if($user->avatar_path)
                            <img src="{{ Storage::url($user->avatar_path) }}" alt="{{ $user->full_name }}" class="w-20 h-20 rounded-full object-cover">
                        @else
                            <div class="w-20 h-20 rounded-full bg-brand-900 flex items-center justify-center text-white text-2xl font-semibold">
                                {{ $user->initials }}
                            </div>
                        @endif
                        <div class="flex-1">
                            <form action="{{ route('profile.avatar.update') }}" method="POST" enctype="multipart/form-data" class="flex flex-col gap-2">
                                @csrf
                                <input type="file" name="avatar" accept="image/*" class="text-sm text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-brand-900 file:text-white hover:file:bg-brand-800 file:cursor-pointer">
                                @error('avatar')
                                    <p class="text-sm text-red-400">{{ $message }}</p>
                                @enderror
                                <div class="flex gap-2">
                                    <button type="submit" class="text-sm text-brand-400 hover:text-brand-300">Upload new photo</button>
                                    @if($user->avatar_path)
                                        <span class="text-gray-600">|</span>
                                        <form action="{{ route('profile.avatar.delete') }}" method="POST" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-sm text-red-400 hover:text-red-300">Remove</button>
                                        </form>
                                    @endif
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Security --}}
            <div class="bg-gray-900 rounded-lg border border-gray-800">
                <div class="px-6 py-4 border-b border-gray-800 flex justify-between items-center">
                    <h3 class="text-base font-semibold text-white">Security</h3>
                    <a href="{{ route('profile.password') }}" class="text-sm text-brand-400 hover:text-brand-300">Change Password</a>
                </div>
                <div class="p-6">
                    <p class="text-sm text-gray-400">Keep your account secure by using a strong password.</p>
                    <a href="{{ route('profile.password') }}" class="mt-4 inline-flex items-center text-sm text-brand-400 hover:text-brand-300">
                        Change your password
                        <svg class="ml-1 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>
                </div>
            </div>

            {{-- System Roles --}}
            <div class="bg-gray-900 rounded-lg border border-gray-800">
                <div class="px-6 py-4 border-b border-gray-800">
                    <h3 class="text-base font-semibold text-white">System Roles</h3>
                </div>
                <div class="p-6">
                    @if($user->roleAssignments->isEmpty())
                        <p class="text-sm text-gray-400">No roles assigned.</p>
                    @else
                        <ul role="list" class="divide-y divide-gray-800">
                            @foreach($user->roleAssignments as $assignment)
                                <li class="py-3 first:pt-0 last:pb-0">
                                    <p class="text-sm font-medium text-white">{{ $assignment->system_role->label() }}</p>
                                    @if($assignment->location)
                                        <p class="text-xs text-gray-400">Location: {{ $assignment->location->name }}</p>
                                    @endif
                                    @if($assignment->department)
                                        <p class="text-xs text-gray-400">Department: {{ $assignment->department->name }}</p>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>

            {{-- Business Roles --}}
            <div class="bg-gray-900 rounded-lg border border-gray-800">
                <div class="px-6 py-4 border-b border-gray-800">
                    <h3 class="text-base font-semibold text-white">Business Roles</h3>
                </div>
                <div class="p-6">
                    @if($user->businessRoles->isEmpty())
                        <p class="text-sm text-gray-400">No business roles assigned.</p>
                    @else
                        <ul role="list" class="divide-y divide-gray-800">
                            @foreach($user->businessRoles as $role)
                                <li class="py-3 first:pt-0 last:pb-0">
                                    <p class="text-sm font-medium text-white">
                                        {{ $role->name }}
                                        @if($role->pivot->is_primary)
                                            <span class="ml-2 inline-flex items-center rounded bg-brand-500/10 px-2 py-0.5 text-xs font-medium text-brand-400">Primary</span>
                                        @endif
                                    </p>
                                    <p class="text-xs text-gray-400">{{ $role->department->name }}</p>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>

            {{-- Employment Details (Read-only) --}}
            @if($user->employmentDetails)
                <div class="bg-gray-900 rounded-lg border border-gray-800">
                    <div class="px-6 py-4 border-b border-gray-800">
                        <h3 class="text-base font-semibold text-white">Employment Details</h3>
                    </div>
                    <div class="p-6 space-y-4">
                        <div>
                            <dt class="text-sm text-gray-400">Employment Status</dt>
                            <dd class="mt-1">
                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium
                                    @switch($user->employmentDetails->employment_status->color())
                                        @case('green') bg-green-500/10 text-green-400 @break
                                        @case('blue') bg-blue-500/10 text-blue-400 @break
                                        @case('yellow') bg-yellow-500/10 text-yellow-400 @break
                                        @case('orange') bg-orange-500/10 text-orange-400 @break
                                        @case('red') bg-red-500/10 text-red-400 @break
                                        @default bg-gray-500/10 text-gray-400
                                    @endswitch
                                ">
                                    {{ $user->employmentDetails->employment_status->label() }}
                                </span>
                            </dd>
                        </div>
                        @if($user->employmentDetails->employment_start_date)
                            <div>
                                <dt class="text-sm text-gray-400">Start Date</dt>
                                <dd class="mt-1 text-sm text-white">{{ $user->employmentDetails->employment_start_date->format('d M Y') }}</dd>
                            </div>
                        @endif
                        @if($user->employmentDetails->pay_type)
                            <div>
                                <dt class="text-sm text-gray-400">Pay Type</dt>
                                <dd class="mt-1 text-sm text-white">{{ $user->employmentDetails->pay_type->label() }}</dd>
                            </div>
                        @endif
                        @if($user->employmentDetails->target_hours_per_week)
                            <div>
                                <dt class="text-sm text-gray-400">Target Hours/Week</dt>
                                <dd class="mt-1 text-sm text-white">{{ $user->employmentDetails->target_hours_per_week }} hours</dd>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            {{-- Availability Summary --}}
            <div class="bg-gray-900 rounded-lg border border-gray-800">
                <div class="px-6 py-4 border-b border-gray-800 flex justify-between items-center">
                    <h3 class="text-base font-semibold text-white">Availability</h3>
                    <a href="{{ route('availability.index') }}" class="text-sm text-brand-400 hover:text-brand-300">Manage</a>
                </div>
                <div class="p-6">
                    @if($user->availability->isEmpty())
                        <p class="text-sm text-gray-400">No availability set.</p>
                        <a href="{{ route('availability.edit') }}" class="mt-2 inline-flex items-center text-sm text-brand-400 hover:text-brand-300">
                            Set your availability
                            <svg class="ml-1 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </a>
                    @else
                        <ul role="list" class="divide-y divide-gray-800">
                            @foreach($user->availability->where('type.value', 'recurring')->take(5) as $availability)
                                <li class="py-2 first:pt-0 last:pb-0 flex justify-between items-center">
                                    <span class="text-sm text-white">{{ $availability->day_name }}</span>
                                    <span class="text-sm text-gray-400">{{ $availability->time_range }}</span>
                                </li>
                            @endforeach
                        </ul>
                        @if($user->availability->count() > 5)
                            <a href="{{ route('availability.index') }}" class="mt-3 inline-flex items-center text-sm text-brand-400 hover:text-brand-300">
                                View all availability
                            </a>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
