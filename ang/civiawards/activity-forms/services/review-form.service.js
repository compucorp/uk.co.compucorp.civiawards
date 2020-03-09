(function (angular, getCrmUrl) {
  var module = angular.module('civiawards');

  module.service('ReviewActivityForm', ReviewActivityForm);

  /**
   * Review Activity Form service.
   */
  function ReviewActivityForm () {
    this.canHandleActivity = canHandleActivity;
    this.getActivityFormUrl = getActivityFormUrl;

    /**
     * @param {object} activity an activity object.
     * @returns {boolean} true when the activity is an application review.
     */
    function canHandleActivity (activity) {
      return activity.type === 'Applicant Review';
    }

    /**
     * Returns the form's URL for the review activity. If the form will
     * be shown in a popup it automatically opens the form in edit mode.
     *
     * @param {object} activity an activity object.
     * @param {object} options form options.
     * @returns {string} the form URL.
     */
    function getActivityFormUrl (activity, options) {
      var action = 'view';
      var isPopupForm = options && options.formType === 'popup';

      if (isPopupForm) {
        action = 'update';
      }

      return getCrmUrl('civicrm/awardreview', {
        action: action,
        id: activity.id,
        reset: 1
      });
    }
  }
})(angular, CRM.url);
