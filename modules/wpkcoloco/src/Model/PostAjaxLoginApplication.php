<?php

namespace WpkColoco\Model;

use Db;
use DbQuery;
use ObjectModel;

/**
 * Class PostAjaxLoginApplication
 */
class PostAjaxLoginApplication extends ObjectModel
{
    /**
     * @var array
     */
    public static $definition = array(
        "table" => "wpk_post_ajax_login_application",
        "primary" => "id_wpk_post_ajax_login_application",
        "fields" => array(
            "id_customer" => array("type" => self::TYPE_INT, "validate" => "isUnsignedInt", "required" => true),
            "is_request_fulfilled" => array("type" => self::TYPE_BOOL, "validate" => "isBool", "required" => true),
            "connection_count" => array("type" => self::TYPE_INT, "validate" => "isUnsignedInt", "required" => true),
            "date_add" => array("type" => self::TYPE_DATE, "validate" => "isDate"),
            "date_upd" => array("type" => self::TYPE_DATE, "validate" => "isDate"),
        ),
    );
    /**
     * @var int
     */
    public $id_customer;
    /**
     * @var bool
     */
    public $is_request_fulfilled;
    /**
     * @var int
     */
    public $connection_count;
    /**
     * @var string
     */
    public $date_add;
    /**
     * @var string
     */
    public $date_upd;

    /**
     * @param $id_customer
     * @return \WpkColoco\Model\PostAjaxLoginApplication|null
     * @throws \PrestaShopException
     */
    public static function getByIdCustomer($id_customer)
    {
        $query = new DbQuery();
        $query->select(self::$definition['primary']);
        $query->from(self::$definition['table']);
        $query->where("id_customer = " . (int)$id_customer);

        $id = (int)Db::getInstance()->getValue($query->build());

        return ($id) ? new PostAjaxLoginApplication($id) : null;
    }

    /**
     * @return bool
     */
    public static function createTables()
    {
        return (bool)Db::getInstance()->execute(
            "CREATE TABLE IF NOT EXISTS " . pSQL(_DB_PREFIX_ . self::$definition['table']) . " (
            `" . pSQL(self::$definition['primary']) . "` int(11) unsigned NOT NULL AUTO_INCREMENT,
            `id_customer` int(11) unsigned NOT NULL,
            `is_request_fulfilled` tinyint(1) NOT NULL DEFAULT 0,
            `connection_count` int(11) unsigned NOT NULL,
            `date_add` datetime,
            `date_upd` datetime,
            PRIMARY KEY (`" . pSQL(self::$definition['primary']) . "`)
        );"
        );
    }

    /**
     * @return bool
     */
    public static function dropTables()
    {
        return (bool)Db::getInstance()->execute(
            "DROP TABLE IF EXISTS " . pSQL(_DB_PREFIX_ . self::$definition['table']) . ";"
        );
    }

    /**
     * @param $id
     * @return bool
     * @throws \PrestaShopException
     */
    public static  function isRequestFullfiled($id){
        $query = new DbQuery();

        $query->select('is_request_fulfilled');
        $query->from(self::$definition['table']);
        $query->where('id_wpk_post_ajax_login_application = '. (int)$id);

        return (bool)Db::getInstance()->getValue($query->build());
    }
}
