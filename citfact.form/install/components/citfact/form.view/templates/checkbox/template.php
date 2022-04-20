<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

$this->setFrameMode(true);
?>

<div class="form-group">
    <label><?= $arResult['LABEL'] ?></label>
    <?php foreach ($arResult['VALUE_LIST'] as $value): ?>
        <?php $checked = ($arResult['MULTIPLE'] == 'Y')
            ? (in_array($value['ID'], $arResult['VALUE'])) ? 'checked="checked"' : ''
            : ($value['ID'] == $arResult['VALUE']) ? 'checked="checked"' : '';
        ?>
        <input type="checkbox" name="<?= $arResult['NAME'] ?>"
               value="<?= $value['ID'] ?>" <?= $checked ?>/> <?= $value['VALUE'] ?>
    <?php endforeach; ?>
</div>
