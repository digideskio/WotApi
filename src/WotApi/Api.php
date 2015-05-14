<?php
/**
 * Класс для работы с Wargaming Api
 *
 * Подробнее об API вы можете прочитать -
 * @link http://ru.wargaming.net/developers/api_reference
 * @author akeinhell (akeinhell@gmail.com)
 *
 */

namespace WotApi;

use Guzzle\Http\Client;
use Guzzle\Http\Exception\CurlException;

/**
 * Класс для работы с Wargaming Api
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
     * @var string application_id приложения - https://ru.wargaming.net/developers/applications/
     */
    public static $Appid = '';

    /**
     * @var string хранится проект к которому идет доступ
     */
    protected static $Project = 'wot';

    /**
     * @var string Регион по умолчанию
     */
    public static $Region = 'ru';

    /**
     * мета-данные запроса
     * @var
     */
    private static $meta;

    /**
     * хранит сгенерированный URL для доступа к API
     * @param string $URL Сгенерированный URL
     */
    private static function setURL($URL)
    {
        self::$URL = $URL;
    }

    /**
     * Устанавливает текущий проект (WoT, Blitz, WoWp, Wgn)
     * @param string $Project Название проекта
     */
    public static function setProject($Project)
    {
        self::$Project = $Project;
    }


    /**
     * Устанавливает регион (RU, CH......)
     * @param string $Region название региона
     */
    public static function setRegion($Region)
    {
        self::$Region = $Region;
    }

    /**
     * @return null|string
     */
    public static function getToken()
    {
        return self::$token;
    }

    /**
     * @return string
     */
    public static function getRegion()
    {
        return self::$Region;
    }

    /**
     * @return string
     */
    public static function getProject()
    {
        return self::$Project;
    }


    /**
     * Устанавливает application_id
     * @param string $Appid application_id полученный https://ru.wargaming.net/developers/applications/
     */
    public static function setApplicationId($Appid)
    {
        self::$Appid = $Appid;
    }

    /**
     * Получает значение application_id
     * @return string
     */
    public static function getApplicationId()
    {
        return self::$Appid;
    }

    /**
     * Устанавливает пользовательский токен
     * @param null|string $token токен
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
    private static $token = null;

    /**
     * @var \Guzzle\Http\Client обертка для доступа к http соединениям
     */
    private static $httpClient = null;

    /**
     * @var \Closure Переменная для хранения Callback вызываемого при успешом получении данных
     */
    private static $successCallback;

    /**
     * @var \Closure Переменная для хранения Callback вызываемого при ошибке
     */
    private static $errorCallback;

    /**
     * @var \Closure Переменная для хранения Callback вызываемого при отправке запроса
     */
    private static $sendCallback;

    /**
     * Хранит настройки для Guzzle клиента
     * @var array настройки для Guzzle
     */
    private static $options = array();


    /**
     * Создает экземпляр для доступа к API
     * @return \WotApi\Api
     */
    public static function create()
    {
        if (is_null(self::$instance)) {
            $options = (getenv('PROXY') && getenv('PROXY_URL')) ?
                ['proxy' => getenv('PROXY_URL')] :
                [];
            self::$httpClient = new Client('', [$options]);
            self::$options = $options;
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
    private static function createUrl($name, $arguments = array())
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

        try {
            $response = self::$httpClient->get($url, null, self::$options)->send();
        } catch (CurlException $e) {
            self::call(self::$errorCallback, $e->getMessage() . PHP_EOL . $e->getError());
        }
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

            self::$meta = isset($data->meta) ? $data->meta : null;

            return $data->data;
        } else {
            self::call(self::$errorCallback, isset($data->error) ? $data->error : null);

            return null;
        }
    }

    /**
     * Генерирует ссылку для авторизации через OpenId
     *
     * @param string $redirect_to редиректит на указанную страницу после авторизации
     *
     * @return null
     */
    public function genAuthUrl($redirect_to = '')
    {
        $redirect_to = empty($redirect_to) ? '' : $redirect_to;
        $url = self::create()->auth->login(array('nofollow' => 1, 'redirect_uri' => $redirect_to));
        $url = $url ? $url->location : false;

        return $url;
    }

    /**
     * Тут творится магия :-)
     * Назначение первого параметра в API запросе
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


    /**
     * Выбор проекта для получения API
     *
     * @param $name
     * @param $arguments
     *
     * @return \WotApi\Api
     */
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

    /**
     * Callback при удачном получении данных
     *
     * @param callable $func
     */
    public static function onSuccess(\Closure $func)
    {
        self::$successCallback = $func;
    }

    /**
     * Callback при отправке
     *
     * @param callable $func
     */
    public static function onSend(\Closure $func)
    {
        self::$sendCallback = $func;
    }

    /**
     * Callback при ошибке
     *
     * @param callable $func
     */
    public static function onError(\Closure $func)
    {
        self::$errorCallback = $func;
    }

    /**
     * Запускает нужный Callback с параметрами
     */
    private static function call()
    {
        $args = func_get_args();
        $function = array_shift($args);
        if (is_callable($function)) {
            call_user_func_array($function, $args);
        }
    }

    /**
     * Получение мета данных запроса
     * Вызывается после получения данных
     * @return mixed
     */
    public static function getMeta()
    {
        return self::$meta;
    }
}
