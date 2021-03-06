<?php
/**
 * @copyright Copyright (c) 2014 Vitaliy Syrchikov
 * @link http://syrchikov.name
 */

namespace maddoger\imagecache;

use maddoger\filebehavior\FileBehavior;
use Yii;
use yii\base\InvalidConfigException;

/**
 * ImageUploadBehavior
 *
 * @author Vitaliy Syrchikov <maddoger@gmail.com>
 * @link http://syrchikov.name
 * @package maddoger\imagcache
 */
class ImageBehavior extends FileBehavior
{
    /**
     * Return path to file in attribute
     * @param $attribute string attribute name
     * @param $presetName string preset name
     * @return string|null
     * @throws InvalidConfigException when ImageCache module not found.
     */
    public function getImageUrl($attribute, $presetName)
    {
        $url = $this->owner->{$attribute};
        if (!$url) {
            return null;
        }
        return $this->getImageCache()->getUrl($url, $presetName);
    }

    /**
     * @return ImageCache
     * @throws InvalidConfigException
     */
    public function getImageCache()
    {
        $imageCache = Yii::$app->get('imageCache');
        if (!$imageCache) {
            throw new InvalidConfigException('ImageCache component must be added to app modules.');
        }
        return $imageCache;
    }

    /**
     * Delete old files
     */
    protected function deleteFileInternal()
    {
        if ($this->oldValue) {

            //Flush cache
            $this->getImageCache()->flushByUrl($this->oldValue);
            $filePath = $this->getFilePathFromUrl($this->oldValue);

            try {
                if (is_file($filePath)) {
                    unlink($filePath);
                }
            } catch (\Exception $e) {

            }
            $this->oldValue = null;
        }
    }
}