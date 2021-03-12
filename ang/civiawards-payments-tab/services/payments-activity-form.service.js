(function (_, angular) {
  var module = angular.module('civiawards-payments-tab');

  module.service('PaymentsActivityForm', PaymentsActivityForm);

  /**
   * Review Activity Form service.
   *
   * @param {object} civicaseCrmUrl civicrm url service
   */
  function PaymentsActivityForm (civicaseCrmUrl) {
    this.canHandleActivity = canHandleActivity;
    this.getActivityFormUrl = getActivityFormUrl;

    /**
     * @param {object} activity an activity object.
     * @returns {boolean} true when the activity is an application review.
     */
    function canHandleActivity (activity) {
      return activity.type === 'Awards Payment';
    }

    /**
     * Returns the form's URL for the review activity. If the form will
     * be shown in a popup it automatically opens the form in edit mode.
     *
     * @param {object} activity an activity object.
     * @param {object} overridingOptions form options.
     * @returns {string} the form URL.
     */
    function getActivityFormUrl (activity, overridingOptions) {
      return civicaseCrmUrl('civicrm/awardpayment', {
        action: 'view',
        reset: 1,
        id: activity.id
      });
    }
  }
})(CRM._, angular);
