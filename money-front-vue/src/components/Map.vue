<template>
  <section class="hero">
    <div class="hero-head">
      <breadcrumb
        :items="[
          {link: '/', icon: 'fas fa-home', text: $t('labels.home')},
          {link: '/maps', icon: 'fas fa-random', text: $tc('objects.transactionMapping', 2)},
          {link: '/maps', text: map.label, isActive: true},
        ]"
      />
    </div>
    <div class="hero-body">
      <div class="container box">
        <h1 class="title container">{{ $tc('objects.transactionMapping', 1) }}</h1>
        <p class="subtitle has-text-grey">{{ $t('labels.mapLabel') }}</p>
        <form novalidate class="section is-max-width-form" @submit.prevent="validateBeforeSubmit">

          <div class="field is-horizontal is-required">
            <div class="field-label is-normal"><label class="label">{{ $t('fieldnames.code') }}</label></div>
            <div class="field-body">
              <div class="field">
                <div class="control has-icons-right">
                  <input v-model="map.code" class="input" type="text" name="code" placeholder="Type a code" :class="{'is-danger': errors.code}" :disabled="!isNew">
                  <span v-show="errors.code" class="icon is-small is-right">
                    <i class="fas fa-exclamation-triangle has-text-danger" />
                  </span>
                  <span v-if="errors.code" class="help is-danger">{{ errors.code.message }}</span>
                </div>
              </div>
            </div>
          </div>

          <div class="field is-horizontal is-required">
            <div class="field-label is-normal"><label class="label">{{ $t('fieldnames.label') }}</label></div>
            <div class="field-body">
              <div class="field">
                <div class="control has-icons-right">
                  <input v-model="map.label" class="input" type="text" name="label" :placeholder="$t('labels.typeJsonAttribute')" :class="{'is-danger': errors.label}">
                  <span v-show="errors.label" class="icon is-small is-right">
                    <i class="fas fa-exclamation-triangle has-text-danger" />
                  </span>
                  <span v-if="errors.label" class="help is-danger">{{ errors.label.message }}</span>
                </div>
              </div>
            </div>
          </div>

          <div class="field is-horizontal is-required">
            <div class="field-label is-normal"><label class="label">{{ $t('fieldnames.dateFormat') }}</label></div>
            <div class="field-body">
              <div class="field">
                <div class="control has-icons-right">
                  <input v-model="map.dateFormat" class="input" type="text" name="dateFormat" placeholder="Type the provided date format (Y-m-d)" :class="{'is-danger': errors.dateFormat}">
                  <span v-show="errors.dateFormat" class="icon is-small is-right">
                    <i class="fas fa-exclamation-triangle has-text-danger" />
                  </span>
                  <span v-if="errors.dateFormat" class="help is-danger">{{ errors.dateFormat.message }}</span>
                </div>
              </div>
            </div>
          </div>

          <div class="field is-horizontal is-required">
            <div class="field-label is-normal"><label class="label">{{ $t('fieldnames.transactionId') }}</label></div>
            <div class="field-body">
              <div class="field">
                <div class="control has-icons-right">
                  <input v-model="map.attributes.fitid" class="input" type="text" name="transactionId" :placeholder="$t('labels.typeJsonAttribute')" :class="{'is-danger': errors.transactionId}">
                  <span v-show="errors.transactionId" class="icon is-small is-right">
                    <i class="fas fa-exclamation-triangle has-text-danger" />
                  </span>
                  <span v-if="errors.transactionId" class="help is-danger">{{ errors.transactionId.message }}</span>
                </div>
              </div>
            </div>
          </div>

          <div class="field is-horizontal is-required">
            <div class="field-label is-normal"><label class="label">{{ $t('fieldnames.transactionLabel') }}</label></div>
            <div class="field-body">
              <div class="field">
                <div class="control has-icons-right">
                  <input v-model="map.attributes.name" class="input" type="text" name="transactionLabel" :placeholder="$t('labels.typeJsonAttribute')" :class="{'is-danger': errors.transactionLabel}">
                  <span v-show="errors.transactionLabel" class="icon is-small is-right">
                    <i class="fas fa-exclamation-triangle has-text-danger" />
                  </span>
                  <span v-if="errors.transactionLabel" class="help is-danger">{{ errors.transactionLabel.message }}</span>
                </div>
              </div>
            </div>
          </div>

          <div class="field is-horizontal">
            <div class="field-label is-normal"><label class="label">{{ $t('fieldnames.memo') }}</label></div>
            <div class="field-body">
              <div class="field">
                <div class="control has-icons-right">
                  <input v-model="map.attributes.memo" class="input" type="text" name="memo" :placeholder="$t('labels.typeJsonAttribute')" :class="{'is-danger': errors.memo}">
                  <span v-show="errors.memo" class="icon is-small is-right">
                    <i class="fas fa-exclamation-triangle has-text-danger" />
                  </span>
                  <span v-if="errors.memo" class="help is-danger">{{ errors.memo.message }}</span>
                </div>
              </div>
            </div>
          </div>

          <div class="field is-horizontal is-required">
            <div class="field-label is-normal"><label class="label">{{ $t('fieldnames.amount') }}</label></div>
            <div class="field-body">
              <div class="field">
                <div class="control has-icons-right">
                  <input v-model="map.attributes.amount" class="input" type="text" name="amount" :placeholder="$t('labels.typeJsonAttribute')" :class="{'is-danger': errors.amount}">
                  <span v-show="errors.amount" class="icon is-small is-right">
                    <i class="fas fa-exclamation-triangle has-text-danger" />
                  </span>
                  <span v-if="errors.amount" class="help is-danger">{{ errors.amount.message }}</span>
                </div>
              </div>
            </div>
          </div>

          <div class="field is-horizontal is-required">
            <div class="field-label is-normal"><label class="label">{{ $t('fieldnames.datePosted') }}</label></div>
            <div class="field-body">
              <div class="field">
                <div class="control has-icons-right">
                  <input v-model="map.attributes.datePosted" class="input" type="text" name="datePosted" :placeholder="$t('labels.typeJsonAttribute')" :class="{'is-danger': errors.datePosted}">
                  <span v-show="errors.datePosted" class="icon is-small is-right">
                    <i class="fas fa-exclamation-triangle has-text-danger" />
                  </span>
                  <span v-if="errors.datePosted" class="help is-danger">{{ errors.datePosted.message }}</span>
                </div>
              </div>
            </div>
          </div>

          <div class="field is-horizontal is-required">
            <div class="field-label is-normal"><label class="label">{{ $t('fieldnames.dateUser') }}</label></div>
            <div class="field-body">
              <div class="field">
                <div class="control has-icons-right">
                  <input v-model="map.attributes.dateUser" class="input" type="text" name="dateUser" :placeholder="$t('labels.typeJsonAttribute')" :class="{'is-danger': errors.dateUser}">
                  <span v-show="errors.dateUser" class="icon is-small is-right">
                    <i class="fas fa-exclamation-triangle has-text-danger" />
                  </span>
                  <span v-if="errors.dateUser" class="help is-danger">{{ errors.dateUser.message }}</span>
                </div>
              </div>
            </div>
          </div>

          <div class="field is-horizontal">
            <div class="field-label is-normal" />
            <div class="field-body">
              <div class="field is-grouped">
                <div class="control">
                  <button class="button is-primary" :disabled="!isOnline"><span class="fa fa-save fa-fw fa-mr" aria-hidden="true" />{{ $t('actions.save') }}</button>
                </div>
                <div class="control">
                  <a class="button is-light" @click="$router.go(-1)"><span class="fa fa-ban fa-fw fa-mr" aria-hidden="true" />{{ $t('actions.cancel') }}</a>
                </div>
                <div class="control">
                  <button v-if="!isNew" type="button" class="button is-danger" role="button" :disabled="!isOnline" @click="deleteMap"><i class="fas fa-trash-alt fa-mr" />{{ $t('actions.delete') }}</button>
                </div>
              </div>
            </div>
          </div>

          <div v-if="error" class="field is-horizontal">
            <div class="field-label is-normal" />
            <div class="field-body">
              <div class="message is-danger">
                <div class="message-body">
                  {{ error }}
                </div>
              </div>
            </div>
          </div>

          <o-loading :active="isLoading" :full-page="false" />
        </form>
      </div>
    </div>
  </section>
</template>

<script>
import Breadcrumb from '@/components/Breadcrumb.vue'
import Modal from '@/components/Modal.vue'
import { useValidator } from '@/services/Validator'

export default {
  name: 'Mapping',
  components: {
    Breadcrumb,
  },
  props: {
    isNew: {
      type: Boolean,
    },
  },
  setup() {
    const { errors, validationRules, validate, validateForm } = useValidator()
    return { errors, validationRules, validate, validateForm }
  },
  data () {
    return {
      map: {
        code: this.$route.params.code,
        label: '',
        dateFormat: 'Y-m-d',
        attributes: {
          datePosted: '',
          fitid: '',
          memo: '',
          dateUser: '',
          name: '',
          amount: '',
        },
      },
      isLoading: false,
      error: null,
    }
  },
  computed: {
    isOnline () {
      return this.$store.isOnline
    },
  },
  created () {
    this.validationRules = {
      code: 'required|alpha|min:2|max:10|is_not:new',
      label: 'required|max:100',
      dateFormat: 'required',
      transactionId: 'required|alpha_num',
      transactionLabel: 'required|alpha_num',
      memo: 'alpha_num',
      amount: 'required|alpha_num',
      datePosted: 'required|alpha_num',
      dateUser: 'required|alpha_num',
    }
    if (!this.isNew) {
      // for existing map, get data
      this.get()
    }
  },
  methods: {
    // get map informations
    async get () {
      this.isLoading = true
      try {
        const response = await this.$http.get(`maps/${this.$route.params.code}`)
        this.map = response.data
      } catch (error) {
        this.error = error.message
      }
      // remove loading overlay when API replies
      this.isLoading = false
    },
    // get map informations
    async deleteMap () {
      const result = await new Promise((resolve) =>
        this.$oruga.modal.open({
          rootClass: 'dialog',
          trapFocus: true,
          component: Modal,
          onCancel: () => resolve(false),
          props: {
            message: this.$t('labels.deleteMapMsg'),
            title: this.$t('labels.deleteMap'),
            type: 'is-danger',
            hasIcon: true,
            iconClass: 'fas fa-trash-alt fa-2x',
            hasCancelButton: true,
            confirmText: this.$t('actions.deleteMap'),
            cancelText: this.$t('actions.cancel'),
            onConfirm: resolve,
            onCancel: () => {
              resolve(false)
            },
          },
        }),
      )
      if (result) {
        this.isLoading = true
        try {
          await this.$http.delete(`maps/${this.$route.params.code}`)
          // return to maps this will reload maps array to store
          this.$router.replace({ name: 'maps' })
        } catch (error) {
          this.error = error.message
        }
        this.isLoading = false
      }
    },
    async validateBeforeSubmit (submitEvent) {
      this.error = null
      if (!this.validateForm(submitEvent)) {
        return
      }
      this.isLoading = true
      if (this.isNew) {
        // creating new map
        try {
          await this.$http.post(`maps/${this.$route.params.code}`, this.map)
          // return to maps this will reload maps array to store
          this.$router.replace({ name: 'maps' })
        } catch (error) {
          this.error = error.message
        }
        this.isLoading = false
        return
      }
      // updating map
      try {
        await this.$http.put(`maps/${this.$route.params.code}`, this.map)
        // return to maps this will reload maps array to store
        this.$router.replace({ name: 'maps' })
      } catch (error) {
        this.error = error.message
      }
      this.isLoading = false
    },
  },
}
</script>
