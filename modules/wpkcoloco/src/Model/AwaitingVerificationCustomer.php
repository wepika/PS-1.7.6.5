<?php

namespace WpkColoco\Model;

use Db;
use DbQuery;
use ObjectModel;

/**
 * Class AwaitingVerificationCustomer
 *
 * @package WpkColoco\Model
 */
class AwaitingVerificationCustomer extends ObjectModel
{
    /**
     * @var array
     */
    public static $definition = array(
        'table' => 'wpk_coloco_awaiting_verification_customer',
        'primary' => 'id_wpk_coloco_awaiting_verification_customer',
        'fields' => array(
            'id_customer' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt', 'required' => true),
            'request_count' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'),
            'idcli' => array('type' => self::TYPE_STRING, 'validate' => 'isCleanHtml'),
            "date_add" => array("type" => self::TYPE_DATE, "validate" => "isDate"),
            "date_upd" => array("type" => self::TYPE_DATE, "validate" => "isDate"),
        ),
    );
    /**
     * @var int
     */
    public $id_customer;
    /**
     * @var int
     */
    public $request_count;
    /**
     * @var string
     */
    public $idcli;
    /**
     * @var string
     */
    public $date_add;
    /**
     * @var string
     */
    public $date_upd;

    /**
     * @param int $id_customer
     * @return \WpkColoco\Model\AwaitingVerificationCustomer|null
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    public static function getAwaitingVerificationCustomerByIdCustomer($id_customer)
    {
        $id = self::getIdAwaitingVerificationCustomerByIdCustomer($id_customer);

        return ($id) ? new AwaitingVerificationCustomer($id) : null;
    }

    /**
     * @param int $id_customer
     * @return int
     * @throws \PrestaShopException
     */
    public static function getIdAwaitingVerificationCustomerByIdCustomer($id_customer)
    {
        if (!$id_customer) {
            return 0;
        }

        $query = new DbQuery();
        $query->select(self::$definition['primary']);
        $query->from(self::$definition['table']);
        $query->where('id_customer = ' . (int)$id_customer);

        return (int)Db::getInstance()->getValue($query->build());
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
            `request_count` int(11) unsigned,
            `idcli` varchar(255),
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
}
