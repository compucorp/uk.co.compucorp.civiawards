<?php

/**
 * @package CRM
 * @copyright CiviCRM LLC (c) 2004-2019
 *
 * Generated from /var/www/site/profiles/compuclient/modules/contrib/civicrm/ext/uk.co.compucorp.civiawards/xml/schema/CRM/CiviAwards/AwardReviewPanel.xml
 * DO NOT EDIT.  Generated by CRM_Core_CodeGen
 * (GenCodeChecksum:ab29dd472cde005c95e37b9301bb022b)
 */

/**
 * Database access object for the AwardReviewPanel entity.
 */
class CRM_CiviAwards_DAO_AwardReviewPanel extends CRM_Core_DAO {

  /**
   * Static instance to hold the table name.
   *
   * @var string
   */
  public static $_tableName = 'civicrm_civiawards_award_panel';

  /**
   * Should CiviCRM log any modifications to this table in the civicrm_log table.
   *
   * @var bool
   */
  public static $_log = TRUE;

  /**
   * Unique AwardReviewPanel ID
   *
   * @var int
   */
  public $id;

  /**
   * The review panel title
   *
   * @var string
   */
  public $title;

  /**
   * FK to Case Type
   *
   * @var int
   */
  public $case_type_id;

  /**
   * An array of panel settings to fetch contacts belonging to this panel
   *
   * @var text
   */
  public $contact_settings;

  /**
   * An array of settings related to the data access the panel contacts have to applications
   *
   * @var text
   */
  public $visibility_settings;

  /**
   * Whether the panel is active or not
   *
   * @var bool
   */
  public $is_active;

  /**
   * Class constructor.
   */
  public function __construct() {
    $this->__table = 'civicrm_civiawards_award_panel';
    parent::__construct();
  }

  /**
   * Returns foreign keys and entity references.
   *
   * @return array
   *   [CRM_Core_Reference_Interface]
   */
  public static function getReferenceColumns() {
    if (!isset(Civi::$statics[__CLASS__]['links'])) {
      Civi::$statics[__CLASS__]['links'] = static::createReferenceColumns(__CLASS__);
      Civi::$statics[__CLASS__]['links'][] = new CRM_Core_Reference_Basic(self::getTableName(), 'case_type_id', 'civicrm_case_type', 'id');
      CRM_Core_DAO_AllCoreTables::invoke(__CLASS__, 'links_callback', Civi::$statics[__CLASS__]['links']);
    }
    return Civi::$statics[__CLASS__]['links'];
  }

  /**
   * Returns all the column names of this table
   *
   * @return array
   */
  public static function &fields() {
    if (!isset(Civi::$statics[__CLASS__]['fields'])) {
      Civi::$statics[__CLASS__]['fields'] = [
        'id' => [
          'name' => 'id',
          'type' => CRM_Utils_Type::T_INT,
          'description' => CRM_CiviAwards_ExtensionUtil::ts('Unique AwardReviewPanel ID'),
          'required' => TRUE,
          'where' => 'civicrm_civiawards_award_panel.id',
          'table_name' => 'civicrm_civiawards_award_panel',
          'entity' => 'AwardReviewPanel',
          'bao' => 'CRM_CiviAwards_DAO_AwardReviewPanel',
          'localizable' => 0,
        ],
        'title' => [
          'name' => 'title',
          'type' => CRM_Utils_Type::T_STRING,
          'title' => CRM_CiviAwards_ExtensionUtil::ts('Title'),
          'description' => CRM_CiviAwards_ExtensionUtil::ts('The review panel title'),
          'required' => TRUE,
          'maxlength' => 255,
          'size' => CRM_Utils_Type::HUGE,
          'where' => 'civicrm_civiawards_award_panel.title',
          'table_name' => 'civicrm_civiawards_award_panel',
          'entity' => 'AwardReviewPanel',
          'bao' => 'CRM_CiviAwards_DAO_AwardReviewPanel',
          'localizable' => 0,
        ],
        'case_type_id' => [
          'name' => 'case_type_id',
          'type' => CRM_Utils_Type::T_INT,
          'description' => CRM_CiviAwards_ExtensionUtil::ts('FK to Case Type'),
          'required' => TRUE,
          'where' => 'civicrm_civiawards_award_panel.case_type_id',
          'table_name' => 'civicrm_civiawards_award_panel',
          'entity' => 'AwardReviewPanel',
          'bao' => 'CRM_CiviAwards_DAO_AwardReviewPanel',
          'localizable' => 0,
        ],
        'contact_settings' => [
          'name' => 'contact_settings',
          'type' => CRM_Utils_Type::T_TEXT,
          'title' => CRM_CiviAwards_ExtensionUtil::ts('Contact Settings'),
          'description' => CRM_CiviAwards_ExtensionUtil::ts('An array of panel settings to fetch contacts belonging to this panel'),
          'where' => 'civicrm_civiawards_award_panel.contact_settings',
          'table_name' => 'civicrm_civiawards_award_panel',
          'entity' => 'AwardReviewPanel',
          'bao' => 'CRM_CiviAwards_DAO_AwardReviewPanel',
          'localizable' => 0,
        ],
        'visibility_settings' => [
          'name' => 'visibility_settings',
          'type' => CRM_Utils_Type::T_TEXT,
          'title' => CRM_CiviAwards_ExtensionUtil::ts('Visibility Settings'),
          'description' => CRM_CiviAwards_ExtensionUtil::ts('An array of settings related to the data access the panel contacts have to applications'),
          'where' => 'civicrm_civiawards_award_panel.visibility_settings',
          'table_name' => 'civicrm_civiawards_award_panel',
          'entity' => 'AwardReviewPanel',
          'bao' => 'CRM_CiviAwards_DAO_AwardReviewPanel',
          'localizable' => 0,
        ],
        'is_active' => [
          'name' => 'is_active',
          'type' => CRM_Utils_Type::T_BOOLEAN,
          'description' => CRM_CiviAwards_ExtensionUtil::ts('Whether the panel is active or not'),
          'required' => TRUE,
          'where' => 'civicrm_civiawards_award_panel.is_active',
          'default' => '1',
          'table_name' => 'civicrm_civiawards_award_panel',
          'entity' => 'AwardReviewPanel',
          'bao' => 'CRM_CiviAwards_DAO_AwardReviewPanel',
          'localizable' => 0,
        ],
      ];
      CRM_Core_DAO_AllCoreTables::invoke(__CLASS__, 'fields_callback', Civi::$statics[__CLASS__]['fields']);
    }
    return Civi::$statics[__CLASS__]['fields'];
  }

  /**
   * Return a mapping from field-name to the corresponding key (as used in fields()).
   *
   * @return array
   *   Array(string $name => string $uniqueName).
   */
  public static function &fieldKeys() {
    if (!isset(Civi::$statics[__CLASS__]['fieldKeys'])) {
      Civi::$statics[__CLASS__]['fieldKeys'] = array_flip(CRM_Utils_Array::collect('name', self::fields()));
    }
    return Civi::$statics[__CLASS__]['fieldKeys'];
  }

  /**
   * Returns the names of this table
   *
   * @return string
   */
  public static function getTableName() {
    return self::$_tableName;
  }

  /**
   * Returns if this table needs to be logged
   *
   * @return bool
   */
  public function getLog() {
    return self::$_log;
  }

  /**
   * Returns the list of fields that can be imported
   *
   * @param bool $prefix
   *
   * @return array
   */
  public static function &import($prefix = FALSE) {
    $r = CRM_Core_DAO_AllCoreTables::getImports(__CLASS__, 'civiawards_award_panel', $prefix, []);
    return $r;
  }

  /**
   * Returns the list of fields that can be exported
   *
   * @param bool $prefix
   *
   * @return array
   */
  public static function &export($prefix = FALSE) {
    $r = CRM_Core_DAO_AllCoreTables::getExports(__CLASS__, 'civiawards_award_panel', $prefix, []);
    return $r;
  }

  /**
   * Returns the list of indices
   *
   * @param bool $localize
   *
   * @return array
   */
  public static function indices($localize = TRUE) {
    $indices = [
      'unique_case_type_panel_title' => [
        'name' => 'unique_case_type_panel_title',
        'field' => [
          0 => 'title',
          1 => 'case_type_id',
        ],
        'localizable' => FALSE,
        'unique' => TRUE,
        'sig' => 'civicrm_civiawards_award_panel::1::title::case_type_id',
      ],
    ];
    return ($localize && !empty($indices)) ? CRM_Core_DAO_AllCoreTables::multilingualize(__CLASS__, $indices) : $indices;
  }

}