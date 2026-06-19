<?php

namespace Vivawallet\VivawalletPhp\Tests;

use Vivawallet\VivawalletPhp\Api\MerchantClient;

class MerchantTest extends BearerAuthenticationTest
{
    public function testMerchantInfo()
    {
        $demoBearerAuthentication = self::testGetBackScopeDemoBearerAuthentication();
        $client     = new MerchantClient($demoBearerAuthentication);
        $response = $client->getInfo();

        $this->assertTrue($response->isSuccessful());
    }

}
