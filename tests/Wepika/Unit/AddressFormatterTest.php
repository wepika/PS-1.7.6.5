<?php

namespace Tests\Wepika\Unit;

use PHPUnit\Framework\TestCase;
use WpkColoco\Wepika\Formatter\AddressFormatter;

class AddressFormatterTest extends TestCase
{

    public function testMergeAddress(){
        $street = 'rue des sapins';
        $number = '17';
        $box = '501';

        $address = new AddressFormatter();

        $this->assertEquals( 'rue des sapins, 17 501',$address->mergeAddressParts($street, $number, $box), 'erreur');
    }

}