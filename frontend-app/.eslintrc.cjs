/* eslint-env node */
require('@rushstack/eslint-patch/modern-module-resolution');

module.exports = {
  root: true,
  'extends': [
    'plugin:vue/vue3-essential',
    'eslint:recommended'
  ],
  parserOptions: {
    ecmaVersion: 'latest'
  },
  rules: {
    // 您可以在此處添加自定義 ESLint 規則
    // 例如：'vue/multi-word-component-names': 'off',
    // 更多規則請參考：https://eslint.vuejs.org/rules/
  }
};
