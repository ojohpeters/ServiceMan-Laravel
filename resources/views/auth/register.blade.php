@extends('layouts.app')

@section('title', 'Register - ServiceMan')

@push('styles')
<style>
    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: scale(0.9);
        }
        to {
            opacity: 1;
            transform: scale(1);
        }
    }
    
    .animate-fade-in {
        animation: fadeIn 0.3s ease-out;
    }
</style>
@endpush

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-2xl w-full">
        <!-- Header -->
        <div class="text-center mb-8">
            <div class="w-20 h-20 bg-blue-600 rounded-lg flex items-center justify-center mx-auto mb-4 shadow-md">
                <i class="fas fa-user-plus text-white text-3xl"></i>
            </div>
            <h2 class="text-4xl font-bold text-gray-900 mb-2">
                Join ServiceMan Today!
            </h2>
            <p class="text-gray-600">Create your account and get started</p>
        </div>

        <!-- Error Messages -->
        @if($errors->any())
            <div class="mb-6 bg-red-50 border-l-4 border-red-500 p-4 rounded-r-lg shadow-md">
                <div class="flex items-start">
                    <i class="fas fa-exclamation-circle text-red-500 mr-3 mt-0.5"></i>
                    <div class="flex-1">
                        <p class="font-semibold text-red-700 mb-2">Please fix the following errors:</p>
                        <ul class="list-disc list-inside space-y-1">
                            @foreach($errors->all() as $error)
                                <li class="text-sm text-red-700">{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endif

        <!-- Registration Form -->
        <div class="bg-white rounded-lg shadow-lg overflow-hidden">
            <div class="px-8 pt-8 pb-6">
                <form method="POST" action="{{ route('register') }}" id="registerForm" class="space-y-6">
            @csrf
            
            <!-- User Type Selection -->
                    <div class="bg-blue-50 rounded-lg p-6 border border-blue-200">
                        <label class="text-base font-bold text-gray-900 mb-3 block">
                            <i class="fas fa-user-tag mr-2 text-blue-600"></i>I am joining as:
                        </label>
                        <div class="grid grid-cols-2 gap-4">
                            <label class="relative">
                                <input type="radio" name="user_type" value="CLIENT" 
                                       class="peer sr-only" 
                                       {{ old('user_type', 'CLIENT') === 'CLIENT' ? 'checked' : '' }}
                                       onchange="toggleUserTypeFields()">
                                <div class="cursor-pointer bg-white border-2 border-gray-300 rounded-xl p-4 text-center peer-checked:border-blue-600 peer-checked:bg-blue-50 transition-all hover:shadow-md">
                                    <i class="fas fa-user text-3xl text-gray-400 peer-checked:text-blue-600 mb-2"></i>
                                    <p class="font-semibold text-gray-900">Client</p>
                                    <p class="text-xs text-gray-500 mt-1">Looking for services</p>
                                </div>
                            </label>
                            <label class="relative">
                                <input type="radio" name="user_type" value="SERVICEMAN" 
                                       class="peer sr-only" 
                                       {{ old('user_type') === 'SERVICEMAN' ? 'checked' : '' }}
                                       onchange="toggleUserTypeFields()">
                                <div class="cursor-pointer bg-white border-2 border-gray-300 rounded-xl p-4 text-center peer-checked:border-purple-600 peer-checked:bg-purple-50 transition-all hover:shadow-md">
                                    <i class="fas fa-tools text-3xl text-gray-400 peer-checked:text-purple-600 mb-2"></i>
                                    <p class="font-semibold text-gray-900">Serviceman</p>
                                    <p class="text-xs text-gray-500 mt-1">Providing services</p>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- Personal Information -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="first_name" class="block text-sm font-semibold text-gray-700 mb-2">
                                <i class="fas fa-user mr-1 text-gray-400"></i>First Name
                            </label>
                            <input id="first_name" name="first_name" type="text" required 
                                   value="{{ old('first_name') }}"
                                   class="appearance-none block w-full px-4 py-3 border border-gray-300 rounded-xl placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all shadow-sm hover:shadow-md"
                                   placeholder="Enter first name">
                        </div>
                        <div>
                            <label for="last_name" class="block text-sm font-semibold text-gray-700 mb-2">
                                <i class="fas fa-user mr-1 text-gray-400"></i>Last Name
                        </label>
                            <input id="last_name" name="last_name" type="text" required 
                                   value="{{ old('last_name') }}"
                                   class="appearance-none block w-full px-4 py-3 border border-gray-300 rounded-xl placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all shadow-sm hover:shadow-md"
                                   placeholder="Enter last name">
                        </div>
                </div>

                    <!-- Username -->
                    <div>
                        <label for="username" class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-at mr-1 text-gray-400"></i>Username
                        </label>
                        <input id="username" name="username" type="text" required 
                               value="{{ old('username') }}"
                               class="appearance-none block w-full px-4 py-3 border border-gray-300 rounded-xl placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all shadow-sm hover:shadow-md"
                               placeholder="Choose a unique username">
            </div>

                    <!-- Email -->
                    <div>
                        <label for="email" class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-envelope mr-1 text-gray-400"></i>Email Address
                        </label>
                        <input id="email" name="email" type="email" required 
                               value="{{ old('email') }}"
                               class="appearance-none block w-full px-4 py-3 border border-gray-300 rounded-xl placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all shadow-sm hover:shadow-md"
                               placeholder="your@email.com">
                    </div>

                    <!-- Phone Number -->
                    <div>
                        <label for="phone_number" class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-phone mr-1 text-gray-400"></i>Phone Number <span class="text-red-500">*</span>
                        </label>
                        <input id="phone_number" name="phone_number" type="tel" required
                               value="{{ old('phone_number') }}"
                               class="appearance-none block w-full px-4 py-3 border border-gray-300 rounded-xl placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all shadow-sm hover:shadow-md"
                               placeholder="+234XXXXXXXXXX">
                        <p class="mt-1 text-xs text-gray-500">
                            <i class="fas fa-info-circle mr-1"></i>
                            Used for service coordination and notifications
                        </p>
                    </div>

                    <!-- Password -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="password" class="block text-sm font-semibold text-gray-700 mb-2">
                                <i class="fas fa-lock mr-1 text-gray-400"></i>Password
                            </label>
                            <input id="password" name="password" type="password" required minlength="8"
                                   class="appearance-none block w-full px-4 py-3 border border-gray-300 rounded-xl placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all shadow-sm hover:shadow-md"
                                   placeholder="Min. 8 characters">
                        </div>
                        <div>
                            <label for="password_confirmation" class="block text-sm font-semibold text-gray-700 mb-2">
                                <i class="fas fa-check-circle mr-1 text-gray-400"></i>Confirm Password
                            </label>
                            <input id="password_confirmation" name="password_confirmation" type="password" required minlength="8"
                                   class="appearance-none block w-full px-4 py-3 border border-gray-300 rounded-xl placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all shadow-sm hover:shadow-md"
                                   placeholder="Confirm password">
                    </div>
                </div>

                    <!-- Serviceman-specific fields -->
                    <div id="servicemanFields" style="display: none;" class="space-y-4 border-t-2 border-purple-200 pt-6">
                        <div class="bg-purple-50 rounded-lg p-4 border border-purple-200">
                            <p class="text-sm font-semibold text-purple-900">
                                <i class="fas fa-briefcase mr-2"></i>Professional Information
                            </p>
                            <p class="text-xs text-purple-700 mt-1">Complete your professional profile to start receiving requests</p>
                </div>

                        <!-- Category Selection (Like Skills) -->
                <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                <i class="fas fa-tag mr-1 text-gray-400"></i>Service Category <span class="text-gray-400 font-normal text-xs">(Optional)</span>
                            </label>
                            
                            <!-- Existing Categories as Radio Buttons -->
                            <div class="bg-purple-50 border border-purple-200 rounded-lg p-4 mb-3">
                                <p class="text-xs font-semibold text-purple-900 mb-3">
                                    <i class="fas fa-list mr-1"></i>Select from existing categories:
                                </p>
                                <div class="grid grid-cols-2 gap-2">
                                    @foreach(\App\Models\Category::where('is_active', true)->get() as $category)
                                        <label class="relative">
                                            <input type="radio" 
                                                   name="category_id" 
                                                   value="{{ $category->id }}" 
                                                   {{ old('category_id') == $category->id ? 'checked' : '' }}
                                                   class="peer sr-only">
                                            <div class="cursor-pointer bg-white border-2 border-purple-200 rounded-lg px-3 py-2 text-sm peer-checked:border-purple-600 peer-checked:bg-purple-100 peer-checked:text-purple-900 transition-all hover:shadow-md text-center">
                                                <i class="fas fa-tag mr-1"></i>{{ $category->name }}
                                            </div>
                                        </label>
                                    @endforeach
                                </div>
                </div>

                            <!-- Category Not Found Notice -->
                            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                                <p class="text-xs font-semibold text-yellow-900 mb-2">
                                    <i class="fas fa-info-circle mr-1"></i>Don't see your service category?
                                </p>
                                <p class="text-xs text-gray-700">
                                    If your service category is not listed above, please <strong>select "Other"</strong> if available, or contact us after registration. Admin will review your profile and assign the appropriate category.
                                </p>
                            </div>
                </div>

                        <!-- Experience -->
                        <div>
                            <label for="experience_years" class="block text-sm font-semibold text-gray-700 mb-2">
                                <i class="fas fa-calendar-alt mr-1 text-gray-400"></i>Years of Experience (Optional)
                            </label>
                            <input id="experience_years" name="experience_years" type="number" min="0" max="50"
                                   value="{{ old('experience_years') }}"
                                   class="appearance-none block w-full px-4 py-3 border border-gray-300 rounded-xl placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all shadow-sm hover:shadow-md"
                                   placeholder="Years of experience">
                    </div>

                        <!-- Skills Selector -->
                    <div>
                            <label for="skills_selector" class="block text-sm font-semibold text-gray-700 mb-2">
                                <i class="fas fa-star mr-1 text-gray-400"></i>Skills & Specializations
                            </label>
                            <div class="space-y-3">
                                <!-- Predefined Skills -->
                                <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                                    <p class="text-xs font-semibold text-gray-600 mb-3 uppercase">Common Skills (Select all that apply)</p>
                                    <div id="skillsCheckboxes" class="grid grid-cols-2 gap-2"></div>
                </div>

                                <!-- Custom Skills -->
                    <div>
                                    <div class="relative">
                                        <input id="custom_skills" 
                                               type="text" 
                                               onkeydown="if(event.key === 'Enter') { event.preventDefault(); event.stopPropagation(); if(this.value.trim()) addCustomSkills(); return false; }"
                                               class="appearance-none block w-full px-4 py-3 pr-12 border border-gray-300 rounded-xl placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all shadow-sm"
                                               placeholder="Type skills separated by comma">
                                        <button type="button" 
                                                id="addSkillsBtn"
                                                onclick="event.preventDefault(); event.stopPropagation(); addCustomSkills(); return false;"
                                                disabled
                                                class="absolute right-2 top-1/2 transform -translate-y-1/2 p-2 rounded-lg transition-all focus:outline-none opacity-30 cursor-not-allowed"
                                                style="pointer-events: none;"
                                                title="Type skills to enable">
                                            <i class="fas fa-check-circle text-xl text-purple-600"></i>
                                        </button>
                                    </div>
                                    <p class="text-xs text-gray-500 mt-1">
                                        <i class="fas fa-info-circle mr-1"></i>
                                        e.g., Solar panel installation, Smart home setup, Water heater repair
                                        <span class="text-purple-600 font-semibold">Press Enter or click the ✓ button to add</span>
                                    </p>
                    </div>

                                <!-- Hidden field for final skills -->
                                <input type="hidden" id="skills" name="skills" value="{{ old('skills') }}">

                                <!-- Selected Skills Display -->
                                <div id="selectedSkills" class="flex flex-wrap gap-2 min-h-[60px] bg-white rounded-lg p-3 border-2 border-gray-200 mt-2">
                                    <p class="text-sm text-gray-400 italic w-full">
                                        <i class="fas fa-info-circle mr-1"></i>
                                        Your skills will appear here as tags when you press Enter
                                    </p>
                                </div>
                                <p class="text-xs text-gray-500 mt-1">
                                    <i class="fas fa-lightbulb mr-1 text-yellow-500"></i>
                                    <strong>Tip:</strong> Type skills like "Electrical wiring, Troubleshooting, Panel installation" and press Enter
                                </p>
                    </div>
                </div>

                        <!-- Bio -->
                    <div>
                            <label for="bio" class="block text-sm font-semibold text-gray-700 mb-2">
                                <i class="fas fa-align-left mr-1 text-gray-400"></i>Professional Bio (Optional)
                            </label>
                            <textarea id="bio" name="bio" rows="3"
                                      class="appearance-none block w-full px-4 py-3 border border-gray-300 rounded-xl placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all shadow-sm hover:shadow-md"
                                      placeholder="Tell clients about your experience and expertise...">{{ old('bio') }}</textarea>
                </div>
            </div>
            
                    <!-- Client-specific fields -->
                    <div id="clientFields" style="display: none;" class="space-y-4 border-t-2 border-blue-200 pt-6">
                        <div class="bg-blue-50 rounded-lg p-4 border border-blue-200">
                            <p class="text-sm font-semibold text-blue-900">
                                <i class="fas fa-home mr-2"></i>Additional Information
                            </p>
                            <p class="text-xs text-blue-700 mt-1">Optional details to help servicemen serve you better</p>
            </div>
            
                <div>
                            <label for="address" class="block text-sm font-semibold text-gray-700 mb-2">
                                <i class="fas fa-map-marker-alt mr-1 text-gray-400"></i>Address (Optional)
                            </label>
                            <textarea id="address" name="address" rows="2"
                                      class="appearance-none block w-full px-4 py-3 border border-gray-300 rounded-xl placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all shadow-sm hover:shadow-md"
                                      placeholder="Your address for service location">{{ old('address') }}</textarea>
                </div>
            </div>

                    <!-- Submit Button -->
                    <div class="pt-4">
                <button type="submit" 
                                class="w-full flex justify-center items-center py-4 px-4 border border-transparent rounded-lg text-base font-semibold text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                            <i class="fas fa-user-plus mr-2"></i>
                    Create Account
                </button>
        </div>

                    <!-- Terms -->
                    <div class="flex items-center">
                        <input id="terms" name="terms" type="checkbox" required
                               class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                        <label for="terms" class="ml-2 block text-xs text-gray-700">
                            I agree to the <a href="#" class="text-blue-600 hover:text-blue-800">Terms of Service</a> and <a href="#" class="text-blue-600 hover:text-blue-800">Privacy Policy</a>
                        </label>
                    </div>
        </form>
            </div>

            <!-- Login Link -->
            <div class="px-8 py-4 bg-gray-50 border-t border-gray-200">
                <p class="text-center text-sm text-gray-600">
                    Already have an account?
                    <a href="{{ route('login') }}" class="font-semibold text-blue-600 hover:text-blue-800 transition-colors">
                        Sign in now <i class="fas fa-arrow-right ml-1"></i>
                    </a>
                </p>
            </div>
        </div>
    </div>
</div>

<script>
let selectedSkillsSet = new Set();
let allAvailableSkills = [];

// Prevent form submission when adding skills
function handleFormSubmit(e) {
    // Check if skills input is focused and has value - if so, add skills instead of submitting
    const customInput = document.getElementById('custom_skills');
    if (customInput && document.activeElement === customInput && customInput.value.trim()) {
        e.preventDefault();
        addCustomSkills();
        return false;
    }
    return true;
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    toggleUserTypeFields();
    fetchAndLoadSkills();
    
    // Load old skills if any
    const oldSkills = "{{ old('skills') }}";
    if (oldSkills && oldSkills.trim()) {
        oldSkills.split(',').forEach(skill => {
            const trimmedSkill = skill.trim();
            if (trimmedSkill) {
                selectedSkillsSet.add(trimmedSkill);
            }
        });
        updateSelectedSkills();
    } else {
        // Initialize empty display
        updateSelectedSkills();
    }
    
    // Category change listener
    const categoryField = document.getElementById('category_id');
    if (categoryField) {
        categoryField.addEventListener('change', function() {
            fetchAndLoadSkills();
        });
    }
    
    // Setup custom skills input listeners after a short delay to ensure element exists
    setTimeout(function() {
        setupCustomSkillsInput();
    }, 100);
    
    // Also setup when serviceman fields are shown
    const servicemanRadio = document.querySelector('input[name="user_type"][value="SERVICEMAN"]');
    if (servicemanRadio) {
        servicemanRadio.addEventListener('change', function() {
            setTimeout(function() {
                setupCustomSkillsInput();
                updateSelectedSkills();
            }, 200);
        });
    }
});

function setupCustomSkillsInput() {
    const customSkillsInput = document.getElementById('custom_skills');
    const addBtn = document.getElementById('addSkillsBtn');
    
    if (!customSkillsInput) return;
    
    // Enable/disable add button based on input
    function toggleAddButton() {
        if (addBtn) {
            const hasValue = customSkillsInput.value.trim().length > 0;
            if (hasValue) {
                // Enable button
                addBtn.disabled = false;
                addBtn.classList.remove('opacity-30', 'cursor-not-allowed');
                addBtn.classList.add('cursor-pointer', 'hover:bg-purple-50');
                addBtn.style.pointerEvents = 'auto';
                addBtn.style.cursor = 'pointer !important';
                addBtn.style.setProperty('cursor', 'pointer', 'important');
                addBtn.title = 'Add skills (or press Enter)';
                // Make icon hoverable
                const icon = addBtn.querySelector('i');
                if (icon) {
                    icon.classList.add('transition-colors');
                }
            } else {
                // Disable button
                addBtn.disabled = true;
                addBtn.classList.add('opacity-30', 'cursor-not-allowed');
                addBtn.classList.remove('cursor-pointer', 'hover:bg-purple-50');
                addBtn.style.pointerEvents = 'none';
                addBtn.style.setProperty('cursor', 'not-allowed', 'important');
                addBtn.title = 'Type skills to enable';
            }
        }
    }
    
    // Check button state on input
    customSkillsInput.addEventListener('input', toggleAddButton);
    
    // Enter key handler - prevent form submission
    customSkillsInput.addEventListener('keydown', function(e) {
        if (e.key === 'Enter' || e.keyCode === 13) {
            e.preventDefault();
            e.stopPropagation();
            e.stopImmediatePropagation();
            if (this.value.trim()) {
                addCustomSkills();
            }
            return false;
        }
    });
    
    // Also prevent default on keypress
    customSkillsInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter' || e.keyCode === 13) {
            e.preventDefault();
            e.stopPropagation();
            e.stopImmediatePropagation();
            return false;
        }
    });
    
    // Prevent form submission when Enter is pressed in skills input
    const form = document.getElementById('registerForm');
    if (form) {
        form.addEventListener('keydown', function(e) {
            const target = e.target;
            if (target && target.id === 'custom_skills' && (e.key === 'Enter' || e.keyCode === 13)) {
                e.preventDefault();
                e.stopPropagation();
                if (target.value.trim()) {
                    addCustomSkills();
                }
                return false;
            }
        }, true); // Use capture phase
    }
    
    // Initial button state
    toggleAddButton();
}

function toggleUserTypeFields() {
    const userType = document.querySelector('input[name="user_type"]:checked')?.value;
    const servicemanFields = document.getElementById('servicemanFields');
    const clientFields = document.getElementById('clientFields');
    const categoryField = document.getElementById('category_id');
    
    if (userType === 'SERVICEMAN') {
            servicemanFields.style.display = 'block';
            clientFields.style.display = 'none';
        categoryField.required = true;
    } else {
            servicemanFields.style.display = 'none';
            clientFields.style.display = 'block';
        categoryField.required = false;
    }
}

function fetchAndLoadSkills() {
    const categoryId = document.getElementById('category_id').value;
    const container = document.getElementById('skillsCheckboxes');
    
    container.innerHTML = '<p class="text-xs text-gray-500">Loading skills...</p>';
    
    // Fetch skills from API
    fetch('/api/skills/common?category_id=' + (categoryId || ''))
        .then(response => response.json())
        .then(data => {
            // Combine all skills sources
            allAvailableSkills = [
                ...data.common,
                ...data.category_specific,
                ...data.from_database
            ];
            
            // Remove duplicates
            allAvailableSkills = [...new Set(allAvailableSkills)];
            
            loadSkillsCheckboxes(allAvailableSkills);
        })
        .catch(error => {
            console.error('Error loading skills:', error);
            // Fallback to basic skills
            allAvailableSkills = ['Customer Service', 'Problem Solving', 'Communication', 'Quality Work'];
            loadSkillsCheckboxes(allAvailableSkills);
        });
}

function loadSkillsCheckboxes(skills) {
    const container = document.getElementById('skillsCheckboxes');
    
    container.innerHTML = '';
    skills.forEach(skill => {
        const div = document.createElement('div');
        div.className = 'flex items-center';
        div.innerHTML = `
            <input type="checkbox" 
                   id="skill_${skill.replace(/\s+/g, '_').replace(/[^a-zA-Z0-9_]/g, '')}" 
                   value="${skill}"
                   ${selectedSkillsSet.has(skill) ? 'checked' : ''}
                   onchange="toggleSkill('${skill.replace(/'/g, "\\'")}')"
                   class="h-4 w-4 text-purple-600 focus:ring-purple-500 border-gray-300 rounded">
            <label for="skill_${skill.replace(/\s+/g, '_').replace(/[^a-zA-Z0-9_]/g, '')}" class="ml-2 text-xs text-gray-700 cursor-pointer hover:text-purple-600">
                ${skill}
            </label>
        `;
        container.appendChild(div);
    });
}

function toggleSkill(skill) {
    if (selectedSkillsSet.has(skill)) {
        selectedSkillsSet.delete(skill);
                } else {
        selectedSkillsSet.add(skill);
    }
    updateSelectedSkills();
}

function addCustomSkills() {
    const customInput = document.getElementById('custom_skills');
    const addBtn = document.getElementById('addSkillsBtn');
    
    if (!customInput) return;
    
    const inputValue = customInput.value.trim();
    if (!inputValue) {
        // Disable button if empty
        if (addBtn) {
            addBtn.disabled = true;
            addBtn.classList.add('opacity-30', 'cursor-not-allowed');
            addBtn.classList.remove('text-purple-600', 'hover:text-purple-700', 'hover:bg-purple-50', 'cursor-pointer');
            addBtn.style.pointerEvents = 'none';
            addBtn.title = 'Type skills to enable';
        }
        return;
    }
    
    // Split by comma and clean up
    const customSkills = inputValue.split(',')
        .map(s => s.trim())
        .filter(s => s && s.length > 0);
    
    if (customSkills.length === 0) {
        customInput.value = '';
        if (addBtn) {
            addBtn.disabled = true;
            addBtn.classList.add('opacity-30', 'cursor-not-allowed');
            addBtn.classList.remove('text-purple-600', 'hover:text-purple-700', 'hover:bg-purple-50', 'cursor-pointer');
            addBtn.style.pointerEvents = 'none';
            addBtn.title = 'Type skills to enable';
        }
        return;
    }
    
    // Add each skill
    let addedCount = 0;
    customSkills.forEach(skill => {
        if (skill && skill.length > 0 && !selectedSkillsSet.has(skill)) {
            selectedSkillsSet.add(skill);
            addedCount++;
        }
    });
    
    // Clear input after adding
    customInput.value = '';
    
    // Disable button after clearing
    if (addBtn) {
        addBtn.disabled = true;
        addBtn.classList.add('opacity-30', 'cursor-not-allowed');
        addBtn.classList.remove('text-purple-600', 'hover:text-purple-700', 'hover:bg-purple-50', 'cursor-pointer');
        addBtn.style.pointerEvents = 'none';
        addBtn.title = 'Type skills to enable';
    }
    
    // Update display
    updateSelectedSkills();
    
    // Visual feedback
    if (addedCount > 0) {
        customInput.classList.add('ring-2', 'ring-green-400');
        if (addBtn) {
            addBtn.classList.add('text-green-600');
        }
        setTimeout(() => {
            customInput.classList.remove('ring-2', 'ring-green-400');
            if (addBtn) {
                addBtn.classList.remove('text-green-600');
            }
        }, 600);
    }
    
    // Focus back on input for next skill entry
    customInput.focus();
}

function removeSkill(skill) {
    selectedSkillsSet.delete(skill);
    updateSelectedSkills();
    loadSkillsCheckboxes(allAvailableSkills); // Refresh checkboxes
}

function updateSelectedSkills() {
    const container = document.getElementById('selectedSkills');
    const hiddenInput = document.getElementById('skills');
    
    if (!container) return;
    
    container.innerHTML = '';
    
    if (selectedSkillsSet.size === 0) {
        container.innerHTML = '<p class="text-sm text-gray-400 italic w-full"><i class="fas fa-info-circle mr-1"></i>No skills selected yet - Type skills above and press Enter</p>';
    } else {
        selectedSkillsSet.forEach(skill => {
            const badge = document.createElement('span');
            badge.className = 'inline-flex items-center px-3 py-1.5 rounded-full text-sm font-medium bg-gradient-to-r from-purple-100 to-purple-50 text-purple-800 border-2 border-purple-300 hover:border-purple-400 transition-all animate-fade-in';
            badge.innerHTML = `
                <i class="fas fa-tag mr-1.5 text-purple-600 text-xs"></i>
                <span>${skill}</span>
                <button type="button" 
                        onclick="removeSkill('${skill.replace(/'/g, "\\'").replace(/"/g, '&quot;')}')" 
                        class="ml-2 text-purple-600 hover:text-red-600 hover:scale-110 transition-transform focus:outline-none"
                        title="Remove skill">
                    <i class="fas fa-times text-xs"></i>
                </button>
            `;
            container.appendChild(badge);
        });
    }
    
    // Update hidden input
    if (hiddenInput) {
        hiddenInput.value = Array.from(selectedSkillsSet).join(', ');
    }
}
</script>
@endsection
