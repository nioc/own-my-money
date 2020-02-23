module.exports = {
  root: true,
  env: {
    node: true,
  },
  extends: [
    'plugin:vue/recommended',
    '@vue/standard',
    'eslint:recommended',
  ],
  rules: {
    // 'no-console': process.env.NODE_ENV === 'production' ? 'error' : 'off',
    'no-console': 'off',
    'no-debugger': process.env.NODE_ENV === 'production' ? 'error' : 'off',
    'vue/multiline-html-element-content-newline': ['error', { allowEmptyLines: true }],
    'vue/singleline-html-element-content-newline': 'off',
    'vue/max-attributes-per-line': 'off',
    'vue/array-bracket-spacing': [
      'error',
      'never',
    ],
    'array-bracket-spacing': [
      'error',
      'never',
    ],
    'vue/arrow-spacing': 'error',
    'arrow-spacing': 'error',
    semi: [
      'error',
      'never',
    ],
    'vue/block-spacing': 'error',
    'block-spacing': 'error',
    'vue/brace-style': 'error',
    'brace-style': 'error',
    'vue/camelcase': 'error',
    camelcase: 'error',
    'vue/comma-dangle': [
      'error',
      'always-multiline',
    ],
    'comma-dangle': [
      'error',
      'always-multiline',
    ],
    'vue/component-name-in-template-casing': ['error', 'kebab-case'],
    'vue/eqeqeq': 'error',
    eqeqeq: 'error',
    'vue/key-spacing': 'error',
    'key-spacing': 'error',
    'vue/object-curly-spacing': 'error',
    'object-curly-spacing': 'error',
    'vue/space-unary-ops': 'error',
    'space-unary-ops': 'error',
  },
  parserOptions: {
    parser: 'babel-eslint',
  },
}
