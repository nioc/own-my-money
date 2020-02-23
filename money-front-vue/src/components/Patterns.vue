<template>
  <section class="hero">

    <div class="hero-head">
      <breadcrumb
        :items="[
          {link: '/', icon: 'fa-home', text: this.$t('labels.home')},
          {link: '/patterns', icon: 'fa-magic', text: this.$tc('objects.pattern', 2), isActive: true},
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

        <b-table :data="displayedPatterns" :striped="true" :hoverable="true" class="table-container" default-sort-direction="desc" @select="edit">
          <template slot-scope="props">
            <b-table-column field="label" :label="$t('fieldnames.label')" sortable>
              {{ props.row.label }}
            </b-table-column>
            <b-table-column field="share" :label="$t('fieldnames.share')" sortable>
              <span v-if="props.row.share !== 100" class="has-text-weight-light has-text-grey">{{ props.row.share }}%</span>
            </b-table-column>
            <b-table-column field="category" :label="$tc('objects.category', 1)" sortable>
              <span v-if="props.row.category && categoriesAndSubcategoriesLookup[props.row.category]">{{ categoriesAndSubcategoriesLookup[props.row.category].label }}</span>
            </b-table-column>
            <b-table-column field="subcategory" :label="$tc('objects.subcategory', 1)" sortable>
              <span v-if="props.row.subcategory && categoriesAndSubcategoriesLookup[props.row.subcategory]">{{ categoriesAndSubcategoriesLookup[props.row.subcategory].label }}</span>
            </b-table-column>
            <b-table-column field="isRecurring" :label="$t('fieldnames.isRecurring')" sortable>
              <i class="fa fa-fw" :class="[props.row.isRecurring ? 'fa-toggle-on' : 'fa-toggle-off']" />
            </b-table-column>
          </template>
        </b-table>

        <div class="field is-grouped">
          <p class="control">
            <button class="button is-primary" role="button" :disabled="!isOnline" @click="create"><span class="icon"><i class="fa fa-plus" /></span><span>{{ $t('actions.addPattern') }}</span></button>
          </p>
          <p class="control">
            <button class="button is-primary" role="button" :disabled="!isOnline" @click="suggest"><span class="icon"><i class="fa fa-cogs" /></span><span>{{ $t('actions.suggestPatterns') }}</span></button>
          </p>
        </div>

        <div v-if="error" class="message is-danger block">
          <div class="message-body">
            {{ error }}
          </div>
        </div>

        <b-table :data="suggestedPatterns" :striped="true" :hoverable="true" class="table-container" @select="createSuggested">
          <template slot-scope="props">
            <b-table-column :label="$t('fieldnames.label')">
              {{ props.row.label }}
            </b-table-column>
            <b-table-column :label="$tc('objects.category', 1)">
              <span v-if="props.row.category && categoriesAndSubcategoriesLookup[props.row.category]">{{ categoriesAndSubcategoriesLookup[props.row.category].label }}</span>
            </b-table-column>
            <b-table-column :label="$tc('objects.subcategory', 1)">
              <span v-if="props.row.subcategory && categoriesAndSubcategoriesLookup[props.row.subcategory]">{{ categoriesAndSubcategoriesLookup[props.row.subcategory].label }}</span>
            </b-table-column>
            <b-table-column :label="$tc('fieldnames.isRecurring', 1)">
              <i class="fa fa-fw" :class="[props.row.isRecurring ? 'fa-toggle-on' : 'fa-toggle-off']" />
            </b-table-column>
            <b-table-column :label="$tc('objects.occurence', 2)">
              {{ props.row.count }}
            </b-table-column>
          </template>
        </b-table>

        <b-loading :is-full-page="false" :active.sync="isLoading" />
      </div>
    </div>

    <b-modal :active.sync="modalPattern.isActive" has-modal-card scroll="keep">
      <transaction-pattern :pattern="modalPattern.pattern" :r-patterns="rPatterns" />
    </b-modal>

  </section>
</template>

<script>
import Config from './../services/Config'
import Breadcrumb from '@/components/Breadcrumb'
import TransactionPattern from '@/components/Pattern'
import CategoriesFactory from './../services/Categories'
export default {
  name: 'Patterns',
  components: {
    TransactionPattern,
    Breadcrumb,
  },
  mixins: [CategoriesFactory],
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
      // resources
      rPatterns: this.$resource(Config.API_URL + 'patterns{/id}'),
      rTransactionsPatterns: this.$resource(Config.API_URL + 'transactions/patterns'),
    }
  },
  computed: {
    isOnline () {
      return this.$store.state.isOnline
    },
    displayedPatterns () {
      if (!this.search) {
        return this.patterns
      }
      const search = this.search.toLowerCase()
      return this.patterns.filter((pattern) => pattern.label.toLowerCase().indexOf(search) > -1)
    },
  },
  mounted () {
    this.get()
    this.getCategories()
  },
  methods: {
    get () {
      this.isLoading = true
      this.rPatterns.query()
        .then((response) => {
          this.patterns = response.body
        }, (response) => {
          if (response.body.message) {
            this.error = response.body.message
            return
          }
          this.error = response.status + ' - ' + response.statusText
        })
        .finally(function () {
          // remove loading overlay when API replies
          this.isLoading = false
        })
    },
    create () {
      const pattern = { label: '', category: '', subcategory: '', isRecurring: false, share: 100, shares: [] }
      this.patterns.push(pattern)
      this.modalPattern.pattern = pattern
      this.modalPattern.isActive = true
    },
    edit (pattern) {
      this.modalPattern.pattern = pattern
      this.modalPattern.isActive = true
    },
    suggest () {
      this.isLoading = true
      this.error = ''
      this.rTransactionsPatterns.query()
        .then((response) => {
          this.suggestedPatterns = response.body
        }, (response) => {
          if (response.body.message) {
            this.error = response.body.message
            return
          }
          this.error = response.status + ' - ' + response.statusText
        })
        .finally(function () {
          // remove loading overlay when API replies
          this.isLoading = false
        })
    },
    createSuggested (suggestedPattern) {
      const pattern = JSON.parse(JSON.stringify(suggestedPattern))
      pattern.share = 100
      pattern.shares = []
      this.patterns.push(pattern)
      this.edit(pattern)
    },
  },
}
</script>
