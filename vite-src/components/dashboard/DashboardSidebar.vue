<template>
  <aside class="sidebar" :class="{ 'sidebar--collapsed': !open }">
    <div class="sidebar__brand">
      <img :src="logoUrl" alt="Caraga Regional Hospital Logo" class="sidebar__logo" />
      <div class="sidebar__brand-text" v-show="open">
        <span class="sidebar__title">CRH Blood Bank</span>
        <span class="sidebar__subtitle">Admin Dashboard</span>
      </div>
    </div>

    <nav class="sidebar__nav">
      <RouterLink
        to="/dashboard"
        class="sidebar__item"
        :class="{ 'sidebar__item--active': isOverviewActive }"
        :title="!open ? 'Overview' : undefined"
      >
        <span class="sidebar__icon" aria-hidden="true">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <rect x="3" y="3" width="7" height="7" rx="1" />
            <rect x="14" y="3" width="7" height="7" rx="1" />
            <rect x="3" y="14" width="7" height="7" rx="1" />
            <rect x="14" y="14" width="7" height="7" rx="1" />
          </svg>
        </span>
        <span class="sidebar__label" v-show="open">Overview</span>
      </RouterLink>

      <template v-for="section in navSections" :key="section.id">
        <!-- Single-link section -->
        <RouterLink
          v-if="section.links.length === 1"
          :to="section.links[0].to"
          class="sidebar__item"
          active-class="sidebar__item--active"
          :title="!open ? section.label : undefined"
        >
          <span class="sidebar__icon" aria-hidden="true" v-html="section.icon" />
          <span class="sidebar__label" v-show="open">{{ section.label }}</span>
        </RouterLink>

        <!-- Expandable section -->
        <div v-else class="sidebar__group">
          <button
            type="button"
            class="sidebar__item sidebar__toggle"
            :class="{ 'sidebar__item--active': isSectionActive(section) }"
            :aria-expanded="isExpanded(section.id)"
            :title="!open ? section.label : undefined"
            @click="toggleSection(section.id)"
          >
            <span class="sidebar__icon" aria-hidden="true" v-html="section.icon" />
            <span class="sidebar__label" v-show="open">{{ section.label }}</span>
            <svg
              v-show="open"
              class="sidebar__chevron"
              :class="{ 'sidebar__chevron--open': isExpanded(section.id) }"
              viewBox="0 0 24 24"
              fill="none"
              stroke="currentColor"
              stroke-width="2"
              aria-hidden="true"
            >
              <path d="M6 9l6 6 6-6" />
            </svg>
          </button>

          <ul v-show="open && isExpanded(section.id)" class="sidebar__sublist">
            <li v-for="link in section.links" :key="link.to">
              <RouterLink
                :to="link.to"
                class="sidebar__subitem"
                active-class="sidebar__subitem--active"
              >
                {{ link.label }}
              </RouterLink>
            </li>
          </ul>
        </div>
      </template>
    </nav>
  </aside>
</template>

<script setup>
import { computed, ref, watch } from 'vue'
import { useRoute } from 'vue-router'
import logoUrl from '../../assets/caraga-regional-hospital20220211.webp'

defineProps({
  open: {
    type: Boolean,
    default: true,
  },
})

const route = useRoute()

const isOverviewActive = computed(() => route.path === '/dashboard')

const navSections = [
  {
    id: 'blood-collection',
    label: 'Blood Collection',
    icon: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 3c-2 4-6 6-6 10a6 6 0 1 0 12 0c0-4-4-6-6-10z"/></svg>`,
    links: [{ to: '/dashboard/blood-collection', label: 'Blood Collection' }],
  },
  {
    id: 'blood-consumption',
    label: 'Blood Consumption',
    icon: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="4" y="4" width="16" height="16" rx="2"/><path d="M9 12h6M12 9v6"/></svg>`,
    links: [{ to: '/dashboard/blood-consumption', label: 'Blood Consumption' }],
  },
  {
    id: 'reports',
    label: 'Reports',
    icon: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 20V4M4 20h16M8 16V10M12 16V7M16 16v-4"/></svg>`,
    links: [{ to: '/dashboard/reports', label: 'Reports' }],
  },
  {
    id: 'staff-accounts',
    label: 'Staff Accounts',
    icon: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg>`,
    links: [{ to: '/dashboard/staff-accounts', label: 'Staff Accounts' }],
  },
]

const expandedSections = ref(new Set())

function getActiveSectionId() {
  const section = navSections.find((s) =>
    s.links.some((link) => link.to === route.path),
  )
  return section?.id ?? null
}

function isExpanded(sectionId) {
  return expandedSections.value.has(sectionId)
}

function isSectionActive(section) {
  return section.links.some((link) => link.to === route.path)
}

function toggleSection(sectionId) {
  const next = new Set(expandedSections.value)
  if (next.has(sectionId)) {
    next.delete(sectionId)
  } else {
    next.add(sectionId)
  }
  expandedSections.value = next
}

function syncExpandedFromRoute() {
  const activeId = getActiveSectionId()
  if (activeId) {
    expandedSections.value = new Set([...expandedSections.value, activeId])
  }
}

syncExpandedFromRoute()

watch(() => route.path, syncExpandedFromRoute)
</script>

<style scoped>
.sidebar {
  position: sticky;
  top: 0;
  height: 100vh;
  align-self: flex-start;
  width: 260px;
  flex-shrink: 0;
  background: var(--color-white);
  border-right: 1px solid #e8eaed;
  overflow-y: auto;
  overflow-x: hidden;
  transition: width 0.25s ease;
  z-index: 90;
  display: flex;
  flex-direction: column;
}

.sidebar--collapsed {
  width: 72px;
}

.sidebar__brand {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0 1.25rem;
  height: 64px;
  border-bottom: 1px solid #e8eaed;
  box-sizing: border-box;
  flex-shrink: 0;
}

.sidebar--collapsed .sidebar__brand {
  padding: 0;
  justify-content: center;
}

.sidebar__logo {
  width: 38px;
  height: 38px;
  object-fit: contain;
  border-radius: 6px;
  flex-shrink: 0;
}

.sidebar__brand-text {
  display: flex;
  flex-direction: column;
  min-width: 0;
  white-space: nowrap;
}

.sidebar__title {
  font-size: 0.95rem;
  font-weight: 700;
  color: #1a1a2e;
  line-height: 1.3;
  overflow: hidden;
  text-overflow: ellipsis;
}

.sidebar__subtitle {
  font-size: 0.72rem;
  color: #8899a8;
  line-height: 1.3;
}

.sidebar__nav {
  display: flex;
  flex-direction: column;
  gap: 0.35rem;
  padding: 1rem 0.75rem;
  flex: 1;
}

.sidebar--collapsed .sidebar__nav {
  padding: 1rem 0.5rem;
  align-items: center;
}

.sidebar__item {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  width: 100%;
  padding: 0.65rem 0.75rem;
  border: none;
  border-radius: 10px;
  background: transparent;
  color: #3d4f5f;
  font-size: 0.9rem;
  font-weight: 500;
  text-decoration: none;
  text-align: left;
  cursor: pointer;
  transition: background 0.2s, color 0.2s;
  box-sizing: border-box;
}

.sidebar--collapsed .sidebar__item {
  justify-content: center;
  padding: 0.5rem;
  width: 48px;
  height: 48px;
}

.sidebar__item:hover {
  background: #f0f4f8;
}

.sidebar__item--active {
  background: #e8f0fe;
  color: #1a3a6b;
  font-weight: 600;
}

.sidebar__toggle {
  font-family: inherit;
}

.sidebar__icon {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 36px;
  height: 36px;
  flex-shrink: 0;
  border-radius: 8px;
  background: #e8f0fe;
  color: #2c5282;
}

.sidebar__icon :deep(svg) {
  width: 18px;
  height: 18px;
}

.sidebar__item--active .sidebar__icon {
  background: #d2e3fc;
  color: #1a3a6b;
}

.sidebar__label {
  flex: 1;
  line-height: 1.3;
  white-space: nowrap;
}

.sidebar__chevron {
  width: 16px;
  height: 16px;
  flex-shrink: 0;
  color: #8899a8;
  transition: transform 0.2s;
}

.sidebar__chevron--open {
  transform: rotate(180deg);
}

.sidebar__group {
  display: flex;
  flex-direction: column;
}

.sidebar__sublist {
  list-style: none;
  margin: 0.15rem 0 0.35rem;
  padding: 0 0 0 3.25rem;
}

.sidebar__subitem {
  display: block;
  padding: 0.5rem 0.75rem;
  border-radius: 8px;
  font-size: 0.85rem;
  color: #5a6a7a;
  text-decoration: none;
  transition: background 0.2s, color 0.2s;
  white-space: nowrap;
}

.sidebar__subitem:hover {
  background: #f5f7fa;
  color: #2c5282;
}

.sidebar__subitem--active {
  background: #e8f0fe;
  color: #1a3a6b;
  font-weight: 600;
}

@media (max-width: 900px) {
  .sidebar {
    position: fixed;
    top: 0;
    left: 0;
    height: 100vh;
    width: 260px;
    z-index: 1000;
    box-shadow: 4px 0 20px rgba(0, 0, 0, 0.15);
    transform: translateX(0);
    transition: transform 0.25s ease;
  }

  .sidebar--collapsed {
    transform: translateX(-100%);
    width: 260px;
  }
}
</style>
