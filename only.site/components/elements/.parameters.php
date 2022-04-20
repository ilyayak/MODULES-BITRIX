<? if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

/** @var array $arCurrentValues */


try {
    if (!\Bitrix\Main\Loader::includeModule('iblock'))
        return;
} catch (\Bitrix\Main\LoaderException $e) {
    return;
}

$arIBlocksTypes = \CIBlockParameters::GetIBlockTypes();

$rsIBlocks = \CIBlock::GetList(['SORT' => 'ASC', 'ID' => 'ASC'], ['TYPE' => $arCurrentValues['IBLOCK_TYPE']]);

$arIBlocks = [];
while ($arRes = $rsIBlocks->Fetch())
    $arIBlocks[$arRes['ID']] = '[' . $arRes['ID'] . '] ' . $arRes['NAME'];


$arPaginationTemplates = \CComponentUtil::GetTemplatesList('bitrix:system.pagenavigation');

$arPaginationValues = ['' => 'Нет'];

foreach ($arPaginationTemplates as $arTemplate)
    $arPaginationValues[$arTemplate['NAME']] = $arTemplate['NAME'];

$arSortBy = [
    'ID' => 'ID',
    'NAME' => 'названию',
    'ACTIVE_FROM' => 'дате начала активности',
    'SORT' => 'полю сортировки',
    'TIMESTAMP_X' => 'дате создания',
    'RAND' => 'в случайном порядке'
];

$arSortOrder = [
    'ASC' => 'По возрастанию',
    'DESC' => 'По убыванию'
];

$arComponentParameters = [
    'GROUPS' => [
        'FILTER' => [
            'NAME' => 'Фильтрация',
            'SORT' => 100
        ],
        'SEF' => [
            'NAME' => 'ЧПУ',
            'SORT' => 200
        ],
        'LIST' => [
            'NAME' => 'Настройки списка',
            'SORT' => 210
        ],
        'DETAIL' => [
            'NAME' => 'Настройки детального просмотра',
            'SORT' => 220
        ],
        'SETTINGS' => [
            'NAME' => 'Дополнительные настройки',
            'SORT' => 250
        ]
    ],
    'PARAMETERS' => [
        'IBLOCK_TYPE' => [
            'PARENT' => 'FILTER',
            'NAME' => 'Тип инфоблока',
            'TYPE' => 'LIST',
            'VALUES' => $arIBlocksTypes,
            'REFRESH' => 'Y'
        ],
        'IBLOCK_ID' => [
            'PARENT' => 'FILTER',
            'NAME' => 'Инфоблок',
            'TYPE' => 'LIST',
            'VALUES' => $arIBlocks,
            'DEFAULT' => '={$_REQUEST["ID"]}',
            'REFRESH' => 'Y'
        ],
        'ONLY_ACTIVE' => [
            'PARENT' => 'FILTER',
            'NAME' => 'Только активные элементы',
            'TYPE' => 'CHECKBOX',
            'DEFAULT' => 'Y'
        ],
        'CHECK_DATES' => [
            'PARENT' => 'FILTER',
            'NAME' => 'Проверять дату активности',
            'TYPE' => 'CHECKBOX',
            'DEFAULT' => 'N'
        ],
        'CHECK_PERMISSIONS' => [
            'PARENT' => 'FILTER',
            'NAME' => 'Проверять доступ к инфоблоку',
            'TYPE' => 'CHECKBOX',
            'DEFAULT' => 'N'
        ],
        'SEF_MODE' => [
            'main' => [
                'NAME' => 'Страница общего списка',
                'DEFAULT' => '',
                'VARIABLES' => [],
            ],
            'section' => [
                'NAME' => 'Страница раздела',
                'DEFAULT' => '#SECTION_CODE_PATH#/',
                'VARIABLES' => ['SECTION_ID', 'SECTION_CODE', 'SECTION_CODE_PATH'],
            ],
            'detail' => [
                'NAME' => 'Страница детального просмотра',
                'DEFAULT' => '#SECTION_CODE_PATH#/#ELEMENT_CODE#/',
                'VARIABLES' => ['SECTION_ID', 'SECTION_CODE', 'SECTION_CODE_PATH', 'ELEMENT_ID', 'ELEMENT_CODE'],
            ]
        ],
        'COUNT' => [
            'PARENT' => 'LIST',
            'NAME' => 'Кол-во элементов (0 - все)',
            'DEFAULT' => '0'
        ],
        'SORT_BY' => [
            'PARENT' => 'LIST',
            'NAME' => 'Сортировка по',
            'TYPE' => 'LIST',
            'DEFAULT' => 'SORT',
            'VALUES' => $arSortBy,
            'ADDITIONAL_VALUES' => 'Y',
        ],
        'SORT_ORDER' => [
            'PARENT' => 'LIST',
            'NAME' => 'Направление сортировки',
            'TYPE' => 'LIST',
            'DEFAULT' => 'ASC',
            'VALUES' => $arSortOrder
        ],
        'SORT_BY_2' => [
            'PARENT' => 'LIST',
            'NAME' => 'Дополнительная сортировка по',
            'TYPE' => 'LIST',
            'DEFAULT' => '',
            'VALUES' => $arSortBy,
            'ADDITIONAL_VALUES' => 'Y',
        ],
        'SORT_ORDER_2' => [
            'PARENT' => 'LIST',
            'NAME' => 'Направление дополнительной сортировки',
            'TYPE' => 'LIST',
            'DEFAULT' => '',
            'VALUES' => $arSortOrder
        ],
        'ACTIVE_DATE_FORMAT' => CIBlockParameters::GetDateFormat('Формат даты', 'SETTINGS'),
        'PAGER_TEMPLATE' => [
            'PARENT' => 'SETTINGS',
            'NAME' => 'Шаблон пагинации',
            'TYPE' => 'LIST',
            'DEFAULT' => '',
            'VALUES' => $arPaginationValues
        ],
        'SET_META_TAGS' => [
            'PARENT' => 'SETTINGS',
            'NAME' => 'Устанавливать мета-теги',
            'TYPE' => 'CHECKBOX',
            'DEFAULT' => 'Y'
        ],
        'CACHE_TIME' => ['DEFAULT' => 604800]
    ]
];