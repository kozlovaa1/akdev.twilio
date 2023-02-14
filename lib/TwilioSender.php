<?php

namespace Akdev\Twilio;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\Config;
use Bitrix\Main\Diag\Debug;
use Bitrix\Main\Mail\EventMessageCompiler;
use Bitrix\Main\Mail\Internal\EventMessageTable;
use Bitrix\Main\Mail\StopException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use Twilio\Exceptions\TwilioException;
use Twilio\Rest\Client;
use Bitrix\Main\Config\Option;

class TwilioSender
{
    /**
     * @throws TwilioException
     */
    public static function sendMessage($template, $data): void
    {
        try {
            $request['message'] = self::getTemplate($template, $data);

            $request['to'] = str_replace(array(' ', '(', ')', '-'), '', $data['PHONE']);;
            self::sendRequest($request);
        } catch (ObjectPropertyException|ArgumentException|StopException|SystemException $e) {
            // TODO возврат ошибки
        }

    }

    /**
     * @throws TwilioException
     */
    private static function sendRequest($request): void
    {
        $mid = pathinfo(dirname(__DIR__))['basename'];
        $twilioAccountSid = Option::get($mid, 'TWILIO_ACCOUNT_SID');
        $twilioAuthToken = Option::get($mid, 'TWILIO_AUTH_TOKEN');
        $twilioSender = Option::get($mid, 'TWILIO_SENDER');
        $message = $request['message'];
        $to = $request['to'];

        $twilio = new Client($twilioAccountSid, $twilioAuthToken);
        $message = $twilio->messages
            ->create("whatsapp:" . $to, // to
                [
                    "from" => "whatsapp:" . $twilioSender,
                    "body" => $message
                ]
            );
    }

    /**
     * Шаблоны {@link https://console.twilio.com/us1/develop/sms/senders/whatsapp-templates/}
     * @param int $template ID почтового шаблона
     * @param array $data Массив полей для заполнения почтового шаблона
     * @return string|null
     * @throws ObjectPropertyException|ArgumentException|SystemException|StopException
     */
    private static function getTemplate(int $template, array $data): string|null
    {
        $data['DEFAULT_EMAIL_FROM'] = Config\Option::get("main", "email_from", "admin@" . $GLOBALS["SERVER_NAME"]);

        try {
            $eventMessage = EventMessageTable::getById($template)->fetch();
            $messageParams = array(
                'FIELDS' => $data,
                'MESSAGE' => $eventMessage,
                'SITE' => SITE_ID,
                'CHARSET' => SITE_CHARSET,
            );

            $message = EventMessageCompiler::createInstance($messageParams);

            try {
                $message->compile();
                $result = [
                    'TO' => $message->getMailTo(),
                    'SUBJECT' => $message->getMailSubject(),
                    'BODY' => $message->getMailBody(),
                ];

                return $result['BODY'];
            } catch (StopException $e) {
                // обработка ошибки компиляции почтового сообщения
                Debug::writeToFile(__FILE__ . ':' . __LINE__ . "\n(" . date('Y-m-d H:i:s') . ")\n" . print_r($e->getMessage(), TRUE) . "\n\n", '', 'log/__debug.log');
                return null;
            }

        } catch (ObjectPropertyException|ArgumentException|SystemException $e) {
            // обработка ошибочного ID шаблона
            Debug::writeToFile(__FILE__ . ':' . __LINE__ . "\n(" . date('Y-m-d H:i:s') . ")\n" . print_r($e->getMessage(), TRUE) . "\n\n", '', 'log/__debug.log');
            return null;
        }

    }

}