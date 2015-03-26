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
        Api::onError(function($error){
                print PHP_EOL.'----onError-----------'.PHP_EOL;
                var_dump($error);
            }
        );
        Api::onSuccess(
            function($response){
                print PHP_EOL.'-------onSuccess--------'.PHP_EOL;
            }
        );
        Api::onSend(
            function($url, $params){
                print PHP_EOL.'-----onSend------'.PHP_EOL;
            }
        );
        $this->assertNull(Api::wot()->qwe->qwe());
        $this->assertNotNull(Api::wot()->encyclopedia->tanks());
        $this->assertNotNull(Api::wot()->genAuthUrl());
    }
}
 