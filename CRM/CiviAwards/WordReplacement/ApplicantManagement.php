<?php

use CRM_CiviAwards_ExtensionUtil as ExtensionUtil;

/**
 * Class AwardsCategory.
 */
class CRM_CiviAwards_WordReplacement_ApplicantManagement implements CRM_Civicase_WordReplacement_BaseInterface {

  /**
   * {@inheritdoc}
   *
   * @return array
   *   Returns the word replacements
   */
  public function get() {
    $configFile = CRM_Core_Resources::singleton()
      ->getPath(ExtensionUtil::LONG_NAME, 'config/word_replacement/applicant_management.php');
    return include $configFile;
  }

}
