<template>
  <section class="hero">
    <div class="hero-head" />
    <div class="hero-body">
      <div class="container box">

        <ul v-if="steps.length" class="steps is-horizontal is-medium is-centered has-content-centered">
          <li v-for="step in steps" :key="step.label" class="steps-segment has-gaps" :class="{'is-active': step.isActive}">
            <span class="steps-marker">
              <span class="icon">
                <i class="fa" :class="step.icon" />
              </span>
            </span>
            <div class="steps-content">
              <p class="heading">{{ step.label }}</p>
            </div>
          </li>
        </ul>

        <div v-else class="content has-text-grey has-text-centered">{{ $t('labels.nothingToDo') }}</div>

        <form v-if="steps.length > 0" novalidate class="section is-max-width-form" @submit.prevent="validateBeforeSubmit">
          <!-- eslint-disable-next-line vue/no-v-html -->
          <div v-if="steps[currentStep].help" class="content has-text-grey has-text-centered" v-html="steps[currentStep].help" />
          <div v-for="field in steps[currentStep].fields" :key="field.name" class="field is-horizontal">
            <div class="field-label is-normal">
              <label class="label">{{ field.label }}</label>
            </div>
            <div class="field-body">
              <div class="field">
                <div class="control has-icons-right">
                  <input v-model="field.value" v-validate="field.validate" class="input" :type="field.type" :name="field.name" :placeholder="field.placeholder" :class="{'is-danger': errors.has(field.name)}">
                  <span v-show="errors.has(field.name)" class="icon is-small is-right">
                    <i class="fa fa-exclamation-triangle" />
                  </span>
                  <span v-show="errors.has(field.name)" class="help is-danger">{{ errors.first(field.name) }}</span>
                </div>
                <p v-if="field.help" class="help">{{ field.help }}</p>
              </div>
            </div>
          </div>

          <div v-if="currentStep < steps.length-1" class="has-text-centered">
            <button class="button is-primary" :disabled="!isOnline || errors.any()"><span class="icon"><i class="fa fa-arrow-circle-right" /></span><span>{{ $t('actions.next') }}</span></button>
          </div>

        </form>

        <div v-if="error" class="is-centered columns">
          <div class="column is-narrow">
            <div class="message is-danger">
              <div class="message-body">
                {{ error }}
              </div>
            </div>
          </div>
        </div>

        <b-loading :is-full-page="false" :active.sync="isLoading" />
      </div>
    </div>
  </section>
</template>

<script>
import Config from './../services/Config'
export default {
  name: 'Setup',
  data () {
    return {
      currentStep: null,
      steps: [],
      error: '',
      isLoading: false,
      // resources
      rSteps: this.$resource(Config.API_URL + 'setup/steps{/code}'),
      rFields: this.$resource(Config.API_URL + 'setup/steps{/code}/fields'),
    }
  },
  computed: {
    isOnline () {
      return this.$store.state.isOnline
    },
  },
  mounted () {
    this.getSteps()
  },
  methods: {
    getSteps () {
      this.isLoading = true
      this.rSteps.query()
        .then((response) => {
          this.steps = response.body
          this.setStep(this.steps.findIndex((step) => step.isActive))
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
    setStep (index) {
      if (index < 0 || index >= this.steps.length) {
        index = 0
      }
      this.steps.map((step) => {
        step.isActive = false
        return step
      })
      if (this.steps.length) {
        this.steps[index].isActive = true
      }
      this.currentStep = index
    },
    validateBeforeSubmit () {
      this.error = ''
      this.$validator.validateAll().then((result) => {
        if (result) {
          this.isLoading = true
          // if validation is ok, call steps/{code}/fields API
          const code = this.steps[this.currentStep].code
          const fields = this.steps[this.currentStep].fields.map((field) => {
            const f = {}
            f.name = field.name
            f.value = field.value
            return f
          })
          this.rFields.update({ code: code }, fields)
            .then((response) => {
              this.currentStep++
              this.setStep(this.currentStep)
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
        }
      })
    },
  },
}
</script>
