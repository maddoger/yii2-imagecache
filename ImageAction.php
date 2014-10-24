<?php
/**
 * @copyright Copyright (c) 2014 Vitaliy Syrchikov
 * @link http://syrchikov.name
 */

namespace maddoger\imagecache;
use Yii;
use yii\base\Action;
use yii\helpers\FileHelper;

/**
 * ImageAction
 *
 * @author Vitaliy Syrchikov <maddoger@gmail.com>
 * @link http://syrchikov.name
 * @package maddoger\imagecache
 */
class ImageAction extends Action
{
    public function run($request)
    {
        $module = ImageCache::getInstance();


    }
}