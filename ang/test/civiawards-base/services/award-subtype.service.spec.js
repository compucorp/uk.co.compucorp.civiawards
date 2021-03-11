(function () {
  describe('AwardSubtype', function () {
    let AwardSubtype, AwardSubtypeMockData;

    beforeEach(module('civiawards-base', 'civiawards.data'));

    beforeEach(inject((_AwardSubtype_, _AwardSubtypeMockData_) => {
      AwardSubtype = _AwardSubtype_;
      AwardSubtypeMockData = _AwardSubtypeMockData_;
    }));

    describe('getAll()', () => {
      it('returns all the sub types', () => {
        expect(AwardSubtype.getAll()).toEqual(AwardSubtypeMockData);
      });
    });
  });
}());
