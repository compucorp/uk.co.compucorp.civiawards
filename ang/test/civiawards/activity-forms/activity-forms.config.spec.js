/* eslint-env jasmine */
(() => {
  describe('Acitivity forms configuration', () => {
    let ActivityFormsProvider;

    beforeEach(module('civicase-base', (_ActivityFormsProvider_) => {
      ActivityFormsProvider = _ActivityFormsProvider_;

      spyOn(ActivityFormsProvider, 'addActivityForms');
    }));

    beforeEach(module('civiawards'));

    beforeEach(inject);

    describe('when the awards module is configured', () => {
      it('adds the reviews activity form before any other activity forms', () => {
        expect(ActivityFormsProvider.addActivityForms)
          .toHaveBeenCalledWith(jasmine.arrayContaining([{
            name: 'ReviewActivityForm',
            weight: -1
          }]));
      });
    });
  });
})();
