<template>
  <form novalidate @submit.prevent="validateBeforeSubmit">
    <div class="modal-card">

      <header class="modal-card-head">
        <p class="modal-card-title">{{ $tc('objects.pattern', 1) }}</p>
      </header>

      <section class="modal-card-body">
        <div class="field">
          <label class="label">{{ $t('fieldnames.label') }}</label>
          <div class="control">
            <input v-model.lazy="pattern.label" v-validate="'required'" class="input" type="text" name="label" placeholder="Transaction name to be find" :class="{'is-danger': errors.has('label')}" @change="count">
            <span v-show="errors.has('label')" class="help is-danger">{{ errors.first('label') }}</span>
          </div>
          <p class="help">{{ $t('labels.patternWildcardHelper') }}<span v-if="matchingCount !== null"> - {{ matchingCount }} {{ $tc('objects.occurence', matchingCount).toLowerCase() }}</span></p>
        </div>
        <div class="field">
          <label class="label">{{ $tc('objects.category', 1) }}</label>
          <div class="control">
            <div class="select">
              <select v-model="pattern.category" name="parent">
                <option value="">-- {{ $tc('objects.category', 1) }} --</option>
                <option v-for="category in categories" :key="category.id" :value="category.id">{{ category.label }}</option>
              </select>
            </div>
          </div>
        </div>
        <div v-if="pattern.category && categoriesAndSubcategoriesLookup[pattern.category] && categoriesAndSubcategoriesLookup[pattern.category].sub.length > 0" class="field">
          <label class="label">{{ $tc('objects.subcategory', 1) }}</label>
          <div class="control">
            <div class="select">
              <select v-model="pattern.subcategory" name="parent">
                <option value="">-- {{ $tc('objects.subcategory', 1) }} --</option>
                <option v-for="subcategory in categoriesAndSubcategoriesLookup[pattern.category].sub" :key="subcategory.id" :value="subcategory.id">{{ subcategory.label }}</option>
              </select>
            </div>
          </div>
        </div>
        <div class="field">
          <label class="label">{{ $t('fieldnames.isRecurring') }}</label>
          <div class="control">
            <b-switch v-model="pattern.isRecurring">{{ pattern.isRecurring ? $t('labels.isRecurring') : $t('labels.isNotRecurring') }}</b-switch>
          </div>
        </div>
        <div class="field">
          <label class="label">{{ $t('fieldnames.dispatch') }}</label>
          <div class="control">
            <table v-if="pattern.shares && pattern.shares.length > 0" class="table is-fullwidth">
              <thead>
                <tr>
                  <th>{{ $tc('objects.user', 1) }}</th>
                  <th>{{ $t('fieldnames.share') }}</th>
                  <th />
                </tr>
              </thead>
              <tbody>
                <tr v-for="(share, index) in pattern.shares" :key="share.user">
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
        <button class="button is-primary" :disabled="!isOnline"><span class="icon"><i class="fa fa-save" /></span><span>{{ $t('actions.save') }}</span></button>
        <button type="button" class="button" @click="$parent.close()">{{ $t('actions.cancel') }}</button>
        <button v-if="pattern.id" type="button" class="button is-danger" :disabled="!isOnline" @click="deletePattern"><span class="icon"><i class="fa fa-trash" /></span><span>{{ $t('actions.delete') }}</span></button>
      </footer>

      <b-loading :is-full-page="false" :active.sync="isLoading" />

    </div>
  </form>
</template>

<script>
import CategoriesFactory from './../services/Categories'
import HoldersFactory from '@/services/Holders'
import Config from './../services/Config'
export default {
  mixins: [CategoriesFactory, HoldersFactory],
  props: {
    rPatterns: {
      type: Object,
      required: true,
    },
    pattern: {
      type: Object,
      required: true,
    },
  },
  data () {
    return {
      error: '',
      matchingCount: null,
      rTransactions: this.$resource(Config.API_URL + 'transactions'),
      isLoading: false,
    }
  },
  computed: {
    isOnline () {
      return this.$store.state.isOnline
    },
    sharesSum () {
      return this.pattern.shares.filter(share => share.user !== null).reduce((acc, item) => acc + item.share, 0)
    },
  },
  watch: {
    'pattern.category' () {
      // clear subcategory field if category has changed
      this.pattern.subcategory = ''
    },
  },
  mounted () {
    this.getCategories(false)
    this.getCurrentHolderId().then((holderId) => {
      if (this.pattern.shares.length === 0) {
        this.addShareLine({ user: holderId, share: 100 })
      }
    })
  },
  methods: {
    deletePattern () {
      this.$buefy.dialog.confirm({
        message: this.$t('labels.deletePatternMsg'),
        title: this.$t('labels.deletePattern'),
        type: 'is-danger',
        hasIcon: true,
        icon: 'trash',
        confirmText: this.$t('actions.deletePattern'),
        cancelText: this.$t('actions.cancel'),
        focusOn: 'cancel',
        onConfirm: () => {
          this.isLoading = true
          this.rPatterns.delete({ id: this.pattern.id })
            .then((response) => {
              // close modal and remove deleted pattern
              this.$parent.close()
              const patterns = this.$parent.$parent.patterns
              const index = patterns.map((pattern) => pattern.id).indexOf(this.pattern.id)
              patterns.splice(index, 1)
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
      this.error = ''
      // call the async validator
      this.$validator.validateAll().then((result) => {
        if (this.pattern.shares.length > 0) {
          // calcul shares sum
          if (this.sharesSum !== 100) {
            this.error = this.$t('labels.invalidDispatch')
            result = false
          }
          // check duplicates
          const sharesByUser = this.pattern.shares.reduce(function (acc, item) {
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
          if (!this.pattern.id) {
            // create new pattern
            this.rPatterns.save(this.pattern)
              .then((response) => {
                this.pattern.id = response.body.id
                this.pattern.share = response.body.share
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
            return
          }
          this.rPatterns.update({ id: this.pattern.id }, this.pattern)
            // update existing pattern
            .then((response) => {
              this.pattern.share = response.body.share
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
    count () {
      if (this.pattern.label) {
        this.rTransactions.query({ pattern: this.pattern.label }).then((response) => {
          this.matchingCount = response.body.length
          console.log(response.body.length)
        }, (response) => {
          // @TODO : add error handling
          console.error(response)
        })
      }
    },
    addShareLine (share) {
      if (!share) {
        share = { user: null, share: 100 - this.sharesSum }
      }
      this.pattern.shares.push(share)
    },
    removeShareLine (index) {
      this.pattern.shares.splice(index, 1)
    },
  },
}
</script>
