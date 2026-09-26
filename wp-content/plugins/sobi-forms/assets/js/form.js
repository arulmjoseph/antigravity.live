/**
 * SobiForms — vanilla AJAX form submission (multi-form).
 */
(function () {
	'use strict';

	var nonceCache = null;
	var nonceFetchPromise = null;
	var SUBMIT_TIMEOUT_MS = 30000;

	document.addEventListener('DOMContentLoaded', function () {
		if (typeof sobiformsData === 'undefined') {
			return;
		}

		document.querySelectorAll('.sobiforms-form').forEach(function (form) {
			bindForm(form);
		});
	});

	function networkErrorMessage() {
		return sobiformsData.i18n && sobiformsData.i18n.networkError
			? sobiformsData.i18n.networkError
			: 'Network error. Please try again.';
	}

	function timeoutErrorMessage() {
		return sobiformsData.i18n && sobiformsData.i18n.timeoutError
			? sobiformsData.i18n.timeoutError
			: 'Request timed out. Please try again.';
	}

	function fetchNonce() {
		if (nonceCache) {
			return Promise.resolve(nonceCache);
		}

		if (nonceFetchPromise) {
			return nonceFetchPromise;
		}

		nonceFetchPromise = fetch(sobiformsData.ajaxUrl, {
			method: 'POST',
			body: new URLSearchParams({ action: 'sobiforms_nonce' }),
			credentials: 'same-origin',
		})
			.then(function (res) {
				return res.json();
			})
			.then(function (data) {
				if (data && data.success && data.data && data.data.nonce) {
					nonceCache = data.data.nonce;
					return nonceCache;
				}
				throw new Error('nonce');
			})
			.finally(function () {
				nonceFetchPromise = null;
			});

		return nonceFetchPromise;
	}

	function prefetchNonce() {
		if (!nonceCache && !nonceFetchPromise) {
			fetchNonce().catch(function () {
				/* fire-and-forget; submit will retry */
			});
		}
	}

	function bindForm(form) {
		var feedback = form.querySelector('.sobiforms-feedback');
		var submitBtn = form.querySelector('.sobiforms-submit');
		if (!feedback || !submitBtn) {
			return;
		}

		applyUrlPrefill(form);

		form.querySelectorAll('[data-sobiforms-maxlength], [data-sobiforms-minlength]').forEach(function (el) {
			bindLengthField(el);
		});

		form.querySelectorAll('[data-sobiforms-max-file-bytes]').forEach(function (el) {
			bindFileField(el);
		});

		form.addEventListener('mouseenter', prefetchNonce, { once: true, passive: true });
		form.addEventListener('focusin', prefetchNonce, { once: true });

		form.addEventListener('submit', function (e) {
			e.preventDefault();

			var fieldsValid = true;
			form.querySelectorAll('[data-sobiforms-maxlength], [data-sobiforms-minlength]').forEach(function (el) {
				if (!validateLengthField(el)) {
					fieldsValid = false;
				}
			});
			form.querySelectorAll('[data-sobiforms-max-file-bytes]').forEach(function (el) {
				if (!validateFileField(el)) {
					fieldsValid = false;
				}
			});

			if (!fieldsValid || !form.checkValidity()) {
				form.reportValidity();
				return;
			}

			submitBtn.disabled = true;
			feedback.hidden = true;
			feedback.className = 'sobiforms-feedback';
			feedback.textContent = '';

			function sendWithNonce(nonce) {
				var body = new FormData(form);
				body.append('action', 'sobiforms_submit');
				body.append('nonce', nonce);

				if (sobiformsData.captureSource !== false) {
					body.append('source_title', document.title);
					body.append('source_path', window.location.pathname);
				}

				var controller = new AbortController();
				var timeoutId = window.setTimeout(function () {
					controller.abort();
				}, SUBMIT_TIMEOUT_MS);

				fetch(sobiformsData.ajaxUrl, {
					method: 'POST',
					body: body,
					credentials: 'same-origin',
					signal: controller.signal,
				})
					.then(function (res) {
						return res.json().then(function (data) {
							return { ok: res.ok, data: data };
						});
					})
					.then(function (result) {
						if (result.ok && result.data.success) {
							var payload = result.data.data || {};
							if (payload.redirect_url) {
								window.location.assign(payload.redirect_url);
								return;
							}
							feedback.textContent = payload.message || '';
							feedback.hidden = false;
							feedback.className = 'sobiforms-feedback is-success';
							form.reset();
						} else {
							var msg =
								result.data.data && result.data.data.message
									? result.data.data.message
									: 'Error';
							feedback.textContent = msg;
							feedback.hidden = false;
							feedback.className = 'sobiforms-feedback is-error';
						}
					})
					.catch(function (err) {
						feedback.textContent =
							err && err.name === 'AbortError'
								? timeoutErrorMessage()
								: networkErrorMessage();
						feedback.hidden = false;
						feedback.className = 'sobiforms-feedback is-error';
					})
					.finally(function () {
						window.clearTimeout(timeoutId);
						submitBtn.disabled = false;
					});
			}

			fetchNonce()
				.then(sendWithNonce)
				.catch(function () {
					feedback.textContent = networkErrorMessage();
					feedback.hidden = false;
					feedback.className = 'sobiforms-feedback is-error';
					submitBtn.disabled = false;
				});
		});
	}

	function applyUrlPrefill(form) {
		// data-sobiforms-prefill is esc_attr( wp_json_encode() ) in PHP; dataset returns decoded JSON text.
		var raw = form.dataset.sobiformsPrefill;
		if (!raw) {
			return;
		}

		var config;
		try {
			config = JSON.parse(raw);
		} catch (err) {
			return;
		}

		if (!Array.isArray(config) || !config.length) {
			return;
		}

		var params = new URLSearchParams(window.location.search);

		config.forEach(function (field) {
			if (!field || !field.keys || !field.name) {
				return;
			}

			var value = null;
			var paramFound = false;
			for (var i = 0; i < field.keys.length; i++) {
				if (params.has(field.keys[i])) {
					value = params.get(field.keys[i]);
					paramFound = true;
					break;
				}
			}

			var el = form.querySelector('[name="' + field.name + '"]');
			if (!el) {
				return;
			}

			if (field.hidden) {
				var resolved = '';
				if (paramFound) {
					resolved = value !== null ? value : '';
				} else if (field.default !== undefined && field.default !== null) {
					resolved = String(field.default);
				}
				el.value = resolved;
				el.dispatchEvent(new Event('input', { bubbles: true }));
				return;
			}

			if (value === null || value === '') {
				return;
			}

			el.value = value;
			el.dispatchEvent(new Event('input', { bubbles: true }));
		});
	}

	function maxLengthMessage(max) {
		var template =
			sobiformsData.i18n && sobiformsData.i18n.maxLength
				? sobiformsData.i18n.maxLength
				: 'Maximum %d characters allowed.';
		return template.replace('%d', String(max));
	}

	function minLengthMessage(min) {
		var template =
			sobiformsData.i18n && sobiformsData.i18n.minLength
				? sobiformsData.i18n.minLength
				: 'Minimum %d characters required.';
		return template.replace('%d', String(min));
	}

	function setFieldError(el, msg) {
		var field = el.closest('.sobiforms-field');
		var errorEl = field ? field.querySelector('.sobiforms-field-error') : null;

		if (msg) {
			el.setCustomValidity(msg);
			if (field) {
				field.classList.add('is-invalid');
			}
			if (errorEl) {
				errorEl.textContent = msg;
				errorEl.hidden = false;
			}
			return false;
		}

		el.setCustomValidity('');
		if (field) {
			field.classList.remove('is-invalid');
		}
		if (errorEl) {
			errorEl.textContent = '';
			errorEl.hidden = true;
		}
		return true;
	}

	function validateLengthField(el) {
		var len = el.value.length;
		var max = parseInt(el.getAttribute('data-sobiforms-maxlength'), 10);
		var min = parseInt(el.getAttribute('data-sobiforms-minlength'), 10);

		if (max && max > 0 && len > max) {
			return setFieldError(el, maxLengthMessage(max));
		}

		if (min && min > 0 && len > 0 && len < min) {
			return setFieldError(el, minLengthMessage(min));
		}

		return setFieldError(el, '');
	}

	function bindLengthField(el) {
		var onValidate = function () {
			validateLengthField(el);
		};

		el.addEventListener('input', onValidate);
		el.addEventListener('blur', onValidate);
	}

	function fileTooLargeMessage(maxBytes) {
		var mb = Math.max(1, Math.round(maxBytes / 1048576));
		var template =
			sobiformsData.i18n && sobiformsData.i18n.fileTooLarge
				? sobiformsData.i18n.fileTooLarge
				: 'File must be smaller than %s.';
		return template.replace('%s', mb + ' MB');
	}

	function validateFileField(el) {
		var maxBytes = parseInt(el.getAttribute('data-sobiforms-max-file-bytes'), 10);
		if (!maxBytes || !el.files || !el.files.length) {
			return setFieldError(el, '');
		}

		if (el.files[0].size > maxBytes) {
			return setFieldError(el, fileTooLargeMessage(maxBytes));
		}

		return setFieldError(el, '');
	}

	function bindFileField(el) {
		var onValidate = function () {
			validateFileField(el);
		};

		el.addEventListener('change', onValidate);
	}
})();
