<template>
  <section class="hero">
    <div class="hero-head">
    </div>
    <div class="hero-body">
      <div class="container box">

        <ul v-if="steps.length" class="steps is-horizontal is-medium is-centered has-content-centered">
          <li v-for="step in steps" :key="step.label" class="steps-segment has-gaps" :class="{ 'is-active': step.isActive }">
            <span class="steps-marker">
              <span class="icon">
                <i class="fa" :class="step.icon"></i>
              </span>
            </span>
            <div class="steps-content">
              <p class="heading">{{ step.label }}</p>
            </div>
          </li>
        </ul>

        <div v-else class="content has-text-grey has-text-centered">All is fine</div>

        <form @submit.prevent="validateBeforeSubmit" novalidate class="section is-max-width-form" v-if="steps.length > 0">
          <div v-if="steps[currentStep].help" v-html="steps[currentStep].help" class="content has-text-grey has-text-centered"/>
          <div class="field is-horizontal" v-for="field in steps[currentStep].fields" :key="field.name">
            <div class="field-label is-normal">
              <label class="label">{{ field.label }}</label>
            </div>
            <div class="field-body">
              <div class="field">
                <div class="control has-icons-right">
                  <input class="input" :type="field.type" :name="field.name" :placeholder="field.placeholder" v-model="field.value" v-validate="field.validate" :class="{ 'is-danger': errors.has(field.name) }">
                  <span class="icon is-small is-right" v-show="errors.has(field.name)">
                    <i class="fa fa-exclamation-triangle"></i>
                  </span>
                  <span v-show="errors.has(field.name)" class="help is-danger">{{errors.first(field.name)}}</span>
                </div>
                <p class="help" v-if="field.help">{{ field.help }}</p>
              </div>
            </div>
          </div>

          <div class="field is-horizontal">
            <div class="field-label is-normal">
            </div>
            <div class="field-body">
              <div class="message is-danger" v-if="error">
                <div class="message-body">
                  {{ error }}
                </div>
              </div>
            </div>
          </div>

          <div class="has-text-centered" v-if="currentStep < steps.length-1">
            <button class="button is-primary" :disabled="errors.any()"><span class="icon"><i class="fa fa-arrow-circle-right"/></span><span>Next</span></button>
          </div>

        </form>
        <b-loading :is-full-page="false" :active.sync="isLoading"></b-loading>
      </div>
    </div>
  </section>
</template>

<script>
import Config from './../services/Config'
export default {
  name: 'setup',
  data () {
    return {
      currentStep: null,
      steps: [],
      error: '',
      isLoading: false,
      // resources
      rSteps: this.$resource(Config.API_URL + 'setup/steps{/code}'),
      rFields: this.$resource(Config.API_URL + 'setup/steps{/code}/fields')
    }
  },
  methods: {
    getSteps () {
      this.isLoading = true
      this.rSteps.query()
        .then(response => {
          this.steps = response.body
          this.setStep(this.steps.findIndex(step => step.isActive))
        }, response => {
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
    setStep: function (index) {
      if (index < 0 || index >= this.steps.length) {
        index = 0
      }
      this.steps.map(step => {
        step.isActive = false
        return step
      })
      if (this.steps.length) {
        this.steps[index].isActive = true
      }
      this.currentStep = index
    },
    validateBeforeSubmit: function () {
      this.error = ''
      this.$validator.validateAll().then((result) => {
        if (result) {
          this.isLoading = true
          // if validation is ok, call steps/{code}/fields API
          let code = this.steps[this.currentStep].code
          let fields = this.steps[this.currentStep].fields.map(field => {
            let f = {}
            f.name = field.name
            f.value = field.value
            return f
          })
          this.rFields.update({ code: code }, fields)
            .then(response => {
              this.currentStep++
              this.setStep(this.currentStep)
            }, response => {
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
    }
  },
  mounted: function () {
    this.getSteps()
  }
}
</script>
