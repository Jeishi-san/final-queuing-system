<template>
  <div class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40">
    <div class="bg-white rounded-2xl w-full max-w-lg p-6">
      <!-- Modal Title -->
      <h2 class="text-xl font-semibold mb-4">
        <slot name="title">Edit Ticket</slot>
      </h2>

      <!-- Modal Body -->
      <div class="space-y-3">
        <slot name="body"></slot>
      </div>

      <!-- Modal Footer -->
      <div class="flex justify-end mt-6 space-x-3">
        <slot name="footer"></slot>
        <button
          class="bg-gray-400 text-white px-4 py-2 rounded hover:bg-gray-500"
          @click="$emit('close')"
        >
          Cancel
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps(['ticket']);
defineEmits(['close', 'save']);
</script>
