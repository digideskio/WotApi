<?php
/**
 * User: akeinhell
 * Date: 06.03.15
 * Time: 13:24
 */

namespace WotApi;

use Guzzle\Http\Client;

/**
 * Description of Api
 *
 * @author akeinhell
 * @method static \WotApi\Api wot()
 * @method static \WotApi\Api wgn()
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
     * @param string $Url
     */
    public static function setURL($URL)
    {
        self::$URL = $URL;
    }

    /**
     * @param string $Project
     */
    public static function setProject($Project)
    {
        self::$Project = $Project;
    }


    /**
     * @param string $Region
     */
    public static function setRegion($Region)
    {
        self::$Region = $Region;
    }

    /**
     * @param string $Appid
     */
    public static function setApplicationId($Appid)
    {
        self::$Appid = $Appid;
    }

    /**
     * @param null|string $token
     */
    public static function setToken($token)
    {
        self::$token = $token;
    }

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
    private static $httpClient = null;

    /**
     * @var \Closure
     */
    private static $successCallback;
    private static $errorCallback;
    private static $sendCallback;


    /**
     * Создает экземпляр для доступа к API
     * @return \WotApi\Api
     */
    public static function create()
    {
        if (is_null(self::$instance)) {
            $options = (getenv('PROXY') && getenv('PROXY_URL')) ?
                array('defaults' => array('proxy' => getenv('PROXY_URL'))) :
                null;
            self::$httpClient = new Client($options);
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
        try {
            $api = self::$action . '/' . $name . '/';
            $args = array('application_id' => self::$Appid);

            foreach ($arguments as $a) {
                $args = array_merge($args, $a);
            }
            if (!is_null(self::$token)) {
                $args = array_merge($args, array('access_token' => self::$token));
            }

            $url = sprintf(self::$URL, self::$Region, self::$Project) . $api . '?' . http_build_query($args);

            return $url;
        } catch (\Exception $e) {
            self::call(self::$errorCallback, $e->getMessage(), $name, $arguments);
        }


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
        $url = self::createUrl($name, $arguments);


        $response = self::$httpClient->get($url)->send();
        list($requestUrl) = explode('?', $url);
        self::call(self::$sendCallback, $requestUrl, $arguments);

        if ($response->getStatusCode() > 400) {
            self::call(self::$errorCallback, 'HTTP ERROR: Code #' . $response->getStatusCode());
            return null;
        }

        /*@var $data Guzzle\Http\Message\Request */
        $data = json_decode((string)$response->getBody());
        if (isset($data->status) && $data->status == 'ok') {
            self::call(self::$successCallback, $data->data);

            return $data->data;
        } else {
            self::call(self::$errorCallback, isset($data->error) ? $data->error : null);

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
        switch (strtolower($name)) {
            case 'blitz':
            case 'wotb':
                self::setURL('http://api.wotblitz.%s/%s/');
                self::setProject('wotb');
                break;
            case 'wowp':
            case 'wow':
                self::setURL('http://api.worldofwarplanes.%s/%s/');
                self::setProject('wowp');
                break;
            default:
                self::setURL('http://api.worldoftanks.%s/%s/');
                self::setProject(strtolower($name));
        }

        return self::create();
    }

    public static function onSuccess(\Closure $func)
    {
        self::$successCallback = $func;
    }

    public static function onSend(\Closure $func)
    {
        self::$sendCallback = $func;
    }

    public static function onError(\Closure $func)
    {
        self::$errorCallback = $func;
    }

    /**
     */
    private static function call()
    {
        $args = func_get_args();
        $function = array_shift($args);
        if (is_callable($function)) {
            call_user_func_array($function, $args);
        }
    }
}
