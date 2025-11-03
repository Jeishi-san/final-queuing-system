<template>
  <div class="text-3xl font-bold">
    {{ time }}
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';

const time = ref('');

const updateClock = () => {
  const now = new Date();
  time.value = now.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit'});
}

onMounted(() => {
  updateClock();
  const interval = setInterval(updateClock, 1000);
  onUnmounted(() => clearInterval(interval));
})
</script>
