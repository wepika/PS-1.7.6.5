<?php

namespace WpkColoco\Model;

use Db;
use DbQuery;
use ObjectModel;

/**
 * Class ColocoCustomer
 */
class ColocoCustomer extends ObjectModel
{
    /**
     * @var array
     */
    public static $definition = array(
        "table" => "wpk_coloco_customer",
        "primary" => "id_wpk_coloco_customer",
        "fields" => array(
            "id_customer" => array("type" => self::TYPE_INT, "validate" => "isUnsignedInt", "required" => true),
            "id_address" => array("type" => self::TYPE_INT, "validate" => "isUnsignedInt"),
            "idcli" => array("type" => self::TYPE_STRING, "validate" => "isCleanHtml", "required" => true),
            "email" => array("type" => self::TYPE_STRING, "validate" => "isEmail"),
            "idm" => array("type" => self::TYPE_STRING, "validate" => "isCleanHtml", "size" => 255),
            "nom" => array("type" => self::TYPE_STRING, "validate" => "isCleanHtml", "size" => 255),
            "prenom" => array("type" => self::TYPE_STRING, "validate" => "isCleanHtml", "size" => 255),
            "dtnai" => array("type" => self::TYPE_STRING, "validate" => "isCleanHtml", "size" => 255),
            "gsm" => array("type" => self::TYPE_STRING, "validate" => "isCleanHtml", "size" => 255),
            "adr1" => array("type" => self::TYPE_STRING, "validate" => "isCleanHtml", "size" => 255),
            "adr2" => array("type" => self::TYPE_STRING, "validate" => "isCleanHtml", "size" => 255),
            "adr3" => array("type" => self::TYPE_STRING, "validate" => "isCleanHtml", "size" => 255),
            "adr4" => array("type" => self::TYPE_STRING, "validate" => "isCleanHtml", "size" => 255),
            "cp" => array("type" => self::TYPE_STRING, "validate" => "isCleanHtml", "size" => 255),
            "ville" => array("type" => self::TYPE_STRING, "validate" => "isCleanHtml", "size" => 255),
            "pays" => array("type" => self::TYPE_STRING, "validate" => "isCleanHtml", "size" => 255),
            "entity" => array("type" => self::TYPE_STRING, "validate" => "isCleanHtml"),
            "idlng" => array("type" => self::TYPE_STRING, "validate" => "isCleanHtml", "size" => 255),
            "explicit_member" => array("type" => self::TYPE_BOOL, "validate" => "isBool", "required" => true),
            "date_add" => array("type" => self::TYPE_DATE, "validate" => "isDate"),
            "date_upd" => array("type" => self::TYPE_DATE, "validate" => "isDate"),
        )
    );
    /**
     * @var int
     */
    public $id_customer;
    /**
     * @var int
     */
    public $id_address;
    /**
     * @var string
     */
    public $idcli;
    /**
     * @var string
     */
    public $email;
    /**
     * @var string
     */
    public $idm;
    /**
     * @var string
     */
    public $nom;
    /**
     * @var string
     */
    public $prenom;
    /**
     * @var string
     */
    public $dtnai;
    /**
     * @var string
     */
    public $gsm;
    /**
     * @var string
     */
    public $adr1;
    /**
     * @var string
     */
    public $adr2;
    /**
     * @var string
     */
    public $adr3;
    /**
     * @var string
     */
    public $adr4;
    /**
     * @var string
     */
    public $cp;
    /**
     * @var string
     */
    public $ville;
    /**
     * @var string
     */
    public $pays;
    /**
     * @var string
     */
    public $entity;
    /**
     * @var string
     */
    public $idlng;
    /**
     * This property equals 0 if a customer has never asked for a Coloco Card but is a PS customer
     *
     * @var bool
     */
    public $explicit_member = 1;
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
     * @return \WpkColoco\Model\ColocoCustomer|null
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    public static function getByIdCustomer($id_customer)
    {
        $id_customer = (int)$id_customer;

        if ($id_customer <= 0) {
            return null;
        }

        $query = new DbQuery();
        $query->select(self::$definition['primary']);
        $query->from(self::$definition['table']);
        $query->where('id_customer = ' . (int)$id_customer);

        $id_coloco_customer = (int)Db::getInstance()->getValue($query->build());

        if ($id_coloco_customer > 0) {
            return new ColocoCustomer($id_coloco_customer);
        }

        return null;
    }

    /**
     * @return bool
     */
    public static function createTables()
    {
        return Db::getInstance()->execute(
            "CREATE TABLE IF NOT EXISTS " . pSQL(_DB_PREFIX_ . self::$definition['table']) . " (
            `" . pSQL(self::$definition['primary']) . "` int(11) unsigned NOT NULL AUTO_INCREMENT,
            `id_customer` int(11) unsigned NOT NULL,
            `id_address` int(11) unsigned,
            `idcli` varchar(25) NOT NULL,
            `email` varchar(255),
            `idm` varchar(255),
            `nom` varchar(255),
            `prenom` varchar(255),
            `dtnai` varchar(255),
            `gsm` varchar(255),
            `adr1` varchar(255),
            `adr2` varchar(255),
            `adr3` varchar(255),
            `adr4` varchar(255),
            `cp` varchar(255),
            `ville` varchar(255),
            `pays` varchar(255),
            `entity` text,
            `idlng` varchar(255),
            `explicit_member` tinyint(1) DEFAULT 1,
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
        return Db::getInstance()->execute(
            "DROP TABLE IF EXISTS " . pSQL(_DB_PREFIX_ . self::$definition['table']) . ";"
        );
    }

    public static function getByIdCli($idcli)
    {

        $query = new DbQuery();
        $query->select(self::$definition['primary']);
        $query->from(self::$definition['table']);
        $query->where('idcli = "' . pSQL($idcli) . '"');

        $id_coloco_customer = (int)Db::getInstance()->getValue($query->build());

        if ($id_coloco_customer > 0) {
            return new ColocoCustomer($id_coloco_customer);
        }

        return null;
    }

    public function add($auto_date = true, $null_values = false)
    {
        // When pulling data form api, entity is an array and must be stored as a json
        if (is_array($this->entity)) {
            $this->entity = json_encode($this->entity);
        }

        return parent::add($auto_date, $null_values);
    }

    public function update($null_values = false)
    {
        // When pulling data form api, entity is an array and must be stored as a json
        if (is_array($this->entity)) {
            $this->entity = json_encode($this->entity);
        }

        return parent::update($null_values);
    }

    public function save($null_values = false, $auto_date = true)
    {
        // When pulling data form api, entity is an array and must be stored as a json
        if (is_array($this->entity)) {
            $this->entity = json_encode($this->entity);
        }

        return parent::save($null_values, $auto_date);
    }

    /**
     * Use obj colocoCustomer
     * Check the lenght
     * return the card type
     */
    public function getCardType()
    {
        if (strlen($this->idcli) !== 12) {
            return false;
        }

        switch (substr($this->idcli, 0, 2)) {
            case '21':
                $type = 'physics';
                break;
            case '31':
                $type = 'digital';
                break;
            default:
                return false;
                break;
        }

        return $type;
    }
}