@props(['shiftStatuses', 'locations', 'departments', 'businessRoles', 'users'])

<div x-show="editModal.isOpen"
     x-cloak
     class="fixed inset-0 z-50 overflow-y-auto"
     x-transition:enter="transition ease-out duration-200"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-150"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0">
    <!-- Backdrop -->
    <div class="fixed inset-0 bg-black/60" @click="editModal.close()"></div>

    <!-- Modal -->
    <div class="flex min-h-full items-center justify-center p-4">
        <div x-show="editModal.isOpen"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             @click.stop
             class="relative w-full max-w-lg bg-gray-900 rounded-xl border border-gray-700 shadow-xl max-h-[90vh] overflow-y-auto">

            <!-- Header -->
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-700">
                <h3 class="text-lg font-semibold text-white" x-text="editModal.isCreateMode ? 'Create Shift' : 'Edit Shift'"></h3>
                <button @click="editModal.close()" class="text-gray-400 hover:text-white transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Loading State (only for edit mode) -->
            <div x-show="editModal.loading && !editModal.isCreateMode" class="px-6 py-12 text-center">
                <svg class="w-8 h-8 mx-auto text-brand-500 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <p class="mt-3 text-gray-400">Loading shift...</p>
            </div>

            <!-- Form -->
            <form x-show="!editModal.loading || editModal.isCreateMode" @submit.prevent="editModal.save()" class="px-6 py-4 space-y-4">
                <!-- Error Message -->
                <div x-show="editModal.error" class="bg-red-500/10 border border-red-500/50 rounded-lg px-4 py-3">
                    <p class="text-sm text-red-400" x-text="editModal.error"></p>
                </div>

                <!-- Location -->
                <div>
                    <label for="shift-location" class="block text-sm font-medium text-gray-400 mb-1.5">Location</label>
                    <select id="shift-location"
                            x-model="editModal.shift.location_id"
                            @change="onLocationChange()"
                            class="w-full px-3 py-2 bg-gray-800 border border-gray-700 rounded-lg text-white focus:ring-2 focus:ring-brand-500 focus:border-brand-500"
                            :class="editModal.errors.location_id ? 'border-red-500' : ''">
                        <template x-for="loc in modalData.locations" :key="loc.id">
                            <option :value="loc.id" x-text="loc.name" :selected="loc.id == editModal.shift.location_id"></option>
                        </template>
                    </select>
                    <p x-show="editModal.errors.location_id" class="mt-1 text-xs text-red-400" x-text="editModal.errors.location_id?.[0]"></p>
                </div>

                <!-- Department -->
                <div>
                    <label for="shift-department" class="block text-sm font-medium text-gray-400 mb-1.5">Department</label>
                    <select id="shift-department"
                            x-model="editModal.shift.department_id"
                            @change="onDepartmentChange()"
                            class="w-full px-3 py-2 bg-gray-800 border border-gray-700 rounded-lg text-white focus:ring-2 focus:ring-brand-500 focus:border-brand-500"
                            :class="editModal.errors.department_id ? 'border-red-500' : ''">
                        <template x-for="dept in getAvailableDepartments()" :key="dept.id">
                            <option :value="dept.id" x-text="dept.name" :selected="dept.id == editModal.shift.department_id"></option>
                        </template>
                    </select>
                    <p x-show="editModal.errors.department_id" class="mt-1 text-xs text-red-400" x-text="editModal.errors.department_id?.[0]"></p>
                </div>

                <!-- Role -->
                <div>
                    <label for="shift-role" class="block text-sm font-medium text-gray-400 mb-1.5">Role</label>
                    <select id="shift-role"
                            x-model="editModal.shift.business_role_id"
                            @change="onRoleChange()"
                            class="w-full px-3 py-2 bg-gray-800 border border-gray-700 rounded-lg text-white focus:ring-2 focus:ring-brand-500 focus:border-brand-500"
                            :class="editModal.errors.business_role_id ? 'border-red-500' : ''">
                        <template x-for="role in getAvailableRoles()" :key="role.id">
                            <option :value="role.id" x-text="role.name" :selected="role.id == editModal.shift.business_role_id"></option>
                        </template>
                    </select>
                    <p x-show="editModal.errors.business_role_id" class="mt-1 text-xs text-red-400" x-text="editModal.errors.business_role_id?.[0]"></p>
                </div>

                <!-- Employee -->
                <div>
                    <label for="shift-employee" class="block text-sm font-medium text-gray-400 mb-1.5">Employee</label>
                    <select id="shift-employee"
                            x-model="editModal.shift.user_id"
                            class="w-full px-3 py-2 bg-gray-800 border border-gray-700 rounded-lg text-white focus:ring-2 focus:ring-brand-500 focus:border-brand-500"
                            :class="editModal.errors.user_id ? 'border-red-500' : ''">
                        <option value="">Unassigned</option>
                        <template x-for="user in getAvailableEmployees()" :key="user.id">
                            <option :value="user.id" x-text="user.name" :selected="user.id == editModal.shift.user_id"></option>
                        </template>
                    </select>
                    <p x-show="editModal.errors.user_id" class="mt-1 text-xs text-red-400" x-text="editModal.errors.user_id?.[0]"></p>
                </div>

                <!-- Date -->
                <div>
                    <label for="shift-date" class="block text-sm font-medium text-gray-400 mb-1.5">Date</label>
                    <input type="date"
                           id="shift-date"
                           x-model="editModal.shift.date"
                           class="w-full px-3 py-2 bg-gray-800 border border-gray-700 rounded-lg text-white focus:ring-2 focus:ring-brand-500 focus:border-brand-500"
                           :class="editModal.errors.date ? 'border-red-500' : ''">
                    <p x-show="editModal.errors.date" class="mt-1 text-xs text-red-400" x-text="editModal.errors.date?.[0]"></p>
                </div>

                <!-- Time Fields -->
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="shift-start-time" class="block text-sm font-medium text-gray-400 mb-1.5">Start Time</label>
                        <input type="time"
                               id="shift-start-time"
                               x-model="editModal.shift.start_time"
                               class="w-full px-3 py-2 bg-gray-800 border border-gray-700 rounded-lg text-white focus:ring-2 focus:ring-brand-500 focus:border-brand-500"
                               :class="editModal.errors.start_time ? 'border-red-500' : ''">
                        <p x-show="editModal.errors.start_time" class="mt-1 text-xs text-red-400" x-text="editModal.errors.start_time?.[0]"></p>
                    </div>
                    <div>
                        <label for="shift-end-time" class="block text-sm font-medium text-gray-400 mb-1.5">End Time</label>
                        <input type="time"
                               id="shift-end-time"
                               x-model="editModal.shift.end_time"
                               class="w-full px-3 py-2 bg-gray-800 border border-gray-700 rounded-lg text-white focus:ring-2 focus:ring-brand-500 focus:border-brand-500"
                               :class="editModal.errors.end_time ? 'border-red-500' : ''">
                        <p x-show="editModal.errors.end_time" class="mt-1 text-xs text-red-400" x-text="editModal.errors.end_time?.[0]"></p>
                    </div>
                </div>

                <!-- Break Duration -->
                <div>
                    <label for="shift-break" class="block text-sm font-medium text-gray-400 mb-1.5">Break (minutes)</label>
                    <input type="number"
                           id="shift-break"
                           x-model="editModal.shift.break_duration_minutes"
                           min="0"
                           max="480"
                           class="w-full px-3 py-2 bg-gray-800 border border-gray-700 rounded-lg text-white focus:ring-2 focus:ring-brand-500 focus:border-brand-500"
                           :class="editModal.errors.break_duration_minutes ? 'border-red-500' : ''">
                    <p x-show="editModal.errors.break_duration_minutes" class="mt-1 text-xs text-red-400" x-text="editModal.errors.break_duration_minutes?.[0]"></p>
                </div>

                <!-- Status (Read-only) -->
                <div>
                    <label class="block text-sm font-medium text-gray-400 mb-1.5">Status</label>
                    <div class="flex items-center gap-2">
                        <span class="inline-flex items-center px-3 py-1.5 rounded-lg text-sm font-medium"
                              :class="{
                                  'bg-gray-500/20 text-gray-400': editModal.shift.status === 'draft',
                                  'bg-blue-500/20 text-blue-400': editModal.shift.status === 'published',
                                  'bg-yellow-500/20 text-yellow-400': editModal.shift.status === 'in_progress',
                                  'bg-green-500/20 text-green-400': editModal.shift.status === 'completed',
                                  'bg-red-500/20 text-red-400': editModal.shift.status === 'missed',
                                  'bg-gray-500/20 text-gray-500': editModal.shift.status === 'cancelled'
                              }">
                            <span x-text="{
                                'draft': 'Draft',
                                'published': 'Published',
                                'in_progress': 'In Progress',
                                'completed': 'Completed',
                                'missed': 'Missed',
                                'cancelled': 'Cancelled'
                            }[editModal.shift.status] || editModal.shift.status"></span>
                        </span>
                        <span x-show="editModal.isCreateMode" class="text-xs text-gray-500">New shifts start as Draft</span>
                    </div>
                </div>

                <!-- Notes -->
                <div>
                    <label for="shift-notes" class="block text-sm font-medium text-gray-400 mb-1.5">Notes</label>
                    <textarea id="shift-notes"
                              x-model="editModal.shift.notes"
                              rows="2"
                              class="w-full px-3 py-2 bg-gray-800 border border-gray-700 rounded-lg text-white placeholder-gray-500 focus:ring-2 focus:ring-brand-500 focus:border-brand-500 resize-none"
                              :class="editModal.errors.notes ? 'border-red-500' : ''"
                              placeholder="Optional notes..."></textarea>
                    <p x-show="editModal.errors.notes" class="mt-1 text-xs text-red-400" x-text="editModal.errors.notes?.[0]"></p>
                </div>

                <!-- Recurrence Section (only for create mode or recurring parent) -->
                <div x-show="editModal.isCreateMode || editModal.shift.is_recurring" class="border-t border-gray-700 pt-4 mt-4">
                    <div class="flex items-center justify-between mb-4">
                        <label class="text-sm font-medium text-gray-400">Recurring Shift</label>
                        <button type="button"
                                x-show="editModal.isCreateMode"
                                @click="editModal.shift.is_recurring = !editModal.shift.is_recurring"
                                :class="editModal.shift.is_recurring ? 'bg-brand-600' : 'bg-gray-700'"
                                class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2 focus:ring-offset-gray-900">
                            <span :class="editModal.shift.is_recurring ? 'translate-x-5' : 'translate-x-0'"
                                  class="pointer-events-none relative inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"></span>
                        </button>
                    </div>

                    <!-- Recurrence Options -->
                    <div x-show="editModal.shift.is_recurring" x-collapse class="space-y-4">
                        <!-- Frequency -->
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-400 mb-1.5">Repeats</label>
                                <select x-model="editModal.shift.recurrence_rule.frequency"
                                        class="w-full px-3 py-2 bg-gray-800 border border-gray-700 rounded-lg text-white focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                                    <option value="daily">Daily</option>
                                    <option value="weekly">Weekly</option>
                                    <option value="monthly">Monthly</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-400 mb-1.5">Every</label>
                                <div class="flex items-center gap-2">
                                    <input type="number"
                                           x-model="editModal.shift.recurrence_rule.interval"
                                           min="1"
                                           max="52"
                                           class="w-20 px-3 py-2 bg-gray-800 border border-gray-700 rounded-lg text-white focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                                    <span class="text-sm text-gray-400" x-text="{
                                        'daily': editModal.shift.recurrence_rule.interval == 1 ? 'day' : 'days',
                                        'weekly': editModal.shift.recurrence_rule.interval == 1 ? 'week' : 'weeks',
                                        'monthly': editModal.shift.recurrence_rule.interval == 1 ? 'month' : 'months'
                                    }[editModal.shift.recurrence_rule.frequency]"></span>
                                </div>
                            </div>
                        </div>

                        <!-- Days of Week (for weekly) -->
                        <div x-show="editModal.shift.recurrence_rule.frequency === 'weekly'" x-collapse>
                            <label class="block text-sm font-medium text-gray-400 mb-2">On these days</label>
                            <div class="flex flex-wrap gap-2">
                                <template x-for="(day, index) in ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat']" :key="index">
                                    <button type="button"
                                            @click="toggleDayOfWeek(index)"
                                            :class="editModal.shift.recurrence_rule.days_of_week?.includes(index) ? 'bg-brand-600 text-white border-brand-500' : 'bg-gray-800 text-gray-400 border-gray-700 hover:border-gray-600'"
                                            class="w-10 h-10 rounded-lg border text-sm font-medium transition-colors">
                                        <span x-text="day.charAt(0)"></span>
                                    </button>
                                </template>
                            </div>
                        </div>

                        <!-- End Condition -->
                        <div>
                            <label class="block text-sm font-medium text-gray-400 mb-2">Ends</label>
                            <div class="space-y-2">
                                <label class="flex items-center gap-3 cursor-pointer">
                                    <input type="radio"
                                           x-model="editModal.recurrenceEndType"
                                           value="never"
                                           class="w-4 h-4 text-brand-600 bg-gray-800 border-gray-600 focus:ring-brand-500">
                                    <span class="text-sm text-gray-300">Never (extends automatically)</span>
                                </label>
                                <label class="flex items-center gap-3 cursor-pointer">
                                    <input type="radio"
                                           x-model="editModal.recurrenceEndType"
                                           value="date"
                                           class="w-4 h-4 text-brand-600 bg-gray-800 border-gray-600 focus:ring-brand-500">
                                    <span class="text-sm text-gray-300">On date</span>
                                    <input type="date"
                                           x-show="editModal.recurrenceEndType === 'date'"
                                           x-model="editModal.shift.recurrence_rule.end_date"
                                           :min="editModal.shift.date"
                                           class="px-2 py-1 bg-gray-800 border border-gray-700 rounded text-sm text-white focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                                </label>
                                <label class="flex items-center gap-3 cursor-pointer">
                                    <input type="radio"
                                           x-model="editModal.recurrenceEndType"
                                           value="occurrences"
                                           class="w-4 h-4 text-brand-600 bg-gray-800 border-gray-600 focus:ring-brand-500">
                                    <span class="text-sm text-gray-300">After</span>
                                    <input type="number"
                                           x-show="editModal.recurrenceEndType === 'occurrences'"
                                           x-model="editModal.shift.recurrence_rule.end_after_occurrences"
                                           min="1"
                                           max="365"
                                           class="w-16 px-2 py-1 bg-gray-800 border border-gray-700 rounded text-sm text-white focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                                    <span x-show="editModal.recurrenceEndType === 'occurrences'" class="text-sm text-gray-300">occurrences</span>
                                </label>
                            </div>
                        </div>

                        <!-- Recurrence Errors -->
                        <div x-show="editModal.errors.recurrence_rule" class="text-xs text-red-400">
                            <p x-text="editModal.errors['recurrence_rule.frequency']?.[0]"></p>
                        </div>
                    </div>
                </div>

                <!-- Edit Scope (for existing recurring shifts) -->
                <div x-show="!editModal.isCreateMode && (editModal.shift.is_recurring || editModal.shift.parent_shift_id)"
                     class="border-t border-gray-700 pt-4 mt-4">
                    <label class="block text-sm font-medium text-gray-400 mb-2">Apply changes to</label>
                    <div class="space-y-2">
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="radio"
                                   x-model="editModal.editScope"
                                   value="single"
                                   class="w-4 h-4 text-brand-600 bg-gray-800 border-gray-600 focus:ring-brand-500">
                            <div>
                                <span class="text-sm text-gray-300">This shift only</span>
                                <p class="text-xs text-gray-500">Detaches from series for individual changes</p>
                            </div>
                        </label>
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="radio"
                                   x-model="editModal.editScope"
                                   value="future"
                                   class="w-4 h-4 text-brand-600 bg-gray-800 border-gray-600 focus:ring-brand-500">
                            <div>
                                <span class="text-sm text-gray-300">This and all future shifts</span>
                                <p class="text-xs text-gray-500">Updates time, break, notes, role, and assignment</p>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Footer Actions -->
                <div class="flex items-center justify-between pt-4 border-t border-gray-700">
                    <!-- Delete Button (only in edit mode) -->
                    <div x-show="!editModal.isCreateMode">
                        <template x-if="!editModal.confirmDelete">
                            <button type="button"
                                    @click="editModal.confirmDelete = true"
                                    class="px-3 py-2 text-sm text-red-400 hover:text-red-300 hover:bg-red-500/10 rounded-lg transition-colors"
                                    :disabled="editModal.saving">
                                Delete Shift
                            </button>
                        </template>
                        <template x-if="editModal.confirmDelete">
                            <div class="space-y-3">
                                <!-- Delete scope for recurring shifts -->
                                <div x-show="editModal.shift.is_recurring || editModal.shift.parent_shift_id" class="text-sm space-y-2">
                                    <label class="flex items-center gap-2 cursor-pointer">
                                        <input type="radio" x-model="editModal.deleteScope" value="single" class="w-3 h-3 text-red-600 bg-gray-800 border-gray-600">
                                        <span class="text-gray-300">Delete this shift only</span>
                                    </label>
                                    <label class="flex items-center gap-2 cursor-pointer">
                                        <input type="radio" x-model="editModal.deleteScope" value="future" class="w-3 h-3 text-red-600 bg-gray-800 border-gray-600">
                                        <span class="text-gray-300">Delete this and all future shifts</span>
                                    </label>
                                </div>
                                <div class="flex items-center gap-2">
                                    <button type="button"
                                            @click="editModal.deleteShift()"
                                            :disabled="editModal.deleting"
                                            class="px-3 py-2 text-sm text-white bg-red-600 hover:bg-red-700 rounded-lg transition-colors disabled:opacity-50">
                                        <span x-show="!editModal.deleting">Confirm Delete</span>
                                        <span x-show="editModal.deleting">Deleting...</span>
                                    </button>
                                    <button type="button"
                                            @click="editModal.confirmDelete = false"
                                            :disabled="editModal.deleting"
                                            class="px-3 py-2 text-sm text-gray-400 hover:text-white transition-colors">
                                        Cancel
                                    </button>
                                </div>
                            </div>
                        </template>
                    </div>
                    <!-- Spacer for create mode -->
                    <div x-show="editModal.isCreateMode"></div>

                    <!-- Save/Cancel/Publish Buttons (hidden during delete confirmation) -->
                    <div x-show="!editModal.confirmDelete" class="flex items-center gap-3">
                        <button type="button"
                                @click="editModal.close()"
                                :disabled="editModal.saving || editModal.publishing"
                                class="px-4 py-2 text-sm text-gray-400 hover:text-white transition-colors">
                            Cancel
                        </button>
                        <button type="submit"
                                :disabled="editModal.saving || editModal.publishing"
                                class="px-4 py-2 text-sm text-white bg-brand-600 hover:bg-brand-700 rounded-lg transition-colors disabled:opacity-50">
                            <span x-show="!editModal.saving" x-text="editModal.isCreateMode ? 'Create Shift' : 'Save Changes'"></span>
                            <span x-show="editModal.saving" class="flex items-center gap-2">
                                <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <span x-text="editModal.isCreateMode ? 'Creating...' : 'Saving...'"></span>
                            </span>
                        </button>
                        <!-- Publish Button (only for draft shifts in edit mode) -->
                        <button type="button"
                                x-show="!editModal.isCreateMode && editModal.shift.status === 'draft'"
                                @click="editModal.publishShift()"
                                :disabled="editModal.saving || editModal.publishing"
                                class="px-4 py-2 text-sm text-white bg-green-600 hover:bg-green-700 rounded-lg transition-colors disabled:opacity-50">
                            <span x-show="!editModal.publishing">Publish</span>
                            <span x-show="editModal.publishing" class="flex items-center gap-2">
                                <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Publishing...
                            </span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
