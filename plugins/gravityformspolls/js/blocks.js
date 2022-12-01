/******/ (function(modules) { // webpackBootstrap
/******/ 	// The module cache
/******/ 	var installedModules = {};
/******/
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/
/******/ 		// Check if module is in cache
/******/ 		if(installedModules[moduleId]) {
/******/ 			return installedModules[moduleId].exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = installedModules[moduleId] = {
/******/ 			i: moduleId,
/******/ 			l: false,
/******/ 			exports: {}
/******/ 		};
/******/
/******/ 		// Execute the module function
/******/ 		modules[moduleId].call(module.exports, module, module.exports, __webpack_require__);
/******/
/******/ 		// Flag the module as loaded
/******/ 		module.l = true;
/******/
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/
/******/
/******/ 	// expose the modules object (__webpack_modules__)
/******/ 	__webpack_require__.m = modules;
/******/
/******/ 	// expose the module cache
/******/ 	__webpack_require__.c = installedModules;
/******/
/******/ 	// define getter function for harmony exports
/******/ 	__webpack_require__.d = function(exports, name, getter) {
/******/ 		if(!__webpack_require__.o(exports, name)) {
/******/ 			Object.defineProperty(exports, name, { enumerable: true, get: getter });
/******/ 		}
/******/ 	};
/******/
/******/ 	// define __esModule on exports
/******/ 	__webpack_require__.r = function(exports) {
/******/ 		if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 			Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 		}
/******/ 		Object.defineProperty(exports, '__esModule', { value: true });
/******/ 	};
/******/
/******/ 	// create a fake namespace object
/******/ 	// mode & 1: value is a module id, require it
/******/ 	// mode & 2: merge all properties of value into the ns
/******/ 	// mode & 4: return value when already ns object
/******/ 	// mode & 8|1: behave like require
/******/ 	__webpack_require__.t = function(value, mode) {
/******/ 		if(mode & 1) value = __webpack_require__(value);
/******/ 		if(mode & 8) return value;
/******/ 		if((mode & 4) && typeof value === 'object' && value && value.__esModule) return value;
/******/ 		var ns = Object.create(null);
/******/ 		__webpack_require__.r(ns);
/******/ 		Object.defineProperty(ns, 'default', { enumerable: true, value: value });
/******/ 		if(mode & 2 && typeof value != 'string') for(var key in value) __webpack_require__.d(ns, key, function(key) { return value[key]; }.bind(null, key));
/******/ 		return ns;
/******/ 	};
/******/
/******/ 	// getDefaultExport function for compatibility with non-harmony modules
/******/ 	__webpack_require__.n = function(module) {
/******/ 		var getter = module && module.__esModule ?
/******/ 			function getDefault() { return module['default']; } :
/******/ 			function getModuleExports() { return module; };
/******/ 		__webpack_require__.d(getter, 'a', getter);
/******/ 		return getter;
/******/ 	};
/******/
/******/ 	// Object.prototype.hasOwnProperty.call
/******/ 	__webpack_require__.o = function(object, property) { return Object.prototype.hasOwnProperty.call(object, property); };
/******/
/******/ 	// __webpack_public_path__
/******/ 	__webpack_require__.p = "";
/******/
/******/
/******/ 	// Load entry module and return exports
/******/ 	return __webpack_require__(__webpack_require__.s = "./js/src/index.js");
/******/ })
/************************************************************************/
/******/ ({

/***/ "./js/src/blocks/polls/block.scss":
/*!****************************************!*\
  !*** ./js/src/blocks/polls/block.scss ***!
  \****************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

// extracted by mini-css-extract-plugin

/***/ }),

/***/ "./js/src/blocks/polls/edit.js":
/*!*************************************!*\
  !*** ./js/src/blocks/polls/edit.js ***!
  \*************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _block_scss__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./block.scss */ "./js/src/blocks/polls/block.scss");
/* harmony import */ var _block_scss__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_block_scss__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _icon__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./icon */ "./js/src/blocks/polls/icon.js");
/**
 * WordPress dependencies
 */
const { __ } = wp.i18n;
const { Dashicon, DatePicker, Dropdown, PanelBody, Placeholder, SelectControl, ServerSideRender, TextControl, ToggleControl } = wp.components;
const { dateI18n } = wp.date;
const { InspectorControls } = wp.hasOwnProperty('blockEditor') ? wp.blockEditor : wp.editor;
const { Component, Fragment } = wp.element;

/**
 * Internal dependencies
 */



class Edit extends Component {

	/**
  * Initialize Edit class.
  *
  * Bind setFormId() function.
  * Reset form preview object if selected form has conditional logic.
  *
  * @since 3.3
  */
	constructor() {

		super(...arguments);

		this.setFormId = this.setFormId.bind(this);

		let form = Edit.getForm(this.props.attributes.formId);
		if (form && form.hasConditionalLogic) {
			this.props.setAttributes({ formPreview: false });
		}
	}

	/**
  * Set the selected form ID.
  *
  * @since 3.3
  *
  * @param {integer} formId Selected form ID.
  */
	setFormId(formId) {

		let form = Edit.getForm(formId);

		this.props.setAttributes({ formId: formId });

		if (form && form.hasConditionalLogic) {
			this.props.setAttributes({ formPreview: false });
		}
	}

	/**
  * Get specific form object.
  *
  * @since  3.3
  * @static
  *
  * @param {integer} formId ID of form to return.
  *
  * @returns {Object} Form object.
  */
	static getForm(formId) {

		return gform_block_polls.forms.find(form => form.id == formId);
	}

	/**
  * Prepare available form objects as select options.
  *
  * @since  3.3
  * @static
  *
  * @returns {{label: *, value: string}[]}
  */
	static getFormOptions() {

		let options = [{
			label: __('Select a Form', 'gravityformspolls'),
			value: ''
		}];

		for (let i = 0; i < gform_block_polls.forms.length; i++) {
			options.push({
				label: gform_block_polls.forms[i].title,
				value: gform_block_polls.forms[i].id
			});
		}

		return options;
	}

	render() {

		let { formId, title, description, mode, ajax, tabindex, style, cookie, cookieDate, displayCounts, displayResultsLink, displayResultsOnSubmission, displayPercentages, formPreview } = this.props.attributes;

		const { setAttributes, isSelected } = this.props;

		const toggleTitle = () => setAttributes({ title: !title });
		const toggleDescription = () => setAttributes({ description: !description });
		const toggleAjax = () => setAttributes({ ajax: !ajax });
		const toggleFormPreview = () => setAttributes({ formPreview: !formPreview });

		const updateTabindex = tabindex => setAttributes({ tabindex: tabindex });

		const setFormIdFromPlaceholder = e => this.setFormId(e.target.value);

		const cookieOptions = [{
			label: __('Do Not Block', 'gravityformspolls'),
			value: ''
		}, {
			label: __('1 vote per day', 'gravityformspolls'),
			value: '1 day'
		}, {
			label: __('1 vote per week', 'gravityformspolls'),
			value: '1 week'
		}, {
			label: __('1 vote per month', 'gravityformspolls'),
			value: '1 month'
		}, {
			label: __('1 vote before specific date', 'gravityformspolls'),
			value: 'date'
		}];

		const controls = [isSelected && gform_block_polls.forms && gform_block_polls.forms.length > 0 && React.createElement(
			InspectorControls,
			{ key: 'inspector' },
			React.createElement(
				PanelBody,
				{
					title: __('Form Settings', 'gravityformspolls')
				},
				React.createElement(SelectControl, {
					label: __('Form', 'gravityformspolls'),
					value: formId,
					options: Edit.getFormOptions(),
					onChange: this.setFormId
				}),
				formId && React.createElement(SelectControl, {
					label: __('Mode', 'gravityformspolls'),
					value: mode,
					options: [{
						label: __('Poll', 'gravityformspolls'),
						value: 'poll'
					}, {
						label: __('Results', 'gravityformspolls'),
						value: 'results'
					}],
					onChange: mode => setAttributes({ mode })
				}),
				formId && 'poll' === mode && React.createElement(ToggleControl, {
					label: __('Form Title', 'gravityformspolls'),
					checked: title,
					onChange: toggleTitle
				}),
				formId && 'poll' === mode && React.createElement(ToggleControl, {
					label: __('Form Description', 'gravityformspolls'),
					checked: description,
					onChange: toggleDescription
				})
			),
			formId && React.createElement(
				PanelBody,
				{
					title: __('Appearance', 'gravityformspolls'),
					initialOpen: true,
					className: 'gform-block__panel'
				},
				React.createElement(SelectControl, {
					label: __('Style', 'gravityformspolls'),
					value: style,
					options: [{
						label: __('Green', 'gravityformspolls'),
						value: 'green'
					}, {
						label: __('Red', 'gravityformspolls'),
						value: 'red'
					}, {
						label: __('Orange', 'gravityformspolls'),
						value: 'orange'
					}, {
						label: __('Blue', 'gravityformspolls'),
						value: 'blue'
					}],
					onChange: style => setAttributes({ style })
				}),
				React.createElement(ToggleControl, {
					label: __('Display Results Percentages', 'gravityformspolls'),
					checked: displayPercentages,
					onChange: () => setAttributes({ displayPercentages: !displayPercentages })
				}),
				React.createElement(ToggleControl, {
					label: __('Display Number of Votes For Each Selection', 'gravityformspolls'),
					checked: displayCounts,
					onChange: () => setAttributes({ displayCounts: !displayCounts })
				}),
				'poll' === mode && React.createElement(ToggleControl, {
					label: __('Display Link To Results Below Form', 'gravityformspolls'),
					checked: displayResultsLink,
					onChange: () => setAttributes({ displayResultsLink: !displayResultsLink })
				}),
				'poll' === mode && React.createElement(ToggleControl, {
					label: __('Display Results Upon Submission', 'gravityformspolls'),
					checked: displayResultsOnSubmission,
					onChange: () => setAttributes({ displayResultsOnSubmission: !displayResultsOnSubmission })
				})
			),
			formId && React.createElement(
				PanelBody,
				{
					title: __('Advanced', 'gravityformspolls'),
					initialOpen: true,
					className: 'gform-block__panel'
				},
				formId && !Edit.getForm(formId).hasConditionalLogic && React.createElement(ToggleControl, {
					label: __('Preview', 'gravityformspolls'),
					checked: formPreview,
					onChange: toggleFormPreview
				}),
				'poll' === mode && React.createElement(ToggleControl, {
					label: __('AJAX', 'gravityformspolls'),
					checked: ajax,
					onChange: toggleAjax
				}),
				'poll' === mode && React.createElement(TextControl, {
					label: __('Tabindex', 'gravityformspolls'),
					value: tabindex,
					onChange: updateTabindex,
					placeholder: '-1'
				}),
				'date' !== cookie && React.createElement(SelectControl, {
					label: __('Repeat Voters', 'gravityformspolls'),
					value: cookie || '',
					options: cookieOptions,
					onChange: cookie => setAttributes({ cookie }),
					className: 'gform-block-poll__cookie'
				}),
				'date' === cookie && React.createElement(
					'div',
					{ className: 'components-base-control' },
					React.createElement(
						'label',
						{ className: 'components-base-control__label' },
						__('Repeat Voters', 'gravityformspolls')
					),
					React.createElement(Dropdown, {
						key: `gform-block-poll__cookie-date`,
						position: 'bottom left',
						contentClassName: 'gform-block-poll__cookie-date-popover',
						className: 'gform-block-poll__cookie-date',
						renderToggle: ({ onToggle, isOpen }) => React.createElement(
							Fragment,
							null,
							React.createElement(SelectControl, {
								value: cookie,
								options: cookieOptions,
								onChange: cookie => setAttributes({ cookie, cookieDate: null })
							}),
							React.createElement(
								'button',
								{
									type: 'button',
									className: 'button-link',
									onClick: onToggle,
									'aria-expanded': isOpen
								},
								cookieDate ? dateI18n('F j, Y', cookieDate) : __('Select a Date', 'gravityformspolls')
							)
						),
						renderContent: () => [React.createElement(DatePicker, {
							key: 'date-picker',
							currentDate: cookieDate,
							onChange: cookieDate => setAttributes({ cookieDate })
						})]
					})
				),
				React.createElement(
					'div',
					{ className: 'components-base-control' },
					React.createElement(
						'p',
						null,
						__('Form ID:', 'gravityformspolls'),
						React.createElement('br', null),
						formId
					)
				)
			)
		)];

		if (!formId || !formPreview || formId && Edit.getForm(formId).hasConditionalLogic) {

			return [controls, React.createElement(
				Placeholder,
				{ key: 'placeholder', className: 'wp-block-embed gform-block__placeholder' },
				React.createElement(
					'div',
					{ className: 'gform-block__placeholder-brand' },
					React.createElement(
						'div',
						{ className: 'gform-icon' },
						_icon__WEBPACK_IMPORTED_MODULE_1__["icon_full"]
					),
					React.createElement(
						'p',
						null,
						React.createElement(
							'strong',
							null,
							'Gravity Forms + Polls'
						)
					)
				),
				gform_block_polls.forms && gform_block_polls.forms.length > 0 && React.createElement(
					'form',
					null,
					React.createElement(
						'select',
						{ value: formId, onChange: setFormIdFromPlaceholder },
						Edit.getFormOptions().map(form => React.createElement(
							'option',
							{ key: form.value, value: form.value },
							form.label
						))
					)
				),
				(!gform_block_polls.forms || gform_block_polls.forms && gform_block_polls.forms.length === 0) && React.createElement(
					'form',
					null,
					React.createElement(
						'p',
						null,
						__('You must have at least one Polls-enabled form to use the block.', 'gravityformspolls')
					)
				)
			)];
		}

		return [controls, React.createElement(ServerSideRender, {
			key: 'form_preview',
			block: 'gravityforms/polls',
			attributes: this.props.attributes
		})];
	}

}

/* harmony default export */ __webpack_exports__["default"] = (Edit);

/***/ }),

/***/ "./js/src/blocks/polls/icon.js":
/*!*************************************!*\
  !*** ./js/src/blocks/polls/icon.js ***!
  \*************************************/
/*! exports provided: icon_compact, icon_full */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "icon_compact", function() { return icon_compact; });
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "icon_full", function() { return icon_full; });
const icon_compact = React.createElement(
	'svg',
	{ xmlns: 'http://www.w3.org/2000/svg', viewBox: '0 0 20 22', width: '100%', height: '100%',
		focusable: 'false', 'aria-hidden': 'true',
		className: 'dashicon dashicon-gravityforms-polls' },
	React.createElement(
		'g',
		null,
		React.createElement(
			'g',
			null,
			React.createElement('path', { className: 'st0', d: 'm19.754 7.04c0 0.062857 0 0.094286 0.030769 0.12571v7.4171 0.22 0.031429c-0.030769 0.062857-0.030769 0.094286-0.030769 0.15714-0.030769 0.031428-0.030769 0.062857-0.030769 0.094286-0.030769 0.031429-0.030769 0.062857-0.030769 0.094286 0 0.062857-0.030769 0.094286-0.030769 0.15714v0.031428c-0.092308 0.44-0.30769 0.88-0.55385 1.2571-0.030769 0-0.030769 0.031429-0.030769 0.031429-0.030769 0.062857-0.061538 0.094286-0.092308 0.15714-0.030769 0.031429-0.030769 0.031429-0.061538 0.062857-0.030769 0.031428-0.061538 0.062857-0.092308 0.094286-0.030769 0.031428-0.061538 0.062857-0.092308 0.094286-0.030769 0.031429-0.030769 0.031429-0.061538 0.062857-0.030769 0.062857-0.092308 0.094286-0.12308 0.12571 0 0-0.030769 0.031429-0.030769 0.031429-0.061538 0.031429-0.092308 0.094286-0.15385 0.12571-0.092308 0.094286-0.21538 0.18857-0.33846 0.25143l-0.18462 0.12571-6.1538 3.5829c-0.46154 0.28286-1.0769 0.44-1.7231 0.44-0.64615 0-1.2615-0.15714-1.7231-0.44l-1.0462-0.62857-5.2308-3.08s-0.030769 0-0.061538-0.031428c-0.89231-0.59714-1.6-1.8229-1.6308-2.8914v-0.12571-7.4171-0.22-0.031429c0.030769-0.062857 0.030769-0.094286 0.030769-0.15714 0.030769-0.031429 0.030769-0.062857 0.030769-0.094286 0.030769-0.031429 0.030769-0.062857 0.030769-0.094286 0-0.062857 0.030769-0.094286 0.030769-0.15714v-0.031429c0.092308-0.44 0.30769-0.88 0.55385-1.2571 0.030769 0 0.030769-0.031429 0.030769-0.031429 0.030769-0.062857 0.061538-0.094286 0.092308-0.15714 0.030769-0.031429 0.030769-0.031429 0.061538-0.062857s0.061538-0.062857 0.092308-0.094286 0.061538-0.062857 0.092308-0.094286c0.030769 0 0.030769-0.031429 0.061538-0.031429 0.030769-0.062857 0.092308-0.094286 0.12308-0.12571 0 0 0.030769-0.031429 0.030769-0.031429 0.061538-0.031429 0.092308-0.094286 0.15385-0.12571 0.092308-0.094286 0.21538-0.18857 0.33846-0.25143l0.18462-0.12571 6.0923-3.5829c0.46154-0.28286 1.0769-0.44 1.7231-0.44 0.64615 0 1.2615 0.15714 1.7538 0.40857l1.0462 0.62857 5.2308 3.08s0.030769 0 0.061538 0.031429c0.89231 0.59714 1.6 1.8229 1.6308 2.8914z' }),
			React.createElement('polygon', { points: '9.6154 15.486 5 15.486 5 9.8286 9.6154 9.8286', fill: '#fff' }),
			React.createElement('polygon', { points: '15.154 14.375 15.154 14.375 15.154 15.486 12.806 15.486 12.806 15.486 10.538 15.486 10.538 7 15.154 7', fill: '#fff' })
		)
	)
);

const icon_full = React.createElement(
	'svg',
	{ xmlns: 'http://www.w3.org/2000/svg', viewBox: '0 0 65 70', width: '100%', height: '100%',
		focusable: 'false', 'aria-hidden': 'true',
		className: 'dashicon dashicon-gravityforms-polls' },
	React.createElement(
		'g',
		null,
		React.createElement(
			'g',
			null,
			React.createElement('path', { className: 'st0', d: 'm64.2 22.4c-0.1-3.4-2.4-7.3-5.3-9.2-0.1-0.1-0.2-0.1-0.2-0.1l-17-9.8-3.4-2c-1.6-0.8-3.6-1.3-5.7-1.3s-4.1 0.5-5.6 1.4l-19.8 11.4-0.6 0.4c-0.4 0.2-0.8 0.5-1.1 0.8-0.2 0.1-0.3 0.3-0.5 0.4l-0.1 0.1c-0.1 0.1-0.3 0.2-0.4 0.4-0.1 0-0.1 0.1-0.2 0.1l-0.3 0.3-0.3 0.3-0.2 0.2c-0.1 0.2-0.2 0.3-0.3 0.5 0 0 0 0.1-0.1 0.1-0.8 1.2-1.5 2.6-1.8 4v0.1c0 0.2-0.1 0.3-0.1 0.5 0 0.1 0 0.2-0.1 0.3 0 0.1 0 0.2-0.1 0.3 0 0.2 0 0.3-0.1 0.5v0.1 0.7 23.6 0.4c0.1 3.4 2.4 7.3 5.3 9.2 0.1 0.1 0.2 0.1 0.2 0.1l17 9.8 3.4 2c1.5 0.9 3.5 1.4 5.6 1.4s4.1-0.5 5.6-1.4l20-11.4 0.6-0.4c0.4-0.2 0.8-0.5 1.1-0.8 0.2-0.1 0.3-0.3 0.5-0.4l0.1-0.1c0.1-0.1 0.3-0.2 0.4-0.4l0.2-0.2 0.3-0.3 0.3-0.3 0.2-0.2c0.1-0.2 0.2-0.3 0.3-0.5 0 0 0-0.1 0.1-0.1 0.8-1.2 1.5-2.6 1.8-4v-0.1c0-0.2 0.1-0.3 0.1-0.5 0-0.1 0-0.2 0.1-0.3 0-0.1 0-0.2 0.1-0.3 0-0.2 0-0.3 0.1-0.5v-0.1-0.7-23.6c-0.1-0.1-0.1-0.2-0.1-0.4z' }),
			React.createElement('path', { className: 'st0', d: 'm60.4 44.9c0 2.4-1.8 5.5-3.9 6.7l-20.4 11.8c-1 0.6-2.4 0.9-3.9 0.9s-2.9-0.3-3.9-0.9l-20.4-11.7c-1.6-0.9-3-2.8-3.6-4.8-0.2-0.6-0.3-1.3-0.3-1.9v-23.6c0-2.4 1.8-5.5 3.9-6.7l20.4-11.8c1-0.6 2.4-0.9 3.9-0.9s2.9 0.3 3.9 0.9l20.4 11.8c1.6 0.9 3 2.8 3.6 4.8 0.2 0.6 0.3 1.3 0.3 1.9v23.5z' }),
			React.createElement(
				'g',
				{ transform: 'translate(16 14)', fill: '#fff' },
				React.createElement('path', { d: 'm11.852 29.28h-10.074c-0.59259 0-1.0667 0.48-1.0667 1.08v10.2c0 0.6 0.47407 1.08 1.0667 1.08h10.074c0.59259 0 1.0667-0.48 1.0667-1.08v-10.2c0-0.6-0.47407-1.08-1.0667-1.08zm-1.0667 10.32h-8.0593v-8.16h8.0593v8.16z' }),
				React.createElement('path', { d: 'm28.8 29.28h-10.074c-0.59259 0-1.0667 0.48-1.0667 1.08v10.2c0 0.6 0.47407 1.08 1.0667 1.08h10.074c0.59259 0 1.0667-0.48 1.0667-1.08v-10.2c0-0.6-0.47407-1.08-1.0667-1.08zm-0.94815 10.32h-8.0593v-8.16h8.0593v8.16z' }),
				React.createElement('polygon', { points: '9.9556 34.08 8.8889 32.88 6.0444 35.76 4.7407 34.44 3.5556 35.52 6.0444 38.04' }),
				React.createElement('path', { d: 'm21.096 37.2v0.12l0.82963 0.84h0.11852l1.6593-1.68h0.11852l1.6593 1.68h0.11852l0.82963-0.84v-0.12l-1.6593-1.68v-0.12l1.6593-1.68v-0.12l-0.82963-0.84h-0.11852l-1.6593 1.68h-0.11852l-1.6593-1.68h-0.11852l-0.82963 0.84v0.12l1.6593 1.68v0.12l-1.6593 1.68z' }),
				React.createElement('polygon', { points: '13.63 25.68 0 25.68 0 8.88 13.63 8.88' }),
				React.createElement('polygon', { points: '31.052 22.32 31.052 22.32 31.052 25.68 24.059 25.68 24.059 25.68 17.304 25.68 17.304 4.6185e-14 31.052 4.6185e-14' })
			)
		)
	)
);



/***/ }),

/***/ "./js/src/blocks/polls/index.js":
/*!**************************************!*\
  !*** ./js/src/blocks/polls/index.js ***!
  \**************************************/
/*! no exports provided */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _edit__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./edit */ "./js/src/blocks/polls/edit.js");
/* harmony import */ var _icon__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./icon */ "./js/src/blocks/polls/icon.js");
/**
 * WordPress dependencies
 */
const { __ } = wp.i18n;
const { registerBlockType } = wp.blocks;

/**
 * Internal dependencies
 */



const blockType = 'gravityforms/polls';

registerBlockType(blockType, {
	title: 'Poll',
	description: __('Display a poll or poll results on your page.', 'gravityformspolls'),
	category: 'embed',
	keywords: ['gravity forms', 'form', 'polls'],
	supports: {
		customClassName: false,
		className: false,
		html: false
	},
	attributes: {
		formId: {
			type: 'string'
		},
		mode: {
			type: 'string',
			default: 'poll'
		},
		title: {
			type: 'boolean',
			default: true
		},
		description: {
			type: 'boolean',
			default: true
		},
		ajax: {
			type: 'boolean',
			default: false
		},
		tabindex: {
			type: 'string'
		},
		style: {
			type: 'string',
			default: 'green'
		},
		cookie: {
			type: 'string',
			default: null
		},
		cookieDate: {
			type: 'string'
		},
		displayCounts: {
			type: 'boolean',
			default: true
		},
		displayResultsLink: {
			type: 'boolean',
			default: true
		},
		displayResultsOnSubmission: {
			type: 'boolean',
			default: true
		},
		displayPercentages: {
			type: 'boolean',
			default: true
		},
		formPreview: {
			type: 'boolean',
			default: true
		}
	},
	icon: _icon__WEBPACK_IMPORTED_MODULE_1__["icon_compact"],
	edit: _edit__WEBPACK_IMPORTED_MODULE_0__["default"],
	save() {
		return null;
	}
});

/***/ }),

/***/ "./js/src/index.js":
/*!*************************!*\
  !*** ./js/src/index.js ***!
  \*************************/
/*! no exports provided */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _blocks_polls_index_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./blocks/polls/index.js */ "./js/src/blocks/polls/index.js");


/***/ })

/******/ });
//# sourceMappingURL=blocks.js.map