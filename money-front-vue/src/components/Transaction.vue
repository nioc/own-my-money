<template>
  <form @submit.prevent="validateBeforeSubmit" novalidate>
    <div class="modal-card">
      <header class="modal-card-head">
        <p class="modal-card-title">{{ $tc('objects.transaction', 1) }}</p>
      </header>
      <section class="modal-card-body">
        <div class="field">
          <label class="label">{{ $t('fieldnames.label') }}</label>
          <div class="control">
            <input class="input" type="text" name="label" :placeholder="$t('fieldnames.label')" v-model="localTransaction.name" v-validate="'required'" :class="{ 'is-danger': errors.has('label') }">
            <span v-show="errors.has('label')" class="help is-danger">{{ errors.first('label') }}</span>
          </div>
        </div>
        <div class="field">
          <label class="label">{{ $t('fieldnames.amount') }}</label>
          <div class="control">
            <input class="input" type="number" name="amount" :placeholder="$t('fieldnames.amount')" v-model="localTransaction.amount" v-validate="'required|decimal:2'" :class="{ 'is-danger': errors.has('amount') }">
            <span v-show="errors.has('amount')" class="help is-danger">{{ errors.first('amount') }}</span>
          </div>
        </div>
        <div class="field">
          <label class="label">{{ $t('fieldnames.date') }}</label>
          <div class="control">
            <b-datepicker :placeholder="$t('fieldnames.date')" icon="calendar" :readonly="false" :max-date="currentDate" v-model="dateUser"></b-datepicker>
          </div>
        </div>
        <div class="field">
          <label class="label">{{ $t('fieldnames.note') }}</label>
          <div class="control">
            <input class="input" type="text" name="note" :placeholder="$t('fieldnames.note')" v-model="localTransaction.note" v-validate="'max:30'" :class="{ 'is-danger': errors.has('note') }">
            <span v-show="errors.has('note')" class="help is-danger">{{ errors.first('note') }}</span>
          </div>
        </div>
        <div class="field">
          <label class="label">{{ $tc('objects.category', 1) }}</label>
          <div class="control">
            <div class="select">
              <select name="parent" v-model="localTransaction.category">
                <option value="">-- {{ $tc('objects.category', 1) }} --</option>
                <option v-for="category in categories" :key="category.id" v-bind:value="category.id">{{ category.label }}</option>
              </select>
            </div>
          </div>
        </div>
        <div class="field" v-if="localTransaction.category && categoriesAndSubcategoriesLookup[localTransaction.category] && categoriesAndSubcategoriesLookup[localTransaction.category].sub.length > 0">
          <label class="label">{{ $tc('objects.subcategory', 1) }}</label>
          <div class="control">
            <div class="select">
              <select name="parent" v-model="localTransaction.subcategory">
                <option value="">-- {{ $tc('objects.subcategory', 1) }} --</option>
                <option v-for="subcategory in categoriesAndSubcategoriesLookup[localTransaction.category].sub" :key="subcategory.id" v-bind:value="subcategory.id">{{ subcategory.label }}</option>
              </select>
            </div>
          </div>
        </div>
        <div class="field">
          <label class="label">{{ $t('fieldnames.isRecurring') }}</label>
          <div class="control">
            <b-switch v-model="localTransaction.isRecurring">{{ localTransaction.isRecurring ? $t('labels.isRecurring') : $t('labels.isNotRecurring') }}</b-switch>
          </div>
        </div>
        <div class="message is-danger block" v-if="error">
          <div class="message-body">
            {{ error }}
          </div>
        </div>
      </section>
      <footer class="modal-card-foot">
        <button class="button is-primary" :disabled="!isOnline">{{ $t('actions.save') }}</button>
        <button class="button" type="button" @click="$parent.close()">{{ $t('actions.cancel') }}</button>
      </footer>
      <b-loading :is-full-page="false" :active.sync="isLoading"></b-loading>
    </div>
  </form>
</template>

<script>
import Config from './../services/Config'
import CategoriesFactory from './../services/Categories'
export default {
  props: ['rTransactions', 'transaction'],
  mixins: [CategoriesFactory],
  data () {
    return {
      error: '',
      isLoading: false,
      currentDate: new Date(),
      localTransaction: JSON.parse(JSON.stringify(this.transaction)),
      // resources
      rCategories: this.$resource(Config.API_URL + 'categories{/id}')
    }
  },
  computed: {
    isOnline () {
      return this.$store.state.isOnline
    },
    dateUser: {
      get () {
        return this.$moment(this.localTransaction.dateUser).toDate()
      },
      set (newValue) {
        this.localTransaction.dateUser = this.$moment(newValue).format()
      }
    }
  },
  methods: {
    validateBeforeSubmit () {
      // call the async validator
      this.$validator.validateAll().then((result) => {
        if (result) {
          // if validation is ok, call accounts API
          this.isLoading = true
          this.localTransaction.amount = parseFloat(this.localTransaction.amount)
          this.rTransactions.update({ id: this.localTransaction.id }, this.localTransaction)
            .then((response) => {
              // update transaction for rendering
              this.localTransaction = response.body
              this.transaction.name = this.localTransaction.name
              this.transaction.amount = this.localTransaction.amount
              this.transaction.dateUser = this.localTransaction.dateUser
              this.transaction.note = this.localTransaction.note
              this.transaction.category = this.localTransaction.category
              this.transaction.subcategory = this.localTransaction.subcategory
              this.transaction.isRecurring = this.localTransaction.isRecurring
              // close
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
    }
  },
  watch: {
    'localTransaction.category' () {
      // clear subcategory field if category has changed
      this.localTransaction.subcategory = ''
    }
  },
  mounted () {
    this.getCategories(false)
  }
}
</script>
