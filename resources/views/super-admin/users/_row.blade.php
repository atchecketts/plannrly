<tr class="hover:bg-gray-800/50 transition-colors {{ $user->trashed() ? 'opacity-60' : '' }}"
    @if($groupKey) x-show="isExpanded('{{ $groupKey }}')" x-cloak @endif>
    <td class="whitespace-nowrap py-4 pl-6 pr-3">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-brand-900/50 rounded-full flex items-center justify-center text-brand-400 font-medium">
                {{ $user->initials }}
            </div>
            <div>
                <p class="font-medium text-white">{{ $user->full_name }}</p>
                <p class="text-sm text-gray-500">{{ $user->email }}</p>
            </div>
        </div>
    </td>
    <td class="whitespace-nowrap px-3 py-4">
        @if($user->tenant)
            <a href="{{ route('super-admin.tenants.show', $user->tenant) }}" class="text-sm text-brand-400 hover:text-brand-300">
                {{ $user->tenant->name }}
            </a>
        @else
            <span class="text-sm text-gray-500">No tenant</span>
        @endif
    </td>
    <td class="whitespace-nowrap px-3 py-4 text-sm">
        @php $role = $user->getHighestRole(); @endphp
        @if($role)
            <span class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium
                @if($role->value === 'super_admin') bg-amber-500/10 text-amber-400 ring-amber-500/20
                @elseif($role->value === 'admin') bg-purple-500/10 text-purple-400 ring-purple-500/20
                @else bg-gray-500/10 text-gray-400 ring-gray-500/20 @endif ring-1 ring-inset">
                {{ $role->label() }}
            </span>
        @else
            <span class="text-gray-500">Employee</span>
        @endif
    </td>
    <td class="whitespace-nowrap px-3 py-4 text-sm">
        @if($user->trashed())
            <span class="inline-flex items-center rounded-md bg-red-500/10 px-2 py-1 text-xs font-medium text-red-400 ring-1 ring-inset ring-red-500/20">
                Deleted
            </span>
        @elseif($user->is_active)
            <span class="inline-flex items-center rounded-md bg-green-500/10 px-2 py-1 text-xs font-medium text-green-400 ring-1 ring-inset ring-green-500/20">
                Active
            </span>
        @else
            <span class="inline-flex items-center rounded-md bg-gray-500/10 px-2 py-1 text-xs font-medium text-gray-400 ring-1 ring-inset ring-gray-500/20">
                Inactive
            </span>
        @endif
    </td>
    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-400">
        {{ $user->last_login_at?->diffForHumans() ?? 'Never' }}
    </td>
    <td class="whitespace-nowrap px-3 py-4 text-right pr-6">
        <div class="flex items-center justify-end gap-2">
            <a href="{{ route('super-admin.users.show', $user) }}" class="p-2 text-gray-400 hover:text-white hover:bg-gray-800 rounded-lg transition-colors" title="View">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                </svg>
            </a>
            @if(!$user->trashed() && !$user->isSuperAdmin())
                <form action="{{ route('super-admin.impersonate.start', $user) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="p-2 text-amber-400 hover:text-amber-300 hover:bg-gray-800 rounded-lg transition-colors" title="Impersonate">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                    </button>
                </form>
            @endif
        </div>
    </td>
</tr>
