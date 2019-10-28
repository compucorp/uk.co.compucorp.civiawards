/* eslint-env jasmine */
(function ($) {
  describe('AwardType', function () {
    var AwardType, AwardTypeData;

    beforeEach(module('civiawards', 'civiawards.data'));

    describe('DateHelper', () => {
      beforeEach(inject((_AwardType_, _AwardTypeData_) => {
        AwardType = _AwardType_;
        AwardTypeData = _AwardTypeData_;
      }));

      describe('getAll()', () => {
        it('returns all the award types', () => {
          expect(AwardType.getAll()).toBe(AwardTypeData);
        });
      });
    });
  });
}(CRM.$));
