<?
use Bitrix\Main\Context,
    Bitrix\Main\Loader,
    Bitrix\Main\Type\DateTime,
    Bitrix\Highloadblock as HL,
    Bitrix\Highloadblock\HighloadBlockTable as HLBT;

class CAbudagovLastModified
{

    public $HLBlockId;


    /**
     * Создается хайлоадблок (если нету), устанавливается ID в переменную класса
     *
     * CAbudagovLastModified constructor.
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\LoaderException
     * @throws \Bitrix\Main\SystemException
     */
    function __construct()
    {
        if (Loader::includeModule("highloadblock")) {
            $this->HLBlockId = COption::GetOptionInt('abudagov.lastmodified', 'HLBlockId');
        }
    }


    /**
     * Получает данные по хешу URL
     *
     * @return array|false
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\SystemException
     */
    public function getData ()
    {
        if ($this->HLBlockId > 0) {
            if ($hlblock = HLBT::getById($this->HLBlockId)->fetch()) {
                $entity = HLBT::compileEntity($hlblock);
                $entityDataClass = $entity->getDataClass();

                $obData = $entityDataClass::getList(array(
                    "select" => array("*"),
                    "filter" => array("UF_HASH_URL" => $this->getHashUrl())
				));

                return $arData = $obData->fetch();
            }
        }
    }


    /**
     * Сравнивает даты и если запрошенная версия старше, то устанавливает заголовок 304 Not Modified
     *
     * @param $date
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\SystemException
     */
    public function ifModifiedSince ($date)
    {
        if ($this->HLBlockId > 0) {
            if ($hlblock = HLBT::getById($this->HLBlockId)->fetch()) {
                $entity = HLBT::compileEntity($hlblock);
                $entityDataClass = $entity->getDataClass();

                $obData = $entityDataClass::getList(array(
                    "select" => array("*"),
                    "filter" => array("UF_HASH_URL" => $this->getHashUrl())
				));

                if ($arData = $obData->fetch()) {
                    $tz = $arData['UF_DATE_MODIFY']->getTimeZone();
                    $date->setTimezone($tz);

                    if ($date->getTimestamp() > $arData['UF_DATE_MODIFY']->getTimestamp()) { // Прямое сравнение не работает :(
                        Context::getCurrent()->getResponse()->setStatus("304 Not Modified");
                    }
                }
            }
        }
    }


    /**
     * Сохраняет запись в БД (выполнить если хеша страницы по данному URL еще нет)
     *
     * @param $arFields
     * @throws \Bitrix\Main\SystemException
     */
    public function saveHash ($arFields)
    {
        if ($this->HLBlockId > 0) {
            if ($hlblock = HLBT::getById($this->HLBlockId)->fetch()) {
                $entity = HLBT::compileEntity($hlblock);
                $entityDataClass = $entity->getDataClass();
                $entityDataClass::add($arFields);
            }
        }
    }


    /**
     * Обновляет запись в БД (выполнить если хеш страницы не совпадает)
     *
     * @param $id
     * @param $arFields
     * @throws \Bitrix\Main\SystemException
     */
    public function updateHash ($id, $arFields)
    {
        if ($this->HLBlockId > 0) {
            if ($hlblock = HLBT::getById($this->HLBlockId)->fetch()) {
                $entity = HLBT::compileEntity($hlblock);
                $entityDataClass = $entity->getDataClass();
                $entityDataClass::update($id, $arFields);
            }
        }
    }


    /**
     * Считаем "отпечаток" URL
     *
     * @return string
     */
    public function getHashUrl ()
    {
        global $APPLICATION;

        return SITE_ID.'-'.md5($APPLICATION->GetCurPageParam());
    }


    /**
     * Считаем "отпечаток" страницы
     *
     * @param $content
     */
    public function getHashPage($content)
    {
        return strlen(base64_encode($content))+strlen($content);
    }

	/**
	 * Аналог apache_request_headers
	 *
	 * @return array
	 */
    public function getRequestHeaders ()
	{
		$return = array();
		foreach ($_SERVER as $key => $value) {
			if (substr($key, 0, 5) == "HTTP_") {
				$key = str_replace(" ", "-", ucwords(strtolower(str_replace("_", " ", substr($key, 5)))));
				$return[$key] = $value;
			} else {
				$return[$key] = $value;
			}
		}
		return $return;
	}


    /**
     * По завершению буферизации сохраняет достаточно уникальный "отпечаток" контента в БД
     *
     * @param $content
     * @throws \Bitrix\Main\ObjectException
     */
    public function setLastModified ($content) {

        global $USER;

		$debug = false;
		/*if ($_SERVER['HTTP_REFERER'] == 'http://last-modified.com/ru/') {
			$debug = true;
		}*/

		$arRewriteParams = array(
			'SECTION_CODE',
			'ELEMENT_CODE',
			'COD'
		);

		$return = false;
		if (!empty($_GET)) {
			foreach ($_GET as $code => $value) {
				if (array_search($code, $arRewriteParams) === false) {
					$return = true;
					break;
				}
			}
		}

		// если есть параметры запроса или это админ часть или пользователь авторизован, то не выполняем код
		if (defined('ERROR_404')) {
			if (ERROR_404 == 'Y') {
				$return = true;
			}
		}
		if ($return || defined('ADMIN_SECTION') || $USER->IsAuthorized()) {
			if (!$debug) {
				return;
			}

		}


        $ob = new CAbudagovLastModified;

        $date = new DateTime();
        $pageHash = $ob->getHashPage($content);
        if ($pageHash > 1000) { // Отбрасываем страницы без контента
            if ($arData = $ob->getData()) {
                if ($arData['UF_HASH_PAGE'] != $pageHash) {
                    $ob->updateHash($arData['ID'], array("UF_HASH_PAGE" => $pageHash, "UF_HASH_URL" => $ob->getHashUrl(), "UF_DATE_MODIFY" => $date));
                } else {
                    $date = $arData['UF_DATE_MODIFY'];
                }
            } else {
                $ob->saveHash(array("UF_HASH_PAGE" => $pageHash, "UF_HASH_URL" => $ob->getHashUrl(), "UF_DATE_MODIFY" => $date));
            }


            Context::getCurrent()->getResponse()->setLastModified($date);
            // Если проверка показывает что заголовок не установлен, нужно отключить ssi в настройках nginx

            // Получает заголовок if-modified-since и передает в функцию, которая формирует ответ
            // todo: вынести в событие до формирования тела страницы, что бы его можны было прервать
            if (function_exists('apache_request_headers')) {
				$arHeaders = apache_request_headers();
			} else {
				$arHeaders = $ob->getRequestHeaders();
			}

			$ifModifiedSince = $arHeaders['If-Modified-Since'];

			if ($ifModifiedSince) {

				$date = \DateTime::createFromFormat(
					"D, d M Y H:i:s T",
					$ifModifiedSince
				);
				$ob->ifModifiedSince($date);
			}

        }


		if ($debug) {
        	global $APPLICATION;
			@define("LOG_FILENAME", $_SERVER["DOCUMENT_ROOT"]."/log.txt");
			AddMessage2Log(array(
				'URL' => $APPLICATION->GetCurPage(),
				'$_GET' => $_GET,
				'$return' => $return,
				'defined("ADMIN_SECTION") || $USER->IsAuthorized() || ERROR_404 == "Y"' => defined('ADMIN_SECTION') || $USER->IsAuthorized() || defined('ERROR_404'),
				'$pageHash' => $pageHash,
				'$date' => $date,
				'function_exists("apache_request_headers")' => function_exists('apache_request_headers'),
				'$ifModifiedSince' => $ifModifiedSince
			), '', 0);
		}
    }
}
?>