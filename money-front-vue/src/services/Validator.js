import { ref, unref, toRaw, watch } from 'vue'

const locale = ref('en')

let i18n

let messages = {
  en: {
    _default: 'The {fieldName} value is not valid',
    required: 'The {fieldName} field is required',
    alpha: `The {fieldName} field may only contain alphabetic characters`,
    // eslint-disable-next-line camelcase
    alpha_num: `The {fieldName} field may only contain alpha-numeric characters`,
    decimal: `The {fieldName} field must be numeric and may contain {arg} decimal points`,
    min: 'The field {fieldName} must must be at least {arg} characters',
    max: 'The field {fieldName} must must be at least {arg} characters',
    email: `The {fieldName} field must be a valid email`,
    // eslint-disable-next-line camelcase
    is_not: `The {fieldName} field must be different from “{arg}”`,
    between: 'The {fieldName} field must be between {min} and {max}',
  },
  fr: {
    _default: `Le champ {fieldName} n'est pas valide`,
    required: `Le champ {fieldName} est obligatoire`,
    alpha: `Le champ {fieldName} ne peut contenir que des lettres`,
    // eslint-disable-next-line camelcase
    alpha_num: `Le champ {fieldName} ne peut contenir que des caractères alpha-numériques`,
    decimal: `Le champ {fieldName} doit être un nombre et peut contenir {arg} décimales`,
    min: `Le champ {fieldName} doit contenir au minimum {arg} caractères`,
    max: `Le champ {fieldName} ne peut pas contenir plus de {arg} caractères`,
    email: `Le champ {fieldName} doit être une adresse e-mail valide`,
    // eslint-disable-next-line camelcase
    is_not: `Le champ {fieldName} doit être différent de « {arg} »`,
    between: `Le champ {fieldName} doit être compris entre {min} et {max}`,
  },
}

let availablesRules = {
  "required": { isValid: (value) => value !== undefined && value !== null && value !== '' },
  "alpha": { isValid: (value) => value !== undefined && value !== null && /^[A-Z\u00C0-\u00FF]*$/i.test(value) },
  "alpha_num": { isValid: (value) => value !== undefined && value !== null && /^[0-9A-Z\u00C0-\u00FF]*$/i.test(value) },
  "decimal": { isValid: (value, arg) => {
    const regex = new RegExp(`^[-+]?\\d*(\\.\\d{1,${arg}})?([eE]{1}[-]?\\d+)?$`)
    return value !== undefined && value !== null && regex.test(value)
  } },
  "min": { isValid: (value, arg) => value !== undefined && value !== null && value.length >= Number.parseInt(arg) },
  "max": { isValid: (value, arg) => value !== undefined && value !== null && value.length <= Number.parseInt(arg) },
  "email": { isValid: (value) => value !== undefined && value !== null && /^\w+([.-]?\w+)*@\w+([.-]?\w+)*(\.\w{2,3})+$/.test(value) },
  "is_not": { isValid: (value, arg) => value !== undefined && value !== null && value !== arg },
  "between": {
    getArgs: (arg) => {
      const [min, max] = arg.split(',') 
      return { min, max }
    },
    isValid: (value, arg) => value !== undefined && value !== null && Number.parseInt(value) <= Number.parseInt(arg.max) && Number.parseInt(value) >= Number.parseInt(arg.min),
  },
}

export function configValidator(options) {
  if (options.rules) {
    availablesRules = {
      ...availablesRules,
      ...options.rules,
    }
  }
  if (options.locale) {
    locale.value = options.locale
  }
  if (options.i18n) {
    i18n = options.i18n
    for (const lang in messages) {
      for (const message in messages[lang]) {
        i18n.global.messages[lang][`validation.${message}`] = messages[lang][message]
      }
    }
  }
}

export function useValidator() {
  const errors = ref({})
  const validationRules = ref({})

  function addError (field, rule, arg) {
    const fieldName = i18n.global.t(`fieldnames.${field}`).replace(/^fieldnames\./, '')
    const namedParameters = (typeof arg === 'object') ? { fieldName, ...arg } : { fieldName, arg }
    const msg = (Object.prototype.hasOwnProperty.call(messages[locale.value], rule)) ? i18n.global.t(`validation.${rule}`, namedParameters) : i18n.global.t(`validation._default`, namedParameters)
    errors.value[field] = {
      message: msg,
      rule,
      fieldName,
      arg,
    }
  }

  function checkRule(field, value, rule, arg) {
    const _arg = (Object.prototype.hasOwnProperty.call(availablesRules[rule], 'getArgs')) ? availablesRules[rule].getArgs(arg) : arg
    if (!availablesRules[rule].isValid(value, _arg)) {
      addError(field, rule, _arg)
      return false
    }
    return true
  }

  function getRules (rules) {
    const allFieldRules = rules
      .split('|')
      .map(rule => {
        const ruleParts = rule.split(':')
        return {
          name: ruleParts[0],
          arg: ruleParts[1],
        }
      })
    const knownFieldRules = allFieldRules.filter(rule => Object.keys(availablesRules).includes(rule.name))
    if (knownFieldRules.length < allFieldRules.length) {
      allFieldRules.forEach(r => {
        if (!knownFieldRules.map(r => r.name).includes(r.name)) {
          console.warn(`Validation rule "${r.name}" is not defined`)
        }
      })
    }
    return knownFieldRules
  }

  function validateFieldRules (field, value) {
    delete errors.value[field]
    const rules = toRaw(unref(validationRules))
    if (Object.hasOwnProperty.call(rules, field)) {
      const fieldRules = getRules(rules[field])
      return fieldRules.every((rule) => checkRule(field, value, rule.name, rule.arg))
    }
    return true
  }

  function validateForm(submit) {
    const values = Object.fromEntries(Array.from(submit.target.elements)
      .filter(element => element.name)
      .map(element => [element.name, element.value]))
    let isvalid = true
    for (const field in toRaw(unref(validationRules))) {
      if (Object.hasOwnProperty.call(values, field)) {
        isvalid = validateFieldRules(field, values[field]) && isvalid
      }
    }
    return isvalid
  }

  function validate(e) {
    return validateFieldRules(e.target.name, e.target.value)
  }

  function resetValidation() {
    errors.value = {}
  }

  // eslint-disable-next-line no-unused-vars
  watch(locale, (newLocale) => {
    for (const field in errors.value) {
      errors.value[field]
      addError(field, errors.value[field].rule, errors.value[field].arg)
    }
  })

  return { 
    errors,
    validationRules,
    validate,
    validateForm,
    resetValidation,
  }
}
