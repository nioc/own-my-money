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
                    <o-switch v-model="accountHolder.isReadOnly" :disabled="!isOnline || accountHolder.userId === holderId" @update:model-value="updateAccountHolder(accountHolder)" />
                  </td>
                  <td>
                    <button :disabled="!isOnline || accountHolder.userId === holderId" class="button is-danger" type="button" @click="removeAccountHolder(index)"><span class="icon"><i class="fas fa-trash-alt" /></span></button>
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
                    <o-switch v-model="newAccountHolder.isReadOnly" />
                  </td>
                  <td>
                    <button :disabled="!isOnline || !newAccountHolder.userId" class="button is-primary" type="button" @click="addAccountHolder()"><span class="icon"><i class="fas fa-plus-square" /></span></button>
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
        <button type="button" class="button" @click="$emit('close')"><span class="icon"><i class="fas fa-ban" /></span><span>{{ $t('actions.close') }}</span></button>
      </footer>

      <o-loading :active="isLoading" :full-page="false" />

    </div>
  </form>
</template>

<script>
import { useStore } from '@/store'
import { mapState } from 'pinia'

export default {
  props: {
    accountId: {
      type: Number,
      required: true,
    },
  },
  emits: [
    'close',
  ],
  data () {
    return {
      accountHolders: [],
      newAccountHolder: {
        userId: null,
        isReadOnly: true,
      },
      error: '',
      isLoading: false,
    }
  },
  computed: {
    notHolders () {
      return this.holders.filter((holder) => this.accountHolders.every((accountHolder) => accountHolder.userId !== holder.id))
    },
    ...mapState(useStore, ['holders', 'holderId', 'isOnline']),
  },
  async mounted () {
    this.isLoading = true
    try {
      const response = await this.$http.get(`accounts/${this.accountId}/holders`)
      this.accountHolders = response.data
    } catch (error) {
      this.error = error.message
    }
    this.isLoading = false
  },
  methods: {
    async updateAccountHolder (accountHolder) {
      this.error = ''
      this.isLoading = true
      try {
        await this.$http.put(`accounts/${this.accountId}/holders/${accountHolder.userId}`, accountHolder)
      } catch (error) {
        this.error = error.message
      }
      this.isLoading = false
    },
    async addAccountHolder () {
      this.error = ''
      this.isLoading = true
      try {
        const response = await this.$http.post(`accounts/${this.accountId}/holders`, this.newAccountHolder)
        this.newAccountHolder = {
          userId: null,
          isReadOnly: true,
        }
        this.accountHolders.push(response.data)
      } catch (error) {
        this.error = error.message
      }
      this.isLoading = false 
    },
    async removeAccountHolder (index) {
      this.error = ''
      this.isLoading = true
      const accountHolder = this.accountHolders[index]
      try {
        await this.$http.delete(`accounts/${this.accountId}/holders/${accountHolder.userId}`)
        this.accountHolders.splice(index, 1)

      } catch (error) {
        this.error = error.message
      }
      this.isLoading = false
    },
  },
}
</script>
