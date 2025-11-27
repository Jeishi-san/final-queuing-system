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
                    v-model="form.holder_name"
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
                    v-model="form.holder_email"
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
                    v-model="form.ticket_number"
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

    const emit = defineEmits(['submitted']);

    const form = ref({
        holder_name: "",
        holder_email: "",
        ticket_number: "",
        issue: "not applicable",
        status: "pending approval"
    });

    const handleSubmit = async () => {
        console.log("Ticket added:", form.value);

        try {
            const response = await axios.post('/tickets', form.value)
            console.log("Ticket added successfully:", response.data);

            setTimeout(() => {
                emit('submitted') // tell the parent submission is successful
            }, 500);

            //window.location.href = "/queue";
        } catch (error) {
            if (error.response) {
            console.error("Adding failed:", error.response.data);
            } else {
            console.error("Error:", error);
            }
        }

    };
</script>
