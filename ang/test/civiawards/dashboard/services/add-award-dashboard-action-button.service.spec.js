/* eslint-env jasmine */

(function (_, getCrmUrl) {
  describe('Add Award Dashboard Action Button', () => {
    let $location, $window, AddAwardDashboardActionButton;

    beforeEach(module('civiawards', ($provide) => {
      $window = { location: { href: '' } };

      $provide.value('$window', $window);
    }));

    beforeEach(inject((_$location_, _AddAwardDashboardActionButton_) => {
      $location = _$location_;
      AddAwardDashboardActionButton = _AddAwardDashboardActionButton_;
    }));

    describe('button visibility', () => {
      var isButtonVisible;

      describe('when viewing the awards dashboard', () => {
        beforeEach(() => {
          $location.search('case_type_category', 'awards');

          isButtonVisible = AddAwardDashboardActionButton.isVisible();
        });

        it('displays the add award button', () => {
          expect(isButtonVisible).toBe(true);
        });
      });

      describe('when viewing any other dashboard', () => {
        beforeEach(() => {
          $location.search('case_type_category', 'cases');

          isButtonVisible = AddAwardDashboardActionButton.isVisible();
        });

        it('does not display the add award button', () => {
          expect(isButtonVisible).toBe(false);
        });
      });
    });

    describe('when clicking the action button', () => {
      const expectedUrl = getCrmUrl('civicrm/a/#/awards/new');

      beforeEach(() => {
        AddAwardDashboardActionButton.clickHandler();
      });

      it('redirects the user to the create award screen', () => {
        expect($window.location.href).toBe(expectedUrl);
      });
    });
  });
})(CRM._, CRM.url);
