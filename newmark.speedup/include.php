<?
namespace Newmark\Speedup;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Page\Asset;
use Bitrix\Main\IO\File;
use CModule;

/**
 * Class Main
 * @package Newmark\Speedup
 */
class Main{
    private static $allOptions;
    private static $preview;
	private static $background;
	private static $loadingClass = 'newmark-lazyload-loading';
    private static $userAgent = 'Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/75.0.3770.142 Safari/537.36';
    private static $isMobile = NULL;
    private static $cssPath;
    private static $cacheTime;
    /**
     * @return mixed
     */
    public static function getModuleId()
    {
        return pathinfo(__DIR__)["basename"];
    }

    /**
     * @param $excludePages
     * @return bool
     */
    private static function checkPagePermission($excludePages){
        $curPage = $GLOBALS['APPLICATION']->GetCurPage();
        $pages  = preg_split("/\r\n|\n|\r/", $excludePages);

        foreach ($pages as $key => $page) {
            if(substr($page,-1) == "*"){
                $pageNoMask = substr($page, 0, -1);
                if($curPage != $pageNoMask && strpos($curPage, $pageNoMask) !== false)
                    return false;
            }

            if($curPage == $page)
                    return false;
        }

        return true;
    }
    /**
     * @param $module_id
     * @return array
     */
    private static function getOptions(){
        if(!empty(self::$allOptions))
            return self::$allOptions;
        $optionsArr = array(
            "switch_on_lazy" 	=> Option::get(self::getModuleId(), "switch_on_lazy", "Y"),
            "include_jquery"     	=> Option::get(self::getModuleId(), "include_jquery", "N"),
            "selector"    	=> Option::get(self::getModuleId(), "selector", ""),
            "exclude_lazy"    	=> Option::get(self::getModuleId(), "exclude_lazy", ""),
            "animation"     	=> Option::get(self::getModuleId(), "animation", "Y"),
            "switch_on_cssinliner" 	=> Option::get(self::getModuleId(), "switch_on_cssinliner", "Y"),
            "max_file_size" 	=> Option::get(self::getModuleId(), "max_file_size", "512"),
            "inline_google_fonts" 	=> Option::get(self::getModuleId(), "inline_google_fonts", "N"),
            "external_inline" 	=> Option::get(self::getModuleId(), "external_inline", "N"),
            "minify_css" 	=> Option::get(self::getModuleId(), "minify_css", "Y"),
            "exclude_cssinliner" 	=> Option::get(self::getModuleId(), "exclude_cssinliner", ""),
            "enable_desktop_cssinliner" => Option::get(self::getModuleId(), 'enable_desktop_cssinliner', 'normal'),
            "enable_desktop_lazy" => Option::get(self::getModuleId(), 'enable_desktop_lazy', 'normal'),
            "cssinliner_cache_time" => Option::get(self::getModuleId(), 'cssinliner_cache_time', '3600'),
            "switch_on_htmlminifier" => Option::get(self::getModuleId(), 'switch_on_htmlminifier', 'Y'),
            "exclude_htmlminifier" => Option::get(self::getModuleId(), 'exclude_htmlminifier', ''),
            "enable_desktop_htmlminifier" => Option::get(self::getModuleId(), 'enable_desktop_htmlminifier', 'normal'),
			"preloader" => Option::get(self::getModuleId(), 'preloader', ''),
			"background_size" => Option::get(self::getModuleId(), 'background_size', '')
        );
        self::$allOptions = $optionsArr;
        return $optionsArr;
    }
    /**
     * @param $css
     * @return mixed
     */
    private static function minimizeCSS($css){
        $css = preg_replace('/\/\*((?!\*\/).)*\*\//','',$css); // negative look ahead
        $css = preg_replace('/\s{2,}/',' ',$css);
        $css = preg_replace('/\s*([:;{}])\s*/','$1',$css);
        $css = preg_replace('/;}/','}',$css);
        return $css;
    }

    /**
     * @param $url
     * @return array
     */
    private static function loadExternalContent($url){
        $curlOptions = array(
            CURLOPT_CUSTOMREQUEST  =>"GET",        //set request type post or get
            CURLOPT_POST           =>false,        //set to GET
            CURLOPT_RETURNTRANSFER => true,     // return web page
            CURLOPT_HEADER         => false,    // don't return headers
            CURLOPT_FOLLOWLOCATION => true,     // follow redirects
            CURLOPT_ENCODING       => "",       // handle all encodings
            CURLOPT_AUTOREFERER    => true,     // set referer on redirect
            CURLOPT_CONNECTTIMEOUT => 120,      // timeout on connect
            CURLOPT_TIMEOUT        => 120,      // timeout on response
            CURLOPT_MAXREDIRS      => 10,       // stop after 10 redirects
            CURLOPT_USERAGENT	   => self::$userAgent
        );

        $ch = curl_init($url);
        curl_setopt_array($ch, $curlOptions);
        $content = curl_exec($ch);
        $info = curl_getinfo($ch);
        curl_close($ch);
        return array(
            'content' => $content,
            'info' => $info
        );
    }

    /**
     * @param $url
     * @return array|bool
     */
    private static function getExternalContent($url){
        $md5Url = md5($url);
        $cachePath = self::$cssPath.'/cache/'.$md5Url.'.css';

        if(File::isFileExists($cachePath)){
            if (!((time() - self::$cacheTime) < filemtime($cachePath))) {
                File::deleteFile($cachePath);
                $extFile = self::loadExternalContent($url);
                File::putFileContents($cachePath, $extFile['content']);
            }
        }else{
            $extFile = self::loadExternalContent($url);
            File::putFileContents($cachePath, $extFile['content']);
        }


        if(File::isFileExists($cachePath)){
            $file = new File($cachePath);
            $content = File::getFileContents($cachePath);
            $size = $file->getSize();

            return array(
                'content' => $content,
                'size' => $size
            );
        }

        return false;

    }
    /**
     * @param $bytes
     * @return float|int
     */
    public static function formatFileSize($bytes){
        return $bytes / 1024;
    }

    /**
     * @param $path
     * @param $maxFileSize
     * @param bool $external
     * @return bool|mixed|string
     */
    private static function checkFileSize($path, $maxFileSize, $external = false){
        if($external){
            $extFileContent = self::getExternalContent($path);

            if(!$extFileContent['content'] || self::formatFileSize($extFileContent['size']) > $maxFileSize) //check external file size
                return false;

            return $extFileContent['content'];
        }else{
            if(!File::isFileExists($path)) //check local file exists
                return false;

            $file = new File($path);
            if(self::formatFileSize($file->getSize()) > $maxFileSize) //check local file size
                return false;

            // return content of local file
            return File::getFileContents($path);
        }
    }
    /**
     * @param $styleUrl
     * @param $options
     * @return bool|mixed|string
     */
    private static function getCssLikeString($styleUrl, $options){
        $maxFileSize = $options['max_file_size'] ? $options['max_file_size'] : 512;
        $inlineGoogle = $options['inline_google_fonts'] == 'Y';
        $externalInline = $options['external_inline'] == 'Y';

        if (strpos($styleUrl, 'http') === 0){
            if(!$externalInline || (strpos($styleUrl, 'fonts.googleapis') !== false && !$inlineGoogle))
                return false;
            if($css = self::checkFileSize($styleUrl, $maxFileSize, true))
                return $css;
        }else{
            $styleUrl = preg_replace('/\?\w+$/', '', $styleUrl);
            if($css = self::checkFileSize($_SERVER['DOCUMENT_ROOT'].$styleUrl, $maxFileSize))
                return $css;
        }

        return false;
    }
    /**
     * @return bool
     */
    public static function speedAddScripts(){
        $options = self::getOptions();

        if(defined("ADMIN_SECTION")
            || !self::checkPagePermission($options['exclude_lazy'])
        ) {
            return false;
        }

        if($options['switch_on_lazy'] == 'Y' && self::checkDesktop($options['enable_desktop_lazy'])) {
            if ($options['include_jquery'] == 'Y')
                Asset::getInstance()->addJs("/bitrix/js/" . self::getModuleId() . "/newmark.lazyload.min.js");
            else
                Asset::getInstance()->addJs("/bitrix/js/" . self::getModuleId() . "/newmark.lazyload.nojq.min.js");

            Asset::getInstance()->addCss("/bitrix/css/" . self::getModuleId() . "/newmark.lazyload.min.css");

            Asset::getInstance()->addString(
                "<script id=\"newmark_lazyload-params\" data-params='" . json_encode(self::getOptions()) . "'></script>",
                true
            );
        }

        return false;
    }

    /**
     * @param $opt
     * @return bool
     */
    private static function checkDesktop($opt){
        if(is_null(self::$isMobile))
            return true;

        switch ($opt){
            case 'normal':
                return true;
                break;
            case 'desktop':
                if(!self::$isMobile)
                    return true;
                break;
            case 'mobile':
                if(self::$isMobile)
                    return true;
                break;
        }
        return false;
    }
    /**
     * @param string $content
     * @return bool
     */
    public static function speedActions(&$content = ''){
        if(!$content || defined("ADMIN_SECTION"))
            return false;

        $options = self::getOptions();

        //set default vars
        if(!class_exists('CLightHTMLEditor'))
            CModule::IncludeModule("fileman");

        self::$isMobile = \CLightHTMLEditor::IsMobileDevice();
        self::$cssPath = $_SERVER['DOCUMENT_ROOT']."/bitrix/css/".self::getModuleId();
        self::$cacheTime = $options['cssinliner_cache_time'] ? $options['cssinliner_cache_time'] : 3600;

        //start lazy?
        if($options['switch_on_lazy'] == 'Y' && self::checkPagePermission($options['exclude_lazy']) && self::checkDesktop($options['enable_desktop_lazy']))
            self::lazyActions($content, $options);

        //start cssinliner?
        global $USER;
        if(!$USER->IsAdmin() && $options['switch_on_cssinliner'] == 'Y' && self::checkPagePermission($options['exclude_cssinliner']) && self::checkDesktop($options['enable_desktop_cssinliner']))
            self::cssinlinerActions($content, $options);

        //start HTML Minifier?
        if(!$USER->IsAdmin() && $options['switch_on_htmlminifier'] == 'Y' && self::checkPagePermission($options['exclude_htmlminifier']) && self::checkDesktop($options['enable_desktop_htmlminifier']))
            self::htmlminifierActions($content);

        return false;
    }

    /**
     * @param $content
     */
    private static function htmlminifierActions(&$content){
        //$search = array('/\>[^\S ]+/s', '/[^\S ]+\</s', '/(\s)+/s');
        //$replace = array('>','<','\\1');
        //$content = preg_replace($search, $replace, $content);

        $search = '%# Collapse whitespace everywhere but in blacklisted elements.
        (?>             # Match all whitespans other than single space.
          [^\S ]\s*     # Either one [\t\r\n\f\v] and zero or more ws,
        | \s{2,}        # or two or more consecutive-any-whitespace.
        ) # Note: The remaining regex consumes no text at all...
        (?=             # Ensure we are not in a blacklist tag.
          [^<]*+        # Either zero or more non-"<" {normal*}
          (?:           # Begin {(special normal*)*} construct
            <           # or a < starting a non-blacklist tag.
            (?!/?(?:textarea|pre|script)\b)
            [^<]*+      # more non-"<" {normal*}
          )*+           # Finish "unrolling-the-loop"
          (?:           # Begin alternation group.
            <           # Either a blacklist start tag.
            (?>textarea|pre|script)\b
          | \z          # or end of file.
          )             # End alternation group.
        )  # If we made it here, we are not in a blacklist tag.
        %Six';
        $content = preg_replace($search, " ", $content);
    }
    /**
     * @param $content
     * @param $options
     */
    private static function lazyActions(&$content, $options){
		if(!class_exists('DomQuery'))
			return;
        //vars
        self::$preview = '/bitrix/images/'.self::getModuleId().'/newmark_empty.png'; //make preview url
		self::$background = '/bitrix/images/'.self::getModuleId().'/newmark_lazy_load.svg';
		if($options['preloader'])
			self::$background = $options['preloader'];

		// make style
		$styleStr = '<style>';
			$styleStr .= '.'.self::$loadingClass.'{';
				$styleStr .= 'background: url('.self::$background.') no-repeat center center;';
				$styleStr .= 'max-width: 100%;';
				$styleStr .= 'max-height: 100%;';
				if($options['background_size'])
					$styleStr .= 'background-size: '.$options['background_size'].';';
			$styleStr .= '}';
		$styleStr .= '</style>';
		// /make style

        $selector = $options['selector'] ? $options['selector'] : 'img';

        $dom = new DomQuery($content);
        $images = $dom->find($selector);
        $imagesArr = array();
        foreach ($images as $image){
            $imgStr = str_replace(' ','',(string) $image);
            $md5 = md5($imgStr);
            $imagesArr[$md5] = $imgStr;
        }

        $content = preg_replace_callback_array(
            array(
                "/<img[^>]+>/" => function($matches) use ($options, $imagesArr){
                    $img = $matches[0];
                    $uniqId = uniqid();
                    $domImg = new DomQuery($img);
                    $md5 = md5(str_replace(' ','', (string) $domImg));
					$classSet = false;

                    if(!array_key_exists($md5, $imagesArr))
                        return $img;

                    preg_match_all('/(\w+)=("[^"]*")/i',$img, $attrs);

                    $imgStr = '<img ';
                    foreach ($attrs[0] as $attr){
                        $attrArr = explode('=', $attr);

                        if($attrArr[0] == 'data-src')
                            continue;

                        if($attrArr[0] == 'src'){
                            $imgStr .= 'src="'.self::$preview.'" ';
                            $imgStr .= 'data-src='.$attrArr[1].' ';
                            continue;
                        }

                        if($attrArr[0] == 'srcset'){
                            $imgStr .= 'srcset="'.self::$preview.'" ';
                            $imgStr .= 'data-srcset='.$attrArr[1].' ';
                            continue;
                        }

						if($attrArr[0] == 'class'){
							$imgStr .= 'class="'.substr($attrArr[1], 1, -1).' '.self::$loadingClass.'" ';
							$classSet = true;
							continue;
						}

                        $imgStr .= $attr.' ';
                    }
                    $imgStr .= 'data-nm-id="'.$uniqId.'"';
					if(!$classSet)
						$imgStr .= ' class="'.self::$loadingClass.'"';

                    $imgStr .= '/>';


                    return $imgStr;
                },
				"/<\/head>/" => function($matches) use ($styleStr){
					return $styleStr.$matches[0];
				}
            ),
            $content
        );

    }

    /**
     * @param $content
     * @param $options
     */
    private static function cssinlinerActions(&$content, $options){
        $content = preg_replace_callback_array(
            array(
                "/<link[^>]+>/" => function($matches){
                    $link = $matches[0];

                    if(strpos($link, 'rel="stylesheet"') === false && strpos($link, "rel='stylesheet'") === false) //if its not stylesheet
                        return $link;
                    preg_match_all('/(\w+)=(?:(\'|")([^"\']*)(\'|"))/i',$link, $attrs); //split attrs
                    $styleUrl = false;
                    foreach ($attrs[0] as $attr){ //find href
                        $attrArr = explode('=', $attr);
                        if($attrArr[0] == 'href'){
                            unset($attrArr[0]);
                            $styleUrl = str_replace('"', '', implode('=',$attrArr));
                            $styleUrl = str_replace("'", '', $styleUrl);
                            break;
                        }
                    }

                    if(!$styleUrl)
                        return $link;

                    $styleContent = self::getCssLikeString($styleUrl, self::getOptions());

                    if(!$styleContent)
                        return $link;

                    $options = self::getOptions();
                    if($options['minify_css'] == 'Y')
                        $styleContent = self::minimizeCSS($styleContent);

                    $styleContent = preg_replace('/@font-face(\s+|){/i', '@font-face{font-display:swap;', $styleContent);
                    return '<style type="text/css">'.$styleContent.'</style>';
                }
            ),
            $content
        );
    }

    /**
     * @return array
     */
    private static function getNonImportantModulesArr(){
        return array(
            "ldap" => "AD/LDAP интеграция (ldap)",
            "pull" => "Push and Pull (pull)",
            "wiki" => "Wiki (wiki)",
            "abtest" => "А/B-тестирование (abtest)",
            "statistic" => "Веб-аналитика (statistic)",
            "cluster" => "Веб-кластер (cluster)",
            "im" => "Веб-мессенджер (im)",
            "webservice" => "Веб-сервисы (webservice)",
            "bizprocdesigner" => "Дизайнер бизнес-процессов (bizprocdesigner)",
            "workflow" => "Документооборот (workflow)",
            "calendar" => "Календарь событий (calendar)",
            "report" => "Конструктор отчетов (report)",
            "idea" => "Менеджер идей (idea)",
            "mobileapp" => "Мобильная платформа (mobileapp) - если не подключено мобильное приложение",
            "eshopapp" => "Мобильное приложение для интернет-магазина (eshopapp) - если не подключено мобильное приложение",
            "learning" => "Обучение (learning)",
            "translate" => "Перевод (translate)",
            "mail" => "Почта (mail)",
            "support" => "Техподдержка (support)",
            "lists" => "Универсальные списки (lists)",
            "scale" => "Управление масштабированием (scale)"
        );
    }

    /**
     * @return mixed
     */
    private static function getAllModules(){
        //Get list of subdirs in modules folder
        $folders = array(
            "/local/modules",
            "/bitrix/modules",
        );
        foreach($folders as $folder)
        {
            $handle = @opendir($_SERVER["DOCUMENT_ROOT"].$folder);
            if($handle)
            {
                while (false !== ($dir = readdir($handle)))
                {
                    if(!isset($arModules[$dir]) && is_dir($_SERVER["DOCUMENT_ROOT"].$folder."/".$dir) && $dir!="." && $dir!=".." && $dir!="main" && strpos($dir, ".") === false)
                    {
                        $module_dir = $_SERVER["DOCUMENT_ROOT"].$folder."/".$dir;
                        if($info = CModule::CreateModuleObject($dir))
                        {
                            $arModules[$dir]["MODULE_ID"] = $info->MODULE_ID;
                            $arModules[$dir]["MODULE_NAME"] = $info->MODULE_NAME;
                            $arModules[$dir]["MODULE_DESCRIPTION"] = $info->MODULE_DESCRIPTION;
                            $arModules[$dir]["MODULE_VERSION"] = $info->MODULE_VERSION;
                            $arModules[$dir]["MODULE_VERSION_DATE"] = $info->MODULE_VERSION_DATE;
                            $arModules[$dir]["MODULE_SORT"] = $info->MODULE_SORT;
                            $arModules[$dir]["MODULE_PARTNER"] = (strpos($dir, ".") !== false) ? $info->PARTNER_NAME : "";
                            $arModules[$dir]["MODULE_PARTNER_URI"] = (strpos($dir, ".") !== false) ? $info->PARTNER_URI : "";
                            $arModules[$dir]["IsInstalled"] = $info->IsInstalled();
                        }
                    }
                }
                closedir($handle);
            }
        }
        \Bitrix\Main\Type\Collection::sortByColumn(
            $arModules,
            ['MODULE_SORT' => SORT_ASC, 'MODULE_NAME' => SORT_STRING],
            '',
            null,
            true
        );
        return $arModules;
    }

    /**
     * @return array
     */
    public static function getNotImportantModulesList(){
        $modules = self::getNonImportantModulesArr();
        $allModules = self::getAllModules();

        foreach ($modules as $module => $name){
            if($onSite = $allModules[$module]) {
                $modules[$module] = array(
                    'NAME' => $onSite['MODULE_NAME'],
                    'INSTALLED' => $onSite['IsInstalled']
                );
            }else{
                unset($modules[$module]);
                continue;
            }
        }

        return $modules;
    }
}

?>
