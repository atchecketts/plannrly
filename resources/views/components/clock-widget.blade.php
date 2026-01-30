@php
    $user = auth()->user();
    $settings = \App\Models\TenantSettings::where('tenant_id', $user->tenant_id)->first();
    $requireGps = $settings?->require_gps_clock_in ?? false;

    $activeEntry = \App\Models\TimeEntry::query()
        ->where('user_id', $user->id)
        ->active()
        ->with('shift.businessRole')
        ->first();

    $todayShifts = \App\Models\Shift::query()
        ->where('user_id', $user->id)
        ->whereDate('date', today())
        ->whereDoesntHave('timeEntry')
        ->published()
        ->with('businessRole')
        ->get();

    // Prepare data for JavaScript
    $activeEntryData = $activeEntry ? [
        'id' => $activeEntry->id,
        'clock_in_at' => $activeEntry->clock_in_at->toIso8601String(),
        'clock_in_timestamp' => $activeEntry->clock_in_at->timestamp,
        'break_start_at' => $activeEntry->break_start_at?->toIso8601String(),
        'break_start_timestamp' => $activeEntry->break_start_at?->timestamp,
        'status' => $activeEntry->status->value,
        'shift' => $activeEntry->shift ? [
            'start_time' => $activeEntry->shift->start_time->format('g:i A'),
            'end_time' => $activeEntry->shift->end_time->format('g:i A'),
            'business_role' => $activeEntry->shift->businessRole?->name,
        ] : null,
    ] : null;

    $todayShiftsData = $todayShifts->map(function ($s) {
        return [
            'id' => $s->id,
            'start_time' => $s->start_time->format('g:i A'),
            'end_time' => $s->end_time->format('g:i A'),
            'business_role' => $s->businessRole?->name,
        ];
    })->values()->all();
@endphp

<div class="bg-gray-900 rounded-lg border border-gray-800 mb-6"
     x-data="clockWidget({
         activeEntry: {{ Js::from($activeEntryData) }},
         todayShifts: {{ Js::from($todayShiftsData) }},
         requireGps: {{ Js::from($requireGps) }},
         csrfToken: '{{ csrf_token() }}',
         routes: {
             clockIn: '{{ route('time-entries.clock-in') }}',
             clockOut: '{{ $activeEntry ? route('time-entries.clock-out', $activeEntry) : '' }}',
             startBreak: '{{ $activeEntry ? route('time-entries.start-break', $activeEntry) : '' }}',
             endBreak: '{{ $activeEntry ? route('time-entries.end-break', $activeEntry) : '' }}',
         }
     })">
    <div class="px-6 py-4 border-b border-gray-800 flex items-center justify-between">
        <h3 class="text-base font-semibold text-white">Time Clock</h3>
        <div class="text-sm text-gray-500" x-text="currentTime"></div>
    </div>

    <!-- Message Display -->
    <div x-show="message" x-cloak
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 transform -translate-y-2"
         x-transition:enter-end="opacity-100 transform translate-y-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="px-6 py-3 border-b border-gray-800"
         :class="messageType === 'success' ? 'bg-green-500/10' : 'bg-red-500/10'">
        <div class="flex items-center gap-2">
            <template x-if="messageType === 'success'">
                <svg class="w-5 h-5 text-green-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
            </template>
            <template x-if="messageType === 'error'">
                <svg class="w-5 h-5 text-red-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </template>
            <span class="text-sm" :class="messageType === 'success' ? 'text-green-400' : 'text-red-400'" x-text="message"></span>
        </div>
    </div>

    <div class="p-6">
        <!-- Clocked In State -->
        <template x-if="state === 'clocked_in' || state === 'on_break'">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <div class="flex items-center gap-3">
                        <span class="relative flex h-3 w-3">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full opacity-75"
                                  :class="state === 'on_break' ? 'bg-yellow-400' : 'bg-green-400'"></span>
                            <span class="relative inline-flex rounded-full h-3 w-3"
                                  :class="state === 'on_break' ? 'bg-yellow-500' : 'bg-green-500'"></span>
                        </span>
                        <span class="text-lg font-semibold text-white" x-text="state === 'on_break' ? 'On Break' : 'Clocked In'"></span>
                    </div>
                    <p class="text-sm text-gray-400 mt-1">
                        <span x-text="'Since ' + clockInTime"></span>
                        <template x-if="activeEntry?.shift?.business_role">
                            <span>
                                &bull; <span x-text="activeEntry.shift.business_role"></span>
                            </span>
                        </template>
                    </p>
                    <!-- Scheduled Time Display -->
                    <template x-if="activeEntry?.shift">
                        <p class="text-xs text-gray-500 mt-1">
                            Scheduled: <span x-text="activeEntry.shift.start_time + ' - ' + activeEntry.shift.end_time"></span>
                        </p>
                    </template>
                    <p class="text-2xl font-bold text-white mt-2" x-text="elapsedTime"></p>
                    <!-- Break Time Display -->
                    <template x-if="state === 'on_break'">
                        <p class="text-sm text-yellow-400 mt-1">Break: <span x-text="breakTime"></span></p>
                    </template>
                </div>

                <div class="flex gap-3">
                    <template x-if="state === 'on_break'">
                        <button @click="endBreak()"
                                :disabled="loading"
                                class="bg-yellow-600 text-white py-2.5 px-6 rounded-lg font-medium hover:bg-yellow-500 transition-colors disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2">
                            <svg x-show="loading" class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            End Break
                        </button>
                    </template>
                    <template x-if="state === 'clocked_in'">
                        <button @click="startBreak()"
                                :disabled="loading"
                                class="border border-gray-700 text-gray-300 py-2.5 px-6 rounded-lg font-medium hover:bg-gray-800 transition-colors disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2">
                            <svg x-show="loading" class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Start Break
                        </button>
                    </template>

                    <button @click="confirmClockOut()"
                            :disabled="loading"
                            class="bg-red-600 text-white py-2.5 px-6 rounded-lg font-medium hover:bg-red-500 transition-colors disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2">
                        <svg x-show="loading" class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Clock Out
                    </button>
                </div>
            </div>
        </template>

        <!-- Ready to Clock In State -->
        <template x-if="state === 'ready' && todayShifts.length > 0">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <p class="text-lg font-semibold text-white">Ready to clock in?</p>
                    <p class="text-sm text-gray-400 mt-1">Select a shift to start your workday.</p>
                    <template x-if="requireGps">
                        <p class="text-xs text-amber-400 mt-2 flex items-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            GPS location required
                            <span x-show="gpsStatus === 'acquired'" class="text-green-400">(acquired)</span>
                            <span x-show="gpsStatus === 'acquiring'" class="text-yellow-400">(acquiring...)</span>
                            <span x-show="gpsStatus === 'error'" class="text-red-400">(failed - please enable location)</span>
                        </p>
                    </template>
                </div>

                <div class="flex items-center gap-3">
                    <select x-model="selectedShiftId" class="bg-gray-800 border border-gray-700 text-white rounded-lg px-4 py-2.5 text-sm">
                        <template x-for="shift in todayShifts" :key="shift.id">
                            <option :value="shift.id" x-text="shift.start_time + ' - ' + shift.end_time + (shift.business_role ? ' (' + shift.business_role + ')' : '')"></option>
                        </template>
                    </select>
                    <button @click="clockIn()"
                            :disabled="loading || (requireGps && gpsStatus !== 'acquired')"
                            class="bg-green-600 text-white py-2.5 px-6 rounded-lg font-medium hover:bg-green-500 transition-colors whitespace-nowrap disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2">
                        <svg x-show="loading" class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Clock In
                    </button>
                </div>
            </div>
        </template>

        <!-- No Shifts State -->
        <template x-if="state === 'no_shifts'">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-gray-800 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div>
                    <p class="text-white font-medium">No Shifts Today</p>
                    <p class="text-sm text-gray-500">You don't have any shifts scheduled for today.</p>
                </div>
            </div>
        </template>
    </div>

    <!-- Confirmation Modal -->
    <div x-show="showConfirmModal" x-cloak
         class="fixed inset-0 z-50 overflow-y-auto"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-black/70" @click="showConfirmModal = false"></div>
            <div class="relative bg-gray-900 rounded-xl border border-gray-700 max-w-md w-full p-6 shadow-xl"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 transform scale-95"
                 x-transition:enter-end="opacity-100 transform scale-100">
                <h3 class="text-lg font-semibold text-white mb-2">Confirm Clock Out</h3>
                <p class="text-gray-400 mb-6">
                    Are you sure you want to clock out? You have been working for <span class="text-white font-medium" x-text="elapsedTime"></span>.
                </p>
                <div class="flex justify-end gap-3">
                    <button @click="showConfirmModal = false"
                            class="px-4 py-2 text-gray-400 hover:text-white transition-colors">
                        Cancel
                    </button>
                    <button @click="clockOut()"
                            :disabled="loading"
                            class="bg-red-600 text-white py-2 px-4 rounded-lg font-medium hover:bg-red-500 transition-colors disabled:opacity-50 flex items-center gap-2">
                        <svg x-show="loading" class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Clock Out
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function clockWidget(config) {
        return {
            activeEntry: config.activeEntry,
            todayShifts: config.todayShifts,
            requireGps: config.requireGps,
            csrfToken: config.csrfToken,
            routes: config.routes,

            state: 'loading',
            loading: false,
            message: '',
            messageType: 'success',
            messageTimeout: null,

            selectedShiftId: config.todayShifts[0]?.id || null,
            showConfirmModal: false,

            elapsedTime: 'Calculating...',
            breakTime: '0m 0s',
            clockInTime: '',
            currentTime: '',
            timerInterval: null,
            clockInterval: null,

            gpsStatus: 'none', // none, acquiring, acquired, error
            gpsLocation: null,

            init() {
                this.updateState();
                this.updateCurrentTime();
                this.clockInterval = setInterval(() => this.updateCurrentTime(), 1000);

                if (this.activeEntry) {
                    this.clockInTime = new Date(this.activeEntry.clock_in_at).toLocaleTimeString('en-US', {
                        hour: 'numeric',
                        minute: '2-digit',
                        hour12: true
                    });
                    this.updateElapsedTime();
                    this.timerInterval = setInterval(() => this.updateElapsedTime(), 1000);
                }

                if (this.requireGps && this.state === 'ready') {
                    this.acquireGps();
                }
            },

            updateState() {
                if (this.activeEntry) {
                    this.state = this.activeEntry.status === 'on_break' ? 'on_break' : 'clocked_in';
                } else if (this.todayShifts.length > 0) {
                    this.state = 'ready';
                } else {
                    this.state = 'no_shifts';
                }
            },

            updateCurrentTime() {
                this.currentTime = new Date().toLocaleTimeString('en-US', {
                    hour: 'numeric',
                    minute: '2-digit',
                    second: '2-digit',
                    hour12: true
                });
            },

            updateElapsedTime() {
                if (!this.activeEntry) return;

                const now = Math.floor(Date.now() / 1000);
                const elapsed = now - this.activeEntry.clock_in_timestamp;

                this.elapsedTime = this.formatDuration(elapsed);

                if (this.state === 'on_break' && this.activeEntry.break_start_timestamp) {
                    const breakElapsed = now - this.activeEntry.break_start_timestamp;
                    this.breakTime = this.formatDuration(breakElapsed);
                }
            },

            formatDuration(seconds) {
                const hours = Math.floor(seconds / 3600);
                const minutes = Math.floor((seconds % 3600) / 60);
                const secs = seconds % 60;

                if (hours > 0) {
                    return `${hours}h ${minutes}m ${secs}s`;
                } else if (minutes > 0) {
                    return `${minutes}m ${secs}s`;
                } else {
                    return `${secs}s`;
                }
            },

            acquireGps() {
                if (!navigator.geolocation) {
                    this.gpsStatus = 'error';
                    return;
                }

                this.gpsStatus = 'acquiring';

                navigator.geolocation.getCurrentPosition(
                    (position) => {
                        this.gpsLocation = {
                            lat: position.coords.latitude,
                            lng: position.coords.longitude
                        };
                        this.gpsStatus = 'acquired';
                    },
                    (error) => {
                        console.error('GPS error:', error);
                        this.gpsStatus = 'error';
                    },
                    { enableHighAccuracy: true, timeout: 10000 }
                );
            },

            showMessage(msg, type = 'success') {
                this.message = msg;
                this.messageType = type;

                if (this.messageTimeout) {
                    clearTimeout(this.messageTimeout);
                }

                this.messageTimeout = setTimeout(() => {
                    this.message = '';
                }, 5000);
            },

            async clockIn() {
                if (this.loading) return;
                if (this.requireGps && !this.gpsLocation) {
                    this.showMessage('GPS location is required to clock in.', 'error');
                    return;
                }

                this.loading = true;

                try {
                    const body = { shift_id: this.selectedShiftId };
                    if (this.gpsLocation) {
                        body.location = this.gpsLocation;
                    }

                    const response = await fetch(this.routes.clockIn, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': this.csrfToken,
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify(body),
                    });

                    const data = await response.json();

                    if (data.success) {
                        this.showMessage(data.message, 'success');
                        // Update state
                        this.activeEntry = {
                            id: data.time_entry.id,
                            clock_in_at: data.time_entry.clock_in_at,
                            clock_in_timestamp: data.time_entry.clock_in_timestamp,
                            status: data.time_entry.status,
                            shift: this.todayShifts.find(s => s.id === this.selectedShiftId),
                        };
                        this.routes.clockOut = this.routes.clockIn.replace('clock-in', data.time_entry.id + '/clock-out');
                        this.routes.startBreak = this.routes.clockIn.replace('clock-in', data.time_entry.id + '/start-break');
                        this.routes.endBreak = this.routes.clockIn.replace('clock-in', data.time_entry.id + '/end-break');
                        this.clockInTime = new Date().toLocaleTimeString('en-US', {
                            hour: 'numeric',
                            minute: '2-digit',
                            hour12: true
                        });
                        this.updateState();
                        this.updateElapsedTime();
                        this.timerInterval = setInterval(() => this.updateElapsedTime(), 1000);
                        // Remove clocked-in shift from available shifts
                        this.todayShifts = this.todayShifts.filter(s => s.id !== this.selectedShiftId);
                    } else {
                        this.showMessage(data.message || 'Failed to clock in.', 'error');
                    }
                } catch (error) {
                    console.error('Clock in error:', error);
                    this.showMessage('An error occurred. Please try again.', 'error');
                } finally {
                    this.loading = false;
                }
            },

            confirmClockOut() {
                this.showConfirmModal = true;
            },

            async clockOut() {
                if (this.loading) return;

                this.loading = true;

                try {
                    const response = await fetch(this.routes.clockOut, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': this.csrfToken,
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({}),
                    });

                    const data = await response.json();

                    if (data.success) {
                        this.showConfirmModal = false;
                        this.showMessage(data.message, 'success');
                        // Clear state
                        if (this.timerInterval) {
                            clearInterval(this.timerInterval);
                        }
                        this.activeEntry = null;
                        this.updateState();
                    } else {
                        this.showMessage(data.message || 'Failed to clock out.', 'error');
                    }
                } catch (error) {
                    console.error('Clock out error:', error);
                    this.showMessage('An error occurred. Please try again.', 'error');
                } finally {
                    this.loading = false;
                }
            },

            async startBreak() {
                if (this.loading) return;

                this.loading = true;

                try {
                    const response = await fetch(this.routes.startBreak, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': this.csrfToken,
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({}),
                    });

                    const data = await response.json();

                    if (data.success) {
                        this.showMessage(data.message, 'success');
                        this.activeEntry.status = 'on_break';
                        this.activeEntry.break_start_at = data.time_entry.break_start_at;
                        this.activeEntry.break_start_timestamp = Math.floor(new Date(data.time_entry.break_start_at).getTime() / 1000);
                        this.updateState();
                    } else {
                        this.showMessage(data.message || 'Failed to start break.', 'error');
                    }
                } catch (error) {
                    console.error('Start break error:', error);
                    this.showMessage('An error occurred. Please try again.', 'error');
                } finally {
                    this.loading = false;
                }
            },

            async endBreak() {
                if (this.loading) return;

                this.loading = true;

                try {
                    const response = await fetch(this.routes.endBreak, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': this.csrfToken,
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({}),
                    });

                    const data = await response.json();

                    if (data.success) {
                        this.showMessage(data.message, 'success');
                        this.activeEntry.status = 'clocked_in';
                        this.activeEntry.break_start_at = null;
                        this.activeEntry.break_start_timestamp = null;
                        this.breakTime = '0m 0s';
                        this.updateState();
                    } else {
                        this.showMessage(data.message || 'Failed to end break.', 'error');
                    }
                } catch (error) {
                    console.error('End break error:', error);
                    this.showMessage('An error occurred. Please try again.', 'error');
                } finally {
                    this.loading = false;
                }
            },
        };
    }
</script>
@endpush
