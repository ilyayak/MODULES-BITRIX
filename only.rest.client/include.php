<?
/**
 * Autoload from lib directory
 */
spl_autoload_register(function ($className) {

    $sModuleId = basename(dirname(__FILE__));
    $className = ltrim($className, '\\');
    $arParts = explode('.', $sModuleId);

    $classPrefix = implode('\\',array_map('ucwords' ,$arParts)).'\\';

    if ( strpos($className,$classPrefix)===false)
        return;

    $fileRelativePath = str_replace($classPrefix,'',$className);

    $arParts = array_splice($arParts, 2);

    if (!empty($arParts)) {
        $fileName = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . str_replace('\\',DIRECTORY_SEPARATOR,$fileRelativePath) . '.php';
        if (file_exists($fileName))
            require_once $fileName;
    }
});

require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'helpers.php';
