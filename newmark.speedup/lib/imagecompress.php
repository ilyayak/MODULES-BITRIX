<?php
namespace Newmark\Speedup;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Config\Option;
Loc::loadMessages(__FILE__);

/**
 * Class ImageCompress
 * @package Newmark\Speedup
 */
class ImageCompress{
    /**
     * @var string
     */
    private static $root = '/upload/';
    /**
     * @var string
     */
    public static $table = '';
    /**
     * @var
     */
    private static $resizedDir;

    /**
     *
     */
    public static function draw(){
        self::drawBtns();
        self::drawTable();
    }

    /**
     *
     */
    private static function drawBtns(){
        $btns = '
            <input type="submit" name="image_compress_start" value="'.Loc::GetMessage("COMPRESS_START").'" class="adm-btn-save"/>
            <input type="submit" name="image_return_start" value="'.Loc::GetMessage("RETURN_START").'"/>
        ';
        echo preg_replace("/\r\n|\n|\r/", '', $btns);

    }

    /**
     *
     */
    private static function drawTable(){

        self::tableAdd('<table id="image-list" clas="display cell-border">');
            self::tableAdd('<thead>');
                self::tableAdd('<tr>');
                    self::tableAdd('<th>'.Loc::GetMessage("IMAGE_TYPE").'</th>');
                    self::tableAdd('<th>'.Loc::GetMessage("IMAGE_NAME").'</th>');
                    self::tableAdd('<th>'.Loc::GetMessage("IMAGE_PATH").'</th>');
                    self::tableAdd('<th>'.Loc::GetMessage("IMAGE_PICTURE").'</th>');
                    self::tableAdd('<th>'.Loc::GetMessage("IMAGE_SIZE").'</th>');
                    self::tableAdd('<th>'.Loc::GetMessage("IMAGE_SIZE_BEFORE").'</th>');
                    self::tableAdd('<th>'.Loc::GetMessage("IMAGE_COMPRESS").'</th>');
                self::tableAdd('</tr>');
            self::tableAdd('</thead>');

            self::tableAdd('<tbody>');
            self::tableAdd(self::drawDataRows());
            self::tableAdd('</tbody>');
        self::tableAdd('</table>');

        echo preg_replace("/\r\n|\n|\r/", '', self::$table);
    }

    /**
     * @param $text
     */
    private static function tableAdd($text){
        self::$table .= $text;
    }

    /**
     * @return string
     */
    private static function drawDataRows(){
        $images = self::getImagesList();
        $rows = array();
        foreach ($images as $image){
            $row = '<tr>';
                $row .= '<td>'.$image['MIME'].'</td>';
                $row .= '<td>'.$image['NAME'].'</td>';
                if($image['IS_RESIZED']) {
                    $row .= '<td>
                        <a href="' . $image['ROOT_PATH'] . '" target="_blank">compressed</a>
                        <br/>
                        <a href="'.$image['RESIZE_ROOT_PATH'].'" target="_blank">original</a>
                    </td>';
                }else{
                    $row .= '<td><a href="' . $image['ROOT_PATH'] . '" target="_blank">' . $image['ROOT_PATH'] . '</a></td>';
                }

                $row .= '<td><img src="'.$image['ROOT_PATH'].'" style="max-width:150px;"/></td>';
                $row .= '<td>'.number_format(Main::formatFileSize($image['SIZE']), 2, '.', ' ').' кб.</td>';
                $row .= '<td>'.number_format(Main::formatFileSize($image['BEFORE_SIZE']), 2, '.', ' ').' кб.</td>';

                if($image['IS_RESIZED']){
                    $row .= '<td><button type="submit" name="image_return_one" value="'.$image['PATH'].'">'.Loc::getMessage('RETURN_ONE_IMG').'</button></td>';
                }else{
                    $row .= '<td><button type="submit" name="image_compress_one" value="'.$image['PATH'].'" class="adm-btn-save">'.Loc::getMessage('COMPRESS_ONE_IMG').'</button></td>';
                }

            $row .= '</tr>';

            $rows[] = $row;
        }
        return implode('',$rows);
    }

    /**
     * @return array
     */
    private static function getFolders(){
        $array = array(self::$root);

        $option = Option::get(Main::getModuleId(), "compress_folders", "");
        if($option)
            $array = preg_split("/\r\n|\n|\r/", $option);

        return $array;
    }

    /**
     * @param $mask
     * @param $imagesArr
     * @param $noResized
     */
    private static function findImages($mask, &$imagesArr, $noResized){
        //finding all pics in dir and sub dir
        $cdir = self::rglob($mask, GLOB_BRACE);
        foreach ($cdir as $value){
            $image = array();
            $pathInfo = pathinfo($value);
            $info = getimagesize($value);

            $image['NAME'] = $pathInfo['basename'];
            $image['PATH'] = $value;
            $image['ROOT_PATH'] =  explode($_SERVER['DOCUMENT_ROOT'], $image['PATH'])[1];
            $image['DIR'] = $pathInfo['dirname'];
            $image['EXT'] = $pathInfo['extension'];
            $image['MIME'] = $info['mime'];
            $image['SIZE'] = filesize($value);


            $image['RESIZE_PATH'] = self::$resizedDir.$image['ROOT_PATH'];
            $image['RESIZE_ROOT_PATH'] = explode($_SERVER['DOCUMENT_ROOT'], $image['RESIZE_PATH'])[1];

            $image['IS_RESIZED'] = file_exists($image['RESIZE_PATH']);

            $image['BEFORE_SIZE'] = $image['IS_RESIZED'] ? filesize($image['RESIZE_PATH']) : 0;


            if($image['IS_RESIZED'] && $noResized)
                continue;

            $imagesArr[] = $image;
        }
    }

    /**
     * @param bool $noResized
     * @param bool $onePic
     * @return array
     */
    private static function getImagesList($noResized = false, $onePic = false)
    {
        $imagesArr = array();
        self::$resizedDir = $_SERVER['DOCUMENT_ROOT'] . '/bitrix/images/' . Main::getModuleId() . '/image_compress_resized_images';

        //mask and path
        if ($onePic){
            $mask = $onePic;
            self::findImages($mask, $imagesArr, $noResized);
        }else {
            $folders = self::getFolders();
            foreach ($folders as $folder){
                $mask = $_SERVER['DOCUMENT_ROOT'] . $folder . "*.{jpg,png}";//эта маска - не регулярка!
                self::findImages($mask, $imagesArr, $noResized);
            }

        }

        return $imagesArr;

    }

    /**
     * @param $pattern
     * @param int $flags
     * @return array
     */
    private static function rglob($pattern, $flags = 0) {
        $files = glob($pattern, $flags);
        foreach (glob(dirname($pattern).'/*', GLOB_ONLYDIR|GLOB_NOSORT) as $dir){

            if(strpos($dir, Main::getModuleId()) !== false)
                continue;

            $files = array_merge($files, self::rglob($dir.'/'.basename($pattern), $flags));
        }
        return $files;
    }

    /**
     *
     */
    public static function compressAll(){
        $images = self::getImagesList(true);
        foreach ($images as $image){
            self::compressImage($image['PATH'], $image['RESIZE_PATH'], 50, $image);
        }
    }

    /**
     *
     */
    public static function returnAll(){
        $images = self::getImagesList();
        foreach ($images as $image){
            self::moveImage($image['RESIZE_PATH'], $image['PATH']);
        }
    }

    /**
     * @param $path
     */
    public static function compressOne($path){
        $image = reset(self::getImagesList(true, $path));
        self::compressImage($image['PATH'], $image['RESIZE_PATH'], 50, $image);
    }

    /**
     * @param $path
     */
    public static function returnOne($path){
        $image = reset(self::getImagesList(false, $path));
        self::moveImage($image['RESIZE_PATH'], $image['PATH']);
    }

    /**
     * @param $source_url
     * @param $destination_url
     * @param $quality
     * @param $image
     * @return bool
     */
    public static function compressImage($source_url, $destination_url, $quality, $image){
        if( $destination_url == NULL || $destination_url == "" ) $destination_url = $source_url;

        if ($image['MIME'] == 'image/jpeg' || $image['MIME'] == 'image/jpg')
        {
            $tmpImg = imagecreatefromjpeg($source_url);
            self::moveImage($source_url, $destination_url);

            imagejpeg($tmpImg, $source_url, $quality);
            //Free up memory
            imagedestroy($tmpImg);
        }
        elseif ($image['MIME'] == 'image/png')
        {
            $tmpImg = imagecreatefrompng($source_url);
            self::moveImage($source_url, $destination_url);


            imageAlphaBlending($tmpImg, true);
            imageSaveAlpha($tmpImg, true);

            /* chang to png quality */
            $png_quality = 9 - round(($quality / 100 ) * 9 );
            imagePng($tmpImg, $source_url, $png_quality);//Compression level: from 0 (no compression) to 9(full compression).
            //Free up memory
            imagedestroy($tmpImg);
        }else
            return FALSE;

        if(filesize($source_url) > filesize($destination_url))
            copy($destination_url, $source_url);


        return $destination_url;
    }

    /**
     * @param $source_url
     * @param $destination_url
     */
    private static function moveImage($source_url, $destination_url){
        $fileName = pathinfo($source_url, PATHINFO_BASENAME);

        if(!file_exists(dirname($destination_url)))
            mkdir(dirname($destination_url), 0700, true);


        rename($source_url, $destination_url);
    }

}