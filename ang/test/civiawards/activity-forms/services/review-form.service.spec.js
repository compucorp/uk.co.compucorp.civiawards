/* eslint-env jasmine */
((_, getCrmUrl) => {
  describe('ReviewActivityForm', () => {
    let activityFormUrl, expectedActivityFormUrl, ReviewActivityForm, canHandle, reviewActivity;

    beforeEach(module('civiawards', 'civicase-base', 'civiawards.data'));

    beforeEach(inject((_ReviewActivitiesMockData_, _ReviewActivityForm_) => {
      ReviewActivityForm = _ReviewActivityForm_;
      reviewActivity = _.chain(_ReviewActivitiesMockData_)
        .first()
        .cloneDeep()
        .value();
    }));

    describe('handling activity forms', () => {
      describe('when handling a review activity type', () => {
        beforeEach(() => {
          reviewActivity.type = 'Applicant Review';
          canHandle = ReviewActivityForm.canHandleActivity(reviewActivity);
        });

        it('can handle the application activity', () => {
          expect(canHandle).toBe(true);
        });
      });

      describe('when handling any other activity type', () => {
        beforeEach(() => {
          reviewActivity.type = 'Housing Support';
          canHandle = ReviewActivityForm.canHandleActivity(reviewActivity);
        });

        it('cannot handle the activity', () => {
          expect(canHandle).toBe(false);
        });
      });
    });

    describe('getting the activity form url', () => {
      describe('when getting the activity form url', () => {
        beforeEach(() => {
          activityFormUrl = ReviewActivityForm.getActivityFormUrl(reviewActivity);
          expectedActivityFormUrl = getCrmUrl('civicrm/awardreview', {
            action: 'view',
            id: reviewActivity.id,
            reset: 1
          });
        });

        it('returns the url for the review activity form', () => {
          expect(activityFormUrl).toEqual(expectedActivityFormUrl);
        });
      });

      describe('when getting the activity popup form url', () => {
        beforeEach(() => {
          activityFormUrl = ReviewActivityForm.getActivityFormUrl(reviewActivity, {
            formType: 'popup'
          });
          expectedActivityFormUrl = getCrmUrl('civicrm/awardreview', {
            action: 'update',
            id: reviewActivity.id,
            reset: 1
          });
        });

        it('returns the url for the review activity popup form', () => {
          expect(activityFormUrl).toEqual(expectedActivityFormUrl);
        });
      });
    });
  });
})(CRM._, CRM.url);
