@extends('layouts.app')

@section('title', 'Register - ServiceMan')

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

                            <!-- Custom Category Input -->
                            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                                <p class="text-xs font-semibold text-blue-900 mb-2">
                                    <i class="fas fa-lightbulb mr-1"></i>Or enter a custom category:
                                </p>
                                <input type="text" 
                                       id="custom_category" 
                                       name="custom_category" 
                                       value="{{ old('custom_category') }}"
                                       placeholder="e.g., HVAC, Locksmith, Tiling, etc."
                                       class="w-full px-4 py-2 border-2 border-blue-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
                                <p class="mt-2 text-xs text-gray-600">
                                    <i class="fas fa-info-circle mr-1"></i>
                                    <strong>Don't see your category above?</strong> Type it here and admin will review it. You can also skip entirely and admin will assign you later.
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
                                    <input id="custom_skills" 
                                           type="text" 
                                           class="appearance-none block w-full px-4 py-3 border border-gray-300 rounded-xl placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all shadow-sm"
                                           placeholder="Add custom skills (comma separated)">
                                    <p class="text-xs text-gray-500 mt-1">e.g., Solar panel installation, Smart home setup</p>
                    </div>

                                <!-- Hidden field for final skills -->
                                <input type="hidden" id="skills" name="skills" value="{{ old('skills') }}">

                                <!-- Selected Skills Display -->
                                <div id="selectedSkills" class="flex flex-wrap gap-2 min-h-[40px] bg-white rounded-lg p-3 border border-gray-200"></div>
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

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    toggleUserTypeFields();
    fetchAndLoadSkills();
    
    // Load old skills if any
    const oldSkills = "{{ old('skills') }}";
    if (oldSkills) {
        oldSkills.split(',').forEach(skill => {
            if (skill.trim()) {
                selectedSkillsSet.add(skill.trim());
            }
        });
        updateSelectedSkills();
    }
    
    // Category change listener
    document.getElementById('category_id').addEventListener('change', function() {
        fetchAndLoadSkills();
    });
    
    // Custom skills input listener
    document.getElementById('custom_skills').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            addCustomSkills();
        }
    });
    
    document.getElementById('custom_skills').addEventListener('blur', addCustomSkills);
});

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
    const customSkills = customInput.value.split(',').map(s => s.trim()).filter(s => s);
    
    customSkills.forEach(skill => {
        if (skill && skill.length > 0) {
            selectedSkillsSet.add(skill);
        }
    });
    
    customInput.value = '';
    updateSelectedSkills();
}

function removeSkill(skill) {
    selectedSkillsSet.delete(skill);
    updateSelectedSkills();
    loadSkillsCheckboxes(allAvailableSkills); // Refresh checkboxes
}

function updateSelectedSkills() {
    const container = document.getElementById('selectedSkills');
    const hiddenInput = document.getElementById('skills');
    
    container.innerHTML = '';
    
    if (selectedSkillsSet.size === 0) {
        container.innerHTML = '<p class="text-sm text-gray-400 italic"><i class="fas fa-info-circle mr-1"></i>No skills selected yet - Add skills above</p>';
    } else {
        selectedSkillsSet.forEach(skill => {
            const badge = document.createElement('span');
            badge.className = 'inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-purple-100 text-purple-800 border border-purple-300';
            badge.innerHTML = `
                <i class="fas fa-check-circle mr-1 text-purple-600"></i>${skill}
                <button type="button" onclick="removeSkill('${skill.replace(/'/g, "\\'")}' )" class="ml-2 text-purple-600 hover:text-purple-900 hover:scale-110 transition-transform">
                    <i class="fas fa-times-circle"></i>
                </button>
            `;
            container.appendChild(badge);
        });
    }
    
    // Update hidden input
    hiddenInput.value = Array.from(selectedSkillsSet).join(', ');
}
</script>
@endsection
