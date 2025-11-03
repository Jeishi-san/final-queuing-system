<template>
    <div class="flex flex-col h-[calc(100vh-100px)] w-full px-10 py-5 box-border overflow-hidden">
                <!-- Table Header -->
                <div class="grid grid-cols-[150px_120px_220px_120px_160px_90px_100px] bg-gray-200 p-3 rounded-t-2xl">
                    <div :class="style_header">Ticket ID</div>
                    <div :class="style_header">Name</div>
                    <div :class="style_header">Email</div>
                    <div :class="style_header">Issue Type</div>
                    <div :class="style_header">Date Added</div>
                    <div :class="style_header">Status</div>
                    <div :class="style_header">Action</div> <!-- Update (Updating the Status), Delete -->
                </div>

                <!-- Table Rows: data must be fetch from db --> 
                 <div class="flex-1 overflow-y-auto rounded-b-2xl bg-[#99bbc4]">
                    <div
                        class="grid grid-cols-[150px_120px_220px_120px_160px_90px_100px] border-b p-3 hover:bg-gray-100 cursor-pointer"
                        v-for="i in 20"
                        :key="i"
                    >
                        <div>INC000012496537</div>
                        <div>Ante Abeth {{ i }}</div>
                        <div>anteabeth{{ i }}@email.com</div>
                        <div>Keyboard</div>
                        <div>2025-10-20 10:35 AM</div>
                        <div>Pending</div>
                        <div class="flex space-x-3">
                            <button class="bg-[rgb(0,122,135)] text-white px-1 rounded hover:bg-[#006873] transition">Update</button>
                            <button class="bg-[#C9302C] text-white px-1 rounded hover:bg-[#A52824] transition">Delete</button>
                        </div>
                    </div>
                    
                </div>
                <!-- More rows can be added similarly -->
            </div>
</template>

<script setup>
    const style_header = "font-semibold text-[#003D5B]";
</script>