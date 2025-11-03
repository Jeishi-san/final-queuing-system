<script setup>
    import { ref } from "vue";

    // img imports
    import icon from '../../../assets/img/login-icon.png';

    const form = ref({
        email: "",
        password: "",
    });

    const handleLogin = async () => {
        console.log("Login attempted with:", form.value);
        try {
            // (1) Get CSRF token from Laravel Sanctum
            await axios.get("/sanctum/csrf-cookie");

            // (2) Submit login data
            const response = await axios.post("/login", {
            email: form.value.email,
            password: form.value.password,
            });

            console.log("Login successful:", response.data);

            // (3) Redirect or show success message
            // router.push("/dashboard"); // or whatever page you want
            window.location.href = "/dashboard";
        } catch (error) {
            if (error.response) {
            console.error("Login failed:", error.response.data);
            } else {
            console.error("Error:", error);
            }
        }

    };

    function onLoginSuccess() {
        globalState.loginSuccess = true
    }
</script>


<template>
    <div class="flex items-center justify-center">
        <div class="min-h-screen w-[325px] flex items-center justify-center">
            <div class="flex flex-col items-center w-full max-w-sm h-[420px] bg-white rounded-[15px] shadow-[0_10px_100px_50px_rgba(0,0,0,0.3)] p-3 pt-6 ">
                <!-- Icon -->
                <div class="flex justify-center mb-1">
                    <a href="/">
                        <img :src="icon" alt="Login Icon" class="w-16 h-16" />
                    </a>
                </div>

                <!-- System Title -->
                <h2 class="text-3xl font-bold text-center text-[#003D5B] mt-2 mb-12"> IT Ops Queuing System</h2>

                <!-- Login Form -->
                <form @submit.prevent="handleLogin" class="space-y-5 w-[80%]">
                    <!-- Email Input -->
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

                    <!-- Password Input -->
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

                    <!-- Submit Button -->
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

                <p class="text-sm mt-4">
                    Don’t have an account?
                    <a href="/register" class="text-blue-600 hover:underline">Register here</a>
                </p>
            </div>
        </div>
        <a href="/">
            <button
                @click="$emit('prev_page')"
                class="fixed bottom-5 left-5 text-white p-1 px-2 shadow-xl rounded-2xl hover:bg-[#029cda] transition"
            >
                <span class="font-bold text-xl">
                    <FontAwesomeIcon :icon="['fas', 'house']" />
                </span>
            </button>
        </a>
    </div>
</template>


