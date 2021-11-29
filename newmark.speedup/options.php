<?
use Bitrix\Main\Localization\Loc;
use	Bitrix\Main\HttpApplication;
use Bitrix\Main\Loader;
use Bitrix\Main\Config\Option;
use Newmark\Speedup\ImageCompress;

Loc::loadMessages(__FILE__);

$request = HttpApplication::getInstance()->getContext()->getRequest();

$module_id = htmlspecialcharsbx($request["mid"] != "" ? $request["mid"] : $request["id"]);

Loader::includeModule($module_id);


$aTabs = array(
    array(
        "DIV" 	  => "edit1",
        "TAB" 	  => Loc::getMessage("NEWMARK_LAZYLOAD_OPTIONS_TAB_NAME"),
        "TITLE"   => Loc::getMessage("NEWMARK_LAZYLOAD_OPTIONS_TAB_TITLE"),
        "OPTIONS" => array(
            Loc::getMessage("NEWMARK_LAZYLOAD_OPTIONS_TAB_COMMON"),
            array(
                "switch_on_lazy",
                Loc::getMessage("NEWMARK_LAZYLOAD_OPTIONS_TAB_SWITCH_ON"),
                "Y",
                array("checkbox")
            ),
            array(
                "include_jquery",
                Loc::getMessage("NEWMARK_LAZYLOAD_OPTIONS_TAB_INCLUDE_JQUERY"),
                "N",
                array("checkbox")
            ),
            Loc::getMessage("NEWMARK_LAZYLOAD_OPTIONS_TAB_ACTION"),
            array(
                "enable_desktop_lazy",
                Loc::getMessage("NEWMARK_SPEEDUP_OPTIONS_ENABLE"),
                "normal",
                array("selectbox", array(
                    "normal" => Loc::getMessage("NEWMARK_SPEEDUP_OPTIONS_ENABLE_NORMAL"),
                    "desktop"   => Loc::getMessage("NEWMARK_SPEEDUP_OPTIONS_ENABLE_DESKTOP"),
                    "mobile"   => Loc::getMessage("NEWMARK_SPEEDUP_OPTIONS_ENABLE_MOBILE")
                ))
            ),
            array(
                "selector",
                Loc::getMessage("NEWMARK_LAZYLOAD_OPTIONS_TAB_SELECTOR"),
                "",
                array("textarea", 5, 40)
            ),
            array(
                "exclude_lazy",
                Loc::getMessage("NEWMARK_LAZYLOAD_OPTIONS_TAB_EXCLUDE"),
                "",
                array("textarea", 10, 40)
            ),
            Loc::getMessage("NEWMARK_LAZYLOAD_OPTIONS_TAB_VIEW"),
            array(
                "animation",
                Loc::getMessage("NEWMARK_LAZYLOAD_OPTIONS_TAB_ANIMATION"),
                "Y",
                array("checkbox")
            ),
			array(
				"preloader",
				Loc::getMessage("NEWMARK_LAZYLOAD_OPTIONS_TAB_PRELOADER"),
				"",
				array("text")
			),
			array(
				"background_size",
				Loc::getMessage('NEWMARK_LAZYLOAD_OPTIONS_TAB_BACKGROUND_SIZE'),
				"",
				array("text")

			),
            Loc::getMessage("NEWMARK_LAZYLOAD_OPTIONS_BOTTOM_NOTE"),
        )
    ),
    array(
        "DIV" 	  => "edit2",
        "TAB" 	  => Loc::getMessage("NEWMARK_CSSINLINER_OPTIONS_TAB_NAME"),
        "TITLE"   => Loc::getMessage("NEWMARK_CSSINLINER_OPTIONS_TAB_TITLE"),
        "OPTIONS" => array(
            Loc::getMessage(!ini_get('allow_url_fopen') ? "NEWMARK_CSSINLINER_OPTIONS_NO_FOPEN" : ""),
            Loc::getMessage("NEWMARK_CSSINLINER_OPTIONS_TAB_COMMON"),
            array(
                "switch_on_cssinliner",
                Loc::getMessage("NEWMARK_CSSINLINER_OPTIONS_TAB_SWITCH_ON"),
                "Y",
                array("checkbox")
            ),
            Loc::getMessage("NEWMARK_CSSINLINER_OPTIONS_TAB_ACTION"),
            array(
                "enable_desktop_cssinliner",
                Loc::getMessage("NEWMARK_SPEEDUP_OPTIONS_ENABLE"),
                "normal",
                array("selectbox", array(
                    "normal" => Loc::getMessage("NEWMARK_SPEEDUP_OPTIONS_ENABLE_NORMAL"),
                    "desktop"   => Loc::getMessage("NEWMARK_SPEEDUP_OPTIONS_ENABLE_DESKTOP"),
                    "mobile"   => Loc::getMessage("NEWMARK_SPEEDUP_OPTIONS_ENABLE_MOBILE")
                ))
            ),
            array(
                "exclude_cssinliner",
                Loc::getMessage("NEWMARK_CSSINLINER_OPTIONS_TAB_EXCLUDE"),
                "",
                array("textarea", 10, 40)
            ),
            array(
                "max_file_size",
                Loc::getMessage("NEWMARK_CSSINLINER_OPTIONS_TAB_MAX_FILE_SIZE"),
                "512",
                array("text", 5)
            ),
            array(
                "cssinliner_cache_time",
                Loc::getMessage("NEWMARK_CSSINLINER_OPTIONS_TAB_CACHE_TIME"),
                "3600",
                array("text", 5)
            ),
            array(
                "external_inline",
                Loc::getMessage("NEWMARK_CSSINLINER_OPTIONS_TAB_EXTERNAL_INLINE"),
                "N",
                array("checkbox")
            ),
            array(
                "inline_google_fonts",
                Loc::getMessage("NEWMARK_CSSINLINER_OPTIONS_TAB_INLINE_GOOGLE_FONTS"),
                "N",
                array("checkbox")
            ),
            Loc::getMessage("NEWMARK_CSSINLINER_OPTIONS_TAB_VIEW"),
            array(
                "minify_css",
                Loc::getMessage("NEWMARK_CSSINLINER_OPTIONS_TAB_MINIFY"),
                "Y",
                array("checkbox")
            ),
            Loc::getMessage("NEWMARK_CSSINLINER_OPTIONS_BOTTOM_NOTE"),
        )
    ),
    array(
        "DIV" 	  => "edit3",
        "TAB" 	  => Loc::getMessage("NEWMARK_HTMLMINIFIER_OPTIONS_TAB_NAME"),
        "TITLE"   => Loc::getMessage("NEWMARK_HTMLMINIFIER_OPTIONS_TAB_TITLE"),
        "OPTIONS" => array(
            Loc::getMessage("NEWMARK_HTMLMINIFIER_OPTIONS_TAB_COMMON"),
            array(
                "switch_on_htmlminifier",
                Loc::getMessage("NEWMARK_HTMLMINIFIER_OPTIONS_TAB_SWITCH_ON"),
                "Y",
                array("checkbox")
            ),
            Loc::getMessage("NEWMARK_HTMLMINIFIER_OPTIONS_TAB_ACTION"),
            array(
                "enable_desktop_htmlminifier",
                Loc::getMessage("NEWMARK_SPEEDUP_OPTIONS_ENABLE"),
                "normal",
                array("selectbox", array(
                    "normal" => Loc::getMessage("NEWMARK_SPEEDUP_OPTIONS_ENABLE_NORMAL"),
                    "desktop"   => Loc::getMessage("NEWMARK_SPEEDUP_OPTIONS_ENABLE_DESKTOP"),
                    "mobile"   => Loc::getMessage("NEWMARK_SPEEDUP_OPTIONS_ENABLE_MOBILE")
                ))
            ),
            array(
                "exclude_htmlminifier",
                Loc::getMessage("NEWMARK_CSSINLINER_OPTIONS_TAB_EXCLUDE"),
                "",
                array("textarea", 10, 40)
            ),
            Loc::getMessage("NEWMARK_HTMLMINIFIER_OPTIONS_BOTTOM_NOTE"),
        )
    ),
    array(
        "DIV" 	  => "image_compress",
        "TAB" 	  => Loc::getMessage("NEWMARK_IMGCOMPRESS_OPTIONS_TAB_NAME"),
        "TITLE"   => Loc::getMessage("NEWMARK_IMGCOMPRESS_OPTIONS_TAB_TITLE"),
        "OPTIONS" => array(
            array(
                "compress_folders",
                Loc::getMessage("NEWMARK_IMGCOMPRESS_OPTIONS_TAB_FOLDERS"),
                "",
                array("textarea", 10, 40)
            )
        )
    ),
    array(
        "DIV" 	  => "modules",
        "TAB" 	  => Loc::getMessage("NEWMARK_MODULES_OPTIONS_TAB_NAME"),
        "TITLE"   => Loc::getMessage("NEWMARK_MODULES_OPTIONS_TAB_TITLE"),
        "OPTIONS" => array(
            Loc::getMessage("NEWMARK_MODULES_DESC"),
        )
    )
);

if($_POST['get_images_table']){
    global $APPLICATION;
    $APPLICATION->RestartBuffer();
    ImageCompress::draw();
    die();
}

if($request->isPost() && check_bitrix_sessid()){
    foreach($aTabs as $aTab){

        foreach($aTab["OPTIONS"] as $arOption){

            if(!is_array($arOption)){

                continue;
            }

            if($arOption["note"]){

                continue;
            }

            if($request["apply"]){
                $optionValue = $request->getPost($arOption[0]);

                if(
                    $arOption[0] == "switch_on_lazy"
                    ||
                    $arOption[0] == "switch_on_cssinliner"
                    ||
                    $arOption[0] == "include_jquery"
                    ||
                    $arOption[0] == "animation"
                    ||
                    $arOption[0] == "inline_google_fonts"
                    ||
                    $arOption[0] == "external_inline"
                    ||
                    $arOption[0] == "minify_css"
                    ||
                    $arOption[0] == 'switch_on_htmlminifier'
                )
                {

                    if($optionValue == ""){

                        $optionValue = "N";
                    }
                }

                Option::set($module_id, $arOption[0], is_array($optionValue) ? implode(",", $optionValue) : $optionValue);
            }elseif($request["default"]){
                Option::set($module_id, $arOption[0], $arOption[2]);
            }

            if($request['image_compress_start']){
                ImageCompress::compressAll();
            }
            if($request['image_return_start']){
                ImageCompress::returnAll();
            }
            if($request['image_compress_one']){
                ImageCompress::compressOne($request['image_compress_one']);
            }
            if($request['image_return_one']){
                ImageCompress::returnOne($request['image_return_one']);
                /*
                CAdminMessage::showMessage(array(
                    "MESSAGE" => 'Изображение '.$request['image_return_one'].' успешно восстановлено',
                    "TYPE" => 'OK',
                ));
                */
            }
        }
    }

    LocalRedirect($APPLICATION->GetCurPage()."?mid=".$module_id."&lang=".LANG."&mid_menu=1");
}

$tabControl = new CAdminTabControl(
    "tabControl",
    $aTabs
);

$tabControl->Begin();

?>

<form action="<? echo($APPLICATION->GetCurPage()); ?>?mid=<? echo($module_id); ?>&lang=<? echo(LANG); ?>" method="post" name="speedup">

    <?
    foreach($aTabs as $aTab){
        if($aTab["OPTIONS"]){

            $tabControl->BeginNextTab();
            __AdmSettingsDrawList($module_id, $aTab["OPTIONS"]);
        }

        if($aTab['DIV'] == 'modules'){
            $modulesArr = \Newmark\Speedup\Main::getNotImportantModulesList();
            $moduleDeleteLink = '/bitrix/admin/module_admin.php?action=&lang='.LANGUAGE_ID.'&'.bitrix_sessid_get().'&uninstall=Удалить';
            $moduleInstallLink = '/bitrix/admin/module_admin.php?action=&lang='.LANGUAGE_ID.'&'.bitrix_sessid_get().'&install=Установить';
            foreach ($modulesArr as $module => $moduleArr){?>
              <tr>
                  <td width="50%"><span style="color:<?=$moduleArr['INSTALLED'] ? 'red' : 'green'?>;"><?=$moduleArr['NAME']?></span></td>
                  <?if($moduleArr['INSTALLED']):?>
                    <td width="50%"><a class="adm-btn adm-btn-green" href="<?=$moduleDeleteLink.'&id='.$module?>" target="_blank"><?=Loc::GetMessage("NEWMARK_MODULES_MODULE_DELETE")?></a></td>
                  <?else:?>
                      <td width="50%"><a class="adm-btn" href="<?=$moduleInstallLink.'&id='.$module?>" target="_blank"><?=Loc::GetMessage("NEWMARK_MODULES_MODULE_INSTALL")?></a></td>
                  <?endif;?>

              </tr>
            <?}
        }
        if($aTab['DIV'] == 'image_compress'){?>
            <script src="/bitrix/js/<?=$module_id?>/newmark.jquery.min.js"></script>

            <script src="/bitrix/js/<?=$module_id?>/datatables.min.js"></script>
            <link href="/bitrix/css/<?=$module_id?>/datatables.min.css" rel="stylesheet"/>

            <script>
                $(function(){
                    var $table = $('#image_compress_edit_table');
                    $.ajax({
                        type: "POST",
                        data: {'get_images_table': 'Y'},
                        success: function (data) {
                            $listTable = $(data);
                            $listTable.insertAfter($table);
                            $('#image-list').DataTable({
                                language: {
                                    "processing": "Подождите...",
                                    "search": "Поиск:",
                                    "lengthMenu": "Показать _MENU_ записей",
                                    "info": "Записи с _START_ до _END_ из _TOTAL_ записей",
                                    "infoEmpty": "Записи с 0 до 0 из 0 записей",
                                    "infoFiltered": "(отфильтровано из _MAX_ записей)",
                                    "infoPostFix": "",
                                    "loadingRecords": "Загрузка записей...",
                                    "zeroRecords": "Записи отсутствуют.",
                                    "emptyTable": "В таблице отсутствуют данные",
                                    "paginate": {
                                        "first": "Первая",
                                        "previous": "Предыдущая",
                                        "next": "Следующая",
                                        "last": "Последняя"
                                    },
                                    "aria": {
                                        "sortAscending": ": активировать для сортировки столбца по возрастанию",
                                        "sortDescending": ": активировать для сортировки столбца по убыванию"
                                    },
                                    "select": {
                                        "rows": {
                                            "_": "Выбрано записей: %d",
                                            "0": "Кликните по записи для выбора",
                                            "1": "Выбрана одна запись"
                                        }
                                    }
                                }
                            });
                        },
                        error: function (data) {
                            alert('images load error, look in console');
                            console.log(data);
                        }
                    });
                })
            </script>
            <style>
                .dataTables_wrapper{
                    margin-top: 30px;
                }
            </style>
        <?}

    }

    $tabControl->Buttons();
	$preloaderFile = Option::get($module_id, 'preloader');

	CAdminFileDialog::ShowScript
	(
		Array(
			"event" => "BtnClick",
			"arResultDest" => array("FORM_NAME" => "speedup", "FORM_ELEMENT_NAME" => "preloader"),
			"arPath" => array("PATH" => GetDirPath($preloaderFile)),
			"select" => 'F',// F - file only, D - folder only
			"operation" => 'O',// O - open, S - save
			"showUploadTab" => true,
			"showAddToMenuTab" => false,
			"fileFilter" => 'jpg,jpeg,png,gif,svg',
			"allowAllFiles" => true,
			"SaveConfig" => true,
		)
	);
	?>
	<script>
		var $preloader = $('input[name="preloader"]');
		$('<input type="button" name="browse" value="..." onClick="BtnClick()">').insertAfter($preloader);
	</script>
    <input type="submit" name="apply" value="<? echo(Loc::GetMessage("NEWMARK_SPEEDUP_OPTIONS_INPUT_APPLY")); ?>" class="adm-btn-save" />
    <input type="submit" name="default" value="<? echo(Loc::GetMessage("NEWMARK_SPEEDUP_OPTIONS_INPUT_DEFAULT")); ?>" />
    <div style="text-align:right;">
        <a href="https://nmark.ru/" target="_blank" style="display:inline-block;">
            <img src="/bitrix/images/<?=$module_id?>/nmlogo.png"/>
        </a>
    </div>

    <?
    echo(bitrix_sessid_post());
    ?>

</form>
<?$tabControl->End();?>
