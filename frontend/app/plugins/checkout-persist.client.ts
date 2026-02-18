import { watch } from 'vue'
import type { DraftCart, ReservedCart } from '~/types/checkout'

type PersistedCheckout = {
  draftCart: DraftCart | null
  reservedCart: ReservedCart | null
  checkoutEmail: string
}

const STORAGE_KEY = 'weroad-checkout-flow-v1'

export default defineNuxtPlugin(() => {
  const draftCart = useState<DraftCart | null>('draft-cart', () => null)
  const reservedCart = useState<ReservedCart | null>('reserved-cart', () => null)
  const checkoutEmail = useState<string>('checkout-email', () => '')

  const rawState = localStorage.getItem(STORAGE_KEY)

  if (rawState) {
    try {
      const parsed = JSON.parse(rawState) as PersistedCheckout
      draftCart.value = parsed.draftCart ?? null
      reservedCart.value = parsed.reservedCart ?? null
      checkoutEmail.value = parsed.checkoutEmail ?? ''
    } catch {
      localStorage.removeItem(STORAGE_KEY)
    }
  }

  watch([draftCart, reservedCart, checkoutEmail], () => {
    const payload: PersistedCheckout = {
      draftCart: draftCart.value,
      reservedCart: reservedCart.value,
      checkoutEmail: checkoutEmail.value
    }

    localStorage.setItem(STORAGE_KEY, JSON.stringify(payload))
  }, { deep: true })
})
