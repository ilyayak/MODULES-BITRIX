<?php


namespace Only\Site\Helpers;


use Bitrix\Main\Config\Option;
use Bitrix\Main\Mail\Event;
use CEventMessage;
use CEventType;
use Only\Site\Sms;

class Note
{
    public static function sendSms(string $phone, string $text)
    {
        $arRequest = [
            'messages' => [
                [
                    'recipient' => $phone,
                    'text'      => $text
                ]
            ],
            'tags'     => [date('Y'), 'Авторизация']
        ];

        return (new Sms($arRequest))->sendText();
    }

    public static function sendPhoneVerificationCode(string $phone, string $code)
    {
        return self::sendSms($phone, "Проверочный код: {$code}");
    }

    protected static function createMailEventIfNotExist($eventCode): bool
    {
        $arEventType = CEventType::GetList(["EVENT_NAME" => $eventCode, "LID" => "ru"])->Fetch();
        if (!$arEventType) {
            $CEventType = new CEventType;
            $eventTypeID = $CEventType->Add(
                [
                    "LID"         => "ru",
                    "EVENT_NAME"  => $eventCode,
                    "NAME"        => $eventCode,
                    "DESCRIPTION" => ""
                ]
            );
            if ($eventTypeID > 0) {
                $CEventMessage = new CEventMessage;
                $eventMessageID = $CEventMessage->Add(
                    [
                        "ACTIVE"     => "Y",
                        "EVENT_NAME" => $eventCode,
                        "LID"        => SITE_ID,
                        "EMAIL_FROM" => "#DEFAULT_EMAIL_FROM#",
                        "EMAIL_TO"   => "#EMAIL_TO#",
                        "SUBJECT"    => "#SUBJECT#",
                        "BODY_TYPE"  => "text",
                        "MESSAGE"    => "#MESSAGE#"
                    ]
                );
                return $eventMessageID > 0;
            }
        }
        return true;
    }

    public static function sendMail($eventCode, $arFields): bool
    {
        if (!$arFields['EMAIL_TO']) {
            $arFields['EMAIL_TO'] = Option::get('main', 'email_from');
        }

        $isFieldExist = self::createMailEventIfNotExist($eventCode);
        if ($isFieldExist) {
            $arParams = [
                "EVENT_NAME" => $eventCode,
                "LID"        => SITE_ID,
                "C_FIELDS"   => $arFields
            ];
            $eventSendResult = Event::send($arParams);
            if ($eventSendResult->isSuccess()) {
                return true;
            }
        }
        return false;
    }
}
