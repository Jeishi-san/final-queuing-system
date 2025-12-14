<script setup>
import { ref, onMounted, computed } from 'vue';
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

onMounted(() => {
    const urlParams = new URLSearchParams(window.location.search);
    const roleParam = urlParams.get('role');
    
    if (roleParam && ['agent', 'it_staff'].includes(roleParam)) {
        form.value.role = roleParam;
    }
});

const formTitle = computed(() => {
    if (form.value.role === 'agent') return 'Agent Registration';
    if (form.value.role === 'it_staff') return 'IT Staff Registration';
    return 'Register Account';
});

const register = async () => {
    // 1. Password Confirmation Check
    if (form.value.password !== form.value.password_confirmation) {
        alert("Passwords do not match.");
        return;
    }

    // 2. ✅ UX FIX: Client-side validation for @concentrix.com
    // This blocks non-Concentrix emails immediately before hitting the server.
    if (!form.value.email.toLowerCase().endsWith('@concentrix.com')) {
        alert('Registration Restricted: Only @concentrix.com email addresses are allowed.');
        return; 
    }

    loading.value = true;

    // 🛡️ SAFETY CHECK: Create a copy of the data
    const payload = { ...form.value };

    // Explicitly set employee_id to null if registering as an Agent
    // This prevents sending an empty string "" which might confuse strict validators
    if (payload.role !== 'it_staff') {
        payload.employee_id = null; 
    }

    try {
        // Send the sanitized 'payload' instead of 'form.value'
        const res = await axios.post('/register', payload);
        console.log('Registration successful:', res.data);
        
        const targetUrl = res.data.redirect_url || '/dashboard';
        await loginAfterRegistration(targetUrl);

    } catch (err) {
        console.error('Registration error:', err);

        if (err.response?.data?.errors) {
            const errors = err.response.data.errors;
            let errorMessage = 'Registration failed:\n';
            for (const field in errors) {
                errorMessage += `${errors[field].join(', ')}\n`;
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

// Updated to accept the targetUrl
const loginAfterRegistration = async (targetUrl) => {
    try {
        await axios.post('/login', {
            email: form.value.email,
            password: form.value.password,
            _token: document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') 
        });

        alert('Account created and logged in successfully!');
        window.location.href = targetUrl; // Use the dynamic URL

    } catch (loginErr) {
        console.error('Auto-login failed:', loginErr);
        alert('Account created! Please login manually.');
        window.location.href = '/login';
    }
}
</script>

<template>
    <div class="min-h-screen flex flex-col items-center justify-center gap-10">

        <a href="/">
            <button
                class="text-white p-1 px-2 hover:text-[#029cda] hover:bg-white rounded-[100%] transition"
            >
                <span class="font-bold text-xl">
                    <FontAwesomeIcon :icon="['fas', 'house']" />
                </span>
            </button>
        </a>

        <div class="max-w-md md:mx-auto bg-white p-6 rounded-2xl shadow-[0_70px_100px_20px_rgba(0,0,0,0.3)] xs:mx-3">
            
            <h2 class="text-2xl font-bold mb-2 text-center text-[#003D5B]">{{ formTitle }}</h2>
            
            <div v-if="form.role" class="flex justify-center mb-6">
                <span class="px-3 py-1 bg-blue-100 text-[#003D5B] text-xs font-semibold rounded-full uppercase tracking-wide">
                    Role: {{ form.role.replace('_', ' ') }}
                </span>
            </div>

            <form @submit.prevent="register">
                <div class="space-y-3">
                    <input v-model="form.name" type="text" placeholder="Full Name" required :class="style_input" />

                    <div v-if="!['agent', 'it_staff'].includes(form.role)">
                        <select v-model="form.role" required :class="style_input">
                            <option value="" disabled>Select Role</option>
                            <option value="agent">Agent</option>
                            <option value="it_staff">IT Staff</option>
                        </select>
                    </div>

                    <input 
                        v-model="form.email" 
                        type="email" 
                        placeholder="Email (@concentrix.com)" 
                        required 
                        :class="style_input" 
                    />
                    
                    <div v-if="form.role === 'it_staff'">
                        <input v-model="form.employee_id" type="text" placeholder="Employee ID" required :class="style_input" />
                    </div>

                    <input v-model="form.password" type="password" placeholder="Password" required :class="style_input" />
                    <input v-model="form.password_confirmation" type="password" placeholder="Confirm Password" required :class="style_input" />
                </div>

                <button
                    type="submit"
                    :disabled="loading"
                    class="w-full mt-4 bg-[#003D5B] text-white py-2 rounded-lg hover:bg-[#029cda] disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    {{ loading ? 'Registering...' : 'Register' }}
                </button>
            </form>

            <p class="text-center text-sm mt-4">
                Already have an account? <a href="/login" class="text-blue-600 hover:underline">Login here</a>
            </p>
        </div>
    </div>
</template>