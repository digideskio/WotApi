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
        Api::onSuccess(function(){var_dump('onSuccess');var_dump(__METHOD__.__LINE__.PHP_EOL);die;});
        Api::onError(function(){var_dump('onError');var_dump(__METHOD__.__LINE__.PHP_EOL);die;});
//        Api::onSend(function(){var_dump('onSend');var_dump(__METHOD__.__LINE__.PHP_EOL);die;});
        $this->assertNotNull(Api::wot()->qwe->qwe());
    }
}
 