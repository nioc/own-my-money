<template>
  <div class="modal-card animation-content">
    <header class="modal-card-head">
      <p class="modal-card-title">{{ title }}</p>
    </header>
    <section class="modal-card-body">
      <div class="media">
        <div v-if="hasIcon" class="media-left">
          <span class="icon has-text-danger is-large">
            <i :class="iconClass" />
          </span>
        </div>
        <div class="media-content">
          <!-- eslint-disable-next-line vue/no-v-html -->
          <span v-html="message" />
          <div v-if="prompt" class="field pt-4">
            <div class="control">
              <input ref="promptInput" v-model="promptValue" :type="prompt.type" :placeholder="prompt.placeholder" required="required" autofocus class="input" :class="{'is-danger': error}">
            </div>
            <p v-if="error" class="help is-danger">{{ error }}</p>
          </div>
        </div>
      </div>
    </section>
    <footer class="modal-card-foot">
      <button v-if="hasCancelButton" type="button" class="button" @click="cancel">{{ cancelText || $t('actions.cancel') }}</button>
      <button type="button" class="button" :class="type" @click="validate">{{ confirmText || $t('actions.ok') }}</button>
    </footer>
  </div>
</template>

<script>
import { nextTick } from 'vue'

export default {
  name: 'Dialog',
  props: {
    type: {
      type: String,
      default: 'is-primary',
    },
    title: {
      type: String,
      default: '',
    },
    message: {
      type: String,
      default: '',
    },
    prompt: {
      type: Object,
      default: null,
    },
    hasIcon: {
      type: Boolean,
      default: false,
    },
    hasCancelButton: {
      type: Boolean,
      default: false,
    },
    iconClass: {
      type: String,
      default: '',
    },
    confirmText: {
      type: String,
      default: null,
    },
    cancelText: {
      type: String,
      default: null,
    },
  },
  emits: [
    'close',
    'promptAnswered',
    'cancel',
    'confirm',
  ],
  data () {
    return {
      error: null,
      promptValue: this.prompt ? this.prompt.value : null,
    }
  },
  mounted () {
    if (this.prompt) {
      nextTick(() => {
        this.$refs.promptInput.focus()
      })
    }
  },
  methods: {
    validate() {
      if (this.prompt) {
        if (!this.validPrompt()) {
          return
        }
        this.$emit('promptAnswered', this.promptValue)
      } else {
        this.$emit('confirm', true)
      }
      this.$emit('close')
    },
    validPrompt() {
      this.error = null
      if (!this.promptValue) {
        this.error = 'Veuillez compléter ce champ.'
        return false
      }
      return true
    },
    cancel () {
      this.$parent.$parent.cancel('button')
    },
  },
}
</script>
