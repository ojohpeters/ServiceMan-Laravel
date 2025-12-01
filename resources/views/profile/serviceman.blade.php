@extends('layouts.app')

@section('title', 'Serviceman Profile')

@section('content')
<div class="max-w-4xl mx-auto py-6 sm:px-6 lg:px-8">
    <!-- Header -->
    <div class="mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Serviceman Profile</h1>
                <p class="text-gray-600">Manage your professional profile and availability</p>
            </div>
            <a href="{{ route('profile') }}" class="text-blue-600 hover:text-blue-800 font-medium">
                <i class="fas fa-arrow-left mr-2"></i>Back to Profile
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-6 bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg">
            <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="mb-6 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg">
            <i class="fas fa-exclamation-circle mr-2"></i>Please fix the following errors:
            <ul class="mt-2 list-disc list-inside">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

     <!-- Profile Form -->
    <div class="bg-white shadow-sm rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-medium text-gray-900">Professional Information</h2>
        </div>
        
        <form method="POST" action="{{ route('profile.serviceman.update') }}" enctype="multipart/form-data" class="p-6 space-y-6">
            @csrf
            @method('PUT')
            
            <!-- Profile Picture Upload -->
            <div class="flex items-center space-x-6">
                <div class="shrink-0">
                    <img id="profilePreview" src="{{ $user->profile_picture_url }}" alt="Profile Picture" 
                         class="h-24 w-24 object-cover rounded-full border-4 border-blue-100 shadow-md">
                </div>
                <div class="flex-1">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-camera mr-2 text-blue-600"></i>Profile Picture
                    </label>
                    <div class="flex items-center space-x-4">
                        <label for="profile_picture" class="cursor-pointer bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium transition-colors inline-flex items-center">
                            <i class="fas fa-upload mr-2"></i>Upload Photo
                        </label>
                        <input type="file" id="profile_picture" name="profile_picture" accept="image/*" class="hidden" onchange="previewImage(event)">
                        <span class="text-sm text-gray-500">JPG, PNG or GIF (Max 2MB)</span>
                    </div>
                </div>
            </div>

            <!-- First Name & Last Name -->
            <div class="grid md:grid-cols-2 gap-6">
                <div>
                    <label for="first_name" class="block text-sm font-medium text-gray-700 mb-2">
                        First Name <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="first_name" name="first_name" 
                           value="{{ old('first_name', $user->first_name) }}" required
                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                </div>
                <div>
                    <label for="last_name" class="block text-sm font-medium text-gray-700 mb-2">
                        Last Name <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="last_name" name="last_name" 
                           value="{{ old('last_name', $user->last_name) }}" required
                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                </div>
            </div>
            
            <!-- Service Category -->
            <div>
                <label for="category_id" class="block text-sm font-medium text-gray-700 mb-2">
                    Service Category <span class="text-gray-400 text-xs">(Optional - Admin can assign)</span>
                </label>
                <select id="category_id" name="category_id" 
                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                    <option value="">Select your service category</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" 
                                {{ old('category_id', $profile->category_id ?? '') == $category->id ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
                <p class="mt-1 text-xs text-gray-500">
                    <i class="fas fa-info-circle mr-1"></i>
                    Leave empty if you're awaiting admin assignment or physical verification
                </p>
            </div>

            <!-- Phone Number -->
            <div>
                <label for="phone_number" class="block text-sm font-medium text-gray-700 mb-2">
                    Phone Number
                </label>
                <input type="tel" id="phone_number" name="phone_number" 
                       value="{{ old('phone_number', $profile->phone_number ?? '') }}"
                       placeholder="Enter your contact number"
                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
            </div>

            <!-- Bio -->
            <div>
                <label for="bio" class="block text-sm font-medium text-gray-700 mb-2">
                    Professional Bio
                </label>
                <textarea id="bio" name="bio" rows="4" 
                          placeholder="Tell clients about your experience, specialties, and approach to service..."
                          class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">{{ old('bio', $profile->bio ?? '') }}</textarea>
                <p class="mt-1 text-sm text-gray-500">Maximum 1000 characters</p>
            </div>

            <!-- Experience Years -->
            <div>
                <label for="experience_years" class="block text-sm font-medium text-gray-700 mb-2">
                    Years of Experience
                </label>
                <input type="number" id="experience_years" name="experience_years" 
                       value="{{ old('experience_years', $profile->experience_years ?? '') }}"
                       min="0" max="50" placeholder="Enter years of experience"
                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
            </div>

            <!-- Skills -->
            <div>
                <label for="skills" class="block text-sm font-medium text-gray-700 mb-2">
                    Skills & Specializations
                </label>
                <textarea id="skills" name="skills" rows="3" 
                          placeholder="List your key skills, certifications, and specializations..."
                          class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">{{ old('skills', $profile->skills ?? '') }}</textarea>
                <p class="mt-1 text-sm text-gray-500">Maximum 1000 characters</p>
            </div>

            <!-- Availability -->
            <div>
                <label class="flex items-center">
                    <input type="checkbox" id="is_available" name="is_available" value="1"
                           {{ old('is_available', $profile->is_available ?? false) ? 'checked' : '' }}
                           class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                    <span class="ml-2 text-sm font-medium text-gray-700">Currently Available for New Jobs</span>
                </label>
                <p class="mt-1 text-sm text-gray-500">When checked, clients can book your services</p>
            </div>

            <!-- Submit Button -->
            <div class="flex items-center justify-end space-x-4 pt-6 border-t border-gray-200">
                <a href="{{ route('profile') }}" 
                   class="bg-gray-300 text-gray-700 px-6 py-2 rounded-lg font-medium hover:bg-gray-400 transition-colors">
                    Cancel
                </a>
                <button type="submit" 
                        class="bg-blue-600 text-white px-6 py-2 rounded-lg font-medium hover:bg-blue-700 transition-colors">
                    <i class="fas fa-save mr-2"></i>Save Profile
                </button>
            </div>
        </form>
    </div>

    <!-- Current Profile Summary -->
    @if($profile)
    <div class="mt-8 bg-gray-50 rounded-lg p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Current Profile Summary</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <h4 class="text-sm font-medium text-gray-500 mb-2">Service Category</h4>
                <p class="text-gray-900">{{ $profile->category->name ?? 'Not specified' }}</p>
            </div>
            <div>
                <h4 class="text-sm font-medium text-gray-500 mb-2">Experience</h4>
                <p class="text-gray-900">{{ $profile->experience_years ? $profile->experience_years . ' years' : 'Not specified' }}</p>
            </div>
            <div>
                <h4 class="text-sm font-medium text-gray-500 mb-2">Contact</h4>
                <p class="text-gray-900">{{ $profile->phone_number ?: 'Not provided' }}</p>
            </div>
            <div>
                <h4 class="text-sm font-medium text-gray-500 mb-2">Availability</h4>
                <span class="px-2 py-1 text-xs font-medium rounded-full {{ $profile->is_available ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                    {{ $profile->is_available ? 'Available' : 'Busy' }}
                </span>
            </div>
            @if($profile->bio)
            <div class="md:col-span-2">
                <h4 class="text-sm font-medium text-gray-500 mb-2">Bio</h4>
                <p class="text-gray-900">{{ $profile->bio }}</p>
            </div>
            @endif
            @if($profile->skills)
            <div class="md:col-span-2">
                <h4 class="text-sm font-medium text-gray-500 mb-2">Skills</h4>
                <p class="text-gray-900">{{ $profile->skills }}</p>
            </div>
            @endif
        </div>
    </div>
    @endif
</div>

@push('scripts')
<script>
    function previewImage(event) {
        const file = event.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('profilePreview').src = e.target.result;
            };
            reader.readAsDataURL(file);
        }
    }
</script>
@endpush
@endsection
