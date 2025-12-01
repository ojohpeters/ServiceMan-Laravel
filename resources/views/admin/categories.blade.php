@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="container mx-auto px-4">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-8">
            <div>
                <h1 class="text-4xl font-bold text-gray-900 flex items-center">
                    <i class="fas fa-tags mr-3 text-blue-600"></i>
                    Categories Management
                </h1>
                <p class="text-gray-600 mt-2 text-lg">Manage service categories and their settings</p>
            </div>
            <button onclick="openCreateModal()" 
                    class="mt-4 md:mt-0 inline-flex items-center px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition-colors">
                <i class="fas fa-plus mr-2"></i>Add New Category
            </button>
        </div>

        @if($categories->count() > 0)
            <!-- Categories Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
                @foreach($categories as $category)
                    <div class="bg-white rounded-lg shadow-md hover:shadow-lg transition-shadow overflow-hidden border {{ $category->is_active ? 'border-green-200' : 'border-gray-200' }}">
                        <!-- Card Header -->
                        <div class="bg-blue-600 p-6">
                            <div class="flex items-center justify-between">
                                <div class="w-16 h-16 bg-white bg-opacity-20 rounded-xl flex items-center justify-center backdrop-blur-sm">
                                    <i class="fas fa-tools text-white text-2xl"></i>
                                </div>
                                <div>
                                    @if($category->is_active)
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-green-100 text-green-800 border-2 border-green-300">
                                            <i class="fas fa-check-circle mr-1"></i>Active
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-red-100 text-red-800 border-2 border-red-300">
                                            <i class="fas fa-times-circle mr-1"></i>Inactive
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Card Body -->
                        <div class="p-6">
                            <h3 class="text-xl font-bold text-gray-900 mb-2">{{ $category->name }}</h3>
                            <p class="text-sm text-gray-600 mb-4 line-clamp-2 min-h-[40px]">
                                {{ $category->description ?? 'No description provided' }}
                            </p>

                            <!-- Stats -->
                            <div class="flex items-center justify-between mb-6 pb-4 border-b border-gray-200">
                                <div class="flex items-center space-x-2">
                                    <div class="bg-blue-100 p-2 rounded-lg">
                                        <i class="fas fa-users text-blue-600"></i>
                                    </div>
                                    <div>
                                        <p class="text-xs text-gray-500 font-semibold uppercase">Servicemen</p>
                                        <p class="text-lg font-bold text-gray-900">{{ $category->servicemen_count ?? 0 }}</p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="text-xs text-gray-500 font-semibold uppercase">Created</p>
                                    <p class="text-sm font-medium text-gray-900">{{ $category->created_at->format('M d, Y') }}</p>
                                </div>
                            </div>

                            <!-- Actions -->
                            <button onclick="editCategory({{ $category->id }}, '{{ addslashes($category->name) }}', '{{ addslashes($category->description ?? '') }}', {{ $category->is_active ? 'true' : 'false' }})" 
                                    class="w-full flex items-center justify-center px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition-colors">
                                <i class="fas fa-edit mr-2"></i>Edit Category
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
            
            <!-- Pagination -->
            <div class="flex justify-center">
                {{ $categories->links() }}
            </div>
        @else
            <!-- Empty State -->
            <div class="bg-white rounded-lg shadow-md p-12 text-center">
                <div class="inline-flex items-center justify-center w-24 h-24 bg-gray-100 rounded-full mb-6">
                    <i class="fas fa-tools text-5xl text-gray-400"></i>
                </div>
                <h3 class="text-2xl font-bold text-gray-900 mb-3">No categories found</h3>
                <p class="text-gray-600 mb-8 text-lg">Get started by creating your first service category.</p>
                <button onclick="openCreateModal()" 
                        class="inline-flex items-center px-8 py-4 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition-colors">
                    <i class="fas fa-plus mr-2"></i>Create Category
                </button>
            </div>
        @endif
    </div>
</div>

<!-- Create/Edit Category Modal -->
<div id="categoryModal" class="fixed inset-0 bg-black bg-opacity-60 backdrop-blur-sm overflow-y-auto h-full w-full hidden z-50 flex items-center justify-center p-4">
    <div class="relative bg-white rounded-lg shadow-xl w-full max-w-2xl transform transition-all animate-fadeIn">
        <!-- Modal Header -->
        <div class="bg-blue-600 px-6 py-5 rounded-t-lg">
            <div class="flex justify-between items-center">
                <h3 class="text-2xl font-bold text-white flex items-center" id="modalTitle">
                    <i class="fas fa-tag mr-3"></i>Create New Category
                </h3>
                <button onclick="closeModal()" type="button" class="text-white hover:bg-white hover:bg-opacity-20 p-2 rounded-lg transition-colors">
                    <i class="fas fa-times text-2xl"></i>
                </button>
            </div>
        </div>
        
        <!-- Modal Body -->
        <form id="categoryForm" method="POST" class="p-6 md:p-8">
            @csrf
            <div class="space-y-5">
                <div>
                    <label for="categoryName" class="block text-sm font-bold text-gray-900 mb-2">
                        <i class="fas fa-tag mr-2 text-blue-600"></i>
                        Category Name <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="categoryName" name="name" required
                           class="w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                           placeholder="e.g., Electrical Services">
                </div>
                
                <div>
                    <label for="categoryDescription" class="block text-sm font-bold text-gray-900 mb-2">
                        <i class="fas fa-align-left mr-2 text-purple-600"></i>
                        Description
                    </label>
                    <textarea id="categoryDescription" name="description" rows="4"
                              class="w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all resize-none"
                              placeholder="Describe what services this category includes..."></textarea>
                </div>
                
                <div>
                    <label for="categoryIcon" class="block text-sm font-bold text-gray-900 mb-2">
                        <i class="fas fa-image mr-2 text-green-600"></i>
                        Icon URL <span class="text-gray-500 text-xs">(Optional)</span>
                    </label>
                    <input type="url" id="categoryIcon" name="icon_url"
                           class="w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                           placeholder="https://example.com/icon.png">
                </div>
                
                <div class="bg-blue-50 border-2 border-blue-200 rounded-xl p-4">
                    <div class="flex items-start">
                        <input type="checkbox" id="categoryActive" name="is_active" value="1" checked
                               class="h-5 w-5 text-blue-600 focus:ring-blue-500 border-gray-300 rounded mt-0.5">
                        <label for="categoryActive" class="ml-3">
                            <span class="block text-sm font-bold text-gray-900">Active Status</span>
                            <span class="block text-xs text-gray-600 mt-1">
                                Category will be visible to users and available for service bookings
                            </span>
                        </label>
                    </div>
                </div>
            </div>
            
            <!-- Modal Footer -->
            <div class="flex flex-col sm:flex-row gap-3 mt-8 pt-6 border-t-2 border-gray-200">
                <button type="button" onclick="closeModal()"
                        class="flex-1 px-6 py-3 bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold rounded-lg transition-colors">
                    <i class="fas fa-times mr-2"></i>Cancel
                </button>
                <button type="submit" id="submitBtn"
                        class="flex-1 px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition-colors">
                    <i class="fas fa-check mr-2"></i>
                    <span id="submitText">Create Category</span>
                </button>
            </div>
        </form>
    </div>
</div>

<style>
@keyframes fadeIn {
    from {
        opacity: 0;
        transform: scale(0.95);
    }
    to {
        opacity: 1;
        transform: scale(1);
    }
}

.animate-fadeIn {
    animation: fadeIn 0.3s ease-out;
}
</style>

<script>
let isEditMode = false;
let editCategoryId = null;

function openCreateModal() {
    isEditMode = false;
    editCategoryId = null;
    document.getElementById('modalTitle').innerHTML = '<i class="fas fa-tag mr-3"></i>Create New Category';
    document.getElementById('submitText').textContent = 'Create Category';
    document.getElementById('categoryForm').action = '{{ route("admin.categories.store") }}';
    document.getElementById('categoryForm').method = 'POST';
    
    // Remove method spoofing for create mode
    const methodInput = document.getElementById('_method');
    if (methodInput) {
        methodInput.remove();
    }
    
    document.getElementById('categoryForm').reset();
    document.getElementById('categoryActive').checked = true;
    document.getElementById('categoryModal').classList.remove('hidden');
}

function editCategory(id, name, description, isActive) {
    isEditMode = true;
    editCategoryId = id;
    document.getElementById('modalTitle').innerHTML = '<i class="fas fa-edit mr-3"></i>Edit Category';
    document.getElementById('submitText').textContent = 'Update Category';
    document.getElementById('categoryForm').action = `/admin/categories/${id}`;
    document.getElementById('categoryForm').method = 'POST';
    
    // Add method spoofing for PUT request
    if (!document.getElementById('_method')) {
        const methodInput = document.createElement('input');
        methodInput.type = 'hidden';
        methodInput.name = '_method';
        methodInput.value = 'PUT';
        methodInput.id = '_method';
        document.getElementById('categoryForm').appendChild(methodInput);
    }
    
    document.getElementById('categoryName').value = name;
    document.getElementById('categoryDescription').value = description;
    document.getElementById('categoryActive').checked = isActive;
    document.getElementById('categoryModal').classList.remove('hidden');
}

function closeModal() {
    document.getElementById('categoryModal').classList.add('hidden');
    if (document.getElementById('_method')) {
        document.getElementById('_method').remove();
    }
}

// Close modal when clicking outside
document.getElementById('categoryModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeModal();
    }
});

// Close modal with Escape key
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        closeModal();
    }
});
</script>
@endsection
