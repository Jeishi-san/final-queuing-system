<script setup>
    import { ref } from "vue";
    import axios from 'axios';

    const emit = defineEmits(['submitted', 'failed', 'close']);

    const loading = ref(false);

    const form = ref({
        holder_name: "", // Hidden per logic
        holder_email: "", // Hidden per logic
        ticket_number: "",
        issue: "not applicable",
        status: "pending approval"
    });

    const handleSubmit = async () => {
        if (!form.value.ticket_number) return;

        console.log("Ticket adding...", form.value);
        loading.value = true;

        try {
            const response = await axios.post('/tickets', form.value)
            console.log("Ticket added successfully:", response.data);

            // Clear input
            form.value.ticket_number = "";

            setTimeout(() => {
                emit('submitted');
            }, 200);

        } catch (error) {
            if (error.response) {
                console.error("Adding failed:", error.response.data);
            } else {
                console.error("Error:", error);
            }

            setTimeout(() => {
                emit('failed');
            }, 500);
        } finally {
            loading.value = false;
        }
    };
</script>

<template>
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 pointer-events-auto">

        <div class="bg-white rounded-2xl shadow-[0_20px_60px_-15px_rgba(0,0,0,0.5)]
                    w-full max-w-md p-8 relative flex flex-col items-center
                    transform transition-all">

            <button
                @click="$parent.showAddTicket = false"
                class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 transition-colors p-2"
            >
                <FontAwesomeIcon :icon="['fas', 'times']" class="text-xl" />
            </button>

            <h3 class="w-full text-2xl font-bold text-[#003D5B] text-center mb-6">
                Add Ticket
            </h3>

            <form @submit.prevent="handleSubmit" class="w-full space-y-6">

                <div class="hidden">
                    <input type="text" id="name" v-model="form.holder_name" placeholder="Name" />
                </div>

                <div class="hidden">
                    <input type="email" id="email" v-model="form.holder_email" placeholder="Email" />
                </div>

                <div class="space-y-2">
                    <label for="ticket_id" class="text-sm font-semibold text-gray-600 ml-1">
                        Ticket Number
                    </label>
                    <input
                        type="text"
                        id="ticket_id"
                        v-model="form.ticket_number"
                        placeholder="e.g. INC000000000001"
                        required
                        autofocus
                        class="w-full px-4 py-3
                               bg-[#003D5B]/5 text-[#003D5B] font-medium text-lg
                               rounded-xl
                               border border-gray-200
                               hover:bg-[#003D5B]/10 hover:border-[#003D5B]/30
                               focus:outline-none focus:ring-2 focus:ring-[#003D5B] focus:border-transparent
                               placeholder-[#003D5B]/40
                               transition-all duration-200"
                    />
                </div>

                <button
                    type="submit"
                    :disabled="loading"
                    class="w-full py-3 mt-2
                           bg-[#003D5B] text-white font-bold text-lg
                           rounded-xl shadow-lg
                           hover:bg-[#029cda] hover:shadow-xl hover:-translate-y-0.5
                           active:scale-95
                           disabled:opacity-70 disabled:cursor-not-allowed
                           transition-all duration-200 flex items-center justify-center"
                >
                    <span v-if="loading">
                        <FontAwesomeIcon :icon="['fas', 'spinner']" spin class="mr-2" />
                        Processing...
                    </span>
                    <span v-else>
                        Submit Ticket
                    </span>
                </button>
            </form>
        </div>
    </div>
</template>
