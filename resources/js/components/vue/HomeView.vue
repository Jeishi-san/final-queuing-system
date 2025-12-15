<script setup>

</script>

<template>
  <div class="min-h-screen flex flex-col items-center justify-center">
    <div class="text-center space-y-4">
      <h1 class="xs:text-2xl md:text-3xl lg:text-4xl font-bold text-white">Welcome to enQ</h1>
      <h1 class="xs:text-md md:text-lg lg:text-2xl font-bold text-white">The Concentrix IT Operations Queuing System</h1>
      <p class="xs:text-sm md:text-md lg:text-lg text-white">
        Monitor queues and streamline IT operations seamlessly.
      </p>

      <div class="flex justify-center gap-4
                    xs:flex-col xs:pt-4
                    md:flex-row md:[padding:revert]
                    ">
        <a
          href="/login"
          class="w-[85%] px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition"
        >
          Login
        </a>

        <a
          href="/queue"
          class="hidden px-6 py-3 border border-white text-white hover:bg-blue-50 hover:text-blue-600 font-semibold rounded-lg transition"
        >
          View Queue Display
        </a>
      </div>
    </div>
  </div>
</template>

