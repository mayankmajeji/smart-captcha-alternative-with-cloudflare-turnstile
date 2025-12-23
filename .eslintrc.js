module.exports = {
	root: true,
	parserOptions: {
		ecmaVersion: 2020,
		sourceType: 'module',
	},
	env: {
		browser: true,
		jquery: true,
		es6: true,
	},
	extends: [
		'plugin:@wordpress/eslint-plugin/recommended',
	],
	globals: {
		turnstilewp: 'readonly',
		turnstile: 'readonly',
	},
	rules: {
		// Allow console.log for debugging (remove in production)
		'no-console': 'warn',
		// Allow jQuery
		'@wordpress/no-global-event-handlers': 'off',
		// Allow inline event handlers in admin context
		'@wordpress/no-unsafe-wp-apis': 'warn',
		// Allow camelCase for WordPress admin scripts
		'camelcase': ['error', { properties: 'never' }],
		// Allow unused vars that start with underscore
		'no-unused-vars': ['error', { argsIgnorePattern: '^_' }],
	},
	ignorePatterns: [
		'node_modules/',
		'vendor/',
		'wordpress/',
		'build/',
		'dist/',
	],
};
