<script setup >
import { ref } from 'vue';

const style_input ='w-full border border-gray-300 p-2 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#003D5B]';

const form = ref({
  name: '',
  role: '',
  email: '',
  employee_id: '',
  password: '',
  password_confirmation: ''
})

const register = async () => {
    if (form.password !== form.password_confirmation) {
        alert("Passwords do not match.");
        return;
    }

    try {
        const res = await axios.post('/register', form.value);
        alert('Account registered successfully!')
        console.log(res.data)
    } catch (err) {
        alert('Registration failed!')
        console.error(err)
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

            <button type="submit" class="w-full mt-4 bg-[#003D5B] text-white py-2 rounded-lg hover:bg-[#004c73]">
                Register
            </button>
            </form>

            <p class="text-center text-sm mt-4">
                Already have an account?
                <a href="/login" class="text-blue-600 hover:underline">Login here</a>
            </p>
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
    </div>
</template>
