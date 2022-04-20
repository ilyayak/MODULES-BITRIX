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

$rsSections = \CIBlockSection::GetList(['SORT' => 'ASC', 'ID' => 'ASC'], ['IBLOCK_ID' => intval($arCurrentValues['IBLOCK_ID'])]);

$arSections = [0 => 'Любой'];
while ($arRes = $rsSections->Fetch())
    $arSections[$arRes['ID']] = '[' . $arRes['ID'] . '] ' . $arRes['NAME'];

$arPaginationTemplates = \CComponentUtil::GetTemplatesList('bitrix:system.pagenavigation');

$arPaginationValues = ['' => 'Нет'];

foreach ($arPaginationTemplates as $arTemplate)
    $arPaginationValues[$arTemplate['NAME']] = $arTemplate['NAME'];

$arSortBy = [
    '' => 'Без сортировки',
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
        'SORT' => [
            'NAME' => 'Сортировка',
            'SORT' => 200
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
        'SECTION_ID' => [
            'PARENT' => 'FILTER',
            'NAME' => 'Раздел',
            'TYPE' => 'LIST',
            'VALUES' => $arSections,
            'DEFAULT' => '={$_REQUEST["ID"]}'
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
        'COUNT' => [
            'PARENT' => 'FILTER',
            'NAME' => 'Кол-во элементов (0 - все)',
            'DEFAULT' => '0'
        ],
        'SORT_BY' => [
            'PARENT' => 'SORT',
            'NAME' => 'Сортировка по',
            'TYPE' => 'LIST',
            'DEFAULT' => 'SORT',
            'VALUES' => $arSortBy,
            'ADDITIONAL_VALUES' => 'Y',
        ],
        'SORT_ORDER' => [
            'PARENT' => 'SORT',
            'NAME' => 'Направление сортировки',
            'TYPE' => 'LIST',
            'DEFAULT' => 'ASC',
            'VALUES' => $arSortOrder
        ],
        'SORT_BY_2' => [
            'PARENT' => 'SORT',
            'NAME' => 'Дополнительная сортировка по',
            'TYPE' => 'LIST',
            'DEFAULT' => '',
            'VALUES' => $arSortBy,
            'ADDITIONAL_VALUES' => 'Y',
        ],
        'SORT_ORDER_2' => [
            'PARENT' => 'SORT',
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
        'CACHE_TIME' => ['DEFAULT' => 604800]
    ]
];