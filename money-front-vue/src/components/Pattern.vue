<template>
  <form @submit.prevent="validateBeforeSubmit" novalidate>
    <div class="modal-card">

      <header class="modal-card-head">
        <p class="modal-card-title">{{ $tc('objects.pattern', 1) }}</p>
      </header>

      <section class="modal-card-body">
        <div class="field">
          <label class="label">{{ $t('fieldnames.label') }}</label>
          <div class="control">
            <input class="input" type="text" name="label" placeholder="Transaction name to be find" v-model.lazy="pattern.label" @change="count" v-validate="'required'" :class="{ 'is-danger': errors.has('label') }">
            <span v-show="errors.has('label')" class="help is-danger">{{ errors.first('label') }}</span>
          </div>
          <p class="help">{{ $t('labels.patternWildcardHelper') }}<span v-if="matchingCount !== null"> - {{ matchingCount }} {{ $tc('objects.occurence', matchingCount).toLowerCase() }}</span></p>
        </div>
        <div class="field">
          <label class="label">{{ $tc('objects.category', 1) }}</label>
          <div class="control">
            <div class="select">
              <select name="parent" v-model="pattern.category">
                <option value="">-- {{ $tc('objects.category', 1) }} --</option>
                <option v-for="category in categories" :key="category.id" v-bind:value="category.id">{{ category.label }}</option>
              </select>
            </div>
          </div>
        </div>
        <div class="field" v-if="pattern.category && categoriesAndSubcategoriesLookup[pattern.category] && categoriesAndSubcategoriesLookup[pattern.category].sub.length > 0">
          <label class="label">{{ $tc('objects.subcategory', 1) }}</label>
          <div class="control">
            <div class="select">
              <select name="parent" v-model="pattern.subcategory">
                <option value="">-- {{ $tc('objects.subcategory', 1) }} --</option>
                <option v-for="subcategory in categoriesAndSubcategoriesLookup[pattern.category].sub" :key="subcategory.id" v-bind:value="subcategory.id">{{ subcategory.label }}</option>
              </select>
            </div>
          </div>
        </div>
        <div class="field">
          <label class="label">{{ $t('fieldnames.isRecurring') }}</label>
          <div class="control">
            <b-switch v-model="pattern.isRecurring">{{ pattern.isRecurring ? $t('labels.isRecurring') : $t('labels.isNotRecurring') }}</b-switch>
          </div>
        </div>

        <div class="message is-danger block" v-if="error">
          <div class="message-body">
            {{ error }}
          </div>
        </div>
      </section>

      <footer class="modal-card-foot">
        <button class="button is-primary" :disabled="!isOnline"><span class="icon"><i class="fa fa-save"/></span><span>{{ $t('actions.save') }}</span></button>
        <button type="button" class="button" @click="$parent.close()">{{ $t('actions.cancel') }}</button>
        <button v-if="pattern.id" type="button" class="button is-danger" :disabled="!isOnline" @click="deletePattern"><span class="icon"><i class="fa fa-trash"/></span><span>{{ $t('actions.delete') }}</span></button>
      </footer>

      <b-loading :is-full-page="false" :active.sync="isLoading"></b-loading>

    </div>
  </form>
</template>

<script>
import CategoriesFactory from './../services/Categories'
import Config from './../services/Config'
export default {
  props: ['rPatterns', 'pattern'],
  mixins: [CategoriesFactory],
  data () {
    return {
      error: '',
      matchingCount: null,
      rTransactions: this.$resource(Config.API_URL + 'transactions'),
      isLoading: false
    }
  },
  computed: {
    isOnline () {
      return this.$store.state.isOnline
    }
  },
  methods: {
    deletePattern () {
      this.$dialog.confirm({
        message: this.$t('labels.deletePatternMsg'),
        title: this.$t('labels.deletePattern'),
        type: 'is-danger',
        hasIcon: true,
        icon: 'trash',
        confirmText: this.$t('actions.deletePattern'),
        focusOn: 'cancel',
        onConfirm: () => {
          this.isLoading = true
          this.rPatterns.delete({ id: this.pattern.id })
            .then((response) => {
              // close modal and remove deleted pattern
              this.$parent.close()
              let patterns = this.$parent.$parent.patterns
              let index = patterns.map((pattern) => pattern.id).indexOf(this.pattern.id)
              patterns.splice(index, 1)
            }, (response) => {
              // remove loading overlay when API replies
              this.isLoading = false
              if (response.body.message) {
                this.error = response.body.message
                return
              }
              this.error = response.status + ' - ' + response.statusText
            })
        }
      })
    },
    validateBeforeSubmit () {
      // call the async validator
      this.$validator.validateAll().then((result) => {
        if (result) {
          // if validation is ok, call accounts API
          this.isLoading = true
          if (!this.pattern.id) {
            // create new pattern
            this.rPatterns.save(this.pattern)
              .then((response) => {
                this.pattern.id = response.body.id
                this.$parent.close()
              }, (response) => {
                // remove loading overlay when API replies
                this.isLoading = false
                if (response.body.message) {
                  this.error = response.body.message
                  return
                }
                this.error = response.status + ' - ' + response.statusText
              })
            return
          }
          this.rPatterns.update({ id: this.pattern.id }, this.pattern)
            // update existing pattern
            .then((response) => {
              this.$parent.close()
            }, (response) => {
              // remove loading overlay when API replies
              this.isLoading = false
              if (response.body.message) {
                this.error = response.body.message
                return
              }
              this.error = response.status + ' - ' + response.statusText
            })
        }
      })
    },
    count () {
      if (this.pattern.label) {
        this.rTransactions.query({ pattern: this.pattern.label }).then((response) => {
          this.matchingCount = response.body.length
          console.log(response.body.length)
        }, (response) => {
          // @TODO : add error handling
          console.error(response)
        })
      }
    }
  },
  watch: {
    'pattern.category' () {
      // clear subcategory field if category has changed
      this.pattern.subcategory = ''
    }
  },
  mounted () {
    this.getCategories(false)
  }
}
</script>
