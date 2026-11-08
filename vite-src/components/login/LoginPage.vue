<template>
  <div class="login-page">
    <div class="login-page__container">
      <!-- Left side spacer reveals hospital branding artwork on the background -->
      <div class="login-page__branding-spacer" />

      <!-- Right side holds the interactive login card -->
      <div class="login-page__form-container">
        <LoginForm @submit="handleLogin" />
      </div>
    </div>
  </div>
</template>

<script setup>
import { useRouter } from 'vue-router'
import LoginForm from './LoginForm.vue'

const router = useRouter()

function handleLogin(credentials) {
  console.log('Login attempt:', credentials)
  router.push('/dashboard')
}
</script>

<style scoped>
.login-page {
  min-height: 100vh;
  width: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  background-image: url('../../assets/ChatGPT Image Aug 17, 2026, 04_55_28 PM.png');
  background-size: cover;
  background-position: center center;
  background-repeat: no-repeat;
  position: relative;
  overflow-x: hidden;
}

.login-page__container {
  display: flex;
  width: 100%;
  min-height: 100vh;
  align-items: center;
}

.login-page__branding-spacer {
  flex: 1.15;
}

.login-page__form-container {
  flex: 0.95;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 2rem 4rem 2rem 1rem;
}

@media (max-width: 1100px) {
  .login-page__form-container {
    padding: 2rem 2rem 2rem 1rem;
  }
}

@media (max-width: 900px) {
  .login-page {
    background-position: 70% center;
  }
  .login-page__container {
    flex-direction: column;
    justify-content: center;
  }
  .login-page__branding-spacer {
    display: none;
  }
  .login-page__form-container {
    flex: 1;
    padding: 2rem 1.5rem;
    width: 100%;
    box-sizing: border-box;
  }
}
</style>
