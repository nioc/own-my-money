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
            <input v-model="localTransaction.memo" class="input" type="text" name="memo" :placeholder="$t('fieldnames.memo')" :class="{'is-danger': errors.memo}">
            <span v-if="errors.memo" class="help is-danger">{{ errors.memo.message }}</span>
          </div>
        </div>
        <div class="field is-required">
          <label class="label">{{ $t('fieldnames.label') }}</label>
          <div class="control">
            <input v-model="localTransaction.name" class="input" type="text" name="label" :placeholder="$t('fieldnames.label')" :class="{'is-danger': errors.label}">
            <span v-if="errors.label" class="help is-danger">{{ errors.label.message }}</span>
          </div>
        </div>
        <div class="field is-required">
          <label class="label">{{ $t('fieldnames.amount') }}</label>
          <div class="control">
            <input v-model="localTransaction.amount" class="input" type="number" name="amount" :placeholder="$t('fieldnames.amount')" :class="{'is-danger': errors.amount}">
            <span v-if="errors.amount" class="help is-danger">{{ errors.amount.message }}</span>
          </div>
        </div>
        <div class="field is-required">
          <label class="label">{{ $t('fieldnames.date') }}</label>
          <div class="control">
            <o-datepicker v-model="dateUser" :placeholder="$t('fieldnames.date')" icon="calendar" :readonly="false" :max-date="currentDate" lists-class="field has-addons" />
          </div>
        </div>
        <div class="field">
          <label class="label">{{ $t('fieldnames.note') }}</label>
          <div class="control">
            <input v-model="localTransaction.note" class="input" type="text" name="note" :placeholder="$t('fieldnames.note')" :class="{'is-danger': errors.note}">
            <span v-if="errors.note" class="help is-danger">{{ errors.note.message }}</span>
          </div>
        </div>
        <div class="field">
          <label class="label">{{ $tc('objects.category', 1) }}</label>
          <div class="control">
            <div class="select">
              <select v-model="localTransaction.category" name="parent">
                <option value="">-- {{ $tc('objects.category', 1) }} --</option>
                <option v-for="category in activeCategories" :key="category.id" :value="category.id">{{ category.label }}</option>
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
            <o-switch v-model="localTransaction.isRecurring">
              {{ localTransaction.isRecurring ? $t('labels.isRecurring') : $t('labels.isNotRecurring') }}
            </o-switch>
          </div>
        </div>
        <div class="field">
          <label class="label">{{ $t('fieldnames.dispatch') }}</label>
          <div class="field has-addons">
            <div class="control is-expanded">
              <input v-model.number="sharePercent" class="input" name="dispatch" disabled>
            </div>
            <div class="control">
              <button class="button" type="button" :disabled="!isOnline" @click="getTransactionDispatch"><span class="icon"><i class="fas fa-pencil-alt" /></span><span>{{ $t('actions.update') }}</span></button>
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
                    <o-field grouped>
                      <o-field expanded root-class="is-expanded">
                        <o-slider v-model="share.share" :custom-formatter="val => val + '%'" />
                      </o-field>
                      <o-field>
                        <o-input v-model.number="share.share" type="number" min="0" max="100" />
                      </o-field>
                    </o-field>
                  </td>
                  <td>
                    <button class="button is-light" type="button" @click="removeShareLine(index)"><span class="icon"><i class="fas fa-trash-alt" /></span></button>
                  </td>
                </tr>
              </tbody>
              <tfoot>
                <tr>
                  <td colspan="3">
                    <button class="button is-light" type="button" @click="addShareLine()"><span class="icon"><i class="fas fa-plus-square" /></span><span>{{ $t('actions.add') }}</span></button>
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
        <button class="button is-primary" :disabled="!isOnline"><span class="icon" aria-hidden="true"><i class="fas fa-save" /></span><span>{{ $t('actions.save') }}</span></button>
        <button class="button" type="button" @click="$emit('close')"><span class="icon" aria-hidden="true"><i class="fas fa-ban" /></span><span>{{ $t('actions.cancel') }}</span></button>
      </footer>
      <o-loading :active="isLoading" :full-page="false" />
    </div>
  </form>
</template>

<script>
import { useValidator } from '@/services/Validator'
import { useStore } from '@/store'
import { mapState } from 'pinia'
import Auth from '@/services/Auth'

export default {
  props: {
    url: {
      type: String,
      required: true,
    },
    transaction: {
      type: Object,
      required: true,
    },
  },
  emits: [
    'close',
    'updated',
  ],
  setup() {
    const { errors, validationRules, validateForm } = useValidator()
    return { errors, validationRules, validateForm }
  },
  data () {
    return {
      error: '',
      isLoading: false,
      currentDate: new Date(),
      localTransaction: JSON.parse(JSON.stringify(this.transaction)),
      userId: Auth.getProfile().id,
    }
  },
  computed: {
    dateUser: {
      get () {
        return this.$dayjs(this.localTransaction.dateUser).toDate()
      },
      set (newValue) {
        this.localTransaction.dateUser = this.$dayjs(newValue).format()
      },
    },
    sharePercent () {
      return ((this.localTransaction.share) ? this.localTransaction.share : (this.localTransaction.accountOwner === this.userId) ? 100 : 0) + '%'
    },
    sharesSum () {
      return this.localTransaction.shares.filter(share => share.user !== null).reduce((acc, item) => acc + item.share, 0)
    },
    ...mapState(useStore, ['isOnline', 'activeCategories', 'categoriesAndSubcategoriesLookup', 'holders', 'holderId']),
  },
  watch: {
    'localTransaction.category' () {
      // clear subcategory field if category has changed
      this.localTransaction.subcategory = ''
    },
  },
  created () {
    this.validationRules = {
      memo: 'max:255',
      label: 'required|max:200',
      amount: 'required|decimal:2',
      note: 'max:50',
    }
  },
  methods: {
    async validateBeforeSubmit (submitEvent) {
      this.error = ''
      if (!this.validateForm(submitEvent)) {
        return
      }
      let result = true
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
        try {
          const response = await this.$http.put(`${this.url}/${this.localTransaction.id}`, this.localTransaction)
          // update transaction for rendering
          this.localTransaction = response.data
          const transactionUpdated = {
            id: this.localTransaction.id,
            memo: this.localTransaction.memo,
            name: this.localTransaction.name,
            amount: this.localTransaction.amount,
            dateUser: this.localTransaction.dateUser,
            note: this.localTransaction.note,
            category: this.localTransaction.category,
            subcategory: this.localTransaction.subcategory,
            isRecurring: this.localTransaction.isRecurring,
            share: this.localTransaction.share,
          }
          // close
          this.$emit('updated', transactionUpdated)
          this.$emit('close')
        } catch (error) {
          this.error = error.message
        }
        this.isLoading = false
      }
    },
    async getTransactionDispatch () {
      this.localTransaction.shares = []
      try {
        const response = await this.$http.get(`${this.url}/${this.localTransaction.id}`)
        this.localTransaction.shares = response.data.shares
        if (this.localTransaction.shares.length === 0) {
          this.addShareLine({ user: this.holderId, share: 100 })
        }
      } catch (error) {
        this.error = error.message
      }
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
