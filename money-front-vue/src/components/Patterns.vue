<template>
  <section class="hero">

    <div class="hero-head">
      <breadcrumb
        :items="[
          {link: '/', icon: 'fas fa-home', text: $t('labels.home')},
          {link: '/patterns', icon: 'fas fa-magic', text: $tc('objects.pattern', 2), isActive: true},
        ]"
      />
    </div>

    <div class="hero-body">
      <div class="container box">
        <h1 class="title container">{{ $tc('objects.pattern', 2) }}</h1>
        <p class="subtitle has-text-grey">{{ $t('labels.patternsLabel') }}</p>

        <div class="field">
          <div class="control has-icons-left">
            <input v-model="search" class="input" type="text" :placeholder="$t('actions.findAPattern')">
            <span class="icon is-small is-left"><i class="fa fa-search" /></span>
          </div>
        </div>

        <o-table :data="displayedPatterns" :striped="true" :hoverable="true" root-class="table-container" td-class="is-clickable" default-sort-direction="desc" @select="edit">
          <o-table-column v-slot="props" field="label" :label="$t('fieldnames.label')" sortable>
            {{ props.row.label }}
          </o-table-column>
          <o-table-column v-slot="props" field="share" :label="$t('fieldnames.share')" sortable>
            <span v-if="props.row.share !== 100" class="has-text-weight-light has-text-grey">{{ props.row.share }}%</span>
          </o-table-column>
          <o-table-column v-slot="props" field="category" :label="$tc('objects.category', 1)" sortable>
            <span v-if="props.row.category && categoriesAndSubcategoriesLookup[props.row.category]">{{ categoriesAndSubcategoriesLookup[props.row.category].label }}</span>
          </o-table-column>
          <o-table-column v-slot="props" field="subcategory" :label="$tc('objects.subcategory', 1)" sortable>
            <span v-if="props.row.subcategory && categoriesAndSubcategoriesLookup[props.row.subcategory]">{{ categoriesAndSubcategoriesLookup[props.row.subcategory].label }}</span>
          </o-table-column>
          <o-table-column v-slot="props" field="isRecurring" :label="$t('fieldnames.isRecurring')" position="centered" sortable>
            <i class="fa fa-fw" :class="[props.row.isRecurring ? 'fa-toggle-on' : 'fa-toggle-off']" />
          </o-table-column>
          <template #empty>
            <section class="section">
              <div class="content has-text-grey has-text-centered">
                <p>{{ $t('labels.nothingToDisplay') }}</p>
              </div>
            </section>
          </template>
        </o-table>

        <div class="field is-grouped">
          <p class="control">
            <button class="button is-primary" role="button" :disabled="!isOnline" @click="create"><span class="icon"><i class="fas fa-plus-square" /></span><span>{{ $t('actions.addPattern') }}</span></button>
          </p>
          <p class="control">
            <button class="button is-primary" role="button" :disabled="!isOnline" @click="suggest"><span class="icon"><i class="fas fa-cogs" /></span><span>{{ $t('actions.suggestPatterns') }}</span></button>
          </p>
        </div>

        <div v-if="error" class="message is-danger block">
          <div class="message-body">
            {{ error }}
          </div>
        </div>

        <o-table :data="suggestedPatterns" :striped="true" :hoverable="true" root-class="table-container" td-class="is-clickable" default-sort="count" default-sort-direction="desc" @select="createSuggested">
          <o-table-column v-slot="props" field="label" :label="$t('fieldnames.label')" sortable>
            {{ props.row.label }}
          </o-table-column>
          <o-table-column v-slot="props" field="category" :label="$tc('objects.category', 1)" sortable>
            <span v-if="props.row.category && categoriesAndSubcategoriesLookup[props.row.category]">{{ categoriesAndSubcategoriesLookup[props.row.category].label }}</span>
          </o-table-column>
          <o-table-column v-slot="props" field="subcategory" :label="$tc('objects.subcategory', 1)" sortable>
            <span v-if="props.row.subcategory && categoriesAndSubcategoriesLookup[props.row.subcategory]">{{ categoriesAndSubcategoriesLookup[props.row.subcategory].label }}</span>
          </o-table-column>
          <o-table-column v-slot="props" field="isRecurring" :label="$tc('fieldnames.isRecurring', 1)" position="centered" sortable>
            <i class="fa fa-fw" :class="[props.row.isRecurring ? 'fa-toggle-on' : 'fa-toggle-off']" />
          </o-table-column>
          <o-table-column v-slot="props" field="count" :label="$tc('objects.occurence', 2)" position="right" sortable>
            {{ props.row.count }}
          </o-table-column>
          <template #empty>
            <section class="section">
              <div class="content has-text-grey has-text-centered">
                <p>{{ $t('labels.nothingToDisplay') }}</p>
              </div>
            </section>
          </template>
        </o-table>

        <o-loading :active="isLoading" :full-page="false" />
      </div>
    </div>

    <o-modal v-model:active="modalPattern.isActive" has-modal-card scroll="keep">
      <transaction-pattern :pattern="modalPattern.pattern" @close="closePatternModal" @pattern-updated="update" @pattern-created="add" @pattern-deleted="remove" />
    </o-modal>

  </section>
</template>

<script>
import Breadcrumb from '@/components/Breadcrumb.vue'
import TransactionPattern from '@/components/Pattern.vue'
import { useStore } from '@/store'
import { mapState } from 'pinia'

export default {
  name: 'Patterns',
  components: {
    TransactionPattern,
    Breadcrumb,
  },
  data () {
    return {
      patterns: [],
      suggestedPatterns: [],
      error: '',
      isLoading: false,
      search: '',
      // modal
      modalPattern: {
        isActive: false,
        pattern: {},
      },
    }
  },
  computed: {
    isOnline () {
      return this.$store.isOnline
    },
    displayedPatterns () {
      if (!this.search) {
        return this.patterns
      }
      const search = this.search.toLowerCase()
      return this.patterns.filter((pattern) => pattern.label.toLowerCase().indexOf(search) > -1)
    },
    ...mapState(useStore, ['categories', 'categoriesAndSubcategoriesLookup']),
  },
  mounted () {
    this.get()
  },
  methods: {
    async get () {
      this.isLoading = true
      try {
        const response = await this.$http.get('patterns')
        this.patterns = response.data
      } catch (error) {
        this.error = error.message
      }
      this.isLoading = false
    },
    create () {
      const pattern = { label: '', category: '', subcategory: '', isRecurring: false, share: 100, shares: [] }
      this.modalPattern.pattern = pattern
      this.modalPattern.isActive = true
    },
    edit (pattern) {
      this.modalPattern.pattern = pattern
      this.modalPattern.isActive = true
    },
    add (pattern) {
      this.patterns.push(pattern)
    },
    update (pattern) {
      const index = this.patterns.findIndex(_pattern => _pattern.id === pattern.id)
      if (index !== -1) {
        this.patterns[index] = pattern
      }
    },
    remove (patternId) {
      const index = this.patterns.findIndex((_pattern) => _pattern.id === patternId)
      this.patterns.splice(index, 1)
    },
    async suggest () {
      this.isLoading = true
      this.error = ''
      try {
        const response = await this.$http.get('transactions/patterns')
        this.suggestedPatterns = response.data
      } catch (error) {
        this.error = error.message
      }
      this.isLoading = false
    },
    createSuggested (suggestedPattern) {
      this.edit({
        label: suggestedPattern.label,
        category: suggestedPattern.category,
        subcategory: suggestedPattern.subcategory,
        isRecurring: suggestedPattern.isRecurring,
        share: suggestedPattern.share || 100,
        shares: suggestedPattern.share || [],
      })
    },
    closePatternModal () {
      this.modalPattern.isActive = false
      this.modalPattern.pattern = {}
    },
  },
}
</script>
