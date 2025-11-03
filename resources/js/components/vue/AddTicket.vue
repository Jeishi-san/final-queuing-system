<template>
    <div class="absolute right-0 top-0 w-[25%] h-full bg-white flex flex-col items-center text-center shadow-[0_10px_50px_5px_rgba(0,0,0,0.3)] p-5">

        <h3 class="w-full text-left text-2xl font-bold text-[#003D5B] mt-5">Add Ticket</h3>

        <!-- Add Ticket Form -->
        <form @submit.prevent="handleSubmit" class="space-y-5 w-full mt-12">
            <!-- Name Input -->
            <div class="relative">
                <input
                    type="text"
                    id="name"
                    v-model="form.name"
                    placeholder="Name"
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
            </div>

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
            </div>

            <!-- Ticket ID Input -->
            <div class="relative">
                <input
                    type="text"
                    id="ticket_id"
                    v-model="form.ticket_id"
                    placeholder="Ticket ID"
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
            </div>

            <!-- Issue Type Select -->
            <div class="relative">
                <select
                    id="issue"
                    v-model="form.issue"
                    placeholder="Select Type"
                    required
                    class="w-full pl-3 pr-3 py-2
                            bg-[#003D5B]/20 text-[#003D5B]
                            rounded-lg
                            border border-transparent
                            hover:bg-[#003D5B]/30 hover:border-[#003D5B]/40
                            focus:outline-none focus:ring-2 focus:ring-[#003D5B]/70 focus:border-[#003D5B]
                            placeholder-[#003D5B]/70
                            transition-all duration-300
                            appearance-none"
                >
                    <option disabled value="">Select Category</option>
                    <option value="hardware">Hardware Issue</option>
                    <option value="software">Software Issue</option>
                    <option value="network">Network Problem</option>
                    <option value="accessory">Accessory Request</option>
                </select>

                <!-- Optional dropdown arrow -->
                <span class="absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none text-[#003D5B]/70">
                    ▼
                </span>
            </div>

            <!-- Submit Button -->
            <button
                type="submit"
                class="w-full py-2 mt-2
                        bg-[#003D5B] text-white font-medium
                        rounded-lg
                        hover:bg-[#029cda]
                        transition-colors duration-300"
            >
            Submit Ticket
            </button>
        </form>

    </div>
</template>

<script setup>
    import { ref } from "vue";
    import { useRouter } from "vue-router";

    const emit = defineEmits(['submitted']);

    const form = ref({
        name: "",
        email: "",
        ticket_id: "",
        issue: "",
    });

    const router = useRouter();

    const handleSubmit = () => {
        console.log("Ticket added:", form.value);
        // You’ll connect this to backend (Laravel) later

        // ... do form validation / API call here

        // Simulate success
        setTimeout(() => {
            emit('submitted') // tell the parent submission is successful
        }, 500);


        router.push('/');
    };
</script>
