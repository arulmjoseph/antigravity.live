/** @type {import('tailwindcss').Config} */
module.exports = {
	important: '.sobiforms-builder-app',
	content: [ './src/admin-builder/**/*.{js,ts,jsx,tsx}' ],
	theme: {
		extend: {
			fontFamily: {
				sans: [ 'Inter', 'system-ui', '-apple-system', 'sans-serif' ],
			},
		},
	},
	corePlugins: {
		preflight: false,
	},
	plugins: [],
};
