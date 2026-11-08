<template>
  <div class="dashboard">
    <DashboardSidebar :open="sidebarOpen" />

    <div class="dashboard__content">
      <DashboardHeader @toggle-sidebar="sidebarOpen = !sidebarOpen" />
      <main class="dashboard__main">
        <RouterView />
      </main>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import DashboardHeader from '../components/dashboard/DashboardHeader.vue'
import DashboardSidebar from '../components/dashboard/DashboardSidebar.vue'

const sidebarOpen = ref(true)
</script>

<style scoped>
.dashboard {
  min-height: 100vh;
  display: flex;
  background: #f5f5f5;
}

.dashboard__content {
  flex: 1;
  display: flex;
  flex-direction: column;
  min-width: 0;
  min-height: 100vh;
}

.dashboard__main {
  flex: 1;
  padding: 1.75rem 2rem;
  min-width: 0;
}

@media (max-width: 900px) {
  .dashboard__main {
    padding: 1.25rem;
  }
}
</style>
