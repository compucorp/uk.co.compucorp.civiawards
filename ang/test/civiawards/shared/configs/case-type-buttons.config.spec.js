/* eslint-env jasmine */

(function (_, angular) {
  var AWARDS_CATEGORY_NAME = 'awards';
  var AWARD_CONFIG_URL = 'civicrm/a/#/awards/';

  describe('CaseTypeButtons provider', () => {
    let AwardMockData, AwardsCategory, CaseTypeButtons, CaseTypesMockData;

    beforeEach(() => {
      module('civicase-base', 'civiawards.data', 'civiawards');
    });

    beforeEach(inject((_AwardMockData_, _CaseTypeButtons_, caseTypeCategoriesMockData, _CaseTypesMockData_) => {
      AwardMockData = _AwardMockData_;
      CaseTypesMockData = _CaseTypesMockData_.get();
      CaseTypeButtons = _CaseTypeButtons_;
      AwardsCategory = _.find(
        caseTypeCategoriesMockData,
        (category) => category.name === AWARDS_CATEGORY_NAME
      );
    }));

    describe('after the awards module has been configured', () => {
      it('it adds the configuration url to the awards case type', () => {
        expect(CaseTypeButtons).toEqual({
          [AwardMockData.name]: [{
            url: AWARD_CONFIG_URL + AwardMockData.id
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
        expect(_.keys(CaseTypeButtons)).not.toContain(nonAwardscaseTypes);
      });
    });
  });
})(CRM._, angular);
