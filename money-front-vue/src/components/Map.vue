<template>
  <section class="hero">
    <div class="hero-head">
      <breadcrumb
        :items="[
          {link: '/', icon: 'fa-home', text: this.$t('labels.home')},
          {link: '/maps', icon: 'fa-random', text: this.$tc('objects.transactionMapping', 2)},
          {link: '/maps', text: map.label, isActive: true},
        ]"
      />
    </div>
    <div class="hero-body">
      <div class="container box">
        <h1 class="title container">{{ $tc('objects.transactionMapping', 1) }}</h1>
        <p class="subtitle has-text-grey">{{ $t('labels.mapLabel') }}</p>
        <form novalidate class="section is-max-width-form" @submit.prevent="validateBeforeSubmit">

          <div class="field is-horizontal">
            <div class="field-label is-normal"><label class="label">{{ $t('fieldnames.code') }}</label></div>
            <div class="field-body">
              <div class="field">
                <div class="control has-icons-right">
                  <input v-model="map.code" v-validate="'required|alpha|min:2|max:10|is_not:new'" class="input" type="text" name="code" placeholder="Type a code" :class="{'is-danger': errors.has('code')}" :disabled="!isNew">
                  <span v-show="errors.has('code')" class="icon is-small is-right">
                    <i class="fa fa-exclamation-triangle" />
                  </span>
                  <span v-show="errors.has('code')" class="help is-danger">{{ errors.first('code') }}</span>
                </div>
              </div>
            </div>
          </div>

          <div class="field is-horizontal">
            <div class="field-label is-normal"><label class="label">{{ $t('fieldnames.label') }}</label></div>
            <div class="field-body">
              <div class="field">
                <div class="control has-icons-right">
                  <input v-model="map.label" v-validate="'required|max:100'" class="input" type="text" name="label" :placeholder="$t('labels.typeJsonAttribute')" :class="{'is-danger': errors.has('label')}">
                  <span v-show="errors.has('label')" class="icon is-small is-right">
                    <i class="fa fa-exclamation-triangle" />
                  </span>
                  <span v-show="errors.has('label')" class="help is-danger">{{ errors.first('label') }}</span>
                </div>
              </div>
            </div>
          </div>

          <div class="field is-horizontal">
            <div class="field-label is-normal"><label class="label">{{ $t('fieldnames.dateFormat') }}</label></div>
            <div class="field-body">
              <div class="field">
                <div class="control has-icons-right">
                  <input v-model="map.dateFormat" v-validate="'required'" class="input" type="text" name="dateFormat" placeholder="Type the provided date format (Y-m-d)" :class="{'is-danger': errors.has('dateFormat')}">
                  <span v-show="errors.has('dateFormat')" class="icon is-small is-right">
                    <i class="fa fa-exclamation-triangle" />
                  </span>
                  <span v-show="errors.has('dateFormat')" class="help is-danger">{{ errors.first('dateFormat') }}</span>
                </div>
              </div>
            </div>
          </div>

          <div class="field is-horizontal">
            <div class="field-label is-normal"><label class="label">{{ $t('fieldnames.transactionId') }}</label></div>
            <div class="field-body">
              <div class="field">
                <div class="control has-icons-right">
                  <input v-model="map.attributes.fitid" v-validate="'required|alpha_num'" class="input" type="text" name="transactionId" :placeholder="$t('labels.typeJsonAttribute')" :class="{'is-danger': errors.has('transactionId')}">
                  <span v-show="errors.has('transactionId')" class="icon is-small is-right">
                    <i class="fa fa-exclamation-triangle" />
                  </span>
                  <span v-show="errors.has('transactionId')" class="help is-danger">{{ errors.first('transactionId') }}</span>
                </div>
              </div>
            </div>
          </div>

          <div class="field is-horizontal">
            <div class="field-label is-normal"><label class="label">{{ $t('fieldnames.label') }}</label></div>
            <div class="field-body">
              <div class="field">
                <div class="control has-icons-right">
                  <input v-model="map.attributes.name" v-validate="'required|alpha_num'" class="input" type="text" name="name" :placeholder="$t('labels.typeJsonAttribute')" :class="{'is-danger': errors.has('name')}">
                  <span v-show="errors.has('name')" class="icon is-small is-right">
                    <i class="fa fa-exclamation-triangle" />
                  </span>
                  <span v-show="errors.has('name')" class="help is-danger">{{ errors.first('name') }}</span>
                </div>
              </div>
            </div>
          </div>

          <div class="field is-horizontal">
            <div class="field-label is-normal"><label class="label">{{ $t('fieldnames.memo') }}</label></div>
            <div class="field-body">
              <div class="field">
                <div class="control has-icons-right">
                  <input v-model="map.attributes.memo" v-validate="'alpha_num'" class="input" type="text" name="memo" :placeholder="$t('labels.typeJsonAttribute')" :class="{'is-danger': errors.has('memo')}">
                  <span v-show="errors.has('memo')" class="icon is-small is-right">
                    <i class="fa fa-exclamation-triangle" />
                  </span>
                  <span v-show="errors.has('memo')" class="help is-danger">{{ errors.first('memo') }}</span>
                </div>
              </div>
            </div>
          </div>

          <div class="field is-horizontal">
            <div class="field-label is-normal"><label class="label">{{ $t('fieldnames.amount') }}</label></div>
            <div class="field-body">
              <div class="field">
                <div class="control has-icons-right">
                  <input v-model="map.attributes.amount" v-validate="'required|alpha_num'" class="input" type="text" name="amount" :placeholder="$t('labels.typeJsonAttribute')" :class="{'is-danger': errors.has('amount')}">
                  <span v-show="errors.has('amount')" class="icon is-small is-right">
                    <i class="fa fa-exclamation-triangle" />
                  </span>
                  <span v-show="errors.has('amount')" class="help is-danger">{{ errors.first('amount') }}</span>
                </div>
              </div>
            </div>
          </div>

          <div class="field is-horizontal">
            <div class="field-label is-normal"><label class="label">{{ $t('fieldnames.datePosted') }}</label></div>
            <div class="field-body">
              <div class="field">
                <div class="control has-icons-right">
                  <input v-model="map.attributes.datePosted" v-validate="'required|alpha_num'" class="input" type="text" name="datePosted" :placeholder="$t('labels.typeJsonAttribute')" :class="{'is-danger': errors.has('datePosted')}">
                  <span v-show="errors.has('datePosted')" class="icon is-small is-right">
                    <i class="fa fa-exclamation-triangle" />
                  </span>
                  <span v-show="errors.has('datePosted')" class="help is-danger">{{ errors.first('datePosted') }}</span>
                </div>
              </div>
            </div>
          </div>

          <div class="field is-horizontal">
            <div class="field-label is-normal"><label class="label">{{ $t('fieldnames.dateUser') }}</label></div>
            <div class="field-body">
              <div class="field">
                <div class="control has-icons-right">
                  <input v-model="map.attributes.dateUser" v-validate="'required|alpha_num'" class="input" type="text" name="dateUser" :placeholder="$t('labels.typeJsonAttribute')" :class="{'is-danger': errors.has('dateUser')}">
                  <span v-show="errors.has('dateUser')" class="icon is-small is-right">
                    <i class="fa fa-exclamation-triangle" />
                  </span>
                  <span v-show="errors.has('dateUser')" class="help is-danger">{{ errors.first('dateUser') }}</span>
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
                  <button v-if="!isNew" type="button" class="button is-danger" role="button" :disabled="!isOnline" @click="deleteMap"><i class="fa fa-trash fa-mr" />{{ $t('actions.delete') }}</button>
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

          <b-loading :is-full-page="false" :active.sync="isLoading" />
        </form>
      </div>
    </div>
  </section>
</template>

<script>
import Config from './../services/Config'
import Breadcrumb from '@/components/Breadcrumb'
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
  data () {
    return {
      rMaps: this.$resource(Config.API_URL + 'maps{/code}'),
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
      return this.$store.state.isOnline
    },
  },
  mounted () {
    if (!this.isNew) {
      // for existing map, get data
      this.get()
    }
  },
  methods: {
    // get map informations
    get () {
      this.isLoading = true
      this.rMaps.get({ code: this.$route.params.code })
        .then((response) => {
          this.map = response.body
        }, (response) => {
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
    },
    // get map informations
    deleteMap () {
      this.$buefy.dialog.confirm({
        message: this.$t('labels.deleteMapMsg'),
        title: this.$t('labels.deleteMap'),
        type: 'is-danger',
        hasIcon: true,
        icon: 'trash',
        confirmText: this.$t('actions.deleteMap'),
        cancelText: this.$t('actions.cancel'),
        focusOn: 'cancel',
        onConfirm: () => {
          this.isLoading = true
          this.rMaps.delete({ code: this.$route.params.code })
            .then((response) => {
              localStorage.removeItem('maps')
              this.$router.replace({ name: 'maps' })
            }, (response) => {
              // remove loading overlay when API replies
              this.isLoading = false
              if (response.body.message) {
                this.error = response.body.message
                return
              }
              this.error = response.status + ' - ' + response.statusText
            })
        },
      })
    },
    validateBeforeSubmit () {
      this.error = null
      // call the async validator
      this.$validator.validateAll().then((result) => {
        if (result) {
          this.isLoading = true
          // if validation is ok, call maps API
          if (this.isNew) {
            // creating new map
            this.rMaps.save(this.map)
              .then((response) => {
                // return to maps
                localStorage.removeItem('maps')
                this.$router.replace({ name: 'maps' })
              }, (response) => {
                // remove loading overlay when API replies
                this.isLoading = false
                if (response.body.message) {
                  this.error = response.body.message
                  return
                }
                this.error = response.status + ' - ' + response.statusText
              })
            return
          }
          // updating map
          this.rMaps.update({ code: this.$route.params.code }, this.map)
            .then((response) => {
              // return to maps
              localStorage.removeItem('maps')
              this.$router.replace({ name: 'maps' })
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
  },
}
</script>
