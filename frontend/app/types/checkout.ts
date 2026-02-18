export type MoodMap = Record<string, number>

export type Travel = {
  id: string
  slug: string
  name: string
  description: string
  starting_date: string
  ending_date: string
  price: number
  moods: MoodMap
  seats_left: number
}

export type CartStatus = 'pending' | 'paid' | 'expired'

export type ReservedCart = {
  id: string
  travel_id: string
  travel_name: string | null
  travel_slug: string | null
  travel_price: number | null
  email: string
  seats: number
  status: CartStatus
  expires_at: string | null
  expires_in_seconds: number
  paid_at: string | null
}

export type DraftCart = {
  travel_id: string
  travel_slug: string
  travel_name: string
  travel_price: number
  seats: number
}
