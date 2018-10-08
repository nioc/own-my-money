<template>
  <form action="">
    <div class="modal-card">
      <header class="modal-card-head">
        <p class="modal-card-title">Transaction</p>
        <button class="delete" aria-label="close" @click="$parent.close()"></button>
      </header>
      <form @submit.prevent="validateBeforeSubmit" novalidate>
        <section class="modal-card-body">
          <div class="field">
            <label class="label">Name</label>
            <div class="control">
              <input class="input" type="text" name="name" placeholder="Transaction name (from bank)" v-model="transaction.name" v-validate="'required'" :class="{ 'is-danger': errors.has('name') }">
              <span v-show="errors.has('name')" class="help is-danger">{{ errors.first('name') }}</span>
            </div>
          </div>
          <div class="field">
            <label class="label">Amount</label>
            <div class="control">
              <input class="input" type="number" name="amount" placeholder="Amount" v-model="transaction.amount" v-validate="'required|decimal:2'" :class="{ 'is-danger': errors.has('amount') }">
              <span v-show="errors.has('amount')" class="help is-danger">{{ errors.first('amount') }}</span>
            </div>
          </div>
          <div class="field">
            <label class="label">Date</label>
            <div class="control">
              <b-datepicker placeholder="End date" icon="calendar" :readonly="false" :max-date="currentDate" v-model="dateUser"></b-datepicker>
            </div>
          </div>
          <div class="field">
            <label class="label">Note</label>
            <div class="control">
              <input class="input" type="text" name="note" placeholder="Note of your choice" v-model="transaction.note" v-validate="'max:30'" :class="{ 'is-danger': errors.has('note') }">
              <span v-show="errors.has('note')" class="help is-danger">{{ errors.first('note') }}</span>
            </div>
          </div>
          <div class="message is-danger block" v-if="error">
            <div class="message-body">
              {{ error }}
            </div>
          </div>
        </section>
        <footer class="modal-card-foot">
          <button class="button is-primary">Save</button>
          <button class="button" type="button" @click="$parent.close()">Cancel</button>
        </footer>
      </form>
      <b-loading :is-full-page="false" :active.sync="isLoading"></b-loading>
    </div>
  </form>
</template>

<script>
import Config from './../services/Config'
export default {
  props: ['accountId', 'transaction'],
  data () {
    return {
      error: '',
      isLoading: false,
      currentDate: new Date(),
      // resources
      rTransactions: this.$resource(Config.API_URL + 'accounts/' + this.accountId + '/transactions{/id}')
    }
  },
  computed: {
    dateUser: {
      get: function () {
        return this.$moment(this.transaction.dateUser).toDate()
      },
      set: function (newValue) {
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
            .then(response => {
              // TODO provide updated transaction
              this.$parent.close()
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
        }
      })
    }
  }
}
</script>
