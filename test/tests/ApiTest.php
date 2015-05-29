<?php
/**
 * User: akeinhell
 * Date: 06.03.15
 * Time: 13:35
 */

namespace tests;


use WotApi\Api;

class ApiTest extends \PHPUnit_Framework_TestCase
{

    function testCallback()
    {
        $error = $success = $send = false;
        Api::onError(function ($message) use (&$error) {
                $error = true;
                var_dump($message);
            }
        );
        Api::onSuccess(
            function () use (&$success) {
                $success = true;
            }
        );
        Api::onSend(
            function ($url) use (&$send) {
                $send = true;
                printf('fetch [%s]' . PHP_EOL, $url);
            }
        );

        $this->assertNull(Api::wot()->qwe->qwe());
        $this->assertNotNull(Api::wot()->encyclopedia->tanks());
        $this->assertNotNull(Api::getMeta());

        $this->assertTrue($error);
        $this->assertTrue($success);
        $this->assertTrue($send);
    }


    function testAuth()
    {
        $this->assertNotNull(Api::wot()->genAuthUrl());
    }

    /**
     * @dataProvider getApiMethods
     */
    function testApiMethods($project, $group, $name, $params)
    {
        /* @var $wrapper Api */
        $wrapper = call_user_func(array('\WotApi\Api', $project));
        $this->assertNotNull($wrapper->$group->$name($params));
    }

    function getApiMethods()
    {
        return [
            'Enciclopedia/tanks' => ['wot', 'encyclopedia', 'tanks', []],
            'Clan info'          => ['wgn', 'clans', 'list', []],
            'WGTV tags'          => ['wgn', 'wgtv', 'tags', []],
            'wowp'               => ['wowp', 'encyclopedia', 'planes', []],
            'wotb'               => ['wotb', 'encyclopedia', 'vehicles', []],
        ];
    }

    function testSetMethods()
    {
        Api::setApplicationId('demo');
        $this->assertEquals('demo', Api::getApplicationId());

        Api::setToken('token');
        $this->assertEquals('token', Api::getToken());

        Api::setRegion('RU');
        $this->assertEquals('RU', Api::getRegion());

        Api::setProject('wot');
        $this->assertEquals('wot', Api::getProject());
    }

}
 