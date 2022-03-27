<template>
  <div v-if="icons.length">
    <i v-for="(icon, index) in icons" :key="index" :class="icon" class="fa-lg fa-fw m-3 is-clickable" :title="icon" @mousedown="$emit('select', icon)" />
  </div>
  <i v-else class="has-text-grey">{{ $t('labels.noResults') }}</i>
</template>

<script>
import icons from '@/assets/fa-icons.json'

export default {
  name: 'IconPicker',
  props: {
    name: {
      type: String,
      required: true,
    },
  },
  emits: [
    'select',
  ],
  data () {
    return {
      icons: [],
    }
  },
  computed: {
    query () {
      return this.name.replace(/fa[srb]? fa-/, '') 
    },
  },
  watch: {
    query: {
      immediate: true,
      handler (newVal) {
        this.searchIcons(newVal)
      },
    },
  },
  methods: {
    searchIcons (query) {
      this.icons = []
      if (query === '') {
        return
      }
      icons
        .filter((item) => item.keywords.some((keyword) => keyword.includes(query)))
        .forEach(icon => {
          icon.styles.forEach((style) => {
            switch (style) {
            case 'solid':
              this.icons.push(`fas fa-${icon.name}`)
              break
            case 'regular':
              this.icons.push(`far fa-${icon.name}`)
              break
            case 'brands':
              this.icons.push(`fab fa-${icon.name}`)
              break
            }
          })
        })
    },
  },
}
</script>
