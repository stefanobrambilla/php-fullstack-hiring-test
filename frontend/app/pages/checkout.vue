<script setup lang="ts">
import type { ReservedCart } from '~/types/checkout'

const { apiBase } = useApi()
const { money } = useFormatters()
const {
  draftCart,
  reservedCart,
  checkoutEmail,
  setReserved,
  clearDraft,
  clearFlow
} = useCheckoutFlow()

if (!draftCart.value && !reservedCart.value) {
  await navigateTo('/cart')
}

const reserving = ref(false)
const paying = ref(false)
const errorMessage = ref('')
const successMessage = ref('')
const now = ref(Date.now())
let timer: ReturnType<typeof setInterval> | null = null

const currentTravelName = computed(() => {
  return reservedCart.value?.travel_name ?? draftCart.value?.travel_name ?? '-'
})

const unitPrice = computed(() => {
  return reservedCart.value?.travel_price ?? draftCart.value?.travel_price ?? 0
})

const seats = computed(() => {
  return reservedCart.value?.seats ?? draftCart.value?.seats ?? 0
})

const total = computed(() => unitPrice.value * seats.value)

const remainingSeconds = computed(() => {
  if (!reservedCart.value?.expires_at || reservedCart.value.status !== 'pending') return 0
  const delta = Math.floor((new Date(reservedCart.value.expires_at).getTime() - now.value) / 1000)
  return Math.max(0, delta)
})

const canReserve = computed(() => {
  return !!draftCart.value && !reservedCart.value && checkoutEmail.value.includes('@')
})

const canPay = computed(() => {
  return !!reservedCart.value && reservedCart.value.status === 'pending' && remainingSeconds.value > 0
})

watch(() => reservedCart.value?.email, (value) => {
  if (value && !checkoutEmail.value) {
    checkoutEmail.value = value
  }
}, { immediate: true })

watch(remainingSeconds, async (value, oldValue) => {
  if (oldValue > 0 && value === 0 && reservedCart.value?.status === 'pending') {
    await refreshCart()
  }
})

onMounted(() => {
  timer = setInterval(() => {
    now.value = Date.now()
  }, 1000)
})

onBeforeUnmount(() => {
  if (timer) clearInterval(timer)
})

async function reserveSeats() {
  if (!draftCart.value) return

  reserving.value = true
  errorMessage.value = ''
  successMessage.value = ''

  try {
    const cart = await $fetch<ReservedCart>(`${apiBase}/carts`, {
      method: 'POST',
      body: {
        travel_id: draftCart.value.travel_id,
        email: checkoutEmail.value,
        seats: draftCart.value.seats
      }
    })

    setReserved(cart)
    successMessage.value = 'Posti bloccati per 15 minuti.'
  } catch (error: any) {
    errorMessage.value = error?.data?.message || 'Prenotazione non riuscita'
  } finally {
    reserving.value = false
  }
}

async function refreshCart() {
  if (!reservedCart.value) return

  try {
    const cart = await $fetch<ReservedCart>(`${apiBase}/carts/${reservedCart.value.id}`)
    setReserved(cart)
  } catch {
    // ignore
  }
}

async function pay() {
  if (!reservedCart.value) return

  paying.value = true
  errorMessage.value = ''
  successMessage.value = ''

  try {
    const result = await $fetch<{ message: string; cart: ReservedCart }>(
      `${apiBase}/carts/${reservedCart.value.id}/pay`,
      { method: 'POST' }
    )

    setReserved(result.cart)
    successMessage.value = result.message

    if (result.cart.status === 'paid') {
      clearDraft()
    }
  } catch (error: any) {
    errorMessage.value = error?.data?.message || 'Pagamento non riuscito'
    await refreshCart()
  } finally {
    paying.value = false
  }
}

async function resetFlow() {
  clearFlow()
  await navigateTo('/cart')
}
</script>

<template>
  <section class="space-y-6">
    <h1 class="display-font text-4xl text-slate-900">Checkout</h1>

    <div class="rise-in rounded-3xl border border-white/70 bg-white/90 p-6 shadow-sm md:p-8">
      <p class="text-xs uppercase tracking-[0.15em] text-slate-500">Riepilogo</p>
      <h2 class="display-font mt-2 text-3xl text-slate-900">{{ currentTravelName }}</h2>

      <div class="mt-4 grid gap-3 rounded-2xl border border-slate-200 bg-slate-50 p-4 md:grid-cols-3">
        <p class="text-sm text-slate-600">Posti: <strong class="text-slate-900">{{ seats }}</strong></p>
        <p class="text-sm text-slate-600">Prezzo: <strong class="text-slate-900">{{ money(unitPrice) }}</strong></p>
        <p class="text-sm text-slate-600">Totale: <strong class="text-slate-900">{{ money(total) }}</strong></p>
      </div>

      <div v-if="!reservedCart" class="mt-6 space-y-4">
        <label class="block text-sm text-slate-700">
          Email
          <input
            v-model="checkoutEmail"
            type="email"
            required
            placeholder="name@email.com"
            class="mt-1 w-full rounded-xl border border-slate-300 bg-white px-3 py-2 outline-none focus:border-slate-900"
          >
        </label>

        <button
          type="button"
          :disabled="!canReserve || reserving"
          class="rounded-xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white transition hover:bg-slate-700 disabled:cursor-not-allowed disabled:bg-slate-400"
          @click="reserveSeats"
        >
          {{ reserving ? 'Blocco posti...' : 'Blocca posti (15 min)' }}
        </button>
      </div>

      <div v-else class="mt-6 space-y-3 rounded-2xl border border-slate-200 bg-slate-50 p-4">
        <p class="text-sm text-slate-700">
          Stato:
          <strong class="text-slate-900">{{ reservedCart.status }}</strong>
        </p>
        <p v-if="reservedCart.status === 'pending'" class="text-sm text-slate-700">
          Scade tra: <strong class="text-slate-900">{{ remainingSeconds }}s</strong>
        </p>

        <div class="flex flex-wrap gap-3">
          <button
            type="button"
            :disabled="!canPay || paying"
            class="rounded-xl bg-orange-500 px-5 py-3 text-sm font-semibold text-white transition hover:bg-orange-400 disabled:cursor-not-allowed disabled:bg-orange-300"
            @click="pay"
          >
            {{ paying ? 'Pagamento...' : 'Paga ora (fake)' }}
          </button>

          <button
            type="button"
            class="rounded-xl border border-slate-300 px-5 py-3 text-sm text-slate-700 transition hover:bg-slate-100"
            @click="refreshCart"
          >
            Aggiorna stato
          </button>

          <button
            type="button"
            class="rounded-xl border border-slate-300 px-5 py-3 text-sm text-slate-700 transition hover:bg-slate-100"
            @click="resetFlow"
          >
            Reset flow
          </button>
        </div>
      </div>

      <p v-if="errorMessage" class="mt-4 text-sm text-red-600">{{ errorMessage }}</p>
      <p v-if="successMessage" class="mt-4 text-sm text-emerald-700">{{ successMessage }}</p>
    </div>
  </section>
</template>
