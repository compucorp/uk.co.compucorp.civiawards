/* eslint-env jasmine */
(function ($) {
  describe('AwardType', function () {
    var AwardType, AwardTypeMockData;

    beforeEach(module('civiawards', 'civiawards.data'));

    describe('DateHelper', () => {
      beforeEach(inject((_AwardType_, _AwardTypeMockData_) => {
        AwardType = _AwardType_;
        AwardTypeMockData = _AwardTypeMockData_;
      }));

      describe('getAll()', () => {
        it('returns all the award types', () => {
          expect(AwardType.getAll()).toBe(AwardTypeMockData);
        });
      });
    });
  });
}(CRM.$));
