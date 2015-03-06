<?php
/**
 * User: akeinhell
 * Date: 06.03.15
 * Time: 13:35
 */

namespace tests;


use WotApi\Api;

class ApiTest extends \PHPUnit_Framework_TestCase {

    function testAuth()
    {
        $this->assertNotNull(Api::wot()->genAuthUrl());
    }
}
 