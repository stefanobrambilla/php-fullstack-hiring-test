<script setup lang="ts">
const { money } = useFormatters()
const { draftCart, reservedCart, updateDraftSeats, clearDraft } = useCheckoutFlow()

const seats = ref(draftCart.value?.seats ?? 1)

watch(() => draftCart.value?.seats, (value) => {
  if (typeof value === 'number') seats.value = value
}, { immediate: true })

watch(seats, (value) => {
  if (!draftCart.value) return

  const safeValue = Math.min(5, Math.max(1, Math.round(Number(value) || 1)))
  if (safeValue !== value) {
    seats.value = safeValue
    return
  }

  updateDraftSeats(safeValue)
})

const subtotal = computed(() => {
  if (!draftCart.value) return 0
  return draftCart.value.travel_price * draftCart.value.seats
})

const reservedSubtotal = computed(() => {
  if (!reservedCart.value) return 0
  return (reservedCart.value.travel_price ?? 0) * reservedCart.value.seats
})
</script>

<template>
  <section class="space-y-6">
    <h1 class="display-font text-4xl text-slate-900">Cart</h1>

    <div v-if="draftCart" class="rise-in rounded-3xl border border-white/70 bg-white/90 p-6 shadow-sm md:p-8">
      <div class="grid gap-6 md:grid-cols-[1fr_auto] md:items-end">
        <div class="space-y-2">
          <p class="text-xs uppercase tracking-[0.15em] text-slate-500">Travel selezionato</p>
          <h2 class="display-font text-3xl text-slate-900">{{ draftCart.travel_name }}</h2>
          <p class="text-sm text-slate-600">Prezzo base: {{ money(draftCart.travel_price) }}</p>
        </div>

        <NuxtLink
          :to="`/travels/${draftCart.travel_slug}`"
          class="rounded-full border border-slate-300 px-4 py-2 text-sm text-slate-700 transition hover:bg-slate-100"
        >
          Cambia travel
        </NuxtLink>
      </div>

      <div class="mt-6 grid gap-4 rounded-2xl border border-slate-200 bg-slate-50 p-4 md:grid-cols-2">
        <label class="text-sm text-slate-700">
          Posti
          <input
            v-model.number="seats"
            type="number"
            min="1"
            max="5"
            class="mt-1 w-full rounded-xl border border-slate-300 bg-white px-3 py-2 outline-none focus:border-slate-900"
          >
        </label>

        <div class="flex flex-col justify-end text-sm text-slate-600">
          <p>Subtotale</p>
          <p class="text-2xl font-semibold text-slate-900">{{ money(subtotal) }}</p>
        </div>
      </div>

      <div class="mt-6 flex flex-wrap items-center gap-3">
        <NuxtLink
          to="/checkout"
          class="rounded-xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white transition hover:bg-slate-700"
        >
          Vai al checkout
        </NuxtLink>

        <button
          type="button"
          class="rounded-xl border border-slate-300 px-5 py-3 text-sm text-slate-700 transition hover:bg-slate-100"
          @click="clearDraft"
        >
          Svuota cart
        </button>
      </div>
    </div>

    <div v-else-if="reservedCart" class="rise-in rounded-3xl border border-white/70 bg-white/90 p-6 shadow-sm md:p-8">
      <p class="text-xs uppercase tracking-[0.15em] text-slate-500">Cart gia creato</p>
      <h2 class="display-font mt-2 text-3xl text-slate-900">{{ reservedCart.travel_name }}</h2>
      <p class="mt-1 text-sm text-slate-600">Posti: {{ reservedCart.seats }}</p>
      <p class="mt-1 text-sm text-slate-600">Subtotale: {{ money(reservedSubtotal) }}</p>

      <NuxtLink
        to="/checkout"
        class="mt-5 inline-flex rounded-xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white transition hover:bg-slate-700"
      >
        Apri checkout
      </NuxtLink>
    </div>

    <div v-else class="rise-in rounded-3xl border border-dashed border-slate-300 bg-white/70 p-8 text-center">
      <p class="text-slate-600">Il cart e vuoto.</p>
      <NuxtLink to="/" class="mt-4 inline-flex rounded-xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white">
        Vai ai travels
      </NuxtLink>
    </div>
  </section>
</template>
