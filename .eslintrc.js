module.exports = {
    env: {
        browser: true,
        es2021: true,
        node: true,
        jquery: true
    },
    extends: [
        'eslint:recommended'
    ],
    parserOptions: {
        ecmaVersion: 12,
        sourceType: 'module'
    },
    rules: {
        'indent': ['error', 4],
        'linebreak-style': ['error', 'unix'],
        'quotes': ['error', 'single'],
        'semi': ['error', 'always'],
        'no-unused-vars': 'warn',
        'no-console': 'warn',
        'prefer-const': 'error',
        'no-var': 'error'
    },
    globals: {
        '$': 'readonly',
        'jQuery': 'readonly',
        'bootstrap': 'readonly'
    }
}; 