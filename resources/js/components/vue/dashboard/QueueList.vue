<template>
  <div class="flex flex-col items-center h-[calc(100vh-100px)] w-full px-10 py-5 box-border overflow-hidden">
    <!-- Table Container -->
    <div class="w-full max-w-6xl flex flex-col h-full">
      <!-- Table Header -->
      <div class="grid grid-cols-5 gap-3 bg-gray-200 p-3 rounded-t-2xl">
        <div :class="style_header">Queue No.</div>
        <div :class="style_header">Ticket ID</div>
        <div :class="style_header">Assigned to</div>
        <div :class="style_header">Last Modified</div>
        <div :class="style_header">Status</div>
      </div>

      <!-- Table Rows (scrollable area) -->
      <div class="flex-1 overflow-y-auto rounded-b-2xl bg-[#99bbc4]">
        <div
          class="grid grid-cols-5 gap-3 border-b p-3 hover:bg-gray-100 cursor-pointer"
          v-for="i in 20"
          :key="i"
        >
          <div>DAM0730002</div>
          <div>INC000012496537</div>
          <div>Randy Damos</div>
          <div>2025-10-20 10:35 AM</div>
          <div>In progress</div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
const style_header = "font-semibold text-[#003D5B]";
</script>
