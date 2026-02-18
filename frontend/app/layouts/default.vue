<script setup lang="ts">
const route = useRoute()
const { draftCart, reservedCart } = useCheckoutFlow()

const cartBadge = computed(() => {
  if (draftCart.value) return draftCart.value.seats
  if (reservedCart.value?.status === 'pending') return reservedCart.value.seats
  return 0
})

const links = [
  { label: 'Travels', to: '/' },
  { label: 'Cart', to: '/cart' }
]

const isActive = (to: string) => {
  if (to === '/') return route.path === '/'
  return route.path.startsWith(to)
}
</script>

<template>
  <div class="relative min-h-screen overflow-x-clip bg-[var(--page-bg)] text-slate-900">
    <div class="pointer-events-none fixed inset-0 -z-10">
      <div class="absolute left-1/2 top-[-220px] h-[520px] w-[940px] -translate-x-1/2 rounded-full 
    " />
      <div class="absolute -left-20 bottom-10 h-72 w-72 rounded-full bg-teal-200/45 blur-3xl" />
      <div class="absolute -right-20 top-40 h-72 w-72 rounded-full bg-orange-200/45 blur-3xl" />
    </div>

    <header class="sticky top-0 z-20 border-b border-white/60 bg-white/70 backdrop-blur-xl">
      <div class="mx-auto flex max-w-6xl items-center justify-between px-4 py-4 md:px-8">
        <NuxtLink to="/" class="flex items-baseline gap-2">
          <img
            src="/weroad-logo.jpeg"
            alt="WeRoad"
            class="h-9 rounded-full object-cover ring-2 ring-white md:h-10"
          >
        </NuxtLink>

        <nav class="flex items-center gap-2 text-sm">
          <NuxtLink
            v-for="link in links"
            :key="link.to"
            :to="link.to"
            class="rounded-full px-3 py-1.5 transition"
            :class="isActive(link.to) ? 'bg-slate-900 text-white' : 'text-slate-700 hover:bg-slate-200/70'"
          >
            {{ link.label }}
            <span
              v-if="link.to === '/cart' && cartBadge > 0"
              class="ml-1 rounded-full bg-orange-400 px-1.5 py-0.5 text-[10px] font-semibold text-white"
            >
              {{ cartBadge }}
            </span>
          </NuxtLink>
        </nav>
      </div>
    </header>

    <main class="mx-auto w-full max-w-6xl px-4 py-8 md:px-8 md:py-10">
      <slot />
    </main>
  </div>
</template>
