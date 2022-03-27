<template>
  <section class="hero">
    <div class="hero-head">
      <breadcrumb :items="breadcrumbItems" />
    </div>
    <div class="hero-body">
      <div class="container box">
        <h1 class="title container">{{ $tc('objects.category', 1) }}</h1>
        <p class="subtitle has-text-grey">{{ $t('actions.editCategory') }}</p>
        <form novalidate class="section is-max-width-form" @submit.prevent="validateBeforeSubmit">

          <div class="field is-horizontal is-required">
            <div class="field-label is-normal">
              <label class="label">{{ $t('fieldnames.label') }}</label>
            </div>
            <div class="field-body">
              <div class="field">
                <div class="control has-icons-right">
                  <input v-model="category.label" class="input" type="text" name="label" placeholder="Type a short description" :class="{'is-danger': errors.label}" @input="validate">
                  <span v-show="errors.label" class="icon is-small is-right">
                    <i class="fas fa-exclamation-triangle has-text-danger" />
                  </span>
                  <span v-if="errors.label" class="help is-danger">{{ errors.label.message }}</span>
                </div>
              </div>
            </div>
          </div>

          <div v-if="!isCategory" class="field is-horizontal is-required">
            <div class="field-label is-normal">
              <label class="label">{{ $t('fieldnames.parent') }}</label>
            </div>
            <div class="field-body">
              <div class="field">
                <div class="control">
                  <div class="select">
                    <select v-model="category.parentId" name="parent" :class="{'is-danger': errors.label}" @input="validate">
                      <option v-for="parentCategory in parentCategories" :key="parentCategory.id" :value="parentCategory.id">{{ parentCategory.label }}</option>
                    </select>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div v-if="isCategory" class="field is-horizontal is-required">
            <div class="field-label is-normal">
              <label class="label">{{ $t('fieldnames.icon') }}</label>
            </div>
            <div class="field-body">
              <div class="field">
                <div class="control has-icons-right">
                  <div v-show="!isIconPickerDisplayed" class="button" @click="toggleIconPicker"><i class="fa-fw" :class="category.icon" /></div>
                  <input v-if="isIconPickerDisplayed" v-model="category.icon" class="input" type="text" name="icon" placeholder="Icon name" :class="{'is-danger': errors.icon}">
                  <span v-if="errors.icon" class="help is-danger">{{ errors.icon.message }}</span>
                </div>
                <icon-picker v-if="isIconPickerDisplayed" :name="category.icon" class="mb-3" @select="setIcon" />
              </div>
            </div>
          </div>

          <div v-if="isCategory" class="field is-horizontal is-required">
            <div class="field-label">
              <label class="label">{{ $t('fieldnames.isBudgeted') }}</label>
            </div>
            <div class="field-body">
              <div class="field">
                <div class="control">
                  <o-switch v-model="category.isBudgeted">
                    {{ category.isBudgeted ? $t('labels.isBudgeted') : $t('labels.isNotBudgeted') }}
                  </o-switch>
                </div>
                <p class="help">{{ $t('labels.isBudgetedHelp') }}</p>
              </div>
            </div>
          </div>

          <div class="field is-horizontal is-required">
            <div class="field-label">
              <label class="label">{{ $t('fieldnames.status') }}</label>
            </div>
            <div class="field-body">
              <div class="field">
                <div class="control">
                  <o-switch v-model="category.status">
                    {{ category.status ? $t('labels.active') : $t('labels.disabled') }}
                  </o-switch>
                </div>
              </div>
            </div>
          </div>

          <div class="field is-horizontal">
            <div class="field-label is-normal" />
            <div class="field-body">
              <div class="field is-grouped">
                <div class="control">
                  <button class="button is-primary" :disabled="!isOnline"><span class="icon"><i class="fas fa-save" aria-hidden="true" /></span><span>{{ $t('actions.save') }}</span></button>
                </div>
                <div class="control">
                  <a class="button is-light" @click="$router.go(-1)"><span class="icon"><i class="fas fa-ban" aria-hidden="true" /></span><span>{{ $t('actions.cancel') }}</span></a>
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
import IconPicker from '@/components/IconPicker.vue'
import { useValidator } from '@/services/Validator'

export default {
  name: 'Category',
  components: {
    Breadcrumb,
    IconPicker,
  },
  props: {
    isCategory: {
      type: Boolean,
    },
  },
  setup() {
    const { errors, validationRules, validate, validateForm } = useValidator()
    return { errors, validationRules, validate, validateForm }
  },
  data () {
    return {
      category: {
        id: parseInt(this.$route.params.id),
        status: true,
      },
      isIconPickerDisplayed: false,
      breadcrumbItems: [],
      isLoading: false,
      error: null,
    }
  },
  computed: {
    isOnline () {
      return this.$store.isOnline
    },
    parentCategories () {
      return this.$store.categories
    },
  },
  created () {
    this.validationRules = {
      label: 'required|min:3',
      parent: 'required',
      icon: 'required',
    }
  },
  mounted () {
    this.breadcrumbItems = [
      { link: '/', icon: 'fas fa-home', text: this.$t('labels.home') },
      { link: '/categories', icon: 'far fa-folder-open', text: this.$tc('objects.category', 2) },
    ]
    if (this.category.id) {
      // for existing category, get data
      this.get()
    }
    if (!this.isCategory) {
      // for subcategory, get parent category id from path and get all ids/labels
      this.category.parentId = parseInt(this.$route.params.pid)
    } else if (!this.category.icon) {
      // set default icon for new category
      this.category.icon = 'fas fa-question'
    }
  },
  methods: {
    // get category informations
    async get () {
      this.isLoading = true
      try {
        const response = await this.$http.get(`categories/${this.category.id}`)
        this.category = response.data
        this.updateBreadcrumbItems()
      } catch (error) {
        console.error(error)
      }
      this.isLoading = false
    },
    updateBreadcrumbItems () {
      // need to copy breadcrumb items array for sync
      const breadcrumbItems = this.breadcrumbItems.slice()
      let currentLevel = 2
      if (!this.isCategory) {
        // it is a subcategory, find and display the parent category
        currentLevel = 3
        if (this.category.parentId) {
          // parent is kwnow, try to find it
          var label = this.category.parentId
          if (this.parentCategories.length > 0) {
            const parent = this.parentCategories.filter((pcategory) => pcategory.id === parseInt(this.category.parentId))
            if (parent.length > 0) {
              // parent is found, update the breadcrumb
              label = parent[0].label
              breadcrumbItems[currentLevel - 1] = { link: { name: 'category', params: { id: this.category.parentId } }, text: label }
            }
          }
        }
      }
      if (this.category.label) {
        // label is kwnow, display it in last breadcrumbs item (regardless category or subcategory)
        breadcrumbItems[currentLevel] = { link: '/categories', text: this.category.label, isActive: true }
      }
      // update breadcrumbs items
      this.breadcrumbItems = breadcrumbItems.slice()
    },
    toggleIconPicker () {
      this.isIconPickerDisplayed = !this.isIconPickerDisplayed
    },
    setIcon (selectedIcon) {
      this.category.icon = selectedIcon
      this.toggleIconPicker()
      this.validate({ target: { name: 'icon', value: selectedIcon } })
    },
    async validateBeforeSubmit (submitEvent) {
      this.error = null
      if (!this.validateForm(submitEvent)) {
        return
      }
      this.isLoading = true
      if (!this.category.id) {
        // creating new (sub)category
        try {
          await this.$http.post('categories', this.category)
          // return to categories this will reload categories array to store
          this.$router.replace({ name: 'categories' })
        } catch (error) {
          this.error = error.message
        }
        // remove loading overlay when API replies
        this.isLoading = false
        return
      }
      // updating new (sub)category
      try {
        await this.$http.put(`categories/${this.category.id}`, this.category)
        // return to categories this will reload categories array to store
        this.$router.replace({ name: 'categories' })
      } catch (error) {
        this.error = error.message
      }
      this.isLoading = false
    },
  },
}
</script>
