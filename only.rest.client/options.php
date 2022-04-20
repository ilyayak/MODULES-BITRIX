<?

use Only\Rest\Client\Dictionaries;

$module_id = 'only.rest.client';
$RIGHT = $APPLICATION->GetGroupRight($module_id);
$arAllOptions = [
    ['base_url', 'Ссылка апи логуса', ['text', 30]],
    ['user', 'Логин апи логуса', ['text', 30]],
    ['password', 'Пароль апи логуса', ['text', 30]],
    ['token', 'Токен апи логуса', ['text', 30]],
    ['rate', 'Тариф', ['select', 'getRates']],
    ['loyalty_base_url', 'Ссылка апи лояльности', ['text', 30]],
    ['loyalty_user', 'Логин апи лояльности', ['text', 30]],
    ['loyalty_password', 'Пароль апи лояльности', ['text', 30]],
    ['loyalty_token', 'Токен апи лояльности', ['text', 30]],
];
\Bitrix\Main\Loader::includeModule('only.rest.client');

$aTabs = [
    ['DIV' => 'edit1', 'TAB' => GetMessage('MAIN_TAB_SET'), 'ICON' => 'perfmon_settings', 'TITLE' => GetMessage('MAIN_TAB_TITLE_SET')],
    ['DIV' => 'edit2', 'TAB' => 'Услуги', 'ICON' => 'perfmon_settings', 'TITLE' => 'Услуги'],
    ['DIV' => 'edit3', 'TAB' => 'Помещения', 'ICON' => 'perfmon_settings', 'TITLE' => 'Помещения'],
];
$tabControl = new CAdminTabControl('tabControl', $aTabs);

if ($REQUEST_METHOD == 'POST' && check_bitrix_sessid()) {
    \Bitrix\Main\Config\Option::delete($module_id);
    foreach ($arAllOptions as $option) {
        if (isset($_REQUEST[$option[0]]))
            \Bitrix\Main\Config\Option::set($module_id, $option[0], $_REQUEST[$option[0]]);
    }
    foreach ($_REQUEST['services'] as $service) {
        if ($service['del'] !== 'Y' && $service['id']) {
            $arOption = [
                'id' => $service['id'],
                'max' => is_numeric($service['max']) ? intval($service['max']) : 1,
                'sort' => is_numeric($service['sort']) ? intval($service['sort']) : 100,
            ];
            if ($service['except']) {
                $arOption['except'] = $service['except'];
            }
            \Bitrix\Main\Config\Option::set($module_id, 'service_' . $service['id'], json_encode($arOption));
            unset($arOption);
        }
    }
    foreach ($_REQUEST['placements'] as $placement) {
        if ($placement['del'] !== 'Y' && $placement['typeId'] && $placement['rateId']) {
            $arOption = [
                'typeId' => $placement['typeId'],
                'rateId' => $placement['rateId'],
                'name' => $placement['name'] ?: '',
                'description' => $placement['description'] ?: '',
                'sort' => is_numeric($placement['sort']) ? intval($placement['sort']) : 100,
                'minTime' => is_numeric($placement['minTime']) ? intval($placement['minTime']) : 2,
                'persons' => [
                    'main' => is_numeric($placement['max']) ? intval($placement['max']) : 1,
                    'additional' => is_numeric($placement['additional']) ? intval($placement['additional']) : 0,
                ],
                'price' => [
                    'main' => [
                        'description' => $placement['descriptionPrice'] ?: '',
                    ],
                    'additional' => [
                        'description' => $placement['descriptionExtraPrice'] ?: '',
                    ],
                ],
            ];

            \Bitrix\Main\Config\Option::set($module_id, 'placement_' . $placement['typeId'] . '_' . $placement['rateId'], json_encode($arOption));
            unset($arOption);
        }
    }
}

?>
<form method="post"
      action="<?= $APPLICATION->GetCurPage() ?>?mid=<?= urlencode($module_id) ?>&amp;lang=<?= LANGUAGE_ID ?>">
    <?
    $tabControl->Begin();
    $tabControl->BeginNextTab();
    $arNotes = [];
    foreach ($arAllOptions as $arOption):
        $val = \Bitrix\Main\Config\Option::get($module_id, $arOption[0]);
        $type = $arOption[2];
        if (isset($arOption[3])) $arNotes[] = $arOption[3];
        ?>
        <tr>
            <td width="40%" nowrap <? if ($type[0] == "textarea") echo 'class="adm-detail-valign-top"' ?>>
                <? if (isset($arOption[3])): ?>
                    <span class="required"><sup><?= count($arNotes) ?></sup></span>
                <? endif; ?>
                <label for="<?= htmlspecialcharsbx($arOption[0]) ?>"><?= $arOption[1] ?>:</label>
            <td width="60%">
                <? if ($type[0] == "checkbox"): ?>
                    <input type="checkbox"
                           name="<?= htmlspecialcharsbx($arOption[0]) ?>"
                           id="<?= htmlspecialcharsbx($arOption[0]) ?>"
                           value="Y"<? if ($val == "Y") echo " checked"; ?>>
                <? elseif ($type[0] == "text"): ?>
                    <input type="text"
                           size="<? echo $type[1] ?>"
                           maxlength="255"
                           value="<?= htmlspecialcharsbx($val) ?>"
                           name="<?= htmlspecialcharsbx($arOption[0]) ?>"
                           id="<?= htmlspecialcharsbx($arOption[0]) ?>">
                    <? if ($arOption[0] == "slow_sql_time")
                        echo GetMessage("PERFMON_OPTIONS_SLOW_SQL_TIME_SEC") ?>
                    <? if ($arOption[0] == "large_cache_size")
                        echo GetMessage("PERFMON_OPTIONS_LARGE_CACHE_SIZE_KB") ?>
                <? elseif ($type[0] == "textarea"): ?>
                    <textarea rows="<?= $type[1] ?>"
                              cols="<?= $type[2] ?>"
                              name="<?= htmlspecialcharsbx($arOption[0]) ?>"
                              id="<?= htmlspecialcharsbx($arOption[0]) ?>">
                        <?= htmlspecialcharsbx($val) ?>
                    </textarea>
                <? elseif ($type[0] == "select"):
                    \Bitrix\Main\Loader::includeModule('only.rest.client');
                    $array = Dictionaries::getInstance()->{$type[1]}();
                    $saved = \Bitrix\Main\Config\Option::get($module_id, $arOption[0]);
                    ?>
                    <select style="width:300px" name="<?= htmlspecialcharsbx($arOption[0]) ?>">
                        <option value="">Выберите <?= $arOption[1] ?></option>
                        <? foreach ($array as $key => $value): ?>
                            <option
                                <? if ($saved == $value['Id']) echo 'selected ' ?>value="<?= $value['Id'] ?>"><?= '[' . $value["Id"] . '] ' . $value['Name'] ?></option>
                        <? endforeach; ?>
                    </select>
                <? endif ?>
            </td>
        </tr>
    <? endforeach ?>
    <? $tabControl->EndTab() ?>

    <? $tabControl->BeginNextTab(); ?>
    <? function _MLGetServiceHTML($type = [])
    {
        global $module_id;
        $cache = Bitrix\Main\Data\Cache::createInstance();
        if ($cache->initCache(86400, 'allServices', $module_id)) {
            $arServices = $cache->getVars();
        } elseif ($cache->startDataCache()) {
            \Bitrix\Main\Loader::includeModule($module_id);
            $arServices = Dictionaries::getInstance()->getServices();
            $cache->endDataCache($arServices);
        }
        $name = htmlspecialcharsbx("services[" . $type["id"] . "]");
        ob_start();
        ?>
        <div class="bx-ml-type" id="type_cont_<?= $type["id"] ?>">
            <div class="bx-ml-type-label">
                <?
                if ($type["b_new"]):?>
                    <input type="hidden" name="<?= $name . "[new]" ?>" value="Y"/>
                <? endif; ?>
                <div id="type_name_<?= $type["id"] ?>"
                     class="bx-ml-editable"><?= $type["b_new"] ? 'Новая услуга' : 'Действующая услуга' ?></div>

                <? if ($type["code"] != "image"): ?>
                    <a id="type_del_<?= $type["id"] ?>" class="bx-ml-type-del"
                       href="javascript:void(0);">Удалить</a>
                <? endif; ?>
            </div>

            <? if ($type["code"] != "image"): ?>
                <div class="bx-ml-type-label-deleted">
                    <input id="type_del_inp_<?= $type["id"] ?>" type="hidden" name="<?= $name . "[del]" ?>" value="N"/>
                    <div id="type_del_name_<?= $type["id"] ?>"><?= htmlspecialcharsex($type["name"]) ?></div>
                    <a id="type_restore_<?= $type["id"] ?>" class="bx-ml-type-restore"
                       href="javascript:void(0);">Восстановить</a>
                </div>
            <? endif; ?>

            <div class="bx-ml-type-params">
                <table border="0" width="100%">
                    <tr class="adm-detail-required-field">
                        <td class="adm-detail-content-cell-l bx-ml-td-left" width="40%">
                            <label for="type_name_inp_<?= $type['id'] ?>">Имя в Логусе:</label>
                        </td>
                        <td>
                            <select style="width:300px" name="<?= $name . '[id]' ?>">
                                <option value="">Выберите услугу</option>
                                <? foreach ($arServices ?? [] as $arService): ?>
                                    <? foreach ($arService['ServiceVariants'] as $serviceVariant): ?>
                                        <option<? if ($type['data']['id'] == $arService['Id'] . '_' . $serviceVariant["Id"]) echo ' selected' ?>
                                                value="<?= $arService['Id'] . '_' . $serviceVariant['Id'] ?>"><?= '[' . $serviceVariant['Id'] . '] ' . $arService['Name'] . (count($arService['ServiceVariants']) > 1 ? ' ' . $serviceVariant['Name'] : '') .  ' [' . $serviceVariant['Price'] . ' p.]' ?></option>
                                    <? endforeach; ?>
                                <? endforeach; ?>
                            </select>
                        </td>
                    </tr>
                    <tr class="adm-detail-required-field">
                        <td class="adm-detail-content-cell-l bx-ml-td-left" width="40%">
                            <label for="type_max_inp_<?= $type["id"] ?>">
                                Максимум<span class="required"><sup>1</sup></span>:
                            </label>
                        </td>
                        <td class="adm-detail-content-cell-r" width="60%">
                            <input type="text" name="<?= $name . '[max]' ?>"
                                   id="type_max_inp_<?= htmlspecialcharsbx($type['id']) ?>"
                                   value="<?= $type['data']['max'] ?? 1 ?>" size="40"/>
                        </td>
                    </tr>

                    <tr class="adm-detail-required-field">
                        <td class="adm-detail-content-cell-l bx-ml-td-left" width="40%">
                            <label for="type_except_inp_<?= $type["id"] ?>">
                                Исключает<span class="required"><sup>2</sup></span>:
                            </label>
                        </td>
                        <td>
                            <select style="width:300px" name="<?= $name . '[except]' ?>">
                                <option value="">Выберите услугу</option>
                                <? foreach ($arServices ?? [] as $arService): ?>
                                    <? foreach ($arService['ServiceVariants'] as $serviceVariant): ?>
                                        <option<?= $type['data']['except'] == $serviceVariant['Id'] ? ' selected' : '' ?>
                                                value="<?= $serviceVariant['Id'] ?>"><?= '[' . $serviceVariant["Id"] . '] ' . $arService['Name'] . (count($arService['ServiceVariants']) > 1 ? ' ' . $serviceVariant["Name"] : '') . ' [' . $serviceVariant['Price'] . ' p.]' ?></option>
                                    <? endforeach; ?>
                                <? endforeach; ?>
                            </select>
                        </td>
                    </tr>
                    <tr class="adm-detail-required-field">
                        <td class="adm-detail-content-cell-l bx-ml-td-left" width="40%">
                            <label for="type_max_inp_<?= $type["id"] ?>">Сортировка<span
                                        class="required"><sup>3</sup></span>:</label>
                        </td>
                        <td class="adm-detail-content-cell-r" width="60%">
                            <input type="text" name="<?= $name . "[sort]" ?>"
                                   id="type_max_inp_<?= htmlspecialcharsbx($type["id"]) ?>"
                                   value="<?= $type['data']['sort'] ?? 100 ?>" size="40"/>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
        <?
        $s = ob_get_contents();
        ob_end_clean();
        return $s;
    }

    $arSavedOptions = \Bitrix\Main\Config\Option::getForModule($module_id);
    $arServices = [];
    foreach ($arSavedOptions as $optionKey => $sSavedOption) {
        if (stripos($optionKey, 'service') !== false) {
            $arServices[] = [
                'id' => count($arServices),
                'data' => json_decode($sSavedOption, true),
            ];
        }
    }
    usort($arServices, function ($a, $b) {
        if ($a['data']['sort'] == $b['data']['sort']) return 0;
        return ($a['data']['sort'] < $b['data']['sort']) ? -1 : 1;
    });
    ?>
    <tr class="bx-ml-hidden-row">
        <td colspan="2" align="center">
            <table id="bxml_service_tbl">
                <? foreach ($arServices as $arService): ?>
                    <tr>
                        <td>
                            <?= _MLGetServiceHTML($arService); ?>
                        </td>
                    </tr>
                <? endforeach; ?>
                <tr>
                    <td align="right">
                        <input onclick="addService();" type="button" value="Добавить услугу >>"
                               title="<?= GetMessage("FILEMAN_ML_ADD_TYPE_TITLE") ?>"/>
                    </td>
                </tr>
                <tr>
                    <td align="left">
                        <?= BeginNote(); ?>
                        <span class="required"><sup>1</sup></span>Максимально в номер<br>
                        <span class="required"><sup>2</sup></span>Услуги нельзя заказать одновременно<br>
                        <span class="required"><sup>3</sup></span>В каком порядке выводить на странице бронирования<br>
                        <?= EndNote(); ?>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
    <? $tabControl->EndTab() ?>
    <? $tabControl->BeginNextTab(); ?>
    <? function _MLGetPlacementHTML($type = [])
    {
        global $module_id;
        $cache = Bitrix\Main\Data\Cache::createInstance();
        if ($cache->initCache(86400, 'allPlacements', $module_id)) {
            $arParams = $cache->getVars();
        } elseif ($cache->startDataCache()) {
            \Bitrix\Main\Loader::includeModule($module_id);
            $arParams['roomTypes'] = Dictionaries::getInstance()->getRoomTypes();
            $arParams['rates'] = Dictionaries::getInstance()->getRates();
            $cache->endDataCache($arParams);
        }
        $name = htmlspecialcharsbx("placements[" . $type["id"] . "]");
        ob_start();
        ?>
        <div class="bx-ml-type" id="type_cont_<?= $type["id"] ?>">
            <div class="bx-ml-type-label">
                <?
                if ($type["b_new"]):?>
                    <input type="hidden" name="<?= $name . "[new]" ?>" value="Y"/>
                <? endif; ?>
                <div id="type_name_<?= $type["id"] ?>"
                     class="bx-ml-editable"><?= $type["b_new"] ? 'Новое помещение' : 'Действующее помещение' ?></div>

                <? if ($type["code"] != "image"): ?>
                    <a id="type_del_<?= $type["id"] ?>" class="bx-ml-type-del"
                       href="javascript:void(0);">Удалить</a>
                <? endif; ?>
            </div>

            <? if ($type["code"] != "image"): ?>
                <div class="bx-ml-type-label-deleted">
                    <input id="type_del_inp_<?= $type["id"] ?>" type="hidden" name="<?= $name . "[del]" ?>" value="N"/>
                    <div id="type_del_name_<?= $type["id"] ?>"><?= htmlspecialcharsex($type["name"]) ?></div>
                    <a id="type_restore_<?= $type["id"] ?>" class="bx-ml-type-restore"
                       href="javascript:void(0);">Восстановить</a>
                </div>
            <? endif; ?>

            <div class="bx-ml-type-params">
                <table border="0" width="100%">
                    <tr class="adm-detail-required-field">
                        <td class="adm-detail-content-cell-l bx-ml-td-left" width="40%">
                            <label for="type_name_inp_<?= $type["id"] ?>">Имя в Логусе:</label>
                        </td>
                        <td>
                            <select style="width:300px" name="<?= $name . '[typeId]' ?>">
                                <option value="">Выберите помещение</option>
                                <? foreach ($arParams['roomTypes'] ?? [] as $roomType): ?>
                                    <option<?= $type['data']['typeId'] == $roomType['Id'] ? ' selected' : '' ?>
                                            value="<?= $roomType['Id'] ?>"><?= '[' . $roomType["Id"] . '] ' . $roomType['Name'] ?></option>
                                <? endforeach; ?>
                            </select>
                        </td>
                    </tr>
                    <tr class="adm-detail-required-field">
                        <td class="adm-detail-content-cell-l bx-ml-td-left" width="40%">
                            <label for="type_max_inp_<?= $type["id"] ?>">Имя на сайте:</label>
                        </td>
                        <td class="adm-detail-content-cell-r" width="60%">
                            <input type="text" name="<?= $name . "[name]" ?>"
                                   id="type_max_inp_<?= htmlspecialcharsbx($type["id"]) ?>"
                                   value="<?= $type['data']['name'] ?? 'Помещение' ?>" size="40"/>
                        </td>
                    </tr>
                    <tr class="adm-detail-required-field">
                        <td class="adm-detail-content-cell-l bx-ml-td-left" width="40%">
                            <label for="type_max_inp_<?= $type["id"] ?>">Описание:</label>
                        </td>
                        <td class="adm-detail-content-cell-r" width="60%">
                            <textarea rows="5" cols="40"
                                      name="<?= $name . '[description]' ?>"
                                      id="type_max_inp_<?= $type['id'] ?>"><?= htmlspecialcharsbx($type['data']['description']) ?>
                            </textarea>
                        </td>
                    </tr>
                    <tr class="adm-detail-required-field">
                        <td class="adm-detail-content-cell-l bx-ml-td-left" width="40%">
                            <label for="type_except_inp_<?= $type["id"] ?>">Тариф:</label>
                        </td>
                        <td>
                            <select style="width:300px" name="<?= $name . '[rateId]' ?>">
                                <option value="">Выберите тариф</option>
                                <? foreach ($arParams['rates'] ?? [] as $rate): ?>
                                    <option<?= $type['data']['rateId'] == $rate['Id'] ? ' selected' : '' ?>
                                            value="<?= $rate['Id'] ?>"><?= '[' . $rate["Id"] . '] ' . $rate['Name'] ?></option>
                                <? endforeach; ?>
                            </select>
                        </td>
                    </tr>
                    <tr class="adm-detail-required-field">
                        <td class="adm-detail-content-cell-l bx-ml-td-left" width="40%">
                            <label for="type_max_inp_<?= $type["id"] ?>">Описание цены:</label>
                        </td>
                        <td class="adm-detail-content-cell-r" width="60%">
                            <textarea rows="5" cols="40"
                                      name="<?= $name . '[descriptionPrice]' ?>"
                                      id="type_max_inp_<?= $type['id'] ?>"><?= htmlspecialcharsbx($type['data']['price']['main']['description']) ?>
                            </textarea>
                        </td>
                    </tr>
                    <tr class="adm-detail-required-field">
                        <td class="adm-detail-content-cell-l bx-ml-td-left" width="40%">
                            <label for="type_max_inp_<?= $type["id"] ?>">Описание доп.цены:</label>
                        </td>
                        <td class="adm-detail-content-cell-r" width="60%">
                            <textarea rows="5" cols="40"
                                      name="<?= $name . '[descriptionExtraPrice]' ?>"
                                      id="type_max_inp_<?= $type['id'] ?>"><?= htmlspecialcharsbx($type['data']['price']['additional']['description']) ?>
                            </textarea>
                        </td>
                    </tr>
                    <tr class="adm-detail-required-field">
                        <td class="adm-detail-content-cell-l bx-ml-td-left" width="40%">
                            <label for="type_max_inp_<?= $type["id"] ?>">
                                Максимум мест<span class="required"><sup>1</sup></span>:
                            </label>
                        </td>
                        <td class="adm-detail-content-cell-r" width="60%">
                            <input type="number" name="<?= $name . "[max]" ?>"
                                   id="type_max_inp_<?= htmlspecialcharsbx($type["id"]) ?>"
                                   value="<?= $type['data']['persons']['main'] ?? 6 ?>" size="40"/>
                        </td>
                    </tr>
                    <tr class="adm-detail-required-field">
                        <td class="adm-detail-content-cell-l bx-ml-td-left" width="40%">
                            <label for="type_max_inp_<?= $type["id"] ?>">
                                Дополнительные места<span class="required"><sup>2</sup></span>:
                            </label>
                        </td>
                        <td class="adm-detail-content-cell-r" width="60%">
                            <input type="number" name="<?= $name . "[additional]" ?>"
                                   id="type_max_inp_<?= htmlspecialcharsbx($type["id"]) ?>"
                                   value="<?= $type['data']['persons']['additional'] ?? 0 ?>" size="40"/>
                        </td>
                    </tr>
                    <tr class="adm-detail-required-field">
                        <td class="adm-detail-content-cell-l bx-ml-td-left" width="40%">
                            <label for="type_max_inp_<?= $type["id"] ?>">Сортировка<span
                                        class="required"><sup>3</sup></span>:</label>
                        </td>
                        <td class="adm-detail-content-cell-r" width="60%">
                            <input type="number" name="<?= $name . "[sort]" ?>"
                                   id="type_max_inp_<?= htmlspecialcharsbx($type["id"]) ?>"
                                   value="<?= $type['data']['sort'] ?? 100 ?>" size="40"/>
                        </td>
                    </tr>
                    <tr class="adm-detail-required-field">
                        <td class="adm-detail-content-cell-l bx-ml-td-left" width="40%">
                            <label for="type_max_inp_<?= $type["id"] ?>">Минимальное время заказа, ч:</label>
                        </td>
                        <td class="adm-detail-content-cell-r" width="60%">
                            <input type="number" name="<?= $name . "[minTime]" ?>"
                                   id="type_max_inp_<?= htmlspecialcharsbx($type["id"]) ?>"
                                   value="<?= $type['data']['minTime'] ?? 2 ?>" size="40"/>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
        <?
        $s = ob_get_contents();
        ob_end_clean();
        return $s;
    }

    $arPlacements = [];
    foreach ($arSavedOptions as $optionKey => $sSavedOption) {
        if (stripos($optionKey, 'placement') !== false) {
            $arPlacements[] = [
                'id' => count($arPlacements),
                'data' => json_decode($sSavedOption, true),
            ];
        }
    }
    usort($arPlacements, function ($a, $b) {
        if ($a['data']['sort'] == $b['data']['sort']) return 0;
        return ($a['data']['sort'] < $b['data']['sort']) ? -1 : 1;
    });

    ?>
    <tr class="bx-ml-hidden-row">
        <td colspan="2" align="center">
            <table id="bxml_placement_tbl">
                <? foreach ($arPlacements as $arPlacement): ?>
                    <tr>
                        <td>
                            <?= _MLGetPlacementHTML($arPlacement); ?>
                        </td>
                    </tr>
                <? endforeach; ?>
                <tr>
                    <td align="right">
                        <input onclick="addPlacement();" type="button" value="Добавить помещение >>"
                               title="<?= GetMessage("FILEMAN_ML_ADD_TYPE_TITLE") ?>"/>
                    </td>
                </tr>
                <tr>
                    <td align="left">
                        <?= BeginNote(); ?>
                        <span class="required"><sup>1</sup></span>Максимально без доплаты<br>
                        <span class="required"><sup>2</sup></span>Места с доплатой<br>
                        <span class="required"><sup>3</sup></span>В каком порядке выводить на странице бронирования<br>
                        <?= EndNote(); ?>
                    </td>
                </tr>
            </table>

            <script>
                window.onload = function () {
                    setTimeout(function () {
                            window.oMLSet = {
                                pTypeTbl: BX("bxml_service_tbl"),
                                curCount: <?= count($arServices) ?>
                            };
                            <? for ($i = 0, $c = count($arServices); $i < $c; $i++):?>
                            InitEventForService(<?=$i?>);
                            <? endfor; ?>
                        },
                        50);
                    setTimeout(function () {
                            window.oMLSet1 = {
                                pTypeTbl: BX("bxml_placement_tbl"),
                                curCount: <?= count($arPlacements) ?>
                            };
                            <? for ($i = 0, $c = count($arPlacements); $i < $c; $i++):?>
                            InitEventForPlacement(<?=$i?>);
                            <? endfor; ?>
                        },
                        50);
                };

                function addService() {
                    var id = window.oMLSet.curCount++;
                    var newCell = window.oMLSet.pTypeTbl.insertRow(window.oMLSet.pTypeTbl.rows.length - 1).insertCell(-1);
                    var typeHtml = '<?= CUtil::JSEscape(_MLGetServiceHTML(["id" => "tmp_ml_type_id", "b_new" => true]));?>';

                    // Replace id, and increase "curCount"
                    typeHtml = typeHtml.replace(/tmp_ml_type_id/ig, id);

                    var code = [], start, end, i, cnt;
                    while ((start = typeHtml.indexOf('<' + 'script>')) != -1) {
                        var end = typeHtml.indexOf('</' + 'script>', start);
                        if (end == -1)
                            break;
                        code[code.length] = typeHtml.substr(start + 8, end - start - 8);
                        typeHtml = typeHtml.substr(0, start) + typeHtml.substr(end + 9);
                    }

                    for (var i = 0, cnt = code.length; i < cnt; i++)
                        if (code[i] != '')
                            jsUtils.EvalGlobal(code[i]);
                    newCell.innerHTML = typeHtml;

                    setTimeout(function () {
                        InitEventForService(id);
                    }, 50);
                }

                function InitEventForService(id) {
                    var oType =
                        {
                            pCont: BX('type_cont_' + id),
                            pName: BX('type_name_' + id),
                            pNameInp: BX('type_name_inp_' + id),
                            pDelLink: BX('type_del_' + id),
                            pRestoreLink: BX('type_restore_' + id),
                            pDelInput: BX('type_del_inp_' + id)
                        };

                    if (oType.pName && oType.pNameInp) {
                        oType.pNameInp.onkeyup = function () {
                            while (oType.pName.firstChild)
                                oType.pName.removeChild(oType.pName.firstChild);

                            oType.pName.appendChild(document.createTextNode(oType.pNameInp.value));
                        };

                        if (oType.pNameInp.value == "") {
                            oType.pNameInp.value = "";
                            oType.pName.innerHTML = "Новая услуга";
                            oType.pNameInp.focus();
                            oType.pNameInp.select();
                        }
                    }

                    if (oType.pDelLink) {
                        oType.pDelLink.onclick = function () {
                            oType.pCont.className = "bx-ml-type bx-ml-type-deleted";
                            if (!oType.pDelName)
                                oType.pDelName = BX("type_del_name_" + id);
                            while (oType.pDelName.firstChild)
                                oType.pDelName.removeChild(oType.pDelName.firstChild);
                            oType.pDelInput.value = "Y";
                        };
                    }

                    if (oType.pRestoreLink) {
                        oType.pRestoreLink.onclick = function () {
                            oType.pCont.className = "bx-ml-type";
                            oType.pDelInput.value = "N";
                        };
                    }
                }

                function addPlacement() {
                    var id = window.oMLSet1.curCount++;
                    var newCell = window.oMLSet1.pTypeTbl.insertRow(window.oMLSet1.pTypeTbl.rows.length - 1).insertCell(-1);
                    var typeHtml = '<?= CUtil::JSEscape(_MLGetPlacementHTML(["id" => "tmp_ml_type_id", "b_new" => true]));?>';

                    // Replace id, and increase "curCount"
                    typeHtml = typeHtml.replace(/tmp_ml_type_id/ig, id);

                    var code = [], start, end, i, cnt;
                    while ((start = typeHtml.indexOf('<' + 'script>')) != -1) {
                        var end = typeHtml.indexOf('</' + 'script>', start);
                        if (end == -1)
                            break;
                        code[code.length] = typeHtml.substr(start + 8, end - start - 8);
                        typeHtml = typeHtml.substr(0, start) + typeHtml.substr(end + 9);
                    }

                    for (var i = 0, cnt = code.length; i < cnt; i++)
                        if (code[i] != '')
                            jsUtils.EvalGlobal(code[i]);
                    newCell.innerHTML = typeHtml;

                    setTimeout(function () {
                        InitEventForPlacement(id);
                    }, 50);
                }

                function InitEventForPlacement(id) {
                    var oType =
                        {
                            pCont: BX('type_cont_' + id),
                            pName: BX('type_name_' + id),
                            pNameInp: BX('type_name_inp_' + id),
                            pDelLink: BX('type_del_' + id),
                            pRestoreLink: BX('type_restore_' + id),
                            pDelInput: BX('type_del_inp_' + id)
                        };

                    if (oType.pName && oType.pNameInp) {
                        oType.pNameInp.onkeyup = function () {
                            while (oType.pName.firstChild)
                                oType.pName.removeChild(oType.pName.firstChild);

                            oType.pName.appendChild(document.createTextNode(oType.pNameInp.value));
                        };

                        if (oType.pNameInp.value == "") {
                            oType.pNameInp.value = "";
                            oType.pName.innerHTML = "Новое помещение";
                            oType.pNameInp.focus();
                            oType.pNameInp.select();
                        }
                    }

                    if (oType.pDelLink) {
                        oType.pDelLink.onclick = function () {
                            oType.pCont.className = "bx-ml-type bx-ml-type-deleted";
                            if (!oType.pDelName)
                                oType.pDelName = BX("type_del_name_" + id);
                            while (oType.pDelName.firstChild)
                                oType.pDelName.removeChild(oType.pDelName.firstChild);
                            oType.pDelInput.value = "Y";
                        };
                    }

                    if (oType.pRestoreLink) {
                        oType.pRestoreLink.onclick = function () {
                            oType.pCont.className = "bx-ml-type";
                            oType.pDelInput.value = "N";
                        };
                    }
                }

            </script>
        </td>
    </tr>
    <? $tabControl->EndTab() ?>
    <? $tabControl->Buttons(); ?>
    <input type="submit"
           name="Update"
           value="<?= GetMessage("MAIN_SAVE") ?>"
           title="<?= GetMessage("MAIN_OPT_SAVE_TITLE") ?>"
           class="adm-btn-save">

    <?= bitrix_sessid_post(); ?>
    <? $tabControl->End(); ?>
</form>
<? if (!empty($arNotes)):
    echo BeginNote();
    foreach ($arNotes as $i => $str): ?>
        <span class="required"><sup><?= $i + 1 ?></sup></span><?= $str ?><br>
    <? endforeach;
    echo EndNote();
endif; ?>
