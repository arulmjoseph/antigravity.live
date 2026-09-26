/**
 * Sobi Forms — Gutenberg block editor (vanilla wp.components).
 */
(function (wp) {
	'use strict';

	var blocks = wp.blocks;
	var element = wp.element;
	var components = wp.components;
	var blockEditor = wp.blockEditor || wp.editor;

	if (!blocks || !element || !components) {
		return;
	}

	var el = element.createElement;
	var SelectControl = components.SelectControl;
	var useBlockProps = blockEditor && blockEditor.useBlockProps
		? blockEditor.useBlockProps
		: function (props) {
			return props || {};
		};
	var forms = (window.sobiformsBlockForms && window.sobiformsBlockForms.forms) || [];

	blocks.registerBlockType('sobiforms/contact', {
		apiVersion: 3,
		title: 'Sobi Forms Contact',
		category: 'sobiforms',
		icon: 'email-alt',
		description: 'Insert a Sobi Forms form.',
		keywords: ['sobiforms', 'sobi forms', 'sobiforms', 'contact', 'email', 'formulaire'],
		attributes: {
			formId: { type: 'number', default: 0 },
		},
		supports: {
			html: false,
			multiple: true,
		},
		edit: function (props) {
			var blockProps = useBlockProps({ className: 'sobiforms-block-editor' });
			var options = [{ label: '— Select form —', value: 0 }].concat(
				forms.map(function (f) {
					return { label: f.title, value: f.id };
				})
			);

			return el(
				'div',
				blockProps,
				el(SelectControl, {
					label: 'Sobi Forms form',
					value: props.attributes.formId || 0,
					options: options,
					onChange: function (val) {
						props.setAttributes({ formId: parseInt(val, 10) || 0 });
					},
				}),
				props.attributes.formId
					? el(
						'p',
						{ style: { fontSize: '12px', color: '#666' } },
						'Shortcode: [sobiforms id="' + props.attributes.formId + '"]'
					)
					: null
			);
		},
		save: function () {
			return null;
		},
	});
})(window.wp);
