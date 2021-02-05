/* eslint-env jasmine */

(function (CRM) {
  CRM['civiawards-base'] = {};
  CRM['civiawards-workflow'] = {};
  CRM['civicase-base'] = {};
  CRM.civiawards = {};
  /**
   * Dependency Injection for civiawards module, defined in
   * ang/civiawards-base.ang.php and ang/civiawards.ang.php
   * For unit testing they needs to be mentioned here
   */
  CRM.angular.requires['civiawards-base'] = ['civicase-base'];
  CRM.angular.requires.civiawards = ['ngRoute', 'civiawards-base', 'dialogService'];
  CRM.angular.requires['civiawards-workflow'] = ['civiawards-base'];
}(CRM));
