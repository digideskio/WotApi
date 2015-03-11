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

Api::setAppid('YOUR_APPLICATION_ID'); // Перед работой с классом укажите свой application_id, получить его можно [тут](https://ru.wargaming.net/developers/applications/)
Api::setToken('USER_AUTH_TOKEN'); // Перед работой с персональными данными игрока необходимо указать его токен полученный после авторизации [подробнее](https://ru.wargaming.net/support/Knowledgebase/Article/View/800/25/autentifikcija-pri-pomoshhi-metodov-public-api)
Api::getAuthUrl(); // возвращает ссылку для авторизации пользователя
$params = array()
Api::wot()->encyclopedia->tanks(); // Получает список техники
Api::wgn()->clans->list();//осуществляет поиск по кланам и сортирует их в указанном порядке

```

Разбор

- Api - Название класса

- Статические методы : 
 1. wot() - доступ к данным [World of tanks](https://ru.wargaming.net/developers/api_reference/wot/account/list/) 
 2. wgn() - доступ к данным [Wargaming.NET](https://ru.wargaming.net/developers/api_reference/wgn/clans/list/) 

- (метод API) encyclopedia->tanks() доступ к списку техники через метод [encyclopedia/tanks](https://ru.wargaming.net/developers/api_reference/wot/encyclopedia/tanks/) 
 
 
 ## Примеры ##
 
```
// Возвращает список техники в игре
Api::wot()->encyclopedia->tanks()

// Возвращает ссылку для авторизации пользователя через OpenID
Api::genAuthUrl()

// Метод возвращает информацию об игроке c ID=666
Api::wot()->account->info(array('account_id'=>666))
```

## Принять участие в разработке ##

Вы можете принять участие в разработке следующим образом:
- Сообщать о возникших ошибках в [Багтрекер](https://github.com/akeinhell/WotApi/issues)
- Высказать предложения о доработке в [Багтрекер](https://github.com/akeinhell/WotApi/issues) с пометкой "Предложение"
- Или присылайте свои Pull Request. Каждый из них будет обязательно рассмотрен.