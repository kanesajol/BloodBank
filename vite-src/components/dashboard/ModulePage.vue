<template>
  <div class="module-page">
    <header class="module-page__header">
      <h2>{{ title }}</h2>
      <p v-if="description" class="module-page__desc">{{ description }}</p>
    </header>
    <div class="module-page__content">
      <slot>
        <p class="module-page__placeholder">This module will be implemented next.</p>
      </slot>
    </div>
  </div>
</template>

<script setup>
defineProps({
  title: { type: String, required: true },
  description: { type: String, default: '' },
})
</script>

<style scoped>
.module-page__header {
  margin-bottom: 1.5rem;
}

.module-page__header h2 {
  font-size: 1.35rem;
  color: var(--color-red-primary);
  margin-bottom: 0.35rem;
}

.module-page__desc {
  color: var(--color-text-muted);
  font-size: 0.9rem;
  line-height: 1.5;
}

.module-page__content {
  background: var(--color-white);
  border-radius: 12px;
  padding: 1.75rem;
  box-shadow: 0 2px 12px rgba(0, 0, 0, 0.06);
}

.module-page__placeholder {
  color: var(--color-text-muted);
  font-style: italic;
}
</style>
