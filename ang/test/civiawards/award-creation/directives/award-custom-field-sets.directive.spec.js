/* eslint-env jasmine */
(function (_, $) {
  describe('civiawardCustomFieldSets', () => {
    var civiawardCustomFieldSetsDirective, $compile, $rootScope, $scope;

    beforeEach(module('civiawards.templates', 'civiawards'));

    beforeEach(inject((_$compile_, _$rootScope_) => {
      $compile = _$compile_;
      $rootScope = _$rootScope_;
    }));

    describe('basic tests', () => {
      beforeEach(() => {
        initDirective();
      });

      it('shows the custom field set UI', () => {
        expect(CRM.loadForm).toHaveBeenCalledWith('/civicrm/award/customfield?entityId=5', {
          target: jasmine.any(Object)
        });
      });
    });

    describe('when saving the award', () => {
      var promise, customFieldSetsTabCallbackFn;

      beforeEach(() => {
        initDirective();

        var customFieldSetsTabObj = _.find($scope.tabs, function (tabObj) {
          return tabObj.name === 'customFieldSets';
        });

        spyOn($.fn, 'trigger').and.callThrough();

        promise = customFieldSetsTabObj.save();

        customFieldSetsTabCallbackFn = jasmine.createSpy('promise');
        promise.then(customFieldSetsTabCallbackFn);
      });

      it('saves the custom field set values', () => {
        expect($.fn.trigger).toHaveBeenCalledWith('click');
      });

      describe('when the save is complete', () => {
        beforeEach(() => {
          civiawardCustomFieldSetsDirective.trigger('crmFormSuccess');
          $scope.$digest();
        });

        it('saves the award itself', () => {
          expect(customFieldSetsTabCallbackFn).toHaveBeenCalled();
        });
      });
    });

    /**
     * Initialise directive
     */
    function initDirective () {
      var html = '<civiaward-custom-field-sets></civiaward-custom-field-sets>';
      $scope = $rootScope.$new();
      $scope.awardId = '5';
      $scope.tabs = [
        { name: 'basicDetails', label: 'Basic Details' },
        { name: 'stages', label: 'Award Stages' },
        { name: 'customFieldSets', label: 'Custom Field Sets' },
        { name: 'reviewPanels', label: 'Panels' },
        { name: 'reviewFields', label: 'Review Fields' }
      ];

      civiawardCustomFieldSetsDirective = $compile(html)($scope);

      $scope.$digest();
    }
  });
}(CRM._, CRM.$));
