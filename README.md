# magegain-novaposhta
Magento 2 novaposhta shipping. Модуль Новой почты (API 2) для Magento 2 

- Autocomplete городов,
- Autocomplete отделений,
- автоматический расчет стоимости доставки
- 2 вида доставки до дома и до склада 



Протестировано пока только на Magento ver. 2.1.3 
Последнее обновление протестированое на 2.1.8


---------------------------------------------------
Скорее всего модуль НЕ будет работать на версиях ниже 2.1 т.к у мадженты был критический баг с получением города во время доставки и они его исправили только в 2.1.2 кажется, подробнее https://github.com/magento/magento2/issues/3789 



Установка


Step 1: Распаковываем в папку app/code 

Step 2: Выключаем кеш System­ >> Cache Management

Step 3: Обновляем мадженту в консоле:

php -f bin/magento maintenance:enable

php bin/magento setup:upgrade

php bin/magento setup:di:compile

php bin/magento dev:source-theme:deploy

php bin/magento cache:clean

php bin/magento setup:static-content:deploy

php bin/magento cache:clean

php -f bin/magento maintenance:disable

После этого идем в настройку методов доставки находим новую почту и вписываем API KEY который можно получить в кабинете новой почты.

После этого идем в Sales там будут 2 пункта меню: синхронизация городов и складов, переходим в каждый и запускаем синхронизацию (первым нужно запускать города). 
Если у вас слабенький сервер синхронизацию складов возможно нужно запустить несколько раз до появление сообщения об удачной синхронизации. Если при синхронизации вы получаете ошибку Exception #0 (Zend_Http_Client_Exception): Unable to read response, or response is empty это значит что апи новой почты не отвечают



