<template>
  <form novalidate @submit.prevent="validateBeforeSubmit">
    <div class="modal-card">

      <header class="modal-card-head">
        <p class="modal-card-title">{{ $tc('objects.pattern', 1) }}</p>
      </header>

      <section class="modal-card-body">
        <div class="field is-required">
          <label class="label">{{ $t('fieldnames.label') }}</label>
          <div class="control">
            <input v-model.lazy="currentPattern.label" class="input" type="text" name="label" placeholder="Transaction name to be find" :class="{'is-danger': errors.label}" @change="count">
            <span v-if="errors.label" class="help is-danger">{{ errors.label.message }}</span>
          </div>
          <p class="help">{{ $t('labels.patternWildcardHelper') }}<span v-if="matchingCount !== null"> - {{ matchingCount }} {{ $tc('objects.occurence', matchingCount).toLowerCase() }}</span></p>
        </div>
        <div class="field">
          <label class="label">{{ $tc('objects.category', 1) }}</label>
          <div class="control">
            <div class="select">
              <select v-model="currentPattern.category" name="category">
                <option value="">-- {{ $tc('objects.category', 1) }} --</option>
                <option v-for="category in categories" :key="category.id" :value="category.id">{{ category.label }}</option>
              </select>
            </div>
          </div>
        </div>
        <div v-if="currentPattern.category && categoriesAndSubcategoriesLookup[currentPattern.category] && categoriesAndSubcategoriesLookup[currentPattern.category].sub.length > 0" class="field">
          <label class="label">{{ $tc('objects.subcategory', 1) }}</label>
          <div class="control">
            <div class="select">
              <select v-model="currentPattern.subcategory" name="subcategory">
                <option value="">-- {{ $tc('objects.subcategory', 1) }} --</option>
                <option v-for="subcategory in categoriesAndSubcategoriesLookup[currentPattern.category].sub" :key="subcategory.id" :value="subcategory.id">{{ subcategory.label }}</option>
              </select>
            </div>
          </div>
        </div>
        <div class="field">
          <label class="label">{{ $t('fieldnames.isRecurring') }}</label>
          <div class="control">
            <o-switch v-model="currentPattern.isRecurring">
              {{ currentPattern.isRecurring ? $t('labels.isRecurring') : $t('labels.isNotRecurring') }}
            </o-switch>
          </div>
        </div>
        <div class="field">
          <label class="label">{{ $t('fieldnames.dispatch') }}</label>
          <div class="control">
            <table v-if="currentPattern.shares && currentPattern.shares.length > 0" class="table is-fullwidth">
              <thead>
                <tr>
                  <th>{{ $tc('objects.user', 1) }}</th>
                  <th>{{ $t('fieldnames.share') }}</th>
                  <th />
                </tr>
              </thead>
              <tbody>
                <tr v-for="(share, index) in currentPattern.shares" :key="share.user">
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
        <button class="button is-primary" :disabled="!isOnline"><span class="icon"><i class="fas fa-save" /></span><span>{{ $t('actions.save') }}</span></button>
        <button type="button" class="button" @click="$emit('close')"><span class="icon"><i class="fas fa-ban" /></span><span>{{ $t('actions.cancel') }}</span></button>
        <button v-if="currentPattern.id" type="button" class="button is-danger" :disabled="!isOnline" @click="deletePattern"><span class="icon"><i class="fas fa-trash-alt" /></span><span>{{ $t('actions.delete') }}</span></button>
      </footer>

      <o-loading :active="isLoading" :full-page="false" />

    </div>
  </form>
</template>

<script>
import { useStore } from '@/store'
import { mapState } from 'pinia'
import { useValidator } from '@/services/Validator'
import Modal from '@/components/Modal.vue'

export default {
  name: 'Pattern',
  props: {
    pattern: {
      type: Object,
      required: true,
    },
  },
  emits: [
    'close',
    'pattern-updated',
    'pattern-created',
    'pattern-deleted',
  ],
  setup() {
    const { errors, validationRules, validate, validateForm, resetValidation } = useValidator()
    return { errors, validationRules, validate, validateForm, resetValidation }
  },
  data () {
    return {
      currentPattern: { ...this.pattern },
      error: '',
      matchingCount: null,
      isLoading: false,
    }
  },
  computed: {
    sharesSum () {
      return this.currentPattern.shares.filter(share => share.user !== null).reduce((acc, item) => acc + item.share, 0)
    },
    ...mapState(useStore, ['categories', 'categoriesAndSubcategoriesLookup', 'holders', 'holderId', 'isOnline']),
  },
  watch: {
    'currentPattern.category' () {
      // clear subcategory field if category has changed
      this.currentPattern.subcategory = ''
    },
    pattern () {
      this.currentPattern = { ...this.pattern }
      if (this.currentPattern.shares && this.currentPattern.shares.length === 0) {
        this.addShareLine({ user: this.holderId, share: 100 })
      }
      this.resetValidation()
    },
  },
  created () {
    this.validationRules = {
      label: 'required',
    }
    if (this.currentPattern.shares.length === 0) {
      this.addShareLine({ user: this.holderId, share: 100 })
    }
  },
  methods: {
    async deletePattern () {
      if (!await new Promise((resolve) =>
        this.$oruga.modal.open({
          rootClass: 'dialog',
          trapFocus: true,
          component: Modal,
          onCancel: () => resolve(false),
          props: {
            message: this.$t('labels.deletePatternMsg'),
            title: this.$t('labels.deletePattern'),
            type: 'is-danger',
            hasIcon: true,
            iconClass: 'fas fa-trash-alt fa-2x',
            hasCancelButton: true,
            confirmText: this.$t('actions.deletePattern'),
            cancelText: this.$t('actions.cancel'),
            onConfirm: resolve,
            onCancel: () => {
              resolve(false)
            },
          },
        }))) {
        return
      }
      this.isLoading = true
      try {
        await this.$http.delete(`patterns/${this.currentPattern.id}`)
        // emit patternId deleted and close modal
        this.$emit('pattern-deleted', this.currentPattern.id)
        this.$parent.$parent.close()
      } catch (error) {
        this.error = error.message
      }
      this.isLoading = false
    },
    async validateBeforeSubmit (submitEvent) {
      this.error = ''
      if (!this.validateForm(submitEvent)) {
        return
      }
      let result = true
      if (this.currentPattern.shares.length > 0) {
        // calcul shares sum
        if (this.sharesSum !== 100) {
          this.error = this.$t('labels.invalidDispatch')
          result = false
        }
        // check duplicates
        const sharesByUser = this.currentPattern.shares.reduce(function (acc, item) {
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
        if (!this.currentPattern.id) {
          // create new pattern
          try {
            const response = await this.$http.post('patterns', this.currentPattern)
            this.currentPattern.id = response.data.id
            this.currentPattern.share = response.data.share
            this.$emit('pattern-created', this.currentPattern)
            this.$parent.$parent.close()
          } catch (error) {
            this.error = error.message
          }
          this.isLoading = false
          return
        }
        // update existing pattern
        try {
          const response = await this.$http.put(`patterns/${this.currentPattern.id}`, this.currentPattern)
          this.currentPattern.share = response.data.share
          this.$emit('pattern-updated', this.currentPattern)
          this.$parent.$parent.close()
        } catch (error) {
          this.error = error.message
        }
        this.isLoading = false
      }
    },
    async count () {
      if (this.currentPattern.label) {
        try {
          const response = await this.$http.get('transactions', { query: { pattern: this.currentPattern.label } })
          this.matchingCount = response.data.length
        } catch (error) {
          // @TODO : add error handling
          console.error(error)
        }
      }
    },
    addShareLine (share) {
      if (!share) {
        share = { user: null, share: 100 - this.sharesSum }
      }
      this.currentPattern.shares.push(share)
    },
    removeShareLine (index) {
      this.currentPattern.shares.splice(index, 1)
    },
  },
}
</script>
