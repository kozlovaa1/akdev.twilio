## Модуль для CMS Битрикс. Отправка сообщений в Whatsapp через сервис Twilio с использованием почтовых шаблонов

Модуль akdev.twilio ...

## Документация
В планах

## Требования
1. Требуется версия 1С-Битрикс от 17.0.0.
2. Требуется установленный пакет [Twilio SDK](https://www.twilio.com/docs/libraries/php)

> Twilio SDK можно установить через composer
> ```
> composer require twilio/sdk
> ```

## Установка
```
$ cd local/modules
$ git clone https://github.com/kozlovaa1/akdev.twilio.git
```
В папку `local/modules` будет склонирован репозиторий модуля, после этого в панели администратора необходимо установить
модуль через страницу "Установленные решения":
```
https://my-site.ru/bitrix/admin/partner_modules.php?lang=ru
```
Перейти на страницу настроек модуля и настроить поля для доступа к API и номера телефонов - отправителя Twilio и номера получателя для тестирования, если необходимо
```
https://my-site.ru/bitrix/admin/settings.php?mid=akdev.twilio&lang=ru
```

## Использование
Создаём почтовый шаблон, полностью соответствующий [согласованному шаблону](https://console.twilio.com/us1/develop/sms/senders/whatsapp-templates/) в Twilio.
Используем данные шаблона:

`$template` - ID шаблона

`$data` - массив данных для шаблона в формате:
```
['FIELD_NAME' = $fieldValue, 'OTHER_FIELD_NAME' = $otherFieldValue, ...]
```

`$recipient` - номер телефона получателя

Выполняем отправку сообщения по созданному почтовому шаблону:
```
Akdev\Twilio\TwilioSender::sendMessage($template, $data, $recipient);
```