<x-layouts.app title="Edit Leave Type">
    <div class="max-w-2xl">
        <div class="mb-6">
            <h2 class="text-lg font-semibold text-white">Edit Leave Type</h2>
            <p class="mt-1 text-sm text-gray-400">Update leave type settings.</p>
        </div>

        <form action="{{ route('leave-types.update', $leaveType) }}" method="POST" class="space-y-5">
            @csrf
            @method('PUT')

            <x-form.input name="name" label="Leave Type Name" required :value="$leaveType->name" />

            <x-form.color name="color" label="Color" :value="$leaveType->color" />

            <div class="space-y-4 bg-gray-800/50 rounded-lg p-4">
                <h3 class="text-sm font-medium text-gray-300">Leave Properties</h3>

                <x-form.checkbox name="requires_approval" label="Requires Approval" :checked="$leaveType->requires_approval" hint="Leave requests of this type must be approved by a manager" />

                <x-form.checkbox name="affects_allowance" label="Affects Allowance" :checked="$leaveType->affects_allowance" hint="Deduct from employee's annual leave allowance" />

                <x-form.checkbox name="is_paid" label="Paid Leave" :checked="$leaveType->is_paid" hint="Employee receives pay during this leave type" />

                <x-form.checkbox name="is_active" label="Active" :checked="$leaveType->is_active" hint="Allow employees to request this leave type" />
            </div>

            <div class="flex justify-end gap-3 pt-4">
                <x-button variant="secondary" :href="route('leave-types.index')">Cancel</x-button>
                <x-button type="submit">Save Changes</x-button>
            </div>
        </form>
    </div>
</x-layouts.app>
