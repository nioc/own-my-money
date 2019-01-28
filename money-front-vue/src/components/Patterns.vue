<template>
  <section class="hero">

    <div class="hero-head">
      <breadcrumb
        :items="[
          {link: '/', icon: 'fa-home', text: this.$t('labels.home')},
          {link: '/patterns', icon: 'fa-magic', text: this.$tc('objects.pattern', 2), isActive: true}
        ]">
      </breadcrumb>
    </div>

    <div class="hero-body">
      <div class="container box">
        <h1 class="title container">{{ $tc('objects.pattern', 2) }}</h1>
        <p class="subtitle has-text-grey">{{ $t('labels.patternsLabel') }}</p>

        <b-table :data=patterns :striped="true" :hoverable="true" @select="edit" class="table-container">
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
          </template>
        </b-table>

        <div class="field is-grouped">
          <p class="control">
            <button class="button is-primary" role="button" @click="create"><span class="icon"><i class="fa fa-plus"/></span><span>{{ $t('actions.addPattern') }}</span></button>
          </p>
          <p class="control">
            <button class="button is-primary" role="button" @click="suggest"><span class="icon"><i class="fa fa-cogs"/></span><span>{{ $t('actions.suggestPatterns') }}</span></button>
          </p>
        </div>

        <div class="message is-danger block" v-if="error">
          <div class="message-body">
            {{ error }}
          </div>
        </div>

        <b-table :data=suggestedPatterns :striped="true" :hoverable="true" @select="createSuggested" class="table-container">
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
            <b-table-column :label="$tc('objects.occurence', 2)">
              {{ props.row.count }}
            </b-table-column>
          </template>
        </b-table>

        <b-loading :is-full-page="false" :active.sync="isLoading"></b-loading>
      </div>
    </div>

    <b-modal :active.sync="modalPattern.isActive" has-modal-card scroll="keep">
      <transaction-pattern v-bind:pattern="modalPattern.pattern" v-bind:rPatterns="rPatterns"></transaction-pattern>
    </b-modal>

  </section>
</template>

<script>
import Config from './../services/Config'
import Breadcrumb from '@/components/Breadcrumb'
import TransactionPattern from '@/components/Pattern'
import CategoriesFactory from './../services/Categories'
export default {
  name: 'patterns',
  components: {
    TransactionPattern,
    Breadcrumb
  },
  mixins: [CategoriesFactory],
  data () {
    return {
      patterns: [],
      suggestedPatterns: [],
      error: '',
      isLoading: false,
      // modal
      modalPattern: {
        isActive: false,
        pattern: {}
      },
      // resources
      rPatterns: this.$resource(Config.API_URL + 'patterns{/id}'),
      rTransactionsPatterns: this.$resource(Config.API_URL + 'transactions/patterns')
    }
  },
  methods: {
    get () {
      this.isLoading = true
      this.rPatterns.query()
        .then(response => {
          this.patterns = response.body
        }, response => {
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
      let pattern = { label: '', category: '', subcategory: '' }
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
      this.rTransactionsPatterns.query()
        .then(response => {
          this.suggestedPatterns = response.body
        }, response => {
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
      let pattern = JSON.parse(JSON.stringify(suggestedPattern))
      this.patterns.push(pattern)
      this.edit(pattern)
    }
  },
  mounted: function () {
    this.get()
    this.getCategories()
  }
}
</script>
