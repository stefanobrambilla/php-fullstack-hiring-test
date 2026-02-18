<script setup lang="ts">
import type { Travel } from '~/types/checkout'

const { apiBase } = useApi()
const { money, shortDate } = useFormatters()

useSeoMeta({
  title: 'WeRoad Checkout',
  description: 'Scegli il viaggio, scegli i posti e completa il checkout.'
})

const { data, pending, error, refresh } = await useFetch<Travel[]>(`${apiBase}/travels`, {
  key: 'travels-home',
  default: () => [],
  server: true,
  lazy: false
})

const travels = computed(() => data.value ?? [])

function shortDescription(text: string) {
  if (text.length <= 180) return text
  return `${text.slice(0, 180)}...`
}
</script>

<template>
  <section class="space-y-8">

    <p v-if="pending" class="text-sm text-slate-500">Carico i viaggi...</p>

    <p v-else-if="error" class="text-sm text-red-600">
      Errore caricamento viaggi.
    </p>

    <p v-else-if="travels.length === 0" class="rounded-2xl border border-dashed border-slate-300 bg-white/70 p-6 text-sm text-slate-600">
      Nessun viaggio disponibile. Controlla seed/migrazioni backend.
    </p>

    <div v-else class="grid gap-5 md:grid-cols-2 xl:grid-cols-3">
      <article
        v-for="travel in travels"
        :key="travel.id"
        class="rise-in overflow-hidden rounded-3xl border border-white/70 bg-white/90 shadow-sm"
      >
        <img
          :src="`/${travel.id}.jpg`"
          :alt="travel.name"
          class="h-48 w-full object-cover"
          loading="lazy"
        >

        <div class="space-y-4 p-5">
          <div class="space-y-1">
            <p class="text-xs uppercase tracking-[0.14em] text-slate-500">
              {{ shortDate(travel.starting_date) }} - {{ shortDate(travel.ending_date) }}
            </p>
            <h3 class="display-font text-2xl leading-tight text-slate-900">
              {{ travel.name }}
            </h3>
          </div>

          <p class="text-sm text-slate-600">
            {{ shortDescription(travel.description) }}
          </p>

          <MoodBars :moods="travel.moods" :max-items="5" />

          <div class="flex items-end justify-between gap-3 border-t border-slate-200 pt-4">
            <div>
              <p class="text-xl font-semibold text-slate-900">{{ money(travel.price) }}</p>
              <p class="text-xs uppercase tracking-[0.12em] text-slate-500">
                {{ travel.seats_left }} posti rimasti
              </p>
            </div>

            <NuxtLink
              :to="`/travels/${travel.slug}`"
              class="rounded-full bg-slate-900 px-4 py-2 text-sm font-medium text-white transition hover:bg-slate-700"
            >
              Scopri
            </NuxtLink>
          </div>
        </div>
      </article>
    </div>
  </section>
</template>
