<x-layouts.app title="Edit Business Role">
    <form action="{{ route('business-roles.update', $businessRole) }}" method="POST" class="space-y-6 bg-white p-6 shadow sm:rounded-lg">
        @csrf
        @method('PUT')

        <div>
            <label for="name" class="block text-sm font-medium text-gray-700">Role Name</label>
            <input type="text" name="name" id="name" value="{{ old('name', $businessRole->name) }}" required
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
            @error('name')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="department_id" class="block text-sm font-medium text-gray-700">Department</label>
            <select name="department_id" id="department_id" required
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                @foreach($departments as $department)
                    <option value="{{ $department->id }}" {{ old('department_id', $businessRole->department_id) == $department->id ? 'selected' : '' }}>
                        {{ $department->name }} ({{ $department->location->name }})
                    </option>
                @endforeach
            </select>
            @error('department_id')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="description" class="block text-sm font-medium text-gray-700">Description (optional)</label>
            <textarea name="description" id="description" rows="3"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">{{ old('description', $businessRole->description) }}</textarea>
        </div>

        <div>
            <label for="default_hourly_rate" class="block text-sm font-medium text-gray-700">Default Hourly Rate (optional)</label>
            <input type="number" step="0.01" name="default_hourly_rate" id="default_hourly_rate" value="{{ old('default_hourly_rate', $businessRole->default_hourly_rate) }}"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
        </div>

        <div>
            <label for="color" class="block text-sm font-medium text-gray-700">Color</label>
            <input type="color" name="color" id="color" value="{{ old('color', $businessRole->color) }}"
                class="mt-1 block h-10 w-20 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        </div>

        <div class="flex items-center">
            <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', $businessRole->is_active) ? 'checked' : '' }}
                class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
            <label for="is_active" class="ml-2 block text-sm text-gray-900">Active</label>
        </div>

        <div class="flex justify-end gap-4">
            <a href="{{ route('business-roles.index') }}" class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                Cancel
            </a>
            <button type="submit" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-500">
                Update Role
            </button>
        </div>
    </form>
</x-layouts.app>
