<?php
/**
 * User: akeinhell
 * Date: 06.03.15
 * Time: 13:24
 */

namespace WotApi;

use Httpful\Request;

/**
 * Description of Api
 *
 * @author akeinhell
 * @method string setAppid(string $application_id)
 * @method string setToken(string $token)
 * @method string setRegion(string $region)
 * @method string setProject(string $project)
 * @method static \WotApi\Api wot()
 */
class Api
{

    /**
     * @var string базовый URL API
     */
    private static $URL = 'http://api.worldoftanks.%s/%s/';

    /**
     * @var string application_id приложения
     */
    public static $Appid = '';

    /**
     * @var string
     */
    protected static $Project = 'wot';
    /**
     * @var string Регион по умолчанию
     */
    public static $Region = 'ru';

    /**
     * @var \WotApi\Api|null Экземпляр обьекта
     */
    private static $instance = null;

    /**
     * @var null первый метод в API запросе
     */
    private static $action = null;

    /**
     * @var string|null токен пользователя
     */
    public static $token = null;

    /**
     * @var \Closure
     */
    private static $successCallback ;
    private static $errorCallback ;
    private static $sendCallback ;


    /**
     * Создает экземпляр для доступа к API
     * @return \WotApi\Api
     */
    public static function create()
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Генерирует API ссылку
     *
     * @param       string $name
     * @param array        $arguments
     *
     * @return string
     */
    public static function createUrl($name, $arguments = array())
    {
        $api = self::$action . '/' . $name . '/';
        $args = array('application_id' => self::$Appid);
        foreach ($arguments as $a) {
            $args = array_merge($args, $a);
        }
        if (!is_null(self::$token)) {
            $args = array_merge($args, array('access_token' => self::$token));
        }

        $url = sprintf(self::$URL, self::$Region, self::$Project). $api . '?' . http_build_query($args);

        return $url;
    }

    /**
     * Вызов метода API или установка значений переменных класса
     *
     * @param  string $name
     * @param  array  $arguments
     *
     * @return Api|array|null
     */
    public function __call($name, $arguments = array())
    {
        if (preg_match('/^set(.*)/', $name, $var)) {
            if (is_array($arguments) && sizeof($arguments) == 1) {
                self::${$var[1]} = $arguments[0];
            } else {
                self::${$var[1]} = $arguments;
            }

            return self::create();
        };
        $url = self::createUrl($name, $arguments);

        if (getenv('PROXY'))
        {
            $response = Request::get($url)
                ->followRedirects()
                ->addOnCurlOption(CURLOPT_PROXY, getenv('PROXY_URL'))
                ->addOnCurlOption(CURLOPT_PROXYPORT, getenv('PROXY_PORT'))
                ->addOnCurlOption(CURLOPT_PROXYUSERPWD, getenv('PROXY_USER'))
                ->send();
        }
        else{
            $response = Request::get($url)
                ->followRedirects()
                ->send();
        }
        self::runCallback(self::$sendCallback);

        $data = $response->body;

        if (isset($data->status) && $data->status == 'ok')
        {
            self::runCallback(self::$successCallback);
            return $data->data;
        }
        else
        {
            self::runCallback(self::$errorCallback);
            return null;
        }
    }

    /**
     * Генерирует ссылку для авторизации через OpenId
     *
     * @param string $redirect_to
     *
     * @return null
     */
    public function genAuthUrl($redirect_to = '')
    {
        $redirect_to = empty($redirect_to) ? '' : $redirect_to;
        self::setProject('wot');
        $url = self::$instance->auth->login(array('nofollow' => 1, 'redirect_uri' => $redirect_to));
        $url = $url ? $url->location : false;

        return $url;
    }

    /**
     * Тут творится магия :-)
     *
     * @param $name
     *
     * @return \WotApi\Api
     */
    public function __get($name)
    {
        self::$action = $name;

        return self::create();
    }

    public static function __callStatic($name, $arguments)
    {
        self::$Project = $name;

        return self::create();
    }

    public  static function onSuccess(\Closure $func)
    {
       self::$successCallback =  $func;
    }

    public  static function onSend(\Closure $func)
    {
        self::$sendCallback =  $func;
    }

    public  static function onError(\Closure $func)
    {
        self::$errorCallback =  $func;
    }

    /**
     * @param \Closure $callback
     * @param null $params
     */
    private static function runCallback($callback, $params=null)
    {
        if ($callback instanceof \Closure) call_user_func($callback, $params);
    }
}
