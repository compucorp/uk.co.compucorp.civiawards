<?php

use Civi\Angular\AngularLoader as AngularLoader;

/**
 * Class CRM_CiviAwards_Page_AwardAngular.
 *
 * Define an Angular base-page for CiviAwards.
 */
class CRM_CiviAwards_Page_AwardAngular extends CRM_Core_Page {

  /**
   * Run Function.
   *
   * This function takes care of all the things common to all
   * pages. This typically involves assigning the appropriate
   * smarty variable :)
   *
   * @return string
   *   The content generated by running this page
   */
  public function run() {
    $loader = new AngularLoader();
    $loader->setPageName('civicrm/award/a');
    $loader->setModules(['crmApp', 'civiawards']);
    $loader->load();
    \Civi::resources()->addSetting([
      'crmApp' => [
        'defaultRoute' => '/awards/new',
      ],
    ]);

    return parent::run();
  }

  /**
   * Get Template File Name.
   *
   * @inheritdoc
   */
  public function getTemplateFileName() {
    return 'Civi/Angular/Page/Main.tpl';
  }

}
