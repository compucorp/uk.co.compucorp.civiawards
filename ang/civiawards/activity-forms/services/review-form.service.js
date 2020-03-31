(function (_, angular, getCrmUrl) {
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
     * @param {object} overridingOptions form options.
     * @returns {string} the form URL.
     */
    function getActivityFormUrl (activity, overridingOptions) {
      var options = _.defaults({}, overridingOptions, {
        action: 'view',
        reset: 1
      });

      if (activity.id) {
        options.id = activity.id;
      }

      if (options.action === 'add') {
        options.case_id = activity.case_id;
      }

      return getCrmUrl('civicrm/awardreview', options);
    }
  }
})(CRM._, angular, CRM.url);
