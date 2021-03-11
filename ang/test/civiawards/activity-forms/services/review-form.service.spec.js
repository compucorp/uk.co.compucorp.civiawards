((_) => {
  describe('ReviewActivityForm', () => {
    let ReviewActivityForm, canHandle,
      civicaseCrmUrl, reviewActivity;

    beforeEach(module('civiawards', 'civicase-base', 'civiawards.data'));

    beforeEach(inject((_ReviewActivitiesMockData_, _ReviewActivityForm_,
      _civicaseCrmUrl_) => {
      ReviewActivityForm = _ReviewActivityForm_;
      civicaseCrmUrl = _civicaseCrmUrl_;
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
          ReviewActivityForm.getActivityFormUrl(reviewActivity);
        });

        it('returns the url for the review activity form', () => {
          expect(civicaseCrmUrl).toHaveBeenCalledWith('civicrm/awardreview', {
            action: 'view',
            id: reviewActivity.id,
            reset: 1
          });
        });
      });

      describe('when getting the update review form url', () => {
        beforeEach(() => {
          ReviewActivityForm.getActivityFormUrl(reviewActivity, {
            action: 'update'
          });
        });

        it('returns the url for the review activity popup form', () => {
          expect(civicaseCrmUrl).toHaveBeenCalledWith('civicrm/awardreview', {
            action: 'update',
            id: reviewActivity.id,
            reset: 1
          });
        });
      });

      describe('when getting the add review form url', () => {
        beforeEach(() => {
          delete reviewActivity.id;
          reviewActivity.case_id = _.uniqueId();
          ReviewActivityForm.getActivityFormUrl(reviewActivity, {
            action: 'add'
          });
        });

        it('returns the url for the review activity popup form', () => {
          expect(civicaseCrmUrl).toHaveBeenCalledWith('civicrm/awardreview', {
            action: 'add',
            case_id: reviewActivity.case_id,
            reset: 1
          });
        });
      });
    });
  });
})(CRM._);
