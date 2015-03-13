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
        Api::onError(function(){
                print 'onError trigger'.PHP_EOL;
            }
        );
        Api::onSuccess(
            function(){print 'onSuccess trigger'.PHP_EOL;}
        );
        Api::onSend(
            function(){
                print 'onSend trigger'.PHP_EOL;
            }
        );
        $this->assertNull(Api::wot()->qwe->qwe());
        $this->assertNotNull(Api::wot()->encyclopedia->tanks());
    }
}
 