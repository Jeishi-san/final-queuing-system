<script setup>
import { ref } from 'vue';
import axios from 'axios';

const style_input = 'w-full border border-gray-300 p-2 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#003D5B]';

const form = ref({
  name: '',
  role: '',
  email: '',
  employee_id: '',
  password: '',
  password_confirmation: ''
})

const loading = ref(false);

const register = async () => {
    if (form.value.password !== form.value.password_confirmation) {
        alert("Passwords do not match.");
        return;
    }

    loading.value = true;

    try {
        // First register the account
        const res = await axios.post('/api/users/create-account', form.value);
        console.log('Registration successful:', res.data);
        
        // Then auto-login with the same credentials
        await loginAfterRegistration();
        
    } catch (err) {
        console.error('Registration error:', err);
        
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
    } finally {
        loading.value = false;
    }
}

// Auto-login function
const loginAfterRegistration = async () => {
    try {
        // Use web login endpoint (not API)
        await axios.post('/login', {
            email: form.value.email,
            password: form.value.password,
            _token: document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') // CSRF token for web forms
        }, {
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            }
        });
        
        // Redirect to dashboard after successful login
        alert('Account created and logged in successfully!');
        window.location.href = '/dashboard';
        
    } catch (loginErr) {
        console.error('Auto-login failed:', loginErr);
        
        // If auto-login fails, show success message and redirect to login
        if (loginErr.response?.status === 422) {
            alert('Account created! Please login with your credentials.');
            window.location.href = '/login';
        } else {
            alert('Account created successfully! Please login.');
            window.location.href = '/login';
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
                
                <input v-model="form.role" type="text" placeholder="Role" required :class="style_input" />
                
                <input v-model="form.email" type="email" placeholder="Email" required :class="style_input" />
                <input v-model="form.employee_id" type="text" placeholder="Employee ID" required :class="style_input" />
                
                <input v-model="form.password" type="password" placeholder="Password" required :class="style_input" />
                <input v-model="form.password_confirmation" type="password" placeholder="Confirm Password" required :class="style_input" />
            </div>

            <button 
                type="submit" 
                :disabled="loading"
                class="w-full mt-4 bg-[#003D5B] text-white py-2 rounded-lg hover:bg-[#004c73] disabled:opacity-50 disabled:cursor-not-allowed"
            >
                {{ loading ? 'Registering...' : 'Register' }}
            </button>
            </form>

            <p class="text-center text-sm mt-4">
                Already have an account? <a href="/login" class="text-blue-600 hover:underline">Login here</a>
            </p>
            <a href="/">    
            <button
                class="fixed bottom-5 left-5 text-white p-1 px-2 shadow-xl rounded-2xl hover:bg-[#029cda] transition"
            >
                <span class="font-bold text-xl">
                    <FontAwesomeIcon :icon="['fas', 'house']" />
                </span>
            </button>
            </a>
        </div>
    </div>
</template>