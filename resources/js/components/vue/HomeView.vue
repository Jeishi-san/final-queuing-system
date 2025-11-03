<script setup>

</script>

<template>
  <div class="min-h-screen flex flex-col items-center justify-center">
    <div class="text-center space-y-4">
      <h1 class="text-4xl font-bold text-white">Welcome to IT OPS Queuing System</h1>
      <p class="text-lg text-white">
        Monitor queues and streamline IT operations seamlessly.
      </p>

      <div class="flex justify-center gap-4 mt-6">
        <a
          href="/login"
          class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition"
        >
          Login
        </a>

        <a
          href="/queue"
          class="px-6 py-3 border border-white text-white hover:bg-blue-50 hover:text-blue-600 font-semibold rounded-lg transition"
        >
          View Queue Display
        </a>
      </div>
    </div>
  </div>
</template>

