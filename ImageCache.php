<?php
/**
 * @copyright Copyright (c) 2014 Vitaliy Syrchikov
 * @link http://syrchikov.name
 */

namespace maddoger\imagecache;

use Yii;
use yii\base\Component;
use yii\base\ErrorException;
use yii\base\InvalidParamException;
use yii\helpers\ArrayHelper;
use yii\helpers\FileHelper;

/**
 * ImageCache
 *
 * @author Vitaliy Syrchikov <maddoger@gmail.com>
 * @link http://syrchikov.name
 * @package maddoger\imagecache
 */
class ImageCache extends Component
{
    /**
     * Path to public static files
     * @var string
     */
    public $staticPath = '@static';

    /**
     * URL to static path
     * @var string
     */
    public $staticUrl = '@staticUrl';

    /**
     * Path to cache folder
     * @var string
     */
    public $cachePath = '@static/ic';

    /**
     * URL to cache path
     * @var string
     */
    public $cacheUrl = '@staticUrl/ic';

    /**
     * @var string
     */
    public $imageClass = 'maddoger\imagecache\Image';

    /**
     * @var bool saving file to cache or generate only
     */
    public $actionSavesFile = true;

    /**
     * @var bool Is need to generate file when getUrl function will be called?
     */
    public $generateWithUrl = true;

    /**
     * @var array known presets
     */
    public $presets = [];

    /**
     * @inheritdoc
     */
    public function init()
    {   
        $this->staticPath = Yii::getAlias($this->staticPath);
        $this->staticUrl = Yii::getAlias($this->staticUrl); 
        
        $this->cachePath = Yii::getAlias($this->cachePath);
        $this->cacheUrl = Yii::getAlias($this->cacheUrl);
    }

    /**
     * Process image and returns it
     * @param $imagePath
     * @param $presetName
     * @return Image
     */
    public function getImage($imagePath, $presetName)
    {
        if (!isset($this->presets[$presetName])) {
            throw new InvalidParamException('Preset "'.$presetName.'" not exists.');
        }

        $preset = $this->presets[$presetName];
        if (!is_array($preset)) {
            $preset = [$preset];
        }

        $image = Yii::createObject($this->imageClass);
        $image->open($imagePath);
        $image->process($preset);
        return $image;
    }

    /**
     * @param $imagePath
     * @param $presetName
     * @return string
     * @throws ErrorException
     */
    public function getUrlByPath($imagePath, $presetName)
    {
        $imagePath = FileHelper::normalizePath(Yii::getAlias($imagePath));

        $cacheImageUrl = str_replace(
            $this->staticPath,
            $this->cacheUrl.'/'.$presetName,
            $imagePath
        );

        //Need to generate file
        if ($this->generateWithUrl) {

            //Need to save file to cache
            $cachePath = str_replace(
                $this->cacheUrl,
                $this->cachePath,
                $cacheImageUrl
            );

            if ((!file_exists($cachePath) || filemtime($cachePath)<filemtime($imagePath)) && file_exists($imagePath)) {
                $image = $this->getImage($imagePath, $presetName);
                if (!FileHelper::createDirectory(dirname($cachePath))) {
                    throw new ErrorException('Directory creation failed.');
                }
                //unlink($cachePath);
                $image->save(
                    $cachePath,
                    ArrayHelper::remove($this->presets[$presetName], 'save', [])
                );
            }
        }

        return $cacheImageUrl;
    }

    /**
     * @param $imageUrl
     * @param $presetName
     * @return string
     * @throws ErrorException
     */
    public function getUrl($imageUrl, $presetName)
    {
        $cacheImageUrl = str_replace(
            $this->staticUrl,
            $this->cacheUrl.'/'.$presetName,
            $imageUrl
        );

        if ($this->generateWithUrl) {
            $imagePath = str_replace(
                $this->staticUrl,
                $this->staticPath,
                Yii::getAlias($imageUrl)
            );

            return $this->getUrlByPath($imagePath, $presetName);
        } else {
            return $cacheImageUrl;
        }
    }
}