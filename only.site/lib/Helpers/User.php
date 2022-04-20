<?php

namespace Only\Site\Helpers;

use Bitrix\Main\UserTable;
use CUser;
use CUserTypeEntity;
use Exception;

class User
{
    public static function findUserByPhone($string, $onlyActive = false)
    {
        $arFilter = ["LOGIN" => fixPhone($string)];
        if ($onlyActive) {
            $arFilter["ACTIVE"] = "Y";
        }

        return UserTable::getList(['filter' => $arFilter])->fetch();
    }

    public static function authSendCode()
    {
        $arPostList = getPostList();
        $phone = fixPhone($arPostList["USER_PHONE"]);

        if ($phone) {
            $arLoyaltyUser = [];
            if ($arPostList['USER_PHONE'] && $arPostList['USER_NAME'] && $arPostList['USER_LAST_NAME'] && $arPostList['USER_BIRTHDATE']) {
                $arLoyaltyUser = $arPostList;
                $_SESSION['USER_PHONE'] = $arPostList['USER_PHONE'];
                $_SESSION['USER_NAME'] = $arPostList['USER_NAME'];
                $_SESSION['USER_LAST_NAME'] = $arPostList['USER_LAST_NAME'];
                $_SESSION['USER_BIRTHDATE'] = $arPostList['USER_BIRTHDATE'];
            } else {
                try {
                    $arLoyaltyUser = self::getLoyaltyUser($phone);
                } catch (Exception $e) {
                    json_result(false, ["errors" => ["USER_PHONE" => $e->getMessage()]]);
                }
            }

            if (empty($arLoyaltyUser)) {
                json_result(
                    true,
                    ["register" => true, "message" => "Заполните дополнительные поля, чтобы зарегистрироваться"]
                );
            }

            $code = mt_rand(1000, 9999);
            $event = "auth#{$phone}";
            $sCodeAddError = (new Code())->add($code, $event);
            if ($sCodeAddError === "") {
                try {
                    $smsResult = Note::sendPhoneVerificationCode($phone, $code);
                } catch (Exception $e) {
                    json_result(false, ["errors" => ["USER_PHONE" => $e->getMessage()]]);
                }
                if ($smsResult['success'] === true) {
                    $_SESSION['USER_PHONE'] = $phone;
                    json_result(true, ["redirect" => PATH_LOGIN_CHECK]);
                } else {
                    json_result(false, ["errors" => ["USER_PHONE" => $smsResult['error']['descr']]]);
                }
            } else {
                json_result(false, ["errors" => ["USER_PHONE" => $sCodeAddError]]);
            }
        } else {
            json_result(false, ["errors" => ["USER_PHONE" => "Неверный формат телефона"]]);
        }
    }

    public static function authLoginOrRegisterByPhone()
    {
        $phone = fixPhone($_SESSION['USER_PHONE']);
        if (!$phone) {
            json_result(false, ["message" => "Вы не указали номер телефона"]);
        }

        $arPostList = getPostList();
        $code = $arPostList['USER_CODE'];

        $event = "auth#{$phone}";
        $sCodeVerifyError = (new Code())->verify($code, $event);
        if ($sCodeVerifyError !== "") {
            json_result(false, ["errors" => ["USER_CODE" => $sCodeVerifyError]]);
        }

        if ($phone) {
            $arUser = self::findUserByPhone($phone);
            $userID = $arUser['ID'];
            $isRegister = false;
            if (!$userID) {
                $arLoyaltyUser = [];

                try {
                    $arLoyaltyUser = self::getLoyaltyUser($phone);
                } catch (Exception $e) {
                    $error = $e->getMessage();
                    json_result(false, ["message" => $error]);
                }

                if (empty($arLoyaltyUser) &&
                    $arPostList['USER_PHONE'] && $arPostList['USER_NAME'] && $arPostList['USER_LAST_NAME'] && $arPostList['USER_BIRTHDATE']) {
                    try {
                        $arData = [
                            'phone'      => "+{$arPostList['USER_PHONE']}",
                            'first_name' => $arPostList['USER_NAME'],
                            'last_name'  => $arPostList['USER_LAST_NAME'],
                            'birth_date' => fixDateForLoyalty($arPostList['USER_BIRTHDATE']),
                        ];
                        (new \Only\Rest\Client\Loyalty\Customers())->create($arData);
                    } catch (Exception $e) {
                        $error = $e->getMessage();
                        // json_result(false, ["message" => $error]);
                    }
                    sleep(5);
                }

                try {
                    $arLoyaltyUser = self::getLoyaltyUser($phone);
                } catch (Exception $e) {
                    $error = $e->getMessage();
                    json_result(false, ["message" => $error]);
                }

                if (empty($arLoyaltyUser)) {
                    json_result(false, ["errors" => ["USER_PHONE" => "Номер телефона не найден"]]);
                }

                self::createUserFieldStringIfNotExist(UF_LOGUS_USER_ID);
                self::createUserFieldStringIfNotExist(UF_LOYALTY_USER_ID);

                $obUser = new CUser();
                $password = md5($phone . time());
                $userID = (int)$obUser->Add(
                    [
                        "ACTIVE"            => "Y",
                        "NAME"              => $arLoyaltyUser['first_name'] ?? prettyPhone($phone),
                        "LAST_NAME"         => $arLoyaltyUser['last_name'],
                        "SECOND_NAME"       => $arLoyaltyUser['middle_name'],
                        "EMAIL"             => $arLoyaltyUser['email'] ?? "{$phone}@olgahotel.ru",
                        "LOGIN"             => $phone,
                        "PERSONAL_PHONE"    => $phone,
                        "PERSONAL_BIRTHDAY" => $arLoyaltyUser['birth_date'],
                        "GROUP_ID"          => [USER_GROUP_GUEST],
                        "PASSWORD"          => $password,
                        "CONFIRM_PASSWORD"  => $password,
                        "LID"               => SITE_ID,
                        UF_LOGUS_USER_ID    => "",
                        UF_LOYALTY_USER_ID  => $arLoyaltyUser['id'] ?? "",
                    ]
                );

                if (!$userID) {
                    json_result(false, ["errors" => ["USER_CODE" => $obUser->LAST_ERROR]]);
                }

                $isRegister = true;
            }

            global $USER;
            $isAuthorized = $USER->Authorize($userID, true, true);
            if ($isAuthorized) {
                unset($_SESSION['USER_PHONE']);
                unset($_SESSION['USER_NAME']);
                unset($_SESSION['USER_LAST_NAME']);
                unset($_SESSION['USER_BIRTHDATE']);
                json_result(true, ["redirect" => $isRegister ? PATH_CABINET_PROFILE : PATH_CABINET_HISTORY]);
            } else {
                json_result(false, ["errors" => ["USER_CODE" => "Не удалось авторизоваться"]]);
            }
        } else {
            json_result(false, ["errors" => ["USER_CODE" => "Неверный формат телефона"]]);
        }
    }

    public static function createUserFieldStringIfNotExist(string $fieldName)
    {
        $arField = CUserTypeEntity::GetList([], ['FIELD_NAME' => $fieldName])->GetNext();
        if (!$arField) {
            $obUserField = new CUserTypeEntity();
            $obUserField->Add(
                [
                    'ENTITY_ID'     => 'USER',
                    'FIELD_NAME'    => $fieldName,
                    'USER_TYPE_ID'  => 'string',
                    'SORT'          => 1000,
                    'MULTIPLE'      => 'N',
                    'MANDATORY'     => 'N',
                    'SHOW_FILTER'   => 'N',
                    'SHOW_IN_LIST'  => 'N',
                    'EDIT_IN_LIST'  => 'Y',
                    'IS_SEARCHABLE' => 'N',
                    'SETTINGS'      => [
                        'SIZE'          => 40,
                        'ROWS'          => 1,
                        'REGEXP'        => '',
                        'MIN_LENGTH'    => 0,
                        'MAX_LENGTH'    => 0,
                        'DEFAULT_VALUE' => ''
                    ]
                ],
                false
            );
        }
    }

    /**
     * @param $phone 70000000000
     * @return array
     * @throws Exception
     */
    public static function getLoyaltyUser($phone): array
    {
        $arResult = (new \Only\Rest\Client\Loyalty\Customers())->getList(
            [
                'phone' => "+{$phone}"
            ]
        );

        if ($arResult['count'] == 0) {
            return [];
        }

        $arUser = $arResult['results'][0];
        $fullName = "{$arUser['last_name']} {$arUser['first_name']} {$arUser['middle_name']}";
        $arUser['full_name'] = $fullName;

        $arUser['birth_date'] = fixDateForBitrix($arUser['birth_date']);

        return $arUser;
    }
}
