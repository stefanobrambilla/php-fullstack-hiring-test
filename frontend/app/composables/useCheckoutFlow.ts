import type { DraftCart, ReservedCart, Travel } from '~/types/checkout'

export function useCheckoutFlow() {
  const draftCart = useState<DraftCart | null>('draft-cart', () => null)
  const reservedCart = useState<ReservedCart | null>('reserved-cart', () => null)
  const checkoutEmail = useState<string>('checkout-email', () => '')

  const addToDraft = (travel: Travel, seats: number) => {
    draftCart.value = {
      travel_id: travel.id,
      travel_slug: travel.slug,
      travel_name: travel.name,
      travel_price: travel.price,
      seats: sanitizeSeats(seats)
    }
    reservedCart.value = null
  }

  const updateDraftSeats = (seats: number) => {
    if (!draftCart.value) return

    draftCart.value = {
      ...draftCart.value,
      seats: sanitizeSeats(seats)
    }
  }

  const clearDraft = () => {
    draftCart.value = null
  }

  const setReserved = (cart: ReservedCart) => {
    reservedCart.value = cart
  }

  const clearFlow = () => {
    draftCart.value = null
    reservedCart.value = null
    checkoutEmail.value = ''
  }

  return {
    draftCart,
    reservedCart,
    checkoutEmail,
    addToDraft,
    updateDraftSeats,
    clearDraft,
    setReserved,
    clearFlow
  }
}

function sanitizeSeats(value: number) {
  if (!Number.isFinite(value)) return 1
  return Math.min(5, Math.max(1, Math.round(value)))
}
