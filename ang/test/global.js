/* eslint-env jasmine */

(function (CRM) {
  CRM['civicase-base'] = {};
  CRM.civiawards = {};
  // CRM['civiawards-workflow'] = {};

  /**
   * Dependency Injection for civiawards module, defined in ang/civiawards.ang.php
   * For unit testing they needs to be mentioned here
   */
  CRM.angular.requires.civiawards = ['ngRoute', 'civicase-base', 'dialogService'];
}(CRM));
