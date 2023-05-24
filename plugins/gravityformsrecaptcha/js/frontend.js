/******/ (function() { // webpackBootstrap
/******/ 	var __webpack_modules__ = ({

/***/ "./js/src/frontend.js":
/*!****************************!*\
  !*** ./js/src/frontend.js ***!
  \****************************/
/***/ (function() {

var _this = this;

/* global jQuery, gform, gforms_recaptcha_recaptcha_strings, grecaptcha */
(function ($, gform, grecaptcha, strings) {
  /**
   * Make the API request to Google to get the reCAPTCHA token right before submission.
   *
   * @since 1.0
   *
   * @param {Object} e The event object.
   * @return {void}
   */
  var getToken = function getToken(e) {
    var form = $(e.data.form);
    var recaptchaField = form.find('.ginput_recaptchav3');
    var dataInput = recaptchaField.find('.gfield_recaptcha_response');

    if (!dataInput.length || dataInput.val().length) {
      return;
    }

    e.preventDefault();
    grecaptcha.ready(function () {
      grecaptcha.execute(strings.site_key, {
        action: 'submit'
      }).then(function (token) {
        if (token.length && typeof token === 'string') {
          dataInput.val(token);
        }

        form.submit();
      });
    });
  };
  /**
   * Add event listeners to the form.
   *
   * @since 1.0
   *
   * @param {string|number} formId The numeric ID of the form.
   * @return {void}
   */


  var addFormEventListeners = function addFormEventListeners(formId) {
    var $form = $("#gform_".concat(formId, ":not(.recaptcha-v3-initialized)"));
    var $submit = $form.find("#gform_submit_button_".concat(formId));
    $form.on('submit', {
      form: $form
    }, getToken);
    $submit.on('click', {
      form: $form
    }, getToken);
    $form.addClass('recaptcha-v3-initialized');
  };
  /**
   * The reCAPTCHA handler.
   *
   * @since 1.0
   *
   * @return {void}
   */


  var gfRecaptcha = function gfRecaptcha() {
    var self = _this;
    /**
     * Initialize the Recaptcha handler.
     *
     * @since 1.0
     *
     * @return {void}
     */

    self.init = function () {
      self.elements = {
        formIds: self.getFormIds()
      };
      self.addEventListeners();
    };
    /**
     * Get an array of form IDs.
     *
     * @since 1.0
     *
     * @return {Array} Array of form IDs.
     */


    self.getFormIds = function () {
      var ids = [];
      $('form').each(function (index) {
        ids.push($('form').get(index).id.split('gform_')[1]);
      });
      return ids;
    };
    /**
     * Add event listeners to the page.
     *
     * @since 1.0
     *
     * @return {void}
     */


    self.addEventListeners = function () {
      self.elements.formIds.forEach(function (formId) {
        addFormEventListeners(formId);
      });
      $(document).on('gform_post_render', function (event, formId) {
        addFormEventListeners(formId);
      });
    };

    self.init();
  }; // Initialize and run the whole shebang.


  $(document).ready(function () {
    gfRecaptcha();
  });
})(jQuery, gform, grecaptcha, gforms_recaptcha_recaptcha_strings);

/***/ })

/******/ 	});
/************************************************************************/
/******/ 	
/******/ 	// startup
/******/ 	// Load entry module and return exports
/******/ 	// This entry module is referenced by other modules so it can't be inlined
/******/ 	var __webpack_exports__ = {};
/******/ 	__webpack_modules__["./js/src/frontend.js"]();
/******/ 	
/******/ })()
;
//# sourceMappingURL=frontend.js.map