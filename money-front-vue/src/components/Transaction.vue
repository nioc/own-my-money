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
        <div class="field">
          <label class="label">{{ $t('fieldnames.dispatch') }}</label>
          <div class="field has-addons">
            <div class="control is-expanded">
              <input class="input" name="dispatch" v-model.number="sharePercent" disabled>
            </div>
            <div class="control">
              <button class="button" type="button" @click="getTransactionDispatch" :disabled="!isOnline"><i class="fa fa-pencil fa-fw fa-mr"/>{{ $t('actions.update') }}</button>
            </div>
          </div>
          <div class="control">
            <table class="table is-fullwidth" v-if="localTransaction.shares && localTransaction.shares.length > 0">
              <thead>
                <tr>
                  <th>{{ $tc('objects.user', 1) }}</th>
                  <th>{{ $t('fieldnames.share') }}</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="share in localTransaction.shares" :key="share.user">
                  <td>
                    <div class="control">
                      <div class="select">
                        <select name="user" v-model="share.user">
                          <option v-for="holder in holders" :key="holder.id" v-bind:value="holder.id">{{ holder.name }}</option>
                        </select>
                      </div>
                    </div>
                  </td>
                  <td class="dispatch-slider">
                    <b-field grouped>
                      <b-field expanded>
                        <b-slider v-model="share.share" :custom-formatter="val => val + '%'"></b-slider>
                      </b-field>
                      <b-field>
                        <b-input type="number" v-model.number="share.share" min=0 max=100 ></b-input>
                      </b-field>
                    </b-field>
                  </td>
                </tr>
              </tbody>
              <tfoot>
                <tr>
                  <td colspan="2">
                    <button class="button is-light" type="button" @click="addShareLine"><i class="fa fa-plus-square fa-fw fa-mr"/>{{ $t('actions.create') }}</button>
                  </td>
                </tr>
              </tfoot>
            </table>
          </div>
        </div>
        <div class="message is-danger block" v-if="error">
          <div class="message-body">
            {{ error }}
          </div>
        </div>
      </section>
      <footer class="modal-card-foot">
        <button class="button is-primary" :disabled="!isOnline"><i aria-hidden="true" class="fa fa-save fa-fw fa-mr"/>{{ $t('actions.save') }}</button>
        <button class="button" type="button" @click="$parent.close()"><i aria-hidden="true" class="fa fa-ban fa-fw fa-mr"/>{{ $t('actions.cancel') }}</button>
      </footer>
      <b-loading :is-full-page="false" :active.sync="isLoading"></b-loading>
    </div>
  </form>
</template>

<script>
import CategoriesFactory from '@/services/Categories'
import HoldersFactory from '@/services/Holders'
export default {
  props: ['rTransactions', 'transaction'],
  mixins: [CategoriesFactory, HoldersFactory],
  data () {
    return {
      error: '',
      isLoading: false,
      currentDate: new Date(),
      localTransaction: JSON.parse(JSON.stringify(this.transaction))
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
    },
    sharePercent () {
      return this.localTransaction.share + '%'
    }
  },
  methods: {
    validateBeforeSubmit () {
      this.error = ''
      // call the async validator
      this.$validator.validateAll().then((result) => {
        if (this.localTransaction.shares.length > 0) {
          // calcul shares sum
          const sum = this.localTransaction.shares.reduce((acc, item) => acc + item.share, 0)
          if (sum !== 100) {
            this.error = this.$t('labels.invalidDispatch')
            result = false
          }
          // check duplicates
          const sharesByUser = this.localTransaction.shares.reduce(function (acc, item) {
            if (typeof acc[item.user] === 'undefined') {
              acc[item.user] = 0
            }
            acc[item.user]++
            return acc
          }, [])
          if (sharesByUser.some(share => share > 1)) {
            this.error = this.$t('labels.duplicatesShares')
            result = false
          }
        }
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
              this.transaction.share = this.localTransaction.share
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
    },
    getTransactionDispatch () {
      this.localTransaction.shares = []
      this.rTransactions.get({ id: this.localTransaction.id })
        .then(response => {
          this.localTransaction.shares = response.body.shares
          if (this.localTransaction.shares.length === 0) {
            this.addShareLine()
          }
        }, (response) => {
          if (response.body.message) {
            this.error = response.body.message
            return
          }
          this.error = response.status + ' - ' + response.statusText
        })
    },
    addShareLine () {
      this.localTransaction.shares.push({ user: null, share: 0 })
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
    this.getHolders()
    this.$set(this.localTransaction, 'shares', [])
  }
}
</script>
<style>
.dispatch-slider {
  width: 75%;
}
.dispatch-slider .input {
  width: 4.5rem !important;
}
</style>
