<template>
    <div class="flex flex-col w-full h-full px-10 py-5 overflow-hidden box-border">
        <!-- Loading State -->
        <div v-if="loading" class="flex justify-center items-center h-64">
            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-[#003D5B]"></div>
        </div>

        <!-- Error State -->
        <div v-else-if="error" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            {{ error }}
        </div>

        <!-- Main Content -->
        <div v-else class="grid grid-cols-3 gap-6 h-full">

            <!-- LEFT: Profile Details -->
            <div class="col-span-1 bg-white rounded-2xl shadow p-6 flex flex-col space-y-6 box-border">

                <!-- Profile Header -->
                <div class="relative col-span-1 bg-white rounded-2xl shadow p-6 flex flex-col items-center text-center">

                    <!-- Profile Picture -->
                    <img
                        :src="getProfileImage(user.profile?.image || user.image)"
                        alt="Profile Photo"
                        class="w-[150px] h-[150px] rounded-full border-4 border-[#003D5B] mb-3 object-cover"
                    />

                    <!-- User Info -->
                    <h2 class="text-xl font-bold text-[#003D5B]">{{ user.profile?.name || user.name }}</h2>

                    <p class="text-gray-600 capitalize">{{ (user.profile?.role || user.role)?.replace('_', ' ') }}</p>

                    <span
                        class="px-3 py-1 my-3 text-xs font-medium rounded-full capitalize"
                        :class="getStatusClass(user.profile?.account_status || user.account_status)">
                        {{ user.profile?.account_status || user.account_status }}
                    </span>

                    <!-- Profile Actions -->
                    <div class="flex gap-2 mt-3">
                        <button
                            @click="navigateToEdit"
                            class="bg-[#003D5B] text-white text-sm px-4 py-2 rounded-xl hover:bg-[#004c73] transition flex items-center gap-2"
                        >
                            <FontAwesomeIcon :icon="['fas', 'pen-to-square']" />
                            Edit Profile
                        </button>
                        <button
                            @click="refreshAllData"
                            :disabled="loading || loadingLogs"
                            class="bg-gray-500 text-white text-sm px-4 py-2 rounded-xl hover:bg-gray-600 transition flex items-center gap-2 disabled:opacity-50"
                        >
                            <FontAwesomeIcon
                                :icon="['fas', 'rotate']"
                                :class="{ 'animate-spin': loading || loadingLogs }"
                            />
                            Refresh
                        </button>
                    </div>

                </div>

                <!-- Divider -->
                <hr class="border-gray-200" />

                <!-- User Info -->
                <div class="text-sm text-gray-700 space-y-2">
                    <p><strong>Email:</strong> {{ user.profile?.email || user.email }}</p>
                    <p><strong>Employee ID:</strong> {{ user.profile?.employee_id || user.employee_id }}</p>
                    <p><strong>Date Joined:</strong> {{ formatDate(user.profile?.created_at || user.created_at) }}</p>
                    <p><strong>Department:</strong> {{ user.profile?.department || user.department || 'Not specified' }}</p>
                    <p><strong>Contact:</strong> {{ user.profile?.contact_number || user.contact_number || 'Not provided' }}</p>
                </div>

                <!-- Performance Stats -->
                <!--
                <div class="mt-auto bg-gray-100 p-4 rounded-xl text-center">
                    <p class="font-medium text-[#003D5B]">
                        Tickets Handled: <span class="font-bold">{{ userStats.tickets_handled || 0 }}</span>
                    </p>
                    <p class="font-medium text-[#003D5B]">
                        Avg. Resolution Time: <span class="font-bold">{{ userStats.average_resolution_time_human || 'No data' }}</span>
                    </p>
                </div> -->
            </div>

            <!-- RIGHT PANEL NOW A SEPARATE COMPONENT -->
            <ActivityLog />
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, computed } from "vue";
import ActivityLog from "./UserActivityLogs.vue";

// Reactive data
const user = ref({});
const loading = ref(true);
const loadingLogs = ref(false);
const error = ref('');

// Computed properties for consistent data access
const userStats = computed(() => {
    return user.value.stats || {};
});

// Status badge classes
const getStatusClass = (status) => {
    const classes = {
        'active': 'bg-green-100 text-green-700',
        'inactive': 'bg-red-100 text-red-700',
        'on-leave': 'bg-yellow-100 text-yellow-700'
    };
    return classes[status] || 'bg-gray-200 text-gray-600';
};

// Get profile image URL
const getProfileImage = (imagePath) => {
    if (!imagePath) return '/images/default-avatar.png';
    if (imagePath.startsWith('http')) return imagePath;
    return `/storage/${imagePath}`;
};

// Format date for display
const formatDate = (dateString) => {
    if (!dateString) return 'N/A';
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
};

// Fetch user profile data
const fetchUserProfile = async () => {
    try {
        loading.value = true;
        const response = await axios.get('/api/user/profile');

        user.value = response.data;
        error.value = '';
    } catch (err) {
        console.error('Error fetching user profile:', err);
        if (err.response?.status === 401) {
            error.value = 'Please log in to view your profile.';
        } else if (err.response?.status === 404) {
            error.value = 'Profile endpoint not found. Please check the API configuration.';
        } else {
            error.value = 'Failed to load profile data. Please try again.';
        }
    } finally {
        loading.value = false;
    }
};

// Refresh all data
const refreshAllData = async () => {
    loading.value = true;
    loadingLogs.value = true;
    await fetchUserProfile();
};

// Navigate to edit profile page
const navigateToEdit = () => {
    sessionStorage.setItem('profileUpdated', 'true');
    window.location.href = '/dashboard/edit-profile';
};

// Initialize component
onMounted(async () => {
    const shouldRefresh = sessionStorage.getItem('profileUpdated') === 'true';

    await refreshAllData();

    if (shouldRefresh) {
        sessionStorage.removeItem('profileUpdated');
    }
});
</script>
