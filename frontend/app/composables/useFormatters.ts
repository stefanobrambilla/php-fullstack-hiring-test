export function useFormatters() {
  const money = (cents: number) => {
    return new Intl.NumberFormat('it-IT', {
      style: 'currency',
      currency: 'EUR'
    }).format(cents / 100)
  }

  const shortDate = (value: string) => {
    const date = new Date(value)
    if (Number.isNaN(date.getTime())) {
      return value
    }

    return new Intl.DateTimeFormat('it-IT', {
      day: '2-digit',
      month: 'short',
      year: 'numeric'
    }).format(date)
  }

  return {
    money,
    shortDate
  }
}
