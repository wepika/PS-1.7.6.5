<?php

namespace WpkColoco\Wepika\Formatter;

/**
 * Class AddressFormatter
 *
 * @package WpkColoco\Wepika\Formatter
 */
class AddressFormatter
{
    /**
     * @var string
     */
    const REGEX_ADDRESS_PATTERN = '/([A-Za-zàáèéìíòóùúâêîôûäëïöüãõçœßėńśŕł\'¨’`".\/() -]+, ([0-9]{1,4}[a-z]?[ \/-]?([a-z]|rez|bis|b(oi|oî)?t(e)?( [a-z])?)? ?[0-9]{0,3}\b))/i';
    /**
     * @var string
     */
    const REGEX_ADDRESS_NUMBER_PATTERN = "/([0-9]{1,4}[a-z]?[ \/-]?([a-z]|rez|bis|b(oi|oî)?t(e)?( [a-z])?)? ?[0-9]{0,3}\b)/i";

    /**
     * @param string $formattedAddress
     * @return array
     */
    public function getAddressParts($formattedAddress)
    {
        $comaPos = strpos($formattedAddress, ",");
        $streetPart = trim(str_replace(",", "", substr($formattedAddress, 0, $comaPos)));
        $afterStreetPart = explode(" ", trim(str_replace(",", "", substr($formattedAddress, $comaPos))));
        $numberPart = array_pop($afterStreetPart);
        $boxPart = implode(" ", $afterStreetPart);

        return array(
            'street' => $streetPart,
            'number' => $numberPart,
            'box' => $boxPart,
        );
    }

    /**
     * @param string $street
     * @param string $number
     * @param string $box
     * @return string
     */
    public function mergeAddressParts($street, $number, $box)
    {
        $mergedAddress = $street;

        if ($number) {
            $mergedAddress .= ", " . $number;
        }

        if ($box) {
            $mergedAddress .= " " . $box;
        }

        return $mergedAddress;
    }

    /**
     * @param string $address1
     * @param string $address2
     * @return string
     */
    public function formatAddress($address1, $address2)
    {
        $address = $address1;
        $cpl_address = $address2;

        if (!empty($address)) {
            if (!empty($cpl_address)) {
                // pré process du cpl_address car parfois y a pas besoin de s'en charger (genre c'est un doublon de l'adresse ou un truc du genre, ou alors le numero de l'adresse etc
                if ($cpl_address == 'NULL') {
                    $cpl_address = null;
                } else {
                    $address = trim(ucfirst($address));
                    $cpl_address = trim(ucfirst($cpl_address));
                    // si les adresses et cpl se valent, si y a ni lettre ni chiffres, et si y a pas de chiffres => à priori on en a pas besoin pour formatter l'adresse
                    if (strtolower($address) == strtolower($cpl_address) || !preg_match(
                            '/[0-9a-z]/i',
                            $cpl_address
                        ) || !preg_match('/[0-9]/', $cpl_address)) {
                        $cpl_address = null;
                    } else {
                        if (empty($address) || $address == 'NULL' || $address == null) {
                            $address = $cpl_address;
                        } else {
                            // si le cpl ressemble a un numero de rue
                            preg_match(self::REGEX_ADDRESS_NUMBER_PATTERN, $cpl_address, $matches);
                            if (!empty($matches) && $matches[0] == $cpl_address) {
                                // et que le numero n'est psa déjà contenu dans l'adresse
                                if (strpos($address, $matches[0]) === false) {
                                    // et que l'adresse ne contient déjà pas un numero de rue
                                    preg_match(self::REGEX_ADDRESS_NUMBER_PATTERN, $address, $matches);
                                    if (empty($matches)) {
                                        // alors on ajoute le numero cpl
                                        $address .= $cpl_address;
                                    }
                                }
                            }
                        }
                    }
                }
            }

            // si elle est déjà ok, y a pas besoin de se faire chier
            preg_match(self::REGEX_ADDRESS_PATTERN, $address, $matches);
            if (empty($matches) || $matches[0] != $address) {
                // parfois y a des fails alors :
                $address = str_replace('\\', '', $address);
                $address = str_replace(';', ',', $address);
                $address = str_replace(',,', ',', $address);
                $address = str_replace(array("N°", "n°", "N:", "*", "#"), "", $address);
                $address = trim($address);
                // pas de complement d'adresse
                if (preg_match('/[0-9]/', $address)) {
                    //contient des chiffres
                    $chunks = $this->getPosOfNumbersInString($address);

                    if (count($chunks) == 1) {
                        // si y a qu'un seul pack de chiffres
                        $chunk = $chunks[0];
                        $number = implode("", $chunks[0]);

                        if ($this->array_key_first($chunk) == 0) {
                            // si l'adresse commence par le n°

                            // trouver la position du premier " " ou ","

                            $charPos = 0;
                            $dotPos = strpos($address, ' ');
                            $wpPos = strpos($address, ',');

                            if ($wpPos !== false && $dotPos !== false) {
                                if ($wpPos <= $dotPos) {
                                    $charPos = $wpPos;
                                } else {
                                    $charPos = $dotPos;
                                }
                            } else {
                                if ($wpPos !== false) {
                                    $charPos = $wpPos;
                                } else {
                                    if ($dotPos !== false) {
                                        $charPos = $dotPos;
                                    }
                                }
                            }

                            if ($charPos) {
                                $partToReplace = substr($address, 0, $charPos + 1);

                                $address = ucfirst(trim(substr($address, strlen($partToReplace))));
                                $address .= ", " . substr($partToReplace, 0, strlen($partToReplace) - 1);
                            }
                        } else {
                            // ne commence pas par le numero
                            if (substr($address, 0, strlen($address) - strlen($number)) == str_replace(
                                    $number,
                                    '',
                                    $address
                                )) {
                                // si on termine par le numero
                                $tAddress = substr($address, 0, strlen($address) - strlen($number));

                                $tAddress = trim($tAddress);
                                // si le dernier char est une virgule
                                if (substr($tAddress, -1) == ",") {
                                    $tAddress = substr($tAddress, 0, strlen($tAddress) - 1);
                                }

                                $address = $tAddress . ", " . $number;
                            } else {
                                // le numéro est qq part

                                // il y a potentiellement une lettre ajoutée au numero
                                $potentialNumberPart = substr($address, $this->array_key_first($chunk));

                                preg_match(self::REGEX_ADDRESS_NUMBER_PATTERN, $potentialNumberPart, $matches);
                                if (empty($matches) || $matches[0] != $address) {
                                    $address = str_replace($matches[0], "", $address);
                                    $address = str_replace(",", "", $address);
                                    $address = trim($address);
                                    $address .= ", " . $matches[0];
                                    $address = ucfirst($address);
                                    $address = trim($address);
                                }
                            }
                        }
                    } else {
                        // si plusieurs

                        // on va déjà check si j'arrive à extraire la partie numéro de l'adresse, et puis recomposer l'adresse voir si ça fonctionne pour certaines
                        // il y a potentiellement une lettre ajoutée au numero
                        preg_match(self::REGEX_ADDRESS_NUMBER_PATTERN, $address, $matches);
                        if (empty($matches) || $matches[0] != $address) {
                            $address = str_replace($matches[0], "", $address);
                            $address = str_replace(",", "", $address);
                            $address = trim($address);
                            $address .= ", " . $matches[0];
                            $address = ucfirst($address);
                            $address = trim($address);
                        }
                    }
                }
            }
        }

        $address = ucfirst(trim($address));
        return $address;
    }

    /**
     * @param string $string
     * @return array
     */
    private function getPosOfNumbersInString($string)
    {
        $pos = array();
        foreach (str_split($string) as $p => $c) {
            if (preg_match('/[0-9]/', $c)) {
                $pos[$p] = $c;
            }
        }

        $count = array_keys($pos)[0];
        $chunkCount = 0;
        $chunks = array();
        foreach ($pos as $key => $value) {
            if ($key == $count) {
                $count++;
            } else {
                $chunkCount++;
                $count = $key + 1;
            }
            $chunks[$chunkCount][$key] = $value;
        }

        return $chunks;
    }

    /**
     * @param array $arr
     * @return int|string|null
     */
    private function array_key_first(array $arr)
    {
        foreach ($arr as $key => $unused) {
            return $key;
        }
        return null;
    }
}