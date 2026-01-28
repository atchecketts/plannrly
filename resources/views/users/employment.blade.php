<x-layouts.app title="Employment Details - {{ $user->full_name }}">
    <div class="max-w-4xl">
        <div class="mb-6">
            <nav class="flex" aria-label="Breadcrumb">
                <ol class="flex items-center space-x-2 text-sm">
                    <li>
                        <a href="{{ route('users.index') }}" class="text-gray-400 hover:text-white">Employees</a>
                    </li>
                    <li>
                        <svg class="w-4 h-4 text-gray-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                        </svg>
                    </li>
                    <li>
                        <a href="{{ route('users.show', $user) }}" class="text-gray-400 hover:text-white">{{ $user->full_name }}</a>
                    </li>
                    <li>
                        <svg class="w-4 h-4 text-gray-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                        </svg>
                    </li>
                    <li>
                        <span class="text-white">Employment Details</span>
                    </li>
                </ol>
            </nav>
        </div>

        <div class="bg-gray-900 rounded-lg border border-gray-800 mb-6">
            <div class="px-6 py-4 flex items-center justify-between border-b border-gray-800">
                <div>
                    <h2 class="text-lg font-semibold text-white">Employment Details</h2>
                    <p class="mt-1 text-sm text-gray-400">Manage employment information for {{ $user->full_name }}</p>
                </div>
                <div class="flex gap-2">
                    <a href="{{ route('users.show', $user) }}" class="text-sm text-gray-400 hover:text-white">View Profile</a>
                    <span class="text-gray-600">|</span>
                    <a href="{{ route('users.edit', $user) }}" class="text-sm text-gray-400 hover:text-white">Edit User</a>
                </div>
            </div>
        </div>

        <form action="{{ route('users.employment.update', $user) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            {{-- Employment Status --}}
            <div class="bg-gray-900 rounded-lg border border-gray-800">
                <div class="px-6 py-4 border-b border-gray-800">
                    <h3 class="text-base font-semibold text-white">Employment Status</h3>
                </div>
                <div class="p-6 space-y-5">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <x-form.select name="employment_status" label="Status" required>
                            @foreach($employmentStatuses as $status)
                                <option value="{{ $status->value }}" @selected(old('employment_status', $user->employmentDetails?->employment_status?->value) === $status->value)>
                                    {{ $status->label() }}
                                </option>
                            @endforeach
                        </x-form.select>

                        <x-form.input
                            name="employment_start_date"
                            type="date"
                            label="Start Date"
                            :value="$user->employmentDetails?->employment_start_date?->format('Y-m-d')"
                        />
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <x-form.input
                            name="employment_end_date"
                            type="date"
                            label="Contract End Date (for fixed-term)"
                            :value="$user->employmentDetails?->employment_end_date?->format('Y-m-d')"
                        />

                        <x-form.input
                            name="final_working_date"
                            type="date"
                            label="Final Working Date"
                            :value="$user->employmentDetails?->final_working_date?->format('Y-m-d')"
                        />
                    </div>

                    <x-form.input
                        name="probation_end_date"
                        type="date"
                        label="Probation End Date"
                        :value="$user->employmentDetails?->probation_end_date?->format('Y-m-d')"
                    />
                </div>
            </div>

            {{-- Compensation --}}
            <div class="bg-gray-900 rounded-lg border border-gray-800">
                <div class="px-6 py-4 border-b border-gray-800">
                    <h3 class="text-base font-semibold text-white">Compensation</h3>
                </div>
                <div class="p-6 space-y-5">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <x-form.select name="pay_type" label="Pay Type" required>
                            @foreach($payTypes as $type)
                                <option value="{{ $type->value }}" @selected(old('pay_type', $user->employmentDetails?->pay_type?->value ?? 'hourly') === $type->value)>
                                    {{ $type->label() }} - {{ $type->description() }}
                                </option>
                            @endforeach
                        </x-form.select>

                        <x-form.input
                            name="currency"
                            label="Currency"
                            :value="$user->employmentDetails?->currency ?? 'GBP'"
                            placeholder="GBP"
                            required
                        />
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <x-form.input
                            name="base_hourly_rate"
                            type="number"
                            step="0.01"
                            label="Base Hourly Rate"
                            :value="$user->employmentDetails?->base_hourly_rate"
                            placeholder="0.00"
                        />

                        <x-form.input
                            name="annual_salary"
                            type="number"
                            step="0.01"
                            label="Annual Salary"
                            :value="$user->employmentDetails?->annual_salary"
                            placeholder="0.00"
                        />
                    </div>

                    <x-form.checkbox
                        name="overtime_eligible"
                        label="Overtime Eligible"
                        :checked="old('overtime_eligible', $user->employmentDetails?->overtime_eligible ?? false)"
                    />
                </div>
            </div>

            {{-- Working Hours --}}
            <div class="bg-gray-900 rounded-lg border border-gray-800">
                <div class="px-6 py-4 border-b border-gray-800">
                    <h3 class="text-base font-semibold text-white">Working Hours</h3>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <x-form.input
                            name="target_hours_per_week"
                            type="number"
                            step="0.5"
                            label="Target Hours/Week"
                            :value="$user->employmentDetails?->target_hours_per_week"
                            placeholder="40"
                        />

                        <x-form.input
                            name="min_hours_per_week"
                            type="number"
                            step="0.5"
                            label="Minimum Hours/Week"
                            :value="$user->employmentDetails?->min_hours_per_week"
                            placeholder="20"
                        />

                        <x-form.input
                            name="max_hours_per_week"
                            type="number"
                            step="0.5"
                            label="Maximum Hours/Week"
                            :value="$user->employmentDetails?->max_hours_per_week"
                            placeholder="48"
                        />
                    </div>
                </div>
            </div>

            {{-- HR Notes --}}
            <div class="bg-gray-900 rounded-lg border border-gray-800">
                <div class="px-6 py-4 border-b border-gray-800">
                    <h3 class="text-base font-semibold text-white">HR Notes</h3>
                    <p class="text-sm text-gray-400">Private notes visible only to administrators.</p>
                </div>
                <div class="p-6">
                    <x-form.textarea
                        name="notes"
                        :value="$user->employmentDetails?->notes"
                        placeholder="Add private HR notes here..."
                        rows="4"
                    />
                </div>
            </div>

            <div class="flex justify-end gap-3">
                <x-button variant="secondary" :href="route('users.show', $user)">Cancel</x-button>
                <x-button type="submit">Save Employment Details</x-button>
            </div>
        </form>
    </div>
</x-layouts.app>
