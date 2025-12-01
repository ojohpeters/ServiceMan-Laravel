@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Users Management</h1>
            <p class="text-gray-600 mt-2">Manage all users in the system</p>
        </div>
        <button onclick="showModal('createAdminModal')" 
                class="inline-flex items-center px-6 py-3 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-lg transition-colors">
            <i class="fas fa-user-shield mr-2"></i>
            <span>Create Admin</span>
        </button>
    </div>

    <!-- Users Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900">All Users</h2>
        </div>
        
        @if($users->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Joined</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($users as $user)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <img src="{{ $user->profile_picture_url }}" 
                                             alt="{{ $user->full_name }}" 
                                             class="w-10 h-10 rounded-full object-cover border-2 border-gray-200">
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">{{ $user->full_name }}</div>
                                            <div class="text-sm text-gray-500">{{ $user->email }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @php
                                        $typeColors = [
                                            'ADMIN' => 'bg-red-100 text-red-800',
                                            'CLIENT' => 'bg-blue-100 text-blue-800',
                                            'SERVICEMAN' => 'bg-green-100 text-green-800',
                                        ];
                                        $typeColor = $typeColors[$user->user_type] ?? 'bg-gray-100 text-gray-800';
                                    @endphp
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $typeColor }}">
                                        {{ $user->user_type }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="space-y-1">
                                        @if($user->is_email_verified)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                <i class="fas fa-check-circle mr-1"></i>Verified
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                <i class="fas fa-clock mr-1"></i>Not Verified
                                            </span>
                                        @endif
                                        
                                        @if($user->user_type === 'SERVICEMAN')
                                            <br>
                                            @if($user->is_approved)
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                    <i class="fas fa-user-check mr-1"></i>Approved
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                    <i class="fas fa-user-times mr-1"></i>Not Approved
                                                </span>
                                            @endif
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $user->created_at->format('M d, Y') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex space-x-3">
                                        @if($user->user_type === 'SERVICEMAN')
                                            <a href="{{ route('servicemen.show', $user) }}" 
                                               class="text-blue-600 hover:text-blue-900" title="View Profile">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            
                                            @if($user->is_approved)
                                                <button onclick="toggleApproval({{ $user->id }}, false, '{{ $user->full_name }}')" 
                                                        class="text-red-600 hover:text-red-900" title="Revoke Approval">
                                                    <i class="fas fa-user-times"></i>
                                                </button>
                                            @else
                                                <button onclick="toggleApproval({{ $user->id }}, true, '{{ $user->full_name }}')" 
                                                        class="text-green-600 hover:text-green-900" title="Approve">
                                                    <i class="fas fa-user-check"></i>
                                                </button>
                                            @endif
                                        @elseif($user->user_type === 'CLIENT')
                                            <span class="text-gray-400" title="Clients cannot be viewed as profiles">
                                                <i class="fas fa-user"></i>
                                            </span>
                                        @else
                                            <span class="text-gray-400" title="Admin user">
                                                <i class="fas fa-shield-alt"></i>
                                            </span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $users->links() }}
            </div>
        @else
            <div class="text-center py-12">
                <i class="fas fa-users text-gray-400 text-6xl mb-4"></i>
                <h3 class="text-lg font-medium text-gray-900 mb-2">No users found</h3>
                <p class="text-gray-500 mb-6">Users will appear here when they register.</p>
            </div>
        @endif
    </div>
</div>

<!-- Create Admin Modal -->
<x-modern-modal id="createAdminModal" title="Create New Admin" icon="user-shield" iconColor="red" size="lg">
    <form id="createAdminForm" method="POST" action="{{ route('admin.users.create-admin') }}">
        @csrf
        <div class="space-y-5">
            <!-- Name Fields -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="first_name" class="block text-sm font-bold text-gray-900 mb-2">
                        <i class="fas fa-user mr-2 text-blue-600"></i>
                        First Name <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           id="first_name" 
                           name="first_name" 
                           required
                           placeholder="Enter first name"
                           class="w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent transition-all">
                </div>
                <div>
                    <label for="last_name" class="block text-sm font-bold text-gray-900 mb-2">
                        <i class="fas fa-user mr-2 text-blue-600"></i>
                        Last Name <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           id="last_name" 
                           name="last_name" 
                           required
                           placeholder="Enter last name"
                           class="w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent transition-all">
                </div>
            </div>

            <!-- Email -->
            <div>
                <label for="email" class="block text-sm font-bold text-gray-900 mb-2">
                    <i class="fas fa-envelope mr-2 text-purple-600"></i>
                    Email Address <span class="text-red-500">*</span>
                </label>
                <input type="email" 
                       id="email" 
                       name="email" 
                       required
                       placeholder="admin@example.com"
                       class="w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent transition-all">
            </div>

            <!-- Phone Number -->
            <div>
                <label for="phone_number" class="block text-sm font-bold text-gray-900 mb-2">
                    <i class="fas fa-phone mr-2 text-green-600"></i>
                    Phone Number <span class="text-red-500">*</span>
                </label>
                <input type="tel" 
                       id="phone_number" 
                       name="phone_number" 
                       required
                       placeholder="080XXXXXXXX"
                       class="w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent transition-all">
            </div>

            <!-- Password Fields -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="password" class="block text-sm font-bold text-gray-900 mb-2">
                        <i class="fas fa-lock mr-2 text-yellow-600"></i>
                        Password <span class="text-red-500">*</span>
                    </label>
                    <input type="password" 
                           id="password" 
                           name="password" 
                           required
                           minlength="8"
                           placeholder="Minimum 8 characters"
                           class="w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent transition-all">
                    <p class="text-xs text-gray-500 mt-1">Minimum 8 characters, include letters and numbers</p>
                </div>
                <div>
                    <label for="password_confirmation" class="block text-sm font-bold text-gray-900 mb-2">
                        <i class="fas fa-lock mr-2 text-yellow-600"></i>
                        Confirm Password <span class="text-red-500">*</span>
                    </label>
                    <input type="password" 
                           id="password_confirmation" 
                           name="password_confirmation" 
                           required
                           minlength="8"
                           placeholder="Re-enter password"
                           class="w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent transition-all">
                </div>
            </div>

            <!-- Warning Banner -->
            <div class="bg-red-50 border-l-4 border-red-500 rounded-r-xl p-4">
                <p class="text-sm text-red-900 font-semibold mb-2">
                    <i class="fas fa-exclamation-triangle mr-2"></i>Admin Privileges
                </p>
                <ul class="text-xs text-red-800 space-y-1.5 ml-6">
                    <li class="flex items-start">
                        <i class="fas fa-check text-red-600 mr-2 mt-0.5"></i>
                        <span>Full access to all platform features</span>
                    </li>
                    <li class="flex items-start">
                        <i class="fas fa-check text-red-600 mr-2 mt-0.5"></i>
                        <span>Can manage users, requests, and settings</span>
                    </li>
                    <li class="flex items-start">
                        <i class="fas fa-check text-red-600 mr-2 mt-0.5"></i>
                        <span>Email will be auto-verified</span>
                    </li>
                </ul>
            </div>
        </div>
    </form>

    <x-slot name="footer">
        <div class="flex flex-col sm:flex-row gap-3 w-full">
            <button type="button" 
                    onclick="hideModal('createAdminModal')"
                    class="flex-1 px-6 py-3 bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold rounded-xl transition-all transform hover:scale-105">
                <i class="fas fa-times mr-2"></i>Cancel
            </button>
            <button type="submit"
                    form="createAdminForm"
                    class="flex-1 px-6 py-3 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-lg transition-colors">
                <i class="fas fa-user-shield mr-2"></i>Create Admin
            </button>
        </div>
    </x-slot>
</x-modern-modal>

<script>
function toggleApproval(userId, approve, userName) {
    const action = approve ? 'approve' : 'revoke approval for';
    const confirmMessage = approve 
        ? `Are you sure you want to APPROVE ${userName}?\n\nThey will be able to login and accept jobs.`
        : `Are you sure you want to REVOKE APPROVAL for ${userName}?\n\nThey will be immediately logged out and unable to login until re-approved.`;
    
    if (confirm(confirmMessage)) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = approve 
            ? `/admin/servicemen/${userId}/approve`
            : `/admin/servicemen/${userId}/revoke-approval`;
        
        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = '{{ csrf_token() }}';
        form.appendChild(csrfToken);
        
        document.body.appendChild(form);
        form.submit();
    }
}
</script>
@endsection
