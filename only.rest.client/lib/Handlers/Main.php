<?php

namespace Only\Rest\Client\Handlers;


use Bitrix\Main\Diag\Debug;

class Main
{
    public static function OnBeforeUserUpdate(&$arFields)
    {
    }

    public static function OnAfterUserUpdate(&$arFields)
    {
        // update user in loyalty
        Debug::dumpToFile($arFields);
        if ($arFields[UF_LOYALTY_USER_ID]) {
            $arData = [];
        }
    }
}
    if ($loyaltyUserId) {
            $arData = [
                'last_name'   => $arFields['LAST_NAME'],
                'phone'       => fixPhone($arFields['PERSONAL_PHONE']),
                'birth_date'  => fixDateForLoyalty($arFields['PERSONAL_BIRTHDAY']),
                'first_name'  => $arFields['NAME'],
                'middle_name' => $arFields['SECOND_NAME'],
                'city'        => $arFields['PERSONAL_CITY'],
                'gender'      => $arFields['PERSONAL_GENDER'],
                'email'       => $arFields['EMAIL'],
            ];
            //Debug::dumpToFile($arData);

            try {
                //(new Customers())->update($loyaltyUserId, $arData);
            } catch (Exception $e) {
                Debug::dumpToFile($e->getMessage(), null, "__OnAfterUserUpdate.log");
            }
        }
    }
}
