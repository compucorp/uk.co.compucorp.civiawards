(function (_, $, angular, confirm) {
  var module = angular.module('civiawards');

  module.directive('civiawardsReviewsCaseTabContent', function () {
    return {
      controller: 'civiawardsReviewsCaseTabContentController',
      restrict: 'E',
      templateUrl: '~/civiawards/reviews-tab/directives/reviews-case-tab-content.directive.html',
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
   * @param {object} $sce angular Strict Contextual Escaping service.
   * @param {Function} civicaseCrmApi the CiviCRM API service.
   * @param {string} reviewsActivityTypeName the reviews activity type name.
   * @param {Function} ts the translation function.
   * @param {Function} crmStatus crm status service
   * @param {Function} civicaseCrmUrl civicrm url service
   * @param {Function} civicaseCrmLoadForm service to load civicrm forms
   * @param {Function} crmApi4 access to CiviCRM API v4
   */
  function civiawardsReviewsCaseTabContentController ($q, $scope, $sce,
    civicaseCrmApi, reviewsActivityTypeName, ts,
    crmStatus, civicaseCrmUrl, civicaseCrmLoadForm, crmApi4) {
    var CRM_FORM_LOAD_EVENT = 'crmFormLoad';
    var CRM_FORM_SUCCESS_EVENT = 'crmFormSuccess.crmPopup crmPopupFormSuccess.crmPopup';
    var REVIEW_FORM_URL = 'civicrm/awardreview';

    $scope.reviewActivities = [];
    $scope.ts = ts;

    $scope.handleAddReviewActivity = handleAddReviewActivity;
    $scope.handleViewReviewActivity = handleViewReviewActivity;
    $scope.handleEditReviewActivity = handleEditReviewActivity;
    $scope.handleDeleteReviewActivity = handleDeleteReviewActivity;
    $scope.trustAsHtml = $sce.trustAsHtml;

    (function init () {
      loadReviewActivities();

      $scope.$on('updateCaseData', loadReviewActivities);
    })();

    /**
     * Deletes the given review.
     *
     * @param {number} reviewActivityId the review's id.
     * @returns {Promise} resolves after the review has been deleted.
     */
    function deleteReviewActivity (reviewActivityId) {
      return civicaseCrmApi('Activity', 'delete', {
        id: reviewActivityId
      });
    }

    /**
     * Returns the reviews for the current application (case).
     *
     * @returns {Promise} resolves after fetching the reviews.
     */
    function getReviewActivities () {
      return crmApi4('ApplicantReviewField', 'get', {
      }).then(function (customFields) {
        const applicantReviewCustomGroups = [];
        customFields.forEach(function (customField) {
          if (customField.custom_group && customField.custom_group[0]) {
            applicantReviewCustomGroups.push(customField.custom_group[0].name);
          }
        });

        return civicaseCrmApi('Activity', 'get', {
          activity_type_id: reviewsActivityTypeName,
          case_id: $scope.caseItem.id,
          options: { limit: 0 },
          sequential: 1,
          'api.OptionValue.getsingle': {
            option_group_id: 'activity_status',
            value: '$value.status_id'
          },
          'api.CustomValue.gettreevalues': {
            entity_id: '$value.id',
            entity_type: 'Activity',
            'custom_group.name': { IN: applicantReviewCustomGroups }
          },
          'api.attachment.getsingle': {
            entity_id: '$value.id',
            entity_table: 'civicrm_activity'
          }
        });
      }).then(function (response) {
        return response.values;
      });
    }

    /**
     * Returns the details for the current award type.
     *
     * @returns {Promise} resolves after fetching the award's details.
     */
    function getAwardDetails () {
      return civicaseCrmApi('AwardDetail', 'getsingle', {
        case_type_id: $scope.caseItem.case_type_id
      });
    }

    /**
     * Format Activities to be used on the View
     *
     * @param {Array} activitiesData list of activities
     * @param {Array} scoringFieldsSortOrder a list of scoring fields' id and weight
     * @returns {Array} formatted list of activities
     */
    function formatActivitiesData (activitiesData, scoringFieldsSortOrder) {
      var sortOrder = _.sortBy(scoringFieldsSortOrder, 'weight');

      return _.map(angular.copy(activitiesData), function (activity) {
        var applicantReviewFields = {};
        activity['api.CustomValue.gettreevalues'].values.map(function (fieldSet) {
          Object.keys(fieldSet.fields).forEach(function (key) {
            var field = fieldSet.fields[key];
            if (field.html_type === 'File' && activity['api.attachment.getsingle'].id) {
              field.value.display =
                '<a href="' + activity['api.attachment.getsingle'].url + '" target="_blank">' + activity['api.attachment.getsingle'].name + '</a>';
            }
            var newKey = key + field.id;
            fieldSet.fields[newKey] = Object.assign({ id: field.id }, field);
            delete fieldSet.fields[key];
          });

          applicantReviewFields = Object.assign(applicantReviewFields, fieldSet.fields);
        });

        activity.status_label = activity['api.OptionValue.getsingle'].label;
        activity.reviewFields = sortOrder.map(function (scoringFieldSortOrder) {
          return _.find(applicantReviewFields, function (field) {
            return field.id === scoringFieldSortOrder.id;
          });
        });

        delete activity['api.OptionValue.getsingle'];
        delete activity['api.CustomValue.gettreevalues'];
        delete activity['api.attachment.getsingle'];

        return activity;
      });
    }

    /**
     * Opens a modal to create a new review activity. It refreshes the list of reviews after
     * successfully saving the form.
     */
    function handleAddReviewActivity () {
      var formUrl = civicaseCrmUrl(REVIEW_FORM_URL, {
        action: 'add',
        case_id: $scope.caseItem.id,
        reset: 1
      });

      civicaseCrmLoadForm(formUrl)
        .on(CRM_FORM_SUCCESS_EVENT, loadReviewActivities);
    }

    /**
     * Opens a form to edit the given review. It refreshes the list of reviews after
     * successfully saving the form.
     *
     * @param {object} reviewActivity the review to edit.
     */
    function handleEditReviewActivity (reviewActivity) {
      var formUrl = civicaseCrmUrl(REVIEW_FORM_URL, {
        action: 'update',
        id: reviewActivity.id,
        reset: 1
      });

      civicaseCrmLoadForm(formUrl)
        .on(CRM_FORM_LOAD_EVENT, popupTitleDecodeEntities)
        .on(CRM_FORM_SUCCESS_EVENT, loadReviewActivities);
    }

    /**
     * Opens a modal showing the review's details.
     *
     * @param {object} reviewActivity the review to view.
     */
    function handleViewReviewActivity (reviewActivity) {
      var formUrl = civicaseCrmUrl(REVIEW_FORM_URL, {
        action: 'view',
        id: reviewActivity.id,
        reset: 1
      });

      civicaseCrmLoadForm(formUrl)
        .on(CRM_FORM_LOAD_EVENT, popupTitleDecodeEntities);
    }

    /**
     * Converts HTML entities in popup title to their corresponding characters.
     *
     * @param {object} event
     *   Event object.
     * @param {object} data
     *   Loaded form data.
     */
    function popupTitleDecodeEntities (event, data) {
      var $popup = $(event.target).closest('.ui-dialog');

      $popup.find('.ui-dialog-title').each(function () {
        $(this).html(htmlEntitiesDecode($(this).html()));
      });
    }

    /**
     * Convert HTML entities to their corresponding characters.
     *
     * @param {string} string
     *   The input string.
     * @returns {string}
     *   Returns the decoded string.
     */
    function htmlEntitiesDecode (string) {
      return string.replace(/&lt;/g, '<').replace(/&gt;/g, '>').replace(/&amp;nbsp;/g, '&nbsp;');
    }

    /**
     * Confirms that the user wants to delete the given activity. Proceeds to delete the review
     * and displays a loading status.
     *
     * @param {object} reviewActivity the review to delete.
     */
    function handleDeleteReviewActivity (reviewActivity) {
      confirm({ title: ts('Delete Review') })
        .on('crmConfirm:yes', function () {
          var promise = deleteReviewActivity(reviewActivity.id)
            .then(loadReviewActivities);

          return crmStatus({
            start: $scope.ts('Deleting...'),
            success: $scope.ts('Deleted')
          }, promise);
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
        reviewActivities: getReviewActivities()
      }).then(function (responses) {
        $scope.reviewActivities = formatActivitiesData(
          responses.reviewActivities,
          responses.awardDetails.review_fields
        );
      }).finally(function () {
        $scope.isLoading = false;
      });
    }
  }
})(CRM._, CRM.$, angular, CRM.confirm);
