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
            <input class="input" type="text" name="label" :placeholder="$t('fieldnames.label')" v-model="transaction.name" v-validate="'required'" :class="{ 'is-danger': errors.has('label') }">
            <span v-show="errors.has('label')" class="help is-danger">{{ errors.first('label') }}</span>
          </div>
        </div>
        <div class="field">
          <label class="label">{{ $t('fieldnames.amount') }}</label>
          <div class="control">
            <input class="input" type="number" name="amount" :placeholder="$t('fieldnames.amount')" v-model="transaction.amount" v-validate="'required|decimal:2'" :class="{ 'is-danger': errors.has('amount') }">
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
            <input class="input" type="text" name="note" :placeholder="$t('fieldnames.note')" v-model="transaction.note" v-validate="'max:30'" :class="{ 'is-danger': errors.has('note') }">
            <span v-show="errors.has('note')" class="help is-danger">{{ errors.first('note') }}</span>
          </div>
        </div>
        <div class="field">
          <label class="label">{{ $tc('objects.category', 1) }}</label>
          <div class="control">
            <div class="select">
              <select name="parent" v-model="transaction.category">
                <option value="">-- {{ $tc('objects.category', 1) }} --</option>
                <option v-for="category in categories" :key="category.id" v-bind:value="category.id">{{ category.label }}</option>
              </select>
            </div>
          </div>
        </div>
        <div class="field" v-if="transaction.category && categoriesAndSubcategoriesLookup[transaction.category] && categoriesAndSubcategoriesLookup[transaction.category].sub.length > 0">
          <label class="label">{{ $tc('objects.subcategory', 1) }}</label>
          <div class="control">
            <div class="select">
              <select name="parent" v-model="transaction.subcategory">
                <option value="">-- {{ $tc('objects.subcategory', 1) }} --</option>
                <option v-for="subcategory in categoriesAndSubcategoriesLookup[transaction.category].sub" :key="subcategory.id" v-bind:value="subcategory.id">{{ subcategory.label }}</option>
              </select>
            </div>
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
        return this.$moment(this.transaction.dateUser).toDate()
      },
      set (newValue) {
        this.transaction.dateUser = this.$moment(newValue).format()
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
          this.transaction.amount = parseFloat(this.transaction.amount)
          this.rTransactions.update({ id: this.transaction.id }, this.transaction)
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
    }
  },
  watch: {
    'transaction.category' () {
      // clear subcategory field if category has changed
      this.transaction.subcategory = ''
    }
  },
  mounted () {
    this.getCategories(false)
  }
}
</script>
