@props([
    'id',
    'title' => 'Modal',
    'size' => 'md', // sm, md, lg, xl, full
    'showClose' => true,
    'icon' => null,
    'iconColor' => 'blue',
])

@php
$sizeClasses = [
    'sm' => 'max-w-md',
    'md' => 'max-w-lg',
    'lg' => 'max-w-2xl',
    'xl' => 'max-w-4xl',
    'full' => 'max-w-full mx-4',
];

$iconColors = [
    'blue' => 'text-blue-600 bg-blue-100',
    'green' => 'text-green-600 bg-green-100',
    'yellow' => 'text-yellow-600 bg-yellow-100',
    'red' => 'text-red-600 bg-red-100',
    'purple' => 'text-purple-600 bg-purple-100',
    'indigo' => 'text-indigo-600 bg-indigo-100',
    'pink' => 'text-pink-600 bg-pink-100',
];
@endphp

<div id="{{ $id }}" 
     class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm hidden z-50 overflow-y-auto transition-all duration-300"
     onclick="if(event.target === this && {{ $showClose ? 'true' : 'false' }}) document.getElementById('{{ $id }}').classList.add('hidden')">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="{{ $sizeClasses[$size] }} w-full bg-white rounded-2xl shadow-2xl transform transition-all duration-300 scale-100 opacity-100 modal-content"
             onclick="event.stopPropagation()">
            <!-- Header -->
            <div class="relative px-8 py-6 border-b border-gray-200 bg-gradient-to-r from-gray-50 to-gray-100">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-4">
                        @if($icon)
                            <div class="{{ $iconColors[$iconColor] }} p-3 rounded-xl shadow-sm">
                                <i class="fas fa-{{ $icon }} text-2xl"></i>
                            </div>
                        @endif
                        <h3 class="text-2xl font-bold text-gray-900">{{ $title }}</h3>
                    </div>
                    @if($showClose)
                        <button type="button" 
                                onclick="document.getElementById('{{ $id }}').classList.add('hidden')"
                                class="text-gray-400 hover:text-gray-600 hover:bg-gray-200 p-2 rounded-lg transition-all">
                            <i class="fas fa-times text-xl"></i>
                        </button>
                    @endif
                </div>
                @isset($subtitle)
                    <p class="text-sm text-gray-600 mt-2">{{ $subtitle }}</p>
                @endisset
            </div>
            
            <!-- Body -->
            <div class="px-8 py-6">
                {{ $slot }}
            </div>
            
            <!-- Footer (if provided) -->
            @isset($footer)
                <div class="px-8 py-5 border-t border-gray-200 bg-gray-50 rounded-b-2xl">
                    {{ $footer }}
                </div>
            @endisset
        </div>
    </div>
</div>

@once
@push('scripts')
<script>
    // Enhanced modal functions
    function showModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.remove('hidden');
            document.body.style.overflow = 'hidden'; // Prevent background scroll
            
            // Animate in
            setTimeout(() => {
                const content = modal.querySelector('.modal-content');
                if (content) {
                    content.classList.add('scale-100', 'opacity-100');
                }
            }, 10);
        }
    }
    
    function hideModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            const content = modal.querySelector('.modal-content');
            
            // Animate out
            if (content) {
                content.classList.remove('scale-100', 'opacity-100');
                content.classList.add('scale-95', 'opacity-0');
            }
            
            setTimeout(() => {
                modal.classList.add('hidden');
                document.body.style.overflow = ''; // Restore scroll
                
                // Reset animation classes
                if (content) {
                    content.classList.remove('scale-95', 'opacity-0');
                }
            }, 200);
        }
    }
    
    function toggleModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            if (modal.classList.contains('hidden')) {
                showModal(modalId);
            } else {
                hideModal(modalId);
            }
        }
    }
    
    // Close modal on Escape key
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            const openModals = document.querySelectorAll('[id$="Modal"]:not(.hidden)');
            openModals.forEach(modal => {
                hideModal(modal.id);
            });
        }
    });
</script>
@endpush
@endonce

