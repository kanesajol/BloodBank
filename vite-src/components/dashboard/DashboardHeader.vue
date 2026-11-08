<template>
  <header class="header">
    <button
      type="button"
      class="header__icon-btn"
      aria-label="Toggle sidebar"
      @click="$emit('toggle-sidebar')"
    >
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
        <path d="M4 6h16M4 12h16M4 18h16" />
      </svg>
    </button>

    <div class="header__actions">
      <div class="header__user" ref="userMenuRef">
        <button
          type="button"
          class="header__user-btn"
          :aria-expanded="userMenuOpen"
          aria-haspopup="true"
          @click="userMenuOpen = !userMenuOpen"
        >
          <span class="header__avatar">AV</span>
          <span class="header__username">Admin</span>
          <svg class="header__chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <path d="M6 9l6 6 6-6" />
          </svg>
        </button>

        <div v-if="userMenuOpen" class="header__dropdown">
          <RouterLink to="/dashboard/profile" class="header__dropdown-item" @click="userMenuOpen = false">
            Staff Profile
          </RouterLink>
          <button type="button" class="header__dropdown-item header__dropdown-item--danger" @click="handleLogout">
            Sign Out
          </button>
        </div>
      </div>
    </div>
  </header>
</template>

<script setup>
import { onMounted, onUnmounted, ref } from 'vue'
import { useRouter } from 'vue-router'

defineEmits(['toggle-sidebar'])

const router = useRouter()
const userMenuOpen = ref(false)
const userMenuRef = ref(null)

function handleLogout() {
  userMenuOpen.value = false
  router.push('/')
}

function handleClickOutside(event) {
  if (userMenuRef.value && !userMenuRef.value.contains(event.target)) {
    userMenuOpen.value = false
  }
}

onMounted(() => {
  document.addEventListener('click', handleClickOutside)
})

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside)
})
</script>

<style scoped>
.header {
  position: sticky;
  top: 0;
  display: flex;
  align-items: center;
  justify-content: space-between;
  background: var(--color-white);
  border-bottom: 1px solid #e8eaed;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
  z-index: 80;
  padding: 0 1.5rem;
  height: 64px;
  box-sizing: border-box;
}

.header__icon-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 38px;
  height: 38px;
  flex-shrink: 0;
  border: 1px solid #e0e4ea;
  border-radius: 8px;
  background: var(--color-white);
  color: #4a5568;
  cursor: pointer;
  transition: background 0.2s, border-color 0.2s;
}

.header__icon-btn:hover {
  background: #f5f7fa;
  border-color: #cdd5df;
}

.header__icon-btn svg {
  width: 18px;
  height: 18px;
}

.header__actions {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  margin-left: auto;
  flex-shrink: 0;
}

.header__user {
  position: relative;
}

.header__user-btn {
  display: flex;
  align-items: center;
  gap: 0.55rem;
  height: 38px;
  padding: 0 0.65rem 0 0.35rem;
  border: 1px solid #e0e4ea;
  border-radius: 8px;
  background: var(--color-white);
  cursor: pointer;
  transition: background 0.2s, border-color 0.2s;
}

.header__user-btn:hover {
  background: #f5f7fa;
  border-color: #cdd5df;
}

.header__avatar {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 28px;
  height: 28px;
  border-radius: 6px;
  background: #e8edf2;
  border: 2px solid #a5d6a7;
  font-size: 0.65rem;
  font-weight: 700;
  color: #37474f;
  flex-shrink: 0;
}

.header__username {
  font-size: 0.85rem;
  font-weight: 600;
  color: #1a1a2e;
  white-space: nowrap;
}

.header__chevron {
  width: 14px;
  height: 14px;
  color: #8899a8;
  flex-shrink: 0;
}

.header__dropdown {
  position: absolute;
  top: calc(100% + 6px);
  right: 0;
  min-width: 160px;
  padding: 0.35rem;
  background: var(--color-white);
  border: 1px solid #e0e4ea;
  border-radius: 10px;
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
  z-index: 20;
}

.header__dropdown-item {
  display: block;
  width: 100%;
  padding: 0.55rem 0.75rem;
  border: none;
  border-radius: 7px;
  background: transparent;
  font-size: 0.85rem;
  font-weight: 500;
  color: #37474f;
  text-decoration: none;
  text-align: left;
  cursor: pointer;
  transition: background 0.15s;
}

.header__dropdown-item:hover {
  background: #f5f7fa;
}

.header__dropdown-item--danger {
  color: var(--color-red-primary);
}

.header__dropdown-item--danger:hover {
  background: #fdeaea;
}

@media (max-width: 480px) {
  .header {
    padding: 0.65rem 0.75rem;
  }
}
</style>
