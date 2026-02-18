<script setup lang="ts">
import type { MoodMap } from '~/types/checkout'

const props = withDefaults(defineProps<{
  moods: MoodMap
  maxItems?: number
}>(), {
  maxItems: 5
})

const moodLabels: Record<string, string> = {
  nature: 'Nature',
  relax: 'Relax',
  history: 'History',
  culture: 'Culture',
  party: 'Party'
}

const moodColors: Record<string, string> = {
  nature: 'from-emerald-500 to-lime-400',
  relax: 'from-sky-500 to-cyan-300',
  history: 'from-amber-500 to-yellow-400',
  culture: 'from-orange-500 to-rose-400',
  party: 'from-fuchsia-500 to-pink-400'
}

const rows = computed(() => {
  const entries = Object.entries(props.moods ?? {})
    .sort(([, valueA], [, valueB]) => valueB - valueA)
    .slice(0, props.maxItems)

  return entries.map(([key, value]) => {
    const normalizedValue = Math.max(0, Math.min(100, Number(value) || 0))
    return {
      key,
      label: moodLabels[key] ?? key,
      value: normalizedValue,
      gradientClass: moodColors[key] ?? 'from-slate-600 to-slate-300'
    }
  })
})
</script>

<template>
  <div class="flex w-full items-end justify-center gap-3 px-2">
    <div
      v-for="row in rows"
      :key="row.key"
      class="flex w-14 shrink-0 flex-col items-center gap-2"
    >
      <div class="text-[11px] font-semibold text-slate-600">
        {{ row.value }}%
      </div>

      <div class="flex w-full items-end justify-center gap-1">
        <span class="text-[10px] font-medium uppercase tracking-[0.08em] text-slate-600 [writing-mode:vertical-rl] rotate-180">
          {{ row.label }}
        </span>

        <div class="relative flex h-32 w-4 items-end overflow-hidden rounded-full bg-slate-200/70">
          <div
            class="w-full rounded-full bg-gradient-to-t transition-all duration-700"
            :class="row.gradientClass"
            :style="{ height: `${Math.max(8, row.value)}%` }"
          />
        </div>
      </div>
    </div>
  </div>
</template>
