<script setup lang="ts">
import type { Travel } from '~/types/checkout'

const route = useRoute()
const { apiBase } = useApi()
const { money, shortDate } = useFormatters()
const { addToDraft } = useCheckoutFlow()

const slug = computed(() => String(route.params.slug ?? ''))

const { data: travel, pending, error, refresh } = await useFetch<Travel>(
  () => `${apiBase}/travels/${slug.value}`,
  {
    key: () => `travel-${slug.value}`
  }
)

const seats = ref(1)

const soldOut = computed(() => (travel.value?.seats_left ?? 0) < 1)

const maxSelectableSeats = computed(() => {
  const seatsLeft = travel.value?.seats_left ?? 0
  if (seatsLeft < 1) return 1
  return Math.min(5, seatsLeft)
})

const total = computed(() => {
  if (!travel.value) return 0
  return travel.value.price * seats.value
})

watch(() => travel.value?.seats_left, (seatsLeft) => {
  if (!seatsLeft || seatsLeft < 1) {
    seats.value = 1
    return
  }

  if (seats.value > seatsLeft) {
    seats.value = seatsLeft
  }
}, { immediate: true })

async function addToCart() {
  if (!travel.value || soldOut.value) return

  addToDraft(travel.value, seats.value)
  await navigateTo('/cart')
}

useSeoMeta(() => ({
  title: travel.value ? `${travel.value.name} | WeRoad` : 'Travel',
  description: travel.value?.description.slice(0, 140) ?? 'Dettaglio travel'
}))
</script>

<template>
  <section class="space-y-6">
    <NuxtLink to="/" class="inline-flex items-center gap-1 text-sm text-slate-600 hover:text-slate-900">
      <span aria-hidden="true">←</span>
      Torna alla lista
    </NuxtLink>

    <p v-if="pending" class="text-sm text-slate-500">Carico viaggio...</p>

    <div v-else-if="error" class="rounded-2xl border border-red-200 bg-red-50 p-5 text-sm text-red-700">
      Errore caricamento viaggio.
      <button class="ml-2 underline" @click="refresh">Riprova</button>
    </div>

    <div v-else-if="travel" class="grid gap-6 lg:grid-cols-[1.25fr_0.95fr]">
      <article class="rise-in rounded-3xl border border-white/70 bg-white/90 p-6 shadow-sm md:p-8">
        <img
          :src="`/${travel.id}.jpg`"
          :alt="travel.name"
          class="mb-6 h-64 w-full rounded-2xl object-cover md:h-80"
        >
        <p class="text-xs uppercase tracking-[0.2em] text-slate-500">
          {{ shortDate(travel.starting_date) }} - {{ shortDate(travel.ending_date) }}
        </p>
        <h1 class="display-font mt-2 text-4xl leading-tight text-slate-900">
          {{ travel.name }}
        </h1>

        <p class="mt-5 whitespace-pre-line text-slate-700">
          {{ travel.description }}
        </p>

        <div class="mt-7 rounded-2xl border border-slate-200 bg-slate-50 p-4">
          <p class="mb-3 text-xs uppercase tracking-[0.15em] text-slate-500">Mood del viaggio</p>
          <MoodBars :moods="travel.moods" :max-items="5" />
        </div>
      </article>

      <aside class="rise-in rounded-3xl border border-white/70 bg-white/90 p-6 shadow-sm md:p-8">
        <p class="text-xs uppercase tracking-[0.2em] text-slate-500">Prenotazione</p>

        <p class="mt-3 text-3xl font-semibold text-slate-900">
          {{ money(travel.price) }}
        </p>
        <p class="mt-1 text-sm text-slate-500">Prezzo per persona</p>

        <div class="mt-6 rounded-xl border border-slate-200 bg-slate-50 p-4">
          <p class="text-xs uppercase tracking-[0.14em] text-slate-500">Disponibilita</p>
          <p class="mt-1 text-lg font-semibold text-slate-900">{{ travel.seats_left }} / 5 posti</p>
        </div>

        <label class="mt-6 block text-sm text-slate-700">
          Posti da aggiungere
          <input
            v-model.number="seats"
            type="number"
            min="1"
            :max="maxSelectableSeats"
            :disabled="soldOut"
            class="mt-1 w-full rounded-xl border border-slate-300 bg-white px-3 py-2 outline-none focus:border-slate-900 disabled:cursor-not-allowed disabled:bg-slate-100"
          >
        </label>

        <p class="mt-4 text-sm text-slate-600">
          Totale: <strong>{{ money(total) }}</strong>
        </p>

        <button
          type="button"
          :disabled="soldOut"
          class="mt-5 w-full rounded-xl bg-slate-900 px-4 py-3 text-sm font-semibold text-white transition hover:bg-slate-700 disabled:cursor-not-allowed disabled:bg-slate-400"
          @click="addToCart"
        >
          {{ soldOut ? 'Sold out' : 'Add to cart' }}
        </button>
      </aside>
    </div>
  </section>
</template>
