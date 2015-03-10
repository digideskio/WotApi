WoTApi
======

PHP класс для удобной работы с Wargaming Api 

## Установка ##

Через Composer 
```
composer require "akeinhell/wot-api" "dev-master"
```

## Примеры использования ##
```
$params = array()
Api::wot()->encyclopedia->tanks()
```

Разбор
Api - Название класса

Статические методы : 
 1. wot() - доступ к данным (World of tanks)[https://ru.wargaming.net/developers/api_reference/wot/account/list/] 
 2. wgn() - доступ к данным (Wargaming.NET)[https://ru.wargaming.net/developers/api_reference/wgn/clans/list/] 

 encyclopedia->tanks() доступ к списку техники через метод (encyclopedia/tanks)[https://ru.wargaming.net/developers/api_reference/wot/encyclopedia/tanks/] 
 
 
 ## Примеры ##
 
```
// Возвращает список техники в игре
Api::wot()->encyclopedia->tanks()

// Возвращает ссылку для авторизации пользователя через OpenID
Api::genAuthUrl()

// Метод возвращает информацию об игроке c ID=666
Api::wot()->account->list(array('account_id'=>666))
```