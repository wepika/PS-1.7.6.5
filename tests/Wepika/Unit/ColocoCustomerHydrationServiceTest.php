<?php

namespace Tests\Wepika\Unit;

use PHPUnit\Framework\TestCase;
use WpkColoco\Model\ColocoCustomer;
use WpkColoco\Service\ColocoCustomer\ColocoCustomerHydrationService;
use WpkColoco\Wepika\OrkardApi\Entity\OrkardCustomer;

class ColocoCustomerHydrationServiceTest extends TestCase
{
    public function testColocoCustomerCanBeHydratedWithOneProperty()
    {
        $colocoCustomerHydrationService = new ColocoCustomerHydrationService();
        $colocoCustomer = $colocoCustomerHydrationService->hydrateFromOrkardCustomer(
            new ColocoCustomer(),
            $this->getOrkardCustomerWithOnePropertySet()
        );

        $this->assertEquals('Antoine', $colocoCustomer->nom);
    }

    private function getOrkardCustomerWithOnePropertySet()
    {
        $orkardCustomer = new OrkardCustomer();
        $orkardCustomer->setNom('Antoine');

        return $orkardCustomer;
    }
}