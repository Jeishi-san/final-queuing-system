<script setup>
import { ref } from 'vue';
// import axios from 'axios';

// // Set base URL for Herd
// axios.defaults.baseURL = 'https://final-queuing-system.test';

const style_input = 'w-full border border-gray-300 p-2 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#003D5B]';

const form = ref({
  name: '',
  role: '',
  email: '',
  employee_id: '',
  department: '',
  contact_number: '',
  password: '',
  password_confirmation: ''
})

const register = async () => {
    // ✅ FIXED: Use .value to access reactive data
    if (form.value.password !== form.value.password_confirmation) {
        alert("Passwords do not match.");
        return;
    }

    try {
        // ✅ CHANGED: Updated to new endpoint '/api/users/create-account'
        const res = await axios.post('/api/users/create-account', form.value);
        alert('Account registered successfully!')
        console.log(res.data)

        // Optional: Redirect to login
        // window.location.href = '/login';

    } catch (err) {
        console.error('Registration error:', err);

        // Better error handling
        if (err.response?.data?.errors) {
            const errors = err.response.data.errors;
            let errorMessage = 'Registration failed:\n';
            for (const field in errors) {
                errorMessage += `${field}: ${errors[field].join(', ')}\n`;
            }
            alert(errorMessage);
        } else if (err.code === 'ERR_NETWORK') {
            alert('Cannot connect to server. Make sure Laravel Herd is running.');
        } else {
            alert('Registration failed! Check console for details.');
        }
    }
}
</script>

<template>
    <div class="min-h-screen flex items-center justify-center">
        <div class="max-w-md mx-auto bg-white p-6 rounded-2xl shadow">
            <h2 class="text-2xl font-bold mb-4 text-center">Register Account</h2>

            <form @submit.prevent="register">
            <div class="space-y-3">
                <input v-model="form.name" type="text" placeholder="Full Name" required :class="style_input" />

                <!-- ✅ Better: Use select for role -->
                <select v-model="form.role" required :class="style_input">
                    <option value="">Select Role</option>
                    <option value="it_staff">IT Staff</option>
                    <option value="team_leader">Team Leader</option>
                    <option value="admin">Admin</option>
                </select>

                <input v-model="form.email" type="email" placeholder="Email" required :class="style_input" />
                <input v-model="form.employee_id" type="text" placeholder="Employee ID" required :class="style_input" />

                <input v-model="form.department" type="text" placeholder="Department" required :class="style_input" />
                <input v-model="form.contact_number" type="text" placeholder="Contact Number" required :class="style_input" />

                <input v-model="form.password" type="password" placeholder="Password" required :class="style_input" />
                <input v-model="form.password_confirmation" type="password" placeholder="Confirm Password" required :class="style_input" />
            </div>

            <button type="submit" class="w-full mt-4 bg-[#003D5B] text-white py-2 rounded-lg hover:bg-[#004c73]">
                Register
            </button>
            </form>

            <p class="text-center text-sm mt-4">
                Already have an account?
                <a href="/login" class="text-blue-600 hover:underline">Login here</a>
            </p>
            <a href="/">
                <button class="fixed bottom-5 left-5 text-white p-1 px-2 shadow-xl rounded-2xl hover:bg-[#029cda] transition">
                    <span class="font-bold text-xl">🏠</span>
                </button>
            </a>
        </div>
    </div>
</template>
