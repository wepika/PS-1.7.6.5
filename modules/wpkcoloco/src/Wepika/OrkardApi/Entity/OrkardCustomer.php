<?php

namespace WpkColoco\Wepika\OrkardApi\Entity;

/**
 * Class OrkardCustomer
 *
 * @package WpkColoco\Wepika\OrkardApi\Entity
 */
class OrkardCustomer
{
    /**
     * @var string
     * Numéro de carte du client
     * Required
     */
    private $idcli;
    /**
     * @var string (hash md5 32 hexa chars)
     * Mot de passe du client encodé
     * Required
     */
    private $pwd = "NOT USED";
    /**
     * @var int
     * Magasin de référence
     * Required
     */
    private $idm;
    /**
     * @var int
     * Card type
     * Fixed value for Magento : 4 (ask value for prestashop)
     * Required
     */
    private $idtcrt = 4;
    /**
     * @var string
     * Nom
     * Required
     */
    private $nom;
    /**
     * @var string
     * Prénom
     * Required
     */
    private $prenom = '';
    /**
     * @var string
     * Numéro de carte d'identité
     */
    private $cin= '';
    /**
     * @var int
     * Etat
     * 0 : carte inactive
     * 1 : carte active (par défaut)
     * Required
     */
    private $etat = 1;
    /**
     * @var int
     * Titre de civilité
     * 0 : Non renseigné
     * 1 : Monsieur
     * 2 : Madame
     */
    private $civ= 0;
    /**
     * @var string
     * Date de naissance au format yyyy-mm-dd
     */
    private $dtnai;
    /**
     * @var string
     * Téléphone mobile
     * Caractères spéciaux autorisés : (),. /+
     */
    private $gsm= '';
    /**
     * @var string
     * Email
     */
    private $email= '';
    /**
     * @var string
     * Complément d'adresse #1
     */
    private $adr2= '';
    /**
     * @var string
     * Complément d'adresse #2
     */
    private $adr3= '';
    /**
     * @var string
     * Complément d'adresse #3
     */
    private $adr4= '';
    /**
     * @var string
     * Code postal
     */
    private $cp= '';
    /**
     * @var string
     * Ville
     */
    private $ville= '';
    /**
     * @var string
     * Pays
     */
    private $pays= '';
    /**
     * @var int
     * Autoriser le phoning
     * 0 : non
     * 1 : oui
     * NOT USED
     */
    private $o_phoning = 0;
    /**
     * @var int
     * Coloco emails
     * 0 : non
     * 1 : oui
     */
    private $o_emailing = 0;
    /**
     * @var int
     * Envoyer un email enseigne
     * 0 : non
     * 1 : oui
     * NOT USED
     */
    private $o_emailing2 = 0;
    /**
     * @var int
     * Coloco newsletter
     * 0 : non
     * 1 : oui
     */
    private $o_texting = 0;
    /**
     * @var int
     * Tom&Co Newsletter
     * 0 : non
     * 1 : oui
     */
    private $o_texting2 = 0;
    /**
     * @var int
     * Problème d'adresse
     * 0 : non
     * 1 : oui
     * NOT USED
     */
    private $pbadr = 0;
    /**
     * @var int
     * Doublon famille
     * 0 : non
     * 1 : oui
     * NOT USED
     */
    private $dblfam = 0;
    /**
     * @var int
     * Envoi sms à faire
     * 0 : non
     * 1 : oui
     * NOT USED
     */
    private $envsms = 0;
    /**
     * @var int
     * Envoie email à faire
     * 0 : non
     * 1 : oui
     * NOT USED
     */
    private $envemail = 0;
    /**
     * @var int
     * Coloco General Condition Agreement
     * 0 : non
     * 1 : oui
     */
    private $stat1 = 0;
    /**
     * @var int
     * Generic field
     * 0 : non
     * 1 : oui
     * NOT USED
     */
    private $stat2 = 0;
    /**
     * @var int
     * Generic field
     * 0 : non
     * 1 : oui
     * NOT USED
     */
    private $stat3 = 0;
    /**
     * @var int
     * Generic field
     * 0 : non
     * 1 : oui
     * NOT USED
     */
    private $stat4 = 0;
    /**
     * @var int
     * Customer screen agreement
     * 0 : non
     * 1 : oui
     */
    private $stat5 = 0;
    /**
     * @var int
     * Je suis un professionnel
     * 0 : non
     * 1 : oui
     */
    private $act = 0;
    /**
     * @var int
     * Situation familiale
     * 0 : inconnu
     * 1 : seul(e)
     * 2 : en couple
     */
    private $sitfam = 0;
    /**
     * @var string
     * Commentaire
     */
    private $com = '';
    /**
     * @var int
     * Catégorie sociale
     * 0 : inconnue
     */
    private $catsoc = 0;
    /**
     * @var int
     * Autoriser l'envoie de sms
     * 0 : non
     * 1 : oui
     */
    private $texting = 0;
    /**
     * @var array
     * Json string exemple : [{'sexe':1, 'dtnai': '2014-01-01'}, {'sexe':2, 'dtnai':'1999-01-01'}]
     */
    private $enfs = array();
    /**
     * @var string
     * Cancellation date for customer information will be cancelled
     * Format yyyy-mm-dd
     */
    private $delete_date;
    /**
     * @var array
     * Json structure of the master data of the pets
     * possessed by the client
     */
    private $entity = array();
    /**
     * @var string
     * Language used by the customer
     * en : English
     * fr : Français
     * fr_BE : Français belge
     * nl : Néerlandais
     */
    private $idlng;
    /*
     * Nouveau champs sorti de l'api de prod :)
     * */

    /**
     * @var string
     */
    private $adr1;
    /**
     * @var string
     */
    private $adr5;
    /**
     * @var string
     */
    private $soldem;
    /**
     * @var string
     */
    private $dtpert;
    /**
     * @var string
     */
    private $nfoyer;
    /**
     * @var string
     */
    private $soldep;
    /**
     * @var string
     */
    private $adp;
    /**
     * @var string
     */
    private $indicmagazine;
    /**
     * @var string
     */
    private $passage;
    /**
     * @var string
     */
    private $gtu;
    /**
     * @var string
     */
    private $iducre;
    /**
     * @var string
     */
    private $seg2;
    /**
     * @var string
     */
    private $totgainautremagv;
    /**
     * @var string
     */
    private $tel2;
    /**
     * @var string
     */
    private $totgainautremagp;
    /**
     * @var string
     */
    private $compte;
    /**
     * @var string
     */
    private $soldemf;
    /**
     * @var string
     */
    private $idmvis;
    /**
     * @var string
     */
    private $dcgne;
    /**
     * @var string
     */
    private $soldemp;
    /**
     * @var string
     */
    private $texting2;
    /**
     * @var string
     */
    private $dtm_tel;
    /**
     * @var string
     */
    private $tel;
    /**
     * @var string
     */
    private $totca;
    /**
     * @var string
     */
    private $frequence;
    /**
     * @var string
     */
    private $seg;
    /**
     * @var string
     */
    private $info2;
    /**
     * @var string
     */
    private $info5;
    /**
     * @var string
     */
    private $nenf;
    /**
     * @var string
     */
    private $dtsoldef;
    /**
     * @var string
     */
    private $enseigne;
    /**
     * @var string
     */
    private $phoning;
    /**
     * @var string
     */
    private $idclipert;
    /**
     * @var string
     */
    private $dtsoldep;
    /**
     * @var string
     */
    private $idu;
    /**
     * @var string
     */
    private $dtsolde;
    /**
     * @var string
     */
    private $indica;
    /**
     * @var string
     */
    private $dtvis;
    /**
     * @var string
     */
    private $idumod;
    /**
     * @var string
     */
    private $tcpt;
    /**
     * @var string
     */
    private $info1;
    /**
     * @var string
     */
    private $info3;
    /**
     * @var string
     */
    private $info4;
    /**
     * @var string
     */
    private $typecarte;
    /**
     * @var string
     */
    private $rsc;
    /**
     * @var string
     */
    private $dtm_adr;
    /**
     * @var string
     */
    private $totvis;
    /**
     * @var string
     */
    private $dtmod;
    /**
     * @var string
     */
    private $idens;
    /**
     * @var string
     */
    private $dtm;
    /**
     * @var string
     */
    private $dtc;
    /**
     * @var string
     */
    private $dtdist;
    /**
     * @var string
     */
    private $emailing;
    /**
     * @var string
     */
    private $cards;
    /**
     * @var string
     */
    private $dtcre;
    /**
     * @var string
     */
    private $phoning2;
    /**
     * @var string
     */
    private $fax;
    /**
     * @var string
     */
    private $emailing2;
    /**
     * @var string
     */
    private $dtcon;
    /**
     * @var string
     */
    private $idclip;
    /**
     * @var string
     */
    private $test;
    /**
     * @var string
     */
    private $idupert;
    /**
     * @var string
     */
    private $status;
    /**
     * @var string
     */
    private $dtm_email;
    /**
     * @var string
     */
    private $soldepp;
    /**
     * @var string
     */
    private $chrono;
    /**
     * @var string
     */
    private $crtenv;
    /**
     * @var string
     */
    private $dtcli;
    /**
     * @var string
     */
    private $soldepf;
    /**
     * @var string
     */
    private $opts;

    /**
     * @param array $array
     * @return $this
     */
    public function hydrateFromArray($array)
    {
        foreach ($array as $key => $value) {
            if (
                property_exists(self::class, $key)
                && method_exists(new OrkardCustomer(), 'set' . str_replace('_', '', ucfirst($key)))
            ) {
                $this->{'set' . str_replace('_', '', ucfirst($key))}($value);
            }
        }

        return $this;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $temp = (array)$this;
        $array = array();
        foreach ($temp as $k => $v) {
            // Clean property name
            $k = preg_match('/^\x00(?:.*?)\x00(.+)/', $k, $matches) ? $matches[1] : $k;
            // To avoid properties that are not implemented yet to be sent to api
            if (
                property_exists(self::class, $k)
                && method_exists(new OrkardCustomer(), 'set' . str_replace('_', '', ucfirst($k)))
            ) {
                $array[$k] = $v;
            }
        }
        return $array;
    }

    /**
     * @return string
     */
    public function getIdcli()
    {
        return $this->idcli;
    }

    /**
     * @param string $idcli
     */
    public function setIdcli($idcli)
    {
        $this->idcli = $idcli;
    }

    /**
     * @return string
     */
    public function getPwd()
    {
        return $this->pwd;
    }

    /**
     * @param string $pwd
     */
    public function setPwd($pwd)
    {
        $this->pwd = $pwd;
    }

    /**
     * @return int
     */
    public function getIdm()
    {
        return $this->idm;
    }

    /**
     * @param int $idm
     */
    public function setIdm($idm)
    {
        $this->idm = $idm;
    }

    /**
     * @return int
     */
    public function getIdtcrt()
    {
        return $this->idtcrt;
    }

    /**
     * @param int $idtcrt
     */
    public function setIdtcrt($idtcrt)
    {
        $this->idtcrt = $idtcrt;
    }

    /**
     * @return string
     */
    public function getNom()
    {
        return $this->nom;
    }

    /**
     * @param string $nom
     */
    public function setNom($nom)
    {
        $this->nom = $nom;
    }

    /**
     * @return string
     */
    public function getPrenom()
    {
        return $this->prenom;
    }

    /**
     * @param string $prenom
     */
    public function setPrenom($prenom)
    {
        $this->prenom = $prenom;
    }

    /**
     * @return string
     */
    public function getCin()
    {
        return $this->cin;
    }

    /**
     * @param string $cin
     */
    public function setCin($cin)
    {
        $this->cin = $cin;
    }

    /**
     * @return int
     */
    public function getEtat()
    {
        return $this->etat;
    }

    /**
     * @param int $etat
     */
    public function setEtat($etat)
    {
        $this->etat = $etat;
    }

    /**
     * @return int
     */
    public function getCiv()
    {
        return $this->civ;
    }

    /**
     * @param int $civ
     */
    public function setCiv($civ)
    {
        $this->civ = $civ;
    }

    /**
     * @return string
     */
    public function getDtnai()
    {
        return $this->dtnai;
    }

    /**
     * @param string $dtnai
     */
    public function setDtnai($dtnai)
    {
        if ($dtnai !='0000-00-00') {
            $this->dtnai = $dtnai;
        }

    }

    /**
     * @return string
     */
    public function getGsm()
    {
        return $this->gsm;
    }

    /**
     * @param string $gsm
     */
    public function setGsm($gsm)
    {
        $this->gsm = $gsm;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param string $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * @return string
     */
    public function getAdr2()
    {
        return $this->adr2;
    }

    /**
     * @param string $adr2
     */
    public function setAdr2($adr2)
    {
        $this->adr2 = $adr2;
    }

    /**
     * @return string
     */
    public function getAdr3()
    {
        return $this->adr3;
    }

    /**
     * @param string $adr3
     */
    public function setAdr3($adr3)
    {
        $this->adr3 = $adr3;
    }

    /**
     * @return string
     */
    public function getAdr4()
    {
        return $this->adr4;
    }

    /**
     * @param string $adr4
     */
    public function setAdr4($adr4)
    {
        $this->adr4 = $adr4;
    }

    /**
     * @return string
     */
    public function getCp()
    {
        return $this->cp;
    }

    /**
     * @param string $cp
     */
    public function setCp($cp)
    {
        $this->cp = $cp;
    }

    /**
     * @return string
     */
    public function getVille()
    {
        return $this->ville;
    }

    /**
     * @param string $ville
     */
    public function setVille($ville)
    {
        $this->ville = $ville;
    }

    /**
     * @return string
     */
    public function getPays()
    {
        return $this->pays;
    }

    /**
     * @param string $pays
     */
    public function setPays($pays)
    {
        $this->pays = $pays;
    }

    /**
     * @return int
     */
    public function getOPhoning()
    {
        return $this->o_phoning;
    }

    /**
     * @param int $o_phoning
     */
    public function setOPhoning($o_phoning)
    {
        $this->o_phoning = $o_phoning;
    }

    /**
     * @return int
     */
    public function getOEmailing()
    {
        return $this->o_emailing;
    }

    /**
     * @param int $o_emailing
     */
    public function setOEmailing($o_emailing)
    {
        $this->o_emailing = $o_emailing;
    }

    /**
     * @return int
     */
    public function getOEmailing2()
    {
        return $this->o_emailing2;
    }

    /**
     * @param int $o_emailing2
     */
    public function setOEmailing2($o_emailing2)
    {
        $this->o_emailing2 = $o_emailing2;
    }

    /**
     * @return int
     */
    public function getOTexting()
    {
        return $this->o_texting;
    }

    /**
     * @param int $o_texting
     */
    public function setOTexting($o_texting)
    {
        $this->o_texting = $o_texting;
    }

    /**
     * @return int
     */
    public function getOTexting2()
    {
        return $this->o_texting2;
    }

    /**
     * @param int $o_texting2
     */
    public function setOTexting2($o_texting2)
    {
        $this->o_texting2 = $o_texting2;
    }

    /**
     * @return int
     */
    public function getPbadr()
    {
        return $this->pbadr;
    }

    /**
     * @param int $pbadr
     */
    public function setPbadr($pbadr)
    {
        $this->pbadr = $pbadr;
    }

    /**
     * @return int
     */
    public function getDblfam()
    {
        return $this->dblfam;
    }

    /**
     * @param int $dblfam
     */
    public function setDblfam($dblfam)
    {
        $this->dblfam = $dblfam;
    }

    /**
     * @return int
     */
    public function getEnvsms()
    {
        return $this->envsms;
    }

    /**
     * @param int $envsms
     */
    public function setEnvsms($envsms)
    {
        $this->envsms = $envsms;
    }

    /**
     * @return int
     */
    public function getEnvemail()
    {
        return $this->envemail;
    }

    /**
     * @param int $envemail
     */
    public function setEnvemail($envemail)
    {
        $this->envemail = $envemail;
    }

    /**
     * @return int
     */
    public function getStat1()
    {
        return $this->stat1;
    }

    /**
     * @param int $stat1
     */
    public function setStat1($stat1)
    {
        $this->stat1 = $stat1;
    }

    /**
     * @return int
     */
    public function getStat2()
    {
        return $this->stat2;
    }

    /**
     * @param int $stat2
     */
    public function setStat2($stat2)
    {
        $this->stat2 = $stat2;
    }

    /**
     * @return int
     */
    public function getStat3()
    {
        return $this->stat3;
    }

    /**
     * @param int $stat3
     */
    public function setStat3($stat3)
    {
        $this->stat3 = $stat3;
    }

    /**
     * @return int
     */
    public function getStat4()
    {
        return $this->stat4;
    }

    /**
     * @param int $stat4
     */
    public function setStat4($stat4)
    {
        $this->stat4 = $stat4;
    }

    /**
     * @return int
     */
    public function getStat5()
    {
        return $this->stat5;
    }

    /**
     * @param int $stat5
     */
    public function setStat5($stat5)
    {
        $this->stat5 = $stat5;
    }

    /**
     * @return int
     */
    public function getAct()
    {
        return $this->act;
    }

    /**
     * @param int $act
     */
    public function setAct($act)
    {
        $this->act = $act;
    }

    /**
     * @return int
     */
    public function getSitfam()
    {
        return $this->sitfam;
    }

    /**
     * @param int $sitfam
     */
    public function setSitfam($sitfam)
    {
        $this->sitfam = $sitfam;
    }

    /**
     * @return string
     */
    public function getCom()
    {
        return $this->com;
    }

    /**
     * @param string $com
     */
    public function setCom($com)
    {
        $this->com = $com;
    }

    /**
     * @return int
     */
    public function getCatsoc()
    {
        return $this->catsoc;
    }

    /**
     * @param int $catsoc
     */
    public function setCatsoc($catsoc)
    {
        $this->catsoc = $catsoc;
    }

    /**
     * @return int
     */
    public function getTexting()
    {
        return $this->texting;
    }

    /**
     * @param int $texting
     */
    public function setTexting($texting)
    {
        $this->texting = $texting;
    }

    /**
     * @return string
     */
    public function getEnfs()
    {
        return $this->enfs;
    }

    /**
     * @param array $enfs
     */
    public function setEnfs($enfs)
    {
        $this->enfs = $enfs;
    }

    /**
     * @return string
     */
    public function getDeleteDate()
    {
        return $this->delete_date;
    }

    /**
     * @param string $delete_date
     */
    public function setDeleteDate($delete_date)
    {
        $this->delete_date = $delete_date;
    }

    /**
     * @return array
     */
    public function getEntity()
    {
        return $this->entity;
    }

    /**
     * @param string $entity
     */
    public function setEntity($entity)
    {
        $this->entity = $entity;
    }

    /**
     * @return string
     */
    public function getIdlng()
    {
        return $this->idlng;
    }

    /**
     * @param string $idlng
     */
    public function setIdlng($idlng)
    {
        $this->idlng = $idlng;
    }

    /**
     * @return string
     */
    public function getAdr1()
    {
        return $this->adr1;
    }

    /**
     * @param string $adr1
     */
    public function setAdr1($adr1)
    {
        $this->adr1 = $adr1;
    }
}