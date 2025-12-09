<template>
  <div class="text-lg font-medium
            xs:text-xl">
    {{ date }}
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';

const date = ref('')

onMounted(() => {
  const now = new Date()
  date.value = now.toLocaleDateString('en-US', {
    // weekday: 'long',
    month: 'long',
    day: 'numeric',
    year: 'numeric'
  })
})
</script>
