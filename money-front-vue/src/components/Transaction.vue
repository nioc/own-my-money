<template>
  <form novalidate @submit.prevent="validateBeforeSubmit">
    <div class="modal-card">
      <header class="modal-card-head">
        <p class="modal-card-title">{{ $tc('objects.transaction', 1) }}</p>
      </header>
      <section class="modal-card-body">
        <div class="field">
          <label class="label">{{ $t('fieldnames.memo') }}</label>
          <div class="control">
            <input v-model="localTransaction.memo" v-validate="'max:255'" class="input" type="text" name="memo" :placeholder="$t('fieldnames.memo')" :class="{'is-danger': errors.has('memo')}">
            <span v-show="errors.has('memo')" class="help is-danger">{{ errors.first('memo') }}</span>
          </div>
        </div>
        <div class="field">
          <label class="label">{{ $t('fieldnames.label') }}</label>
          <div class="control">
            <input v-model="localTransaction.name" v-validate="'required|max:200'" class="input" type="text" name="label" :placeholder="$t('fieldnames.label')" :class="{'is-danger': errors.has('label')}">
            <span v-show="errors.has('label')" class="help is-danger">{{ errors.first('label') }}</span>
          </div>
        </div>
        <div class="field">
          <label class="label">{{ $t('fieldnames.amount') }}</label>
          <div class="control">
            <input v-model="localTransaction.amount" v-validate="'required|decimal:2'" class="input" type="number" name="amount" :placeholder="$t('fieldnames.amount')" :class="{'is-danger': errors.has('amount')}">
            <span v-show="errors.has('amount')" class="help is-danger">{{ errors.first('amount') }}</span>
          </div>
        </div>
        <div class="field">
          <label class="label">{{ $t('fieldnames.date') }}</label>
          <div class="control">
            <b-datepicker v-model="dateUser" :placeholder="$t('fieldnames.date')" icon="calendar" :readonly="false" :max-date="currentDate" />
          </div>
        </div>
        <div class="field">
          <label class="label">{{ $t('fieldnames.note') }}</label>
          <div class="control">
            <input v-model="localTransaction.note" v-validate="'max:50'" class="input" type="text" name="note" :placeholder="$t('fieldnames.note')" :class="{'is-danger': errors.has('note')}">
            <span v-show="errors.has('note')" class="help is-danger">{{ errors.first('note') }}</span>
          </div>
        </div>
        <div class="field">
          <label class="label">{{ $tc('objects.category', 1) }}</label>
          <div class="control">
            <div class="select">
              <select v-model="localTransaction.category" name="parent">
                <option value="">-- {{ $tc('objects.category', 1) }} --</option>
                <option v-for="category in categories" :key="category.id" :value="category.id">{{ category.label }}</option>
              </select>
            </div>
          </div>
        </div>
        <div v-if="localTransaction.category && categoriesAndSubcategoriesLookup[localTransaction.category] && categoriesAndSubcategoriesLookup[localTransaction.category].sub.length > 0" class="field">
          <label class="label">{{ $tc('objects.subcategory', 1) }}</label>
          <div class="control">
            <div class="select">
              <select v-model="localTransaction.subcategory" name="parent">
                <option value="">-- {{ $tc('objects.subcategory', 1) }} --</option>
                <option v-for="subcategory in categoriesAndSubcategoriesLookup[localTransaction.category].sub" :key="subcategory.id" :value="subcategory.id">{{ subcategory.label }}</option>
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
              <input v-model.number="sharePercent" class="input" name="dispatch" disabled>
            </div>
            <div class="control">
              <button class="button" type="button" :disabled="!isOnline" @click="getTransactionDispatch"><i class="fa fa-pencil fa-fw fa-mr" />{{ $t('actions.update') }}</button>
            </div>
          </div>
          <div class="control">
            <table v-if="localTransaction.shares && localTransaction.shares.length > 0" class="table is-fullwidth">
              <thead>
                <tr>
                  <th>{{ $tc('objects.user', 1) }}</th>
                  <th>{{ $t('fieldnames.share') }}</th>
                  <th />
                </tr>
              </thead>
              <tbody>
                <tr v-for="(share, index) in localTransaction.shares" :key="share.user">
                  <td>
                    <div class="control">
                      <div class="select">
                        <select v-model="share.user" name="user">
                          <option v-for="holder in holders" :key="holder.id" :value="holder.id">{{ holder.name }}</option>
                        </select>
                      </div>
                    </div>
                  </td>
                  <td class="dispatch-slider">
                    <b-field grouped>
                      <b-field expanded>
                        <b-slider v-model="share.share" :custom-formatter="val => val + '%'" />
                      </b-field>
                      <b-field>
                        <b-input v-model.number="share.share" type="number" min="0" max="100" />
                      </b-field>
                    </b-field>
                  </td>
                  <td>
                    <button class="button is-light" type="button" @click="removeShareLine(index)"><i class="fa fa-trash fa-fw fa-mr" /></button>
                  </td>
                </tr>
              </tbody>
              <tfoot>
                <tr>
                  <td colspan="3">
                    <button class="button is-light" type="button" @click="addShareLine()"><i class="fa fa-plus-square fa-fw fa-mr" />{{ $t('actions.add') }}</button>
                  </td>
                </tr>
              </tfoot>
            </table>
          </div>
        </div>
        <div v-if="error" class="message is-danger block">
          <div class="message-body">
            {{ error }}
          </div>
        </div>
      </section>
      <footer class="modal-card-foot">
        <button class="button is-primary" :disabled="!isOnline"><i aria-hidden="true" class="fa fa-save fa-fw fa-mr" />{{ $t('actions.save') }}</button>
        <button class="button" type="button" @click="$parent.close()"><i aria-hidden="true" class="fa fa-ban fa-fw fa-mr" />{{ $t('actions.cancel') }}</button>
      </footer>
      <b-loading :is-full-page="false" :active.sync="isLoading" />
    </div>
  </form>
</template>

<script>
import CategoriesFactory from '@/services/Categories'
import HoldersFactory from '@/services/Holders'
export default {
  mixins: [CategoriesFactory, HoldersFactory],
  props: {
    rTransactions: {
      type: Object,
      required: true,
    },
    transaction: {
      type: Object,
      required: true,
    },
  },
  data () {
    return {
      error: '',
      isLoading: false,
      currentDate: new Date(),
      localTransaction: JSON.parse(JSON.stringify(this.transaction)),
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
      },
    },
    sharePercent () {
      return this.localTransaction.share + '%'
    },
    sharesSum () {
      return this.localTransaction.shares.filter(share => share.user !== null).reduce((acc, item) => acc + item.share, 0)
    },
  },
  watch: {
    'localTransaction.category' () {
      // clear subcategory field if category has changed
      this.localTransaction.subcategory = ''
    },
  },
  mounted () {
    this.getCategories(false)
  },
  methods: {
    validateBeforeSubmit () {
      this.error = ''
      // call the async validator
      this.$validator.validateAll().then((result) => {
        if (this.localTransaction.shares && this.localTransaction.shares.length > 0) {
          // calcul shares sum
          if (this.sharesSum !== 100) {
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
              this.transaction.memo = this.localTransaction.memo
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
      this.$set(this.localTransaction, 'shares', [])
      this.rTransactions.get({ id: this.localTransaction.id })
        .then(response => {
          this.localTransaction.shares = response.body.shares
          this.getCurrentHolderId().then((holderId) => {
            if (this.localTransaction.shares.length === 0) {
              this.addShareLine({ user: holderId, share: 100 })
            }
          })
        }, (response) => {
          if (response.body.message) {
            this.error = response.body.message
            return
          }
          this.error = response.status + ' - ' + response.statusText
        })
    },
    addShareLine (share) {
      if (!share) {
        share = { user: null, share: 100 - this.sharesSum }
      }
      this.localTransaction.shares.push(share)
    },
    removeShareLine (index) {
      this.localTransaction.shares.splice(index, 1)
    },
  },
}
</script>
