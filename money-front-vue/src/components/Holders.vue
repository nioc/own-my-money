<template>
  <form novalidate @submit.prevent="validateBeforeSubmit">
    <div class="modal-card">

      <header class="modal-card-head">
        <p class="modal-card-title">{{ $tc('objects.holder', 2) }}</p>
      </header>

      <section class="modal-card-body">
        <div class="field">
          <div class="control">
            <table v-if="accountHolders && accountHolders.length > 0" class="table is-center">
              <thead>
                <tr>
                  <th>{{ $tc('objects.user', 1) }}</th>
                  <th>{{ $t('labels.readOnly') }}</th>
                  <th />
                </tr>
              </thead>
              <tbody>
                <tr v-for="(accountHolder, index) in accountHolders" :key="accountHolder.userId">
                  <td>
                    <div class="control">
                      <div class="select">
                        <select v-model="accountHolder.userId" name="user" disabled>
                          <option v-for="holder in holders" :key="holder.id" :value="holder.id">{{ holder.name }}</option>
                        </select>
                      </div>
                    </div>
                  </td>
                  <td>
                    <b-switch v-model="accountHolder.isReadOnly" :disabled="!isOnline || accountHolder.userId === holderId" @input="updateAccountHolder(accountHolder)" />
                  </td>
                  <td>
                    <button :disabled="!isOnline || accountHolder.userId === holderId" class="button is-danger" type="button" @click="removeAccountHolder(index)"><i class="fa fa-trash fa-fw" /></button>
                  </td>
                </tr>
              </tbody>
              <tfoot>
                <tr>
                  <td>
                    <div class="control">
                      <div class="select">
                        <select v-model="newAccountHolder.userId" name="user">
                          <option v-for="holder in notHolders" :key="holder.id" :value="holder.id">{{ holder.name }}</option>
                        </select>
                      </div>
                    </div>
                  </td>
                  <td>
                    <b-switch v-model="newAccountHolder.isReadOnly" />
                  </td>
                  <td>
                    <button :disabled="!isOnline || !newAccountHolder.userId" class="button is-primary" type="button" @click="addAccountHolder()"><i class="fa fa-plus-square fa-fw" /></button>
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
        <button type="button" class="button" @click="$parent.close()">{{ $t('actions.close') }}</button>
      </footer>

      <b-loading :is-full-page="false" :active.sync="isLoading" />

    </div>
  </form>
</template>

<script>
import HoldersFactory from '@/services/Holders'
import Config from './../services/Config'
export default {
  mixins: [HoldersFactory],
  props: {
    accountId: {
      type: Number,
      required: true,
    },
  },
  data () {
    return {
      holderId: '',
      accountHolders: [],
      newAccountHolder: {
        userId: null,
        isReadOnly: true,
      },
      error: '',
      rAccountHolders: this.$resource(Config.API_URL + 'accounts{/aid}/holders/{userId}'),
      isLoading: false,
    }
  },
  computed: {
    isOnline () {
      return this.$store.state.isOnline
    },
    notHolders () {
      return this.holders.filter((holder) => this.accountHolders.every((accountHolder) => accountHolder.userId !== holder.id))
    },
  },
  mounted () {
    this.getCurrentHolderId().then((holderId) => {
      this.holderId = holderId
    })
    this.isLoading = true
    this.rAccountHolders.query({ aid: this.accountId })
      .then((response) => {
        this.accountHolders = response.body
      }, (response) => {
        if (response.body.message) {
          this.error = response.body.message
          return
        }
        this.error = response.status + ' - ' + response.statusText
      }).finally(() => { this.isLoading = false })
  },
  methods: {
    updateAccountHolder (accountHolder) {
      this.error = ''
      this.isLoading = true
      this.rAccountHolders.update({ aid: this.accountId, userId: accountHolder.userId }, accountHolder)
        .then((response) => {
        }, (response) => {
          if (response.body.message) {
            this.error = response.body.message
            return
          }
          this.error = response.status + ' - ' + response.statusText
        }).finally(() => { this.isLoading = false })
    },
    addAccountHolder () {
      this.error = ''
      this.isLoading = true
      this.rAccountHolders.save({ aid: this.accountId }, this.newAccountHolder)
        .then((response) => {
          this.newAccountHolder = {
            userId: null,
            isReadOnly: true,
          }
          this.accountHolders.push(response.body)
        }, (response) => {
          if (response.body.message) {
            this.error = response.body.message
            return
          }
          this.error = response.status + ' - ' + response.statusText
        }).finally(() => { this.isLoading = false })
    },
    removeAccountHolder (index) {
      this.error = ''
      this.isLoading = true
      const accountHolder = this.accountHolders[index]
      this.rAccountHolders.delete({ aid: this.accountId, userId: accountHolder.userId }, accountHolder)
        .then((response) => {
          this.accountHolders.splice(index, 1)
        }, (response) => {
          if (response.body.message) {
            this.error = response.body.message
            return
          }
          this.error = response.status + ' - ' + response.statusText
        }).finally(() => { this.isLoading = false })
    },
  },
}
</script>
