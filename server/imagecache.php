<?php

use yii\helpers\FileHelper;
use yii\web\Request;
use maddoger\imagecache\ImageCache;

require(__DIR__ . '/../../vendor/autoload.php');
require(__DIR__ . '/../../vendor/yiisoft/yii2/Yii.php');
require(__DIR__ . '/../../common/config/bootstrap.php');

$config = yii\helpers\ArrayHelper::merge(
    require(__DIR__ . '/../../common/config/main.php'),
    require(__DIR__ . '/../../common/config/main-local.php')
);

$request = new Request();
$request->init();

$imageCacheConfig = $config['components']['imageCache'];
/**
 * @var maddoger\imagecache\ImageCache $imageCache
 */
$imageCache = Yii::createObject($imageCacheConfig);

$cachedUrl = $request->getUrl();

$preg = '/^'.preg_quote(Yii::getAlias($imageCache->cacheUrl), '/').'\/(.*?)\/(.*?)\.(.*?)$/';

if (preg_match($preg, $cachedUrl, $matches)) {

    $presetName = $matches[1];
    $imagePath = Yii::getAlias($imageCache->staticPath.DIRECTORY_SEPARATOR.$matches[2].'.'.$matches[3]);
    $format = strtolower($matches[3]);
    if (file_exists($imagePath)) {
        try {
            $image = $imageCache->getImage($imagePath, $presetName);
            if ($image && $image->isValid()) {

                if ($imageCache->actionSavesFile) {
                    $cachedPath = Yii::getAlias($imageCache->cachePath.DIRECTORY_SEPARATOR.$presetName.DIRECTORY_SEPARATOR.$matches[2].'.'.$matches[3]);
                    //var_dump($cachedPath);
                    FileHelper::createDirectory(dirname($cachedPath));
                    $image->save($cachedPath);
                }

                header('Content-type: '. FileHelper::getMimeTypeByExtension($imagePath), true, 200);
                exit($image->get($format));
            }
        } catch (Exception $e) {
            header('HTTP/1.0 400 Bad Request');
            exit($e);
        }

    } else {
        header('HTTP/1.0 404 Not Found');
        exit('Not Found');
    }
}

header('HTTP/1.0 400 Bad Request');
exit('Bad Request');