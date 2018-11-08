<template>
  <form @submit.prevent="validateBeforeSubmit" novalidate>
    <div class="modal-card">

      <header class="modal-card-head">
        <p class="modal-card-title">Pattern</p>
      </header>

      <section class="modal-card-body">
        <div class="field">
          <label class="label">Label</label>
          <div class="control">
            <input class="input" type="text" name="label" placeholder="Transaction name to be find" v-model.lazy="pattern.label" @change="count" v-validate="'required'" :class="{ 'is-danger': errors.has('label') }">
            <span v-show="errors.has('label')" class="help is-danger">{{ errors.first('label') }}</span>
          </div>
          <p class="help">Wildcard character * is allowed<span v-if="matchingCount !== null"> - {{ matchingCount }} results</span></p>
        </div>
        <div class="field">
          <label class="label">Category</label>
          <div class="control">
            <div class="select">
              <select name="parent" v-model="pattern.category">
                <option value="">-- Category --</option>
                <option v-for="category in categories" :key="category.id" v-bind:value="category.id">{{ category.label }}</option>
              </select>
            </div>
          </div>
        </div>
        <div class="field" v-if="pattern.category && categoriesAndSubcategoriesLookup[pattern.category] && categoriesAndSubcategoriesLookup[pattern.category].sub.length > 0">
          <label class="label">Subcategory</label>
          <div class="control">
            <div class="select">
              <select name="parent" v-model="pattern.subcategory">
                <option value="">-- Subcategory --</option>
                <option v-for="subcategory in categoriesAndSubcategoriesLookup[pattern.category].sub" :key="subcategory.id" v-bind:value="subcategory.id">{{ subcategory.label }}</option>
              </select>
            </div>
          </div>
        </div>

        <div class="message is-danger block" v-if="error">
          <div class="message-body">
            {{ error }}
          </div>
        </div>
      </section>

      <footer class="modal-card-foot">
        <button class="button is-primary"><span class="icon"><i class="fa fa-save"/></span><span>Save</span></button>
        <button type="button" class="button" @click="$parent.close()">Cancel</button>
        <button v-if="pattern.id" type="button" class="button is-danger" @click="deletePattern"><span class="icon"><i class="fa fa-trash"/></span><span>Delete</span></button>
      </footer>

      <b-loading :is-full-page="false" :active.sync="isLoading"></b-loading>

    </div>
  </form>
</template>

<script>
import CategoriesFactory from './../services/Categories'
import Config from './../services/Config'
export default {
  props: ['rPatterns', 'pattern'],
  mixins: [CategoriesFactory],
  data () {
    return {
      error: '',
      matchingCount: null,
      rTransactions: this.$resource(Config.API_URL + 'transactions'),
      isLoading: false
    }
  },
  methods: {
    deletePattern () {
      this.$dialog.confirm({
        message: 'Are you sure you want to delete this pattern?',
        title: 'Deleting pattern',
        type: 'is-danger',
        hasIcon: true,
        icon: 'trash',
        confirmText: 'Delete pattern',
        focusOn: 'cancel',
        onConfirm: () => {
          this.isLoading = true
          this.rPatterns.delete({id: this.pattern.id})
            .then(response => {
              // close modal and remove deleted pattern
              this.$parent.close()
              let patterns = this.$parent.$parent.patterns
              let index = patterns.map(pattern => pattern.id).indexOf(this.pattern.id)
              patterns.splice(index, 1)
            }, response => {
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
    validateBeforeSubmit () {
      // call the async validator
      this.$validator.validateAll().then((result) => {
        if (result) {
          // if validation is ok, call accounts API
          this.isLoading = true
          if (!this.pattern.id) {
            // create new pattern
            this.rPatterns.save(this.pattern)
              .then(response => {
                this.pattern.id = response.body.id
                this.$parent.close()
              }, response => {
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
            .then(response => {
              this.$parent.close()
            }, response => {
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
        this.rTransactions.query({pattern: this.pattern.label}).then(response => {
          this.matchingCount = response.body.length
          console.log(response.body.length)
        }, response => {
          // @TODO : add error handling
          console.error(response)
        })
      }
    }
  },
  watch: {
    'pattern.category': function () {
      // clear subcategory field if category has changed
      this.pattern.subcategory = ''
    }
  },
  mounted: function () {
    this.getCategories(false)
  }
}
</script>
