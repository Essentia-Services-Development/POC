import _ from 'underscore';
import $ from 'jquery';
import { observer, hooks, modules, Promise } from 'peepso';
import { userid as USER_ID, sections } from 'peepsodata';

const INPUT_SELECTOR = 'input[type=text],input[type=checkbox],input[type=radio],textarea,select';
const SAVE_ALL_ERROR_NOTICE =
	sections && sections.profile && sections.profile.textSaveAllErrorNotice;

class SectionProfileAbout {
	constructor(container) {
		this.$container = $(container);
		this.$btnEditAll = this.$container.find('.ps-js-btn-edit-all');
		this.$btnSaveAll = this.$container.find('.ps-js-btn-save-all').hide();

		this.$fields = this.$container
			.find('.ps-js-profile-item')
			// Exclude separator fields.
			.filter(function () {
				let isSeparator = $(this).find('.ps-js-profile-separator').length;
				return !isSeparator;
			});

		// Hide the editAll button if no field exists.
		if (!this.$fields.length) {
			this.$btnEditAll.hide();
		}

		// Initialize existing fields.
		this.$fields.each((i, field) => this.fieldInit(field));

		// Handle beforeunload event.
		this.fieldChanged = [];
		this.fieldChangedHandler = showNotice => (this.fieldChanged.length ? true : showNotice);
		observer.addFilter('beforeunload', this.fieldChangedHandler, 10, 1);

		// Handle events.
		this.$btnEditAll.on('click', () => this.fieldsEditAll());
		this.$btnSaveAll.on('click', () => this.fieldsSaveAll());
	}

	/**
	 * Initialize field functionality.
	 *
	 * @param {HTMLElement} field
	 */
	fieldInit(field) {
		$(field)
			.on('click', '.ps-js-btn-edit', () => this.fieldEdit(field))
			.on('click', '.ps-js-btn-cancel', () => this.fieldEditCancel(field))
			.on('click', '.ps-js-btn-save', () => this.fieldEditSave(field));
	}

	/**
	 * Get available field data.
	 *
	 * @param {HTMLElement} descendantElement
	 * @returns {Object}
	 */
	fieldGetData(descendantElement) {
		let $field = $(descendantElement).closest('.ps-js-profile-item'),
			data;

		if ($field.length) {
			data = $field.data();

			if (!data.$field) {
				let $form = $field.find('.ps-list-info-content-form'),
					$inputs = $form.find(INPUT_SELECTOR);

				data = {
					$field,
					$label: $field.find('.ps-list-info-content-text'),
					$form: $field.find('.ps-list-info-content-form'),
					$error: $field.find('.ps-list-info-content-error'),
					$validation: $field.find('.ps-js-validation'),
					$inputs,
					$btnEdit: $field.find('.ps-js-btn-edit'),
					$btnCancel: $form.find('.ps-js-btn-cancel'),
					$btnSave: $form.find('.ps-js-btn-save'),
					id: $inputs.data('id'),
					type: this.fieldGetType($inputs),
					value: this.fieldGetValue($inputs)
				};

				$field.data(data);
			}
		}

		return data;
	}

	/**
	 * Get the type of the field input set.
	 *
	 * @param {JQuery} $inputs
	 * @return {string}
	 */
	fieldGetType($inputs) {
		let $input = $inputs.eq(0),
			tagName = $input.prop('tagName').toLowerCase(),
			type = $input.attr('type') || '';

		if (['select', 'textarea'].indexOf(tagName) > -1) {
			type = tagName;
		} else if (type === 'text' && $input.hasClass('datepicker')) {
			type = 'datepicker';
		}

		return type;
	}

	/**
	 * Get the submittable value from the field input set.
	 *
	 * @param {JQuery} $inputs
	 * @return {string|Object}
	 */
	fieldGetValue($inputs) {
		let type = this.fieldGetType($inputs),
			value,
			formatted;

		if (type === 'checkbox') {
			value = $inputs.filter(':checked').map((i, input) => input.value);
			value = JSON.stringify([...value]);
		} else if (type === 'radio') {
			value = $inputs.filter(':checked').val();
		} else if (type === 'datepicker') {
			formatted = $inputs.val();
			value = $inputs.data('value');
			value = { value, formatted };
		} else if (type === 'text') {
			value = $inputs.val().trim();
		} else {
			value = $inputs.val();
		}

		return value;
	}

	/**
	 * Set the field input set state from the submittable value.
	 *
	 * @param {JQuery} $inputs
	 * @param {string|Object} value
	 */
	fieldSetValue($inputs, value) {
		let type = this.fieldGetType($inputs);

		if (type === 'checkbox') {
			value = JSON.parse(value);
			$inputs.each((i, input) => {
				input.checked = value.indexOf(input.value) > -1;
			});
		} else if (type === 'radio') {
			$inputs.each((i, input) => {
				input.checked = input.value === value;
			});
		} else if (type === 'datepicker') {
			$inputs.val(value.formatted);
			$inputs.data('value', value.value);
		} else {
			$inputs.val(value);
		}
	}

	/**
	 * Start editing and show field editor form.
	 *
	 * @param {HTMLElement} descendantElement
	 * @param {boolean} [noFocus=false]
	 */
	fieldEdit(descendantElement, noFocus = false) {
		let fieldData = this.fieldGetData(descendantElement),
			{ $label, $form, $error, $validation, $inputs, id, type } = fieldData;

		// Handle Enter key.
		if (type === 'text') {
			$inputs.off('keydown').on('keydown', e => {
				if (e.keyCode === 13) {
					e.preventDefault();
					e.stopPropagation();
					this.fieldEditSave(e.target);
				}
			});
		}

		// Initialize character counter on field input if applicable.
		let counterUpdater = () => {};
		if (['text', 'textarea'].indexOf(type) > -1) {
			let $counter = $inputs.next('.ps-js-counter');
			if ($counter.length) {
				$counter.show();
				counterUpdater = _.throttle(len => $counter.html(len), 500);
				counterUpdater($inputs.val().length);
			}
		}

		// Handle input event.
		$inputs.off('input.ps-field').on('input.ps-field', e => {
			if (this.fieldChanged.indexOf(id) === -1) {
				this.fieldChanged.push(id);
			}

			counterUpdater(e.target.value.length);
		});

		$label.hide();
		$form.show();
		$error.hide();
		$validation.addClass('ps-alert--neutral').removeClass('ps-alert--abort');

		// Should focus if its not triggered by the `editAll` button.
		if (!noFocus) {
			if (['checkbox', 'radio'].indexOf(type) === -1) {
				$inputs.focus();
			}
		}
	}

	/**
	 * Cancel editing and hide field editor form.
	 *
	 * @param {HTMLElement} descendantElement
	 */
	fieldEditCancel(descendantElement) {
		let fieldData = this.fieldGetData(descendantElement),
			{ $label, $form, $error, $inputs, id, value } = fieldData;

		this.fieldSetValue($inputs, value);

		$label.show();
		$form.hide();
		$error.hide();

		// Remove field from marked as changed.
		let index = this.fieldChanged.indexOf(id);
		if (index > -1) {
			this.fieldChanged.splice(index, 1);
		}
	}

	/**
	 * Finalize editing and hide field editor form.
	 *
	 * @param {HTMLElement} descendantElement
	 * @returns {Promise}
	 */
	fieldEditSave(descendantElement) {
		let fieldData = this.fieldGetData(descendantElement),
			{ $field, $label, $form, $error, $inputs, $btnCancel, $btnSave, id, type } = fieldData,
			$loading = $btnSave.find('img'),
			value = this.fieldGetValue($inputs);

		$inputs.attr('disabled', 'disabled');
		$btnSave.add($btnCancel).attr('disabled', 'disabled');
		$loading.show();

		// Filter datepicker value.
		if (type === 'datepicker') {
			value = value.value;
		}

		// Apply filters.
		value = observer.applyFilters('profile_field_save', value, $inputs);

		// Update field success handler.
		let onSuccess = data => {
			let { display_value: displayValue } = data,
				$labelContent = $label.find('.ps-list-info-content-data');

			// Update cached value.
			fieldData.value = value;

			// Update label.
			if (typeof displayValue !== 'undefined') {
				displayValue = observer.applyFilters('peepso_parse_content', displayValue);
				$labelContent.html(displayValue);
			} else if (type === 'select') {
				$labelContent.html($inputs.find('option:selected').text());
			} else {
				$labelContent.html($inputs.val());
			}

			$label.show();
			$form.hide();
			$error.hide();

			// Highlight field.
			$field.addClass('ps-list-info-success');
			setTimeout(() => $field.removeClass('ps-list-info-success'), 1000);

			observer.doAction('profile_field_updated', fieldData, data);
			hooks.doAction('profile_completeness_updated', USER_ID, data);

			// Remove field from marked as changed.
			let index = this.fieldChanged.indexOf(id);
			if (index > -1) {
				this.fieldChanged.splice(index, 1);
			}
		};

		// Update field error handler.
		let onError = error => {
			let { $validation } = fieldData,
				errors = [];

			$validation.addClass('ps-alert--neutral').removeClass('ps-alert--abort');

			if (error) {
				for (let prop in error) {
					let $error = $form.find('.ps-js-validation-' + prop);
					if ($error.length) {
						$error.addClass('ps-alert--abort').removeClass('ps-alert--neutral');
					} else {
						errors.push(error[prop]);
					}
				}
			}

			// Update default error container.
			if (errors.length) {
				errors = errors.join('<br>');
				$error.html(errors).show();
			} else {
				$error.hide();
			}

			// Highlight field.
			$field.addClass('ps-list-info-error');
			setTimeout(() => $field.removeClass('ps-list-info-error'), 1000);
		};

		// Update field finally handler.
		let onFinally = () => {
			$inputs.removeAttr('disabled');
			$btnSave.add($btnCancel).removeAttr('disabled');
			$loading.hide();
		};

		return new Promise((resolve, reject) => {
			let hasError = false;

			// Update field.
			modules.user
				.updateField(USER_ID, id, value)
				.then(onSuccess)
				.catch(error => {
					onError(error);
					hasError = true;
				})
				.then(() => {
					onFinally();
					hasError ? reject($field[0]) : resolve();
				});
		});
	}

	/**
	 * Handle edit all behavior.
	 */
	fieldsEditAll() {
		this.$btnEditAll.hide();
		this.$btnSaveAll.show();

		this.$fields.each((i, field) => {
			this.fieldEdit(field, 'nofocus');
		});
	}

	/**
	 * Handle save all behavior.
	 */
	fieldsSaveAll() {
		this.$btnSaveAll.attr('disabled', 'disabled');

		let promises = [];
		this.$fields.each((i, field) => {
			promises.push(this.fieldEditSave(field));
		});

		// https://davidwalsh.name/promises-results#comment-510381
		Promise.all(promises.map(p => p.catch(error => error))).then(result => {
			let errorFields = result.filter(field => field);

			this.$btnSaveAll.removeAttr('disabled');

			if (errorFields.length) {
				// Center element in the viewport.
				let scrollTop = $(errorFields[0]).offset().top - window.innerHeight / 2;
				let onComplete = _.debounce(() => alert(SAVE_ALL_ERROR_NOTICE), 200);
				$('html, body').animate({ scrollTop }, 1000, onComplete);
			} else {
				this.$btnSaveAll.hide();
				this.$btnEditAll.show();
			}
		});
	}
}

export default {
	init() {
		let sections = document.querySelectorAll('[data-ps-section="profile/about"]');
		if (sections.length) {
			[...sections].forEach(section => new SectionProfileAbout(section));
		}
	}
};
