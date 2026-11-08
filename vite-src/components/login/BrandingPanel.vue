<template>
  <section class="branding">
    <div
      class="branding__bg"
      :style="{
        backgroundImage: `linear-gradient(rgba(255, 255, 255, 0.84), rgba(255, 255, 255, 0.90)), url('${loginBgUrl}')`
      }"
    />
    <div class="branding__dots branding__dots--top" aria-hidden="true" />
    <div class="branding__dots branding__dots--bottom" aria-hidden="true" />

    <div class="branding__content">
      <p class="welcome">Welcome to</p>
      <h2 class="hospital">CARAGA REGIONAL HOSPITAL</h2>
      <h1 class="title">
        BL<span class="blood-drop" aria-hidden="true">
          <svg viewBox="0 0 40 52" fill="none">
            <path
              d="M20 2C20 2 4 24 4 36C4 46.5 11.2 52 20 52C28.8 52 36 46.5 36 36C36 24 20 2 20 2Z"
              fill="#990000"
            />
            <rect x="17" y="22" width="6" height="14" rx="1" fill="white" />
            <rect x="13" y="26" width="14" height="6" rx="1" fill="white" />
          </svg>
        </span>OD BANK
      </h1>

      <div class="divider">
        <span class="heart" aria-hidden="true">♥</span>
      </div>

      <p class="mission">
        We are committed to provide safe, sufficient, and quality blood to save
        lives and improve health in our community.
      </p>

      <p class="system-name">
        Blood Collection and Consumption Reporting Management System
      </p>
    </div>

    <div class="ekg-graphic" aria-hidden="true">
      <svg viewBox="0 0 400 80" class="ekg-svg">
        <line x1="0" y1="40" x2="60" y2="40" stroke="#990000" stroke-width="2" />
        <polyline
          points="60,40 75,40 85,10 95,70 105,40 120,40"
          fill="none"
          stroke="#990000"
          stroke-width="2"
        />
        <circle cx="120" cy="40" r="18" fill="#cc0000" opacity="0.9" />
        <circle cx="120" cy="40" r="12" fill="#990000" />
        <polyline
          points="138,40 155,40 165,15 175,65 185,40 200,40"
          fill="none"
          stroke="#990000"
          stroke-width="2"
        />
        <line x1="200" y1="40" x2="400" y2="40" stroke="#990000" stroke-width="2" />
      </svg>
    </div>
  </section>
</template>

<script setup>
import loginBgUrl from '../../assets/ChatGPT Image Aug 17, 2026, 04_55_28 PM.png'
</script>

<style scoped>
.branding {
  flex: 1.2;
  position: relative;
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  padding: 3rem 2rem 5rem;
  background: #fff;
  overflow: hidden;
  z-index: 1;
}

.branding__bg {
  position: absolute;
  inset: 0;
  background-size: cover;
  background-position: center;
  background-repeat: no-repeat;
  opacity: 1;
}

.branding__dots {
  position: absolute;
  width: 60px;
  height: 60px;
  background-image: radial-gradient(circle, #ccc 1.5px, transparent 1.5px);
  background-size: 12px 12px;
  opacity: 0.5;
}

.branding__dots--top {
  top: 2rem;
  left: 2rem;
}

.branding__dots--bottom {
  bottom: 2rem;
  left: 2rem;
}

.branding__content {
  position: relative;
  text-align: center;
  max-width: 540px;
  z-index: 1;
}

.welcome {
  font-size: 1rem;
  color: #333;
  margin-bottom: 0.25rem;
}

.hospital {
  color: #990000;
  font-size: clamp(0.85rem, 2vw, 1.1rem);
  letter-spacing: 0.12em;
  font-weight: 700;
  margin-bottom: 0.25rem;
}

.title {
  color: #990000;
  font-size: clamp(2.2rem, 6vw, 3.8rem);
  font-weight: 900;
  letter-spacing: 0.04em;
  line-height: 1.1;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0;
}

.blood-drop {
  display: inline-flex;
  width: 0.75em;
  height: 1em;
  margin: 0 -0.02em;
  vertical-align: middle;
}

.blood-drop svg {
  width: 100%;
  height: 100%;
}

.divider {
  position: relative;
  width: 80%;
  max-width: 320px;
  height: 2px;
  background: #990000;
  margin: 1.25rem auto;
}

.heart {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  background: #fff;
  padding: 0 0.5rem;
  color: #990000;
  font-size: 1rem;
}

.mission {
  color: #333;
  font-size: 0.92rem;
  line-height: 1.65;
  max-width: 420px;
  margin: 0 auto;
}

.system-name {
  margin-top: 1.25rem;
  font-size: 0.8rem;
  color: #888;
  font-style: italic;
}

.ekg-graphic {
  position: absolute;
  bottom: 2.5rem;
  left: 50%;
  transform: translateX(-50%);
  width: min(400px, 80%);
  z-index: 1;
}

.ekg-svg {
  width: 100%;
  height: auto;
}

@media (max-width: 900px) {
  .branding {
    flex: none;
    min-height: 50vh;
    padding: 2.5rem 1.5rem 3rem;
  }

  .ekg-graphic {
    display: none;
  }
}
</style>
