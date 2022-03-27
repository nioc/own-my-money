<template>
  <section class="hero">
    <div class="hero-head" />
    <div class="hero-body">
      <div class="container box">
        <o-steps v-if="steps.length" v-model="currentStepIndex" :animated="true" :has-navigation="false" size="large" step-link-label-class="is-size-7 is-uppercase has-text-weight-light">

          <o-step-item v-for="(step, index) in steps" :key="index" :step="index" :label="step.label" :icon="step.icon" :variant="index < currentStep ? 'success': ''" :clickable="false">

            <form novalidate class="section is-max-width-form" @submit.prevent="validateBeforeSubmit">
              <!-- eslint-disable-next-line vue/no-v-html -->
              <div v-if="step.help" class="content has-text-grey has-text-centered" v-html="step.help" />

              <div v-for="field in step.fields" :key="field.name" class="field is-horizontal">
                <div class="field-label is-normal">
                  <label class="label">{{ field.label }}</label>
                </div>
                <div class="field-body">
                  <div class="field">
                    <div class="control has-icons-right">
                      <input v-model="field.value" class="input" :type="field.type" :name="field.name" :placeholder="field.placeholder" :class="{'is-danger': errors[field.name]}" @input="validate">
                      <span v-show="errors[field.name]" class="icon is-small is-right">
                        <i class="fas fa-exclamation-triangle has-text-danger" />
                      </span>
                      <span v-if="errors[field.name]" class="help is-danger">{{ errors[field.name].message }}</span>
                    </div>
                    <p v-if="field.help" class="help">{{ field.help }}</p>
                  </div>
                </div>
              </div>

              <div v-if="currentStep < steps.length-1" class="has-text-centered">
                <button class="button is-primary" :disabled="!isOnline"><span class="icon"><i class="fas fa-arrow-circle-right" /></span><span>{{ $t('actions.next') }}</span></button>
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

          </o-step-item>

        </o-steps>

        <div v-else class="content has-text-grey has-text-centered">{{ $t('labels.nothingToDo') }}</div>

        <o-loading :active="isLoading" :full-page="false" />
      </div>
    </div>
  </section>
</template>

<script>
import { useValidator } from '@/services/Validator'

export default {
  name: 'Setup',
  setup() {
    const { errors, validationRules, validate, validateForm } = useValidator()
    return { errors, validationRules, validate, validateForm }
  },
  data () {
    return {
      currentStep: null,
      steps: [],
      error: '',
      isLoading: false,
    }
  },
  computed: {
    isOnline () {
      return this.$store.isOnline
    },
    currentStepIndex () {
      return this.currentStep+1
    },
  },
  mounted () {
    this.getSteps()
  },
  methods: {
    async getSteps () {
      this.isLoading = true
      try {
        const response = await this.$http.get('setup/steps')
        this.steps = response.data.map((step) => {
          step.icon = step.icon.replace('fa-', '')
          return step
        })
        this.setStep(this.steps.findIndex((step) => step.isActive))
      } catch (error) {
        this.error = error.message
      }
      this.isLoading = false
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
      const rules = {}
      this.steps[index].fields.forEach(field => {
        if (Object.prototype.hasOwnProperty.call(field, 'validate')) {
          rules[field.name] = field.validate
        }
      })
      this.validationRules = rules
    },
    async validateBeforeSubmit (submitEvent) {
      this.error = ''
      if (!this.validateForm(submitEvent)) {
        return
      }
      this.isLoading = true
      const code = this.steps[this.currentStep].code
      const fields = this.steps[this.currentStep].fields.map((field) => {
        return {
          name: field.name,
          value: field.value,
        }
      })
      try {
        await this.$http.put(`setup/steps/${code}/fields`, fields)
        this.currentStep++
        this.setStep(this.currentStep)
      } catch (error) {
        this.error = error.message
      }
      this.isLoading = false
    },
  },
}
</script>
