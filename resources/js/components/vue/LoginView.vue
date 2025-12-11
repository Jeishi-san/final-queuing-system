<script setup>
import { ref } from "vue";
import axios from 'axios'; 

// Set base URL for Herd
axios.defaults.withCredentials = true; 

// img imports
import icon from '../../../assets/img/login-icon.png';

const form = ref({
    email: "",
    password: "",
    // You can keep other properties in 'form' if needed for rendering, 
    // but we will only send email/password in the axios call.
});

const handleLogin = async () => {
    console.log("Login attempted with:", form.value);
    try {
        await axios.get("/sanctum/csrf-cookie");

        // 1. Perform Login
        // ✅ FIX: Explicitly send ONLY the validated fields (email and password)
        await axios.post("/login", {
            email: form.value.email,
            password: form.value.password,
        });

        // 2. Fetch User to check Role
        const userRes = await axios.get("/api/user");
        const role = userRes.data.role;

        console.log("Login successful. Role:", role);

        // 3. Redirect based on role (Super Admin, Admin, and IT Staff all go to dashboard)
        if (role === 'agent') {
            window.location.href = "/queue";
        } else {
            // This covers 'it_staff', 'admin', and 'super_admin' roles
            window.location.href = "/dashboard";
        }

    } catch (error) {
        console.error("Login error:", error);
        if (error.response?.data?.errors) {
            const errors = error.response.data.errors;
            const errorKeys = Object.keys(errors);

            // Check if the error is the generic "auth.failed" message from the backend
            if (errorKeys.length === 1 && errorKeys[0] === 'email' && errors.email[0] === 'These credentials do not match our records.') {
                 alert('Login failed! Check your email and password.');
            } else {
                 let errorMessage = 'Login failed:\n';
                 for (const field in errors) {
                     errorMessage += `${errors[field].join(', ')}\n`;
                 }
                 alert(errorMessage);
            }
        } else if (error.code === 'ERR_NETWORK') {
            alert('Cannot connect to server. Make sure Laravel Herd is running.');
        } else {
            alert('Login failed! Check your email and password.');
        }
    }
};
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
        <div class="w-[325px] flex items-center justify-center">
            <div class="flex flex-col items-center w-full max-w-sm min-h-[440px] bg-white rounded-[15px] shadow-[0_70px_100px_50px_rgba(0,0,0,0.3)] p-3 pt-6 pb-6">
                
                <div class="flex justify-center mb-1">
                    <a href="/">
                        <img :src="icon" alt="Login Icon" class="w-16 h-16" />
                    </a>
                </div>

                <h2 class="text-3xl font-bold text-center text-[#003D5B] mt-2 mb-8"> IT Ops Queuing System</h2>

                <form @submit.prevent="handleLogin" class="space-y-5 w-[80%]">
                    <div class="relative">
                        <input
                            type="email"
                            id="email"
                            v-model="form.email"
                            placeholder="Email"
                            required
                            class="w-full pl-3 pr-3 py-2
                                   bg-[#003D5B]/20 text-[#003D5B]
                                   rounded-lg
                                   border border-transparent
                                   hover:bg-[#003D5B]/30 hover:border-[#003D5B]/40
                                   focus:outline-none focus:ring-2 focus:ring-[#003D5B]/70 focus:border-[#003D5B]
                                   placeholder-[#003D5B]/70
                                   transition-all duration-300"
                        />
                        <span class="absolute right-3 top-2 text-[#003D5B]">
                            <FontAwesomeIcon :icon="['fas', 'user']" />
                        </span>
                    </div>

                    <div class="relative">
                        <input
                            type="password"
                            id="password"
                            v-model="form.password"
                            placeholder="Password"
                            required
                            class="w-full pl-3 pr-3 py-2
                                   bg-[#003D5B]/20 text-[#003D5B]
                                   rounded-lg
                                   border border-transparent
                                   hover:bg-[#003D5B]/30 hover:border-[#003D5B]/40
                                   focus:outline-none focus:ring-2 focus:ring-[#003D5B]/70 focus:border-[#003D5B]
                                   placeholder-[#003D5B]/70
                                   transition-all duration-300"
                        />
                        <span class="absolute right-3 top-2 text-[#003D5B]">
                            <FontAwesomeIcon :icon="['fas', 'lock']" />
                        </span>
                    </div>

                    <button
                        type="submit"
                        class="w-full py-2
                               bg-[#003D5B] text-white font-medium
                               rounded-lg
                               hover:bg-[#029cda]
                               transition-colors duration-300"
                    >
                        Login
                    </button>
                </form>

                <div class="w-[80%] mt-8 border-t border-gray-200 pt-4">
                    <p class="text-xs text-center text-gray-500 mb-3">Register as new user:</p>
                    <div class="flex gap-2">
                        <a href="/register?role=agent" 
                            class="flex-1 py-2 text-xs text-center border border-[#003D5B] text-[#003D5B] rounded-md 
                                     hover:bg-[#003D5B] hover:text-white transition-all duration-300">
                            Agent
                        </a>
                        <a href="/register?role=it_staff" 
                            class="flex-1 py-2 text-xs text-center border border-[#003D5B] text-[#003D5B] rounded-md 
                                     hover:bg-[#003D5B] hover:text-white transition-all duration-300">
                            IT Staff
                        </a>
                    </div>
                </div>

            </div>
        </div>
    </div>
</template>