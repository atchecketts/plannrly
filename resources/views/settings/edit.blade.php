<x-layouts.app title="Settings">
    <div class="max-w-4xl">
        <div class="mb-6">
            <h2 class="text-lg font-semibold text-white">Organization Settings</h2>
            <p class="mt-1 text-sm text-gray-400">Configure your organization's scheduling and leave preferences.</p>
        </div>

        <form action="{{ route('settings.update') }}" method="POST" class="space-y-8">
            @csrf
            @method('PUT')

            <div class="bg-gray-900 rounded-lg border border-gray-800 p-6">
                <h3 class="text-base font-semibold text-white mb-4">Scheduling Options</h3>

                <div class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <x-form.select name="week_starts_on" label="Week Starts On" required>
                            @foreach($weekDays as $value => $label)
                                <option value="{{ $value }}" {{ old('week_starts_on', $settings->week_starts_on) == $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </x-form.select>

                        <x-form.select name="timezone" label="Timezone" required>
                            @foreach($timezones as $value => $label)
                                <option value="{{ $value }}" {{ old('timezone', $settings->timezone) == $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </x-form.select>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <x-form.input name="day_starts_at" label="Day Starts At" type="time" required :value="old('day_starts_at', $settings->day_starts_at?->format('H:i') ?? '06:00')" />
                        <x-form.input name="day_ends_at" label="Day Ends At" type="time" required :value="old('day_ends_at', $settings->day_ends_at?->format('H:i') ?? '22:00')" />
                    </div>

                    <x-form.input name="missed_grace_minutes" label="Missed Shift Grace Period (minutes)" type="number" min="0" max="60" required :value="old('missed_grace_minutes', $settings->missed_grace_minutes ?? 15)" hint="Time allowed before a shift is marked as missed" />
                </div>
            </div>

            <div class="bg-gray-900 rounded-lg border border-gray-800 p-6">
                <h3 class="text-base font-semibold text-white mb-4">Display Formats</h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-form.select name="date_format" label="Date Format" required>
                        @foreach($dateFormats as $value => $label)
                            <option value="{{ $value }}" {{ old('date_format', $settings->date_format) == $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </x-form.select>

                    <x-form.select name="time_format" label="Time Format" required>
                        @foreach($timeFormats as $value => $label)
                            <option value="{{ $value }}" {{ old('time_format', $settings->time_format) == $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </x-form.select>
                </div>
            </div>

            <div class="bg-gray-900 rounded-lg border border-gray-800 p-6">
                <h3 class="text-base font-semibold text-white mb-4">Feature Toggles</h3>

                <div class="space-y-4">
                    <x-form.checkbox name="enable_clock_in_out" label="Enable Clock In/Out" :checked="old('enable_clock_in_out', $settings->enable_clock_in_out)" hint="Allow employees to clock in and out of shifts" />

                    <x-form.checkbox name="enable_shift_acknowledgement" label="Require Shift Acknowledgement" :checked="old('enable_shift_acknowledgement', $settings->enable_shift_acknowledgement)" hint="Employees must acknowledge published shifts" />

                    <x-form.checkbox name="notify_on_publish" label="Notify on Schedule Publish" :checked="old('notify_on_publish', $settings->notify_on_publish)" hint="Send notifications when shifts are published" />
                </div>
            </div>

            <div class="bg-gray-900 rounded-lg border border-gray-800 p-6" x-data="{ clockEnabled: {{ $settings->enable_clock_in_out ? 'true' : 'false' }} }">
                <h3 class="text-base font-semibold text-white mb-4">Clock In/Out Settings</h3>

                <div class="space-y-4">
                    <x-form.checkbox name="enable_clock_in_out" label="Enable Clock In/Out" :checked="old('enable_clock_in_out', $settings->enable_clock_in_out)" hint="Allow employees to clock in and out of shifts" x-model="clockEnabled" />

                    <div x-show="clockEnabled" x-collapse class="space-y-4 pt-4 border-t border-gray-800">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <x-form.input name="clock_in_grace_minutes" label="Clock In Grace Period (minutes)" type="number" min="0" max="60" :value="old('clock_in_grace_minutes', $settings->clock_in_grace_minutes ?? 15)" hint="Time before/after shift start that clock in is allowed" />
                            <x-form.input name="overtime_threshold_minutes" label="Overtime Threshold (minutes)" type="number" min="0" max="1440" :value="old('overtime_threshold_minutes', $settings->overtime_threshold_minutes ?? 480)" hint="Minutes worked before overtime applies (default 8 hours = 480)" />
                        </div>

                        <x-form.checkbox name="require_gps_clock_in" label="Require GPS Location" :checked="old('require_gps_clock_in', $settings->require_gps_clock_in)" hint="Employees must share their location when clocking in" />

                        <x-form.checkbox name="require_manager_approval" label="Require Manager Approval" :checked="old('require_manager_approval', $settings->require_manager_approval)" hint="Time entries must be approved by a manager before being finalized" />

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-4 border-t border-gray-800">
                            <x-form.checkbox name="auto_clock_out_enabled" label="Enable Auto Clock Out" :checked="old('auto_clock_out_enabled', $settings->auto_clock_out_enabled)" hint="Automatically clock out employees at a specific time" />
                            <x-form.input name="auto_clock_out_time" label="Auto Clock Out Time" type="time" :value="old('auto_clock_out_time', $settings->auto_clock_out_time ?? '23:00')" hint="Time to automatically clock out employees" />
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-gray-900 rounded-lg border border-gray-800 p-6">
                <h3 class="text-base font-semibold text-white mb-4">Leave Management</h3>

                <x-form.select name="leave_carryover_mode" label="Leave Carryover Policy" required>
                    @foreach($carryoverModes as $value => $label)
                        <option value="{{ $value }}" {{ old('leave_carryover_mode', $settings->leave_carryover_mode) == $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </x-form.select>
            </div>

            <div class="bg-gray-900 rounded-lg border border-gray-800 p-6">
                <h3 class="text-base font-semibold text-white mb-4">Organization Preferences</h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-form.select name="default_currency" label="Default Currency" required>
                        @foreach($currencies as $value => $label)
                            <option value="{{ $value }}" {{ old('default_currency', $settings->default_currency) == $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </x-form.select>

                    <x-form.color name="primary_color" label="Brand Color" :value="old('primary_color', $settings->primary_color ?? '#6366f1')" />
                </div>
            </div>

            <div class="flex justify-end gap-3 pt-4">
                <x-button type="submit">Save Settings</x-button>
            </div>
        </form>
    </div>
</x-layouts.app>
