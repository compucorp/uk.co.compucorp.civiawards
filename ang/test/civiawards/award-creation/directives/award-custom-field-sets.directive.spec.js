(function (_, $) {
  describe('civiawardCustomFieldSets', () => {
    var civiawardCustomFieldSetsDirective, $compile, $rootScope, $scope,
      civicaseCrmLoadForm;

    beforeEach(module('civiawards.templates', 'civiawards'));

    beforeEach(inject((_$compile_, _$rootScope_, _civicaseCrmLoadForm_) => {
      $compile = _$compile_;
      $rootScope = _$rootScope_;
      civicaseCrmLoadForm = _civicaseCrmLoadForm_;
    }));

    describe('basic tests', () => {
      describe('when creating new award', function () {
        beforeEach(() => {
          civicaseCrmLoadForm.calls.reset();
          initDirective();
        });

        it('does not fetch the custom fields information', () => {
          expect(civicaseCrmLoadForm).not.toHaveBeenCalled();
        });
      });

      describe('when editing existing award', function () {
        beforeEach(() => {
          civicaseCrmLoadForm.calls.reset();
          initDirective(5);
        });

        it('shows the custom field set UI', () => {
          expect(civicaseCrmLoadForm).toHaveBeenCalledWith('/civicrm/award/customfield?entityId=5', {
            target: jasmine.any(Object)
          });
        });
      });
    });

    describe('when saving the award', () => {
      let originalTriggerFn, originalUnblockFn, promise,
        customFieldSetsTabCallbackFn, customFieldSetsTabObj;
      const fixture = `
        <div class="civiaward__custom-field-sets__container test-fixture">
          <div class="award-custom-field"></div>
        </div>
      `;

      beforeEach(() => {
        initDirective(5);

        customFieldSetsTabObj = _.find($scope.tabs, function (tabObj) {
          return tabObj.name === 'customFieldSets';
        });

        originalTriggerFn = $.fn.trigger;
        originalUnblockFn = $.fn.unblock;
        spyOn($.fn, 'trigger').and.callThrough();
        spyOn($.fn, 'unblock').and.callThrough();
      });

      afterEach(() => {
        $.fn.trigger = originalTriggerFn;
        $.fn.unblock = originalUnblockFn;
      });

      describe('when there are custom fields defined', () => {
        beforeEach(() => {
          $('body').append(fixture);
          promise = customFieldSetsTabObj.save();

          customFieldSetsTabCallbackFn = jasmine.createSpy('promise');
          promise.then(customFieldSetsTabCallbackFn);
        });

        afterEach(() => {
          $('.test-fixture').remove();
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

          it('hides the loading screen for custom field sets', () => {
            expect($.fn.unblock).toHaveBeenCalled();
          });
        });
      });

      describe('and there are no custom fields defined', () => {
        beforeEach(() => {
          promise = customFieldSetsTabObj.save();
          customFieldSetsTabCallbackFn = jasmine.createSpy('promise');

          promise.then(customFieldSetsTabCallbackFn);
          $scope.$digest();
        });

        it('saves the award itself', () => {
          expect(customFieldSetsTabCallbackFn).toHaveBeenCalled();
        });
      });
    });

    /**
     * Initialise directive
     *
     * @param {string/number} awardId award id
     */
    function initDirective (awardId) {
      var html = '<civiaward-custom-field-sets></civiaward-custom-field-sets>';
      $scope = $rootScope.$new();
      $scope.awardId = awardId;
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
