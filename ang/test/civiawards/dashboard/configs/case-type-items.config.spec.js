/* eslint-env jasmine */

(function (_) {
  var AWARDS_CATEGORY_NAME = 'awards';

  describe('Case Type Items configuration', () => {
    let AwardMockData, AwardsCategory, DashboardCaseTypeItems, CaseTypesMockData;

    beforeEach(module('civiawards.data', 'civicase-base', 'civiawards'));

    beforeEach(inject((_AwardMockData_, _DashboardCaseTypeItems_, caseTypeCategoriesMockData, _CaseTypesMockData_) => {
      AwardMockData = _AwardMockData_;
      CaseTypesMockData = _CaseTypesMockData_.get();
      DashboardCaseTypeItems = _DashboardCaseTypeItems_;
      AwardsCategory = _.find(
        caseTypeCategoriesMockData,
        (category) => category.name === AWARDS_CATEGORY_NAME
      );
    }));

    describe('after the awards module has been configured', () => {
      const AWARD_BUTTON_PATH = '~/civiawards/dashboard/directives/edit-award-button.html';

      it('it adds the edit award button template to the awards case type', () => {
        expect(DashboardCaseTypeItems).toEqual({
          [AwardMockData[0].name]: [{
            templateUrl: AWARD_BUTTON_PATH
          }],
          [AwardMockData[1].name]: [{
            templateUrl: AWARD_BUTTON_PATH
          }]
        });
      });
    });

    describe('when requesting the buttons for non award case types', () => {
      let nonAwardscaseTypes;

      beforeEach(() => {
        nonAwardscaseTypes = _.chain(CaseTypesMockData)
          .filter(caseType => caseType.category !== AwardsCategory.value)
          .map('name')
          .value();
      });

      it('it does not add configuration buttons to non award case types', () => {
        expect(_.keys(DashboardCaseTypeItems)).not.toContain(nonAwardscaseTypes);
      });
    });
  });
})(CRM._);
