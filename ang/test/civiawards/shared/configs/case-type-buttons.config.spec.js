/* eslint-env jasmine */

(function (_, angular, getCrmUrl) {
  var AWARDS_CATEGORY_NAME = 'awards';
  var AWARD_CONFIG_URL = 'civicrm/a/#/awards/';

  describe('CaseTypeButtons provider', () => {
    let AwardMockData, AwardsCategory, DashboardCaseTypeButtons, CaseTypesMockData;

    beforeEach(module('civiawards.data', 'civicase-base', 'civiawards'));

    beforeEach(inject((_AwardMockData_, _DashboardCaseTypeButtons_, caseTypeCategoriesMockData, _CaseTypesMockData_) => {
      AwardMockData = _AwardMockData_;
      CaseTypesMockData = _CaseTypesMockData_.get();
      DashboardCaseTypeButtons = _DashboardCaseTypeButtons_;
      AwardsCategory = _.find(
        caseTypeCategoriesMockData,
        (category) => category.name === AWARDS_CATEGORY_NAME
      );
    }));

    describe('after the awards module has been configured', () => {
      it('it adds the configuration url to the awards case type', () => {
        expect(DashboardCaseTypeButtons).toEqual({
          [AwardMockData[0].name]: [{
            icon: 'fa fa-cog',
            url: getCrmUrl(AWARD_CONFIG_URL + AwardMockData[0].id)
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
        expect(_.keys(DashboardCaseTypeButtons)).not.toContain(nonAwardscaseTypes);
      });
    });
  });
})(CRM._, angular, CRM.url);
