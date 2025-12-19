<template>
  <div class="h-full bg-gray-50 dark:bg-gray-900 p-6">
    <div class="max-w-4xl mx-auto bg-white dark:bg-gray-800 rounded-2xl shadow-lg transition-all duration-300">
      <div class="p-8">
        <h2 class="text-2xl font-bold mb-6 text-gray-900 dark:text-white">
          Edit Your Profile
        </h2>

        <!-- ✅ Success Message -->
        <div v-if="successMessage" 
             class="bg-green-100 border border-green-300 text-green-800 px-4 py-3 rounded mb-6 text-center">
          {{ successMessage }}
        </div>

        <!-- ✅ Error Message -->
        <div v-if="errorMessage" 
             class="bg-red-100 border border-red-300 text-red-800 px-4 py-3 rounded mb-6 text-center">
          {{ errorMessage }}
        </div>

        <form @submit.prevent="updateProfile" class="space-y-6" enctype="multipart/form-data">
          <!-- 🖼 Profile Picture -->
          <div class="flex items-center gap-6 p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
            <div class="relative cursor-pointer" @click="triggerFileInput">
                  <img :src="profileImagePreview || getProfileImage(user.image) || defaultAvatar"
                   alt="Profile Picture"
                   class="w-20 h-20 rounded-full object-cover border-4 border-gray-300 dark:border-gray-600 shadow-sm transition-all duration-300 hover:border-blue-500">
              <div class="absolute bottom-0 right-0 bg-blue-600 text-white text-xs px-2 py-1 rounded-md shadow-sm">
                Edit
              </div>
            </div>

            <div class="flex-1">
              <label class="block text-gray-700 dark:text-gray-300 font-semibold mb-2">Upload New Picture</label>
              <input type="file" 
                     ref="fileInput"
                     @change="handleImageUpload"
                     class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 dark:bg-gray-600 dark:text-gray-300 dark:border-gray-500 focus:ring-2 focus:ring-blue-500"
                     accept="image/*">
              <p class="text-sm text-gray-500 mt-1" v-if="selectedFile">
                Selected: {{ selectedFile.name }}
              </p>
              <span v-if="errors.image" class="text-red-500 text-sm">{{ errors.image[0] }}</span>
            </div>
          </div>

          <!-- Personal Information Section -->
          <div class="space-y-4">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-3">Personal Information</h3>
            
            <!-- 🧍 Name -->
            <div>
              <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Full Name</label>
              <input id="name" 
                     type="text" 
                     v-model="form.name"
                     class="w-full p-3 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-300"
                     :class="{ 'border-red-500': errors.name }"
                     placeholder="Enter your full name">
              <span v-if="errors.name" class="text-red-500 text-sm">{{ errors.name[0] }}</span>
            </div>

            <!-- 📧 Email -->
            <div>
              <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Email Address</label>
              <input id="email" 
                     type="email" 
                     v-model="form.email"
                     class="w-full p-3 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-300"
                     :class="{ 'border-red-500': errors.email }"
                     placeholder="Enter your email">
              <span v-if="errors.email" class="text-red-500 text-sm">{{ errors.email[0] }}</span>
            </div>

            <!-- 📞 Contact Number -->
            <div>
              <label for="contact_number" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Contact Number</label>
              <div class="relative">
                <input id="contact_number" 
                       type="text" 
                       v-model="form.contact_number"
                       class="w-full p-3 pl-10 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-300"
                       :class="{ 'border-red-500': errors.contact_number }"
                       placeholder="+63 912 345 6789">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                  <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                  </svg>
                </div>
              </div>
              <span v-if="errors.contact_number" class="text-red-500 text-sm">{{ errors.contact_number[0] }}</span>
              <p class="text-xs text-gray-500 mt-1">Enter your mobile number with country code</p>
            </div>
          </div>

          <!-- 🔒 Password Change (Optional Section) -->
          <div class="pt-4 border-t border-gray-200 dark:border-gray-700">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-3">Change Password (Optional)</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">New Password</label>
                <input type="password" 
                       v-model="form.password" 
                       placeholder="Enter new password"
                       class="w-full p-3 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-gray-200 focus:ring-2 focus:ring-blue-500 transition-all duration-300"
                       :class="{ 'border-red-500': errors.password }">
              </div>
              
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Confirm New Password</label>
                <input type="password" 
                       v-model="form.password_confirmation" 
                       placeholder="Confirm new password"
                       class="w-full p-3 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-gray-200 focus:ring-2 focus:ring-blue-500 transition-all duration-300">
              </div>
            </div>
            <span v-if="errors.password" class="text-red-500 text-sm">{{ errors.password[0] }}</span>
            <p class="text-xs text-gray-500 mt-2">Leave blank if you don't want to change your password</p>
          </div>

          <!-- 💾 Save Button -->
          <div class="flex justify-end pt-4">
            <button type="submit"
                    :disabled="loading"
                    class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-medium shadow-md transition duration-300 focus:ring-2 focus:ring-blue-400 disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2">
              <span v-if="loading" class="animate-spin">⟳</span>
              {{ loading ? 'Saving...' : 'Save Changes' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, watch } from 'vue'

// Reactive data
const user = ref({})
const form = reactive({
  name: '',
  email: '',
  contact_number: '',
  password: '',
  password_confirmation: ''
})
const errors = ref({})
const loading = ref(false)
const successMessage = ref('')
const errorMessage = ref('')
const fileInput = ref(null)
const selectedFile = ref(null)
const profileImagePreview = ref(null)
// Default avatar resolved via Vite from resources/assets
const defaultAvatar = new URL('../../../../assets/img/profile.png', import.meta.url).href

// Get profile image URL
const getProfileImage = (imagePath) => {
  if (!imagePath) return null;
  const filename = typeof imagePath === 'string' ? imagePath.split('/').pop() : '';
  if (filename === 'super_admin.jpg') return defaultAvatar;
  if (imagePath.startsWith('http')) return imagePath;
  return `/storage/${imagePath}`;
};

// Fetch user data
const fetchUserProfile = async () => {
  try {
    const response = await axios.get('/api/user/profile')
    user.value = response.data.user
    
    // Populate form with current user data
    form.name = user.value.name || ''
    form.email = user.value.email || ''
    form.contact_number = user.value.contact_number || ''
    
  } catch (error) {
    console.error('Error fetching user profile:', error)
    errorMessage.value = 'Failed to load profile data'
  }
}

// Trigger file input
const triggerFileInput = () => {
  fileInput.value.click()
}

// Handle image upload
const handleImageUpload = (event) => {
  const file = event.target.files[0]
  if (!file) return

  // Validate file type and size
  const validTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif']
  const maxSize = 2 * 1024 * 1024 // 2MB

  if (!validTypes.includes(file.type)) {
    errorMessage.value = 'Please select a valid image file (JPEG, PNG, JPG, GIF)'
    return
  }

  if (file.size > maxSize) {
    errorMessage.value = 'Image size must be less than 2MB'
    return
  }

  selectedFile.value = file
  
  // Create preview
  const reader = new FileReader()
  reader.onload = (e) => {
    profileImagePreview.value = e.target.result
  }
  reader.readAsDataURL(file)
  
  // Clear any previous errors
  errorMessage.value = ''
}

// Update profile
const updateProfile = async () => {
  loading.value = true
  errors.value = {}
  successMessage.value = ''
  errorMessage.value = ''

  try {
    const formData = new FormData()
    
    // Add form data
    formData.append('name', form.name)
    formData.append('email', form.email)
    formData.append('contact_number', form.contact_number)
    formData.append('_method', 'PUT') // Laravel method spoofing for PUT
    
    // Add password if provided
    if (form.password) {
      formData.append('password', form.password)
      formData.append('password_confirmation', form.password_confirmation)
    }
    
    // Add image if selected
    if (selectedFile.value) {
      formData.append('image', selectedFile.value)
    }

    // Use the correct API endpoint - update the specific user
    const response = await axios.post(`/api/users/${user.value.id}`, formData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    })

    successMessage.value = 'Profile updated successfully!'
    
    // Clear password fields
    form.password = ''
    form.password_confirmation = ''
    
    // Update user data
    user.value = { ...user.value, ...response.data.user }
    
    // Clear image selection after successful upload
    if (selectedFile.value) {
      selectedFile.value = null
      profileImagePreview.value = null
      fileInput.value.value = ''
    }
    
    // Redirect after success
    setTimeout(() => {
      window.location.href = '/dashboard/my-profile';
    }, 2000)
    
  } catch (error) {
    console.error('Error updating profile:', error)
    
    if (error.response?.data?.errors) {
      errors.value = error.response.data.errors
    } else {
      errorMessage.value = error.response?.data?.message || 'Failed to update profile. Please try again.'
    }
  } finally {
    loading.value = false
  }
}

// Clear messages after 5 seconds
const clearMessages = () => {
  setTimeout(() => {
    successMessage.value = ''
    errorMessage.value = ''
  }, 5000)
}

// Watch for message changes to clear them
watch(successMessage, clearMessages)
watch(errorMessage, clearMessages)

// Initialize component
onMounted(() => {
  fetchUserProfile()
})
</script>

<style scoped>
/* Custom styles for the Vue component */
.animate-spin {
  animation: spin 1s linear infinite;
}

@keyframes spin {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}

/* Smooth transitions for all interactive elements */
input, button, img {
  transition: all 0.3s ease;
}

/* Focus styles */
input:focus {
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(59, 130, 246, 0.15);
}

/* Hover effects */
button:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(59, 130, 246, 0.3);
}
</style>