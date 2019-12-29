(function (_, angular, confirm, loadForm, setLoadingStatus, getCrmUrl) {
  var module = angular.module('civiawards');

  module.directive('civiawardsReviewsCaseTabContent', function () {
    return {
      controller: 'civiawardsReviewsCaseTabContentController',
      restrict: 'E',
      templateUrl: '~/civiawards/reviews-tab/directives/reviews-case-tab-content.html',
      scope: {
        caseItem: '='
      }
    };
  });

  module.controller('civiawardsReviewsCaseTabContentController', civiawardsReviewsCaseTabContentController);

  /**
   * The reviews case tab content controller.
   *
   * @param {object} $q the $q service.
   * @param {object} $scope the scope object.
   * @param {Function} crmApi the CiviCRM API service.
   * @param {string} reviewsActivityTypeName the reviews activity type name.
   * @param {string} reviewScoringFieldsGroupName the review scoring fields group name.
   * @param {Function} ts the translation function.
   */
  function civiawardsReviewsCaseTabContentController ($q, $scope, crmApi, reviewsActivityTypeName,
    reviewScoringFieldsGroupName, ts) {
    var CRM_FORM_SUCCESS_EVENT = 'crmFormSuccess.crmPopup crmPopupFormSuccess.crmPopup';
    var REVIEW_FORM_URL = 'civicrm/awardreview';

    $scope.reviewActivities = [];
    $scope.scoringFields = [];
    $scope.ts = ts;

    $scope.handleAddReviewActivity = handleAddReviewActivity;
    $scope.handleViewReviewActivity = handleViewReviewActivity;
    $scope.handleEditReviewActivity = handleEditReviewActivity;
    $scope.handleDeleteReviewActivity = handleDeleteReviewActivity;

    (function init () {
      loadReviewActivities();
    })();

    /**
     * Deletes the given review.
     *
     * @param {number} reviewActivityId the review's id.
     * @returns {Promise} resolves after the review has been deleted.
     */
    function deleteReviewActivity (reviewActivityId) {
      return crmApi('Activity', 'delete', {
        id: reviewActivityId
      });
    }

    /**
     * Returns the details for the current award type.
     *
     * @returns {Promise} resolves after fetching the award's details.
     */
    function getAwardDetails () {
      return crmApi('AwardDetail', 'getsingle', {
        case_type_id: $scope.caseItem.case_type_id
      });
    }

    /**
     * Returns the reviews for the current application (case).
     *
     * @returns {Promise} resolves after fetching the reviews.
     */
    function getReviewActivities () {
      return crmApi('Activity', 'get', {
        activity_type_id: reviewsActivityTypeName,
        case_id: $scope.caseItem.id,
        options: { limit: 0 },
        sequential: 1
      })
        .then(function (response) {
          return response.values;
        });
    }

    /**
     * Returns the scoring fields that are used for reviewing applications. This returns
     * the field's labels, names, types, etc. but not the actual field value. The value is
     * stored in the review (activity) itself.
     *
     * @returns {Promise} resolves after fetching the scoring fields.
     */
    function getScoringFields () {
      return crmApi('CustomField', 'get', {
        custom_group_id: reviewScoringFieldsGroupName,
        options: { limit: 0 },
        sequential: 0
      })
        .then(function (response) {
          return response.values;
        });
    }

    /**
     * @param {object[]} scoringFieldsSortOrder a list of scoring fields' id and weight.
     * @param {object[]} scoringFields a list of scoring fields with all the necessary data.
     * @returns {object[]} returns the scoring fields sorted by weight and containing all the necessary data.
     */
    function getSortedScoringFields (scoringFieldsSortOrder, scoringFields) {
      return _.sortBy(scoringFieldsSortOrder, 'weight')
        .map(function (scoringFieldSortOrder) {
          return scoringFields[scoringFieldSortOrder.id];
        });
    }

    /**
     * Opens a modal to create a new review activity. It refreshes the list of reviews after
     * successfully saving the form.
     */
    function handleAddReviewActivity () {
      var formUrl = getCrmUrl(REVIEW_FORM_URL, {
        action: 'add',
        case_id: $scope.caseItem.id,
        reset: 1
      });

      loadForm(formUrl)
        .on(CRM_FORM_SUCCESS_EVENT, loadReviewActivities);
    }

    /**
     * Opens a form to edit the given review. It refreshes the list of reviews after
     * successfully saving the form.
     *
     * @param {object} reviewActivity the review to edit.
     */
    function handleEditReviewActivity (reviewActivity) {
      var formUrl = getCrmUrl(REVIEW_FORM_URL, {
        action: 'update',
        id: reviewActivity.id,
        reset: 1
      });

      loadForm(formUrl)
        .on(CRM_FORM_SUCCESS_EVENT, loadReviewActivities);
    }

    /**
     * Opens a modal showing the review's details.
     *
     * @param {object} reviewActivity the review to view.
     */
    function handleViewReviewActivity (reviewActivity) {
      var formUrl = getCrmUrl(REVIEW_FORM_URL, {
        action: 'view',
        id: reviewActivity.id,
        reset: 1
      });

      loadForm(formUrl);
    }

    /**
     * Confirms that the user wants to delete the given activity. Proceeds to delete the review
     * and displays a loading status.
     *
     * @param {object} reviewActivity the review to delete.
     */
    function handleDeleteReviewActivity (reviewActivity) {
      confirm()
        .on('crmConfirm:yes', function () {
          var loadingStatus = setLoadingStatus();

          deleteReviewActivity(reviewActivity.id)
            .then(loadReviewActivities)
            .then(
              function () {
                loadingStatus.resolve();
              },
              function () {
                loadingStatus.reject();
              }
            );
        });
    }

    /**
     * Loads the reviews for the current application (case). It uses the application's
     * award details to determine the sort order of the scoring fields.
     */
    function loadReviewActivities () {
      $scope.isLoading = true;

      $q.all({
        awardDetails: getAwardDetails(),
        reviewActivities: getReviewActivities(),
        scoringFields: getScoringFields()
      })
        .then(function (responses) {
          $scope.reviewActivities = responses.reviewActivities;
          $scope.scoringFields = getSortedScoringFields(
            responses.awardDetails.review_fields,
            responses.scoringFields
          );
        })
        .finally(function () {
          $scope.isLoading = false;
        });
    }
  }
})(CRM._, angular, CRM.confirm, CRM.loadForm, CRM.status, CRM.url);
