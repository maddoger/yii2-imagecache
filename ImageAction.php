<?php
/**
 * @copyright Copyright (c) 2014 Vitaliy Syrchikov
 * @link http://syrchikov.name
 */

namespace maddoger\imagecache;
use Yii;
use yii\base\Action;
use yii\helpers\FileHelper;
use yii\web\BadRequestHttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * ImageAction
 *
 * @author Vitaliy Syrchikov <maddoger@gmail.com>
 * @link http://syrchikov.name
 * @package maddoger\imagecache
 */
class ImageAction extends Action
{
    public function run($url=null)
    {
        /**
         * @var ImageCache $imageCache
         */
        $imageCache = Yii::$app->get('imageCache');
        $cachedUrl = $url ?: Yii::$app->request->getUrl();
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

                        Yii::$app->response->format = Response::FORMAT_RAW;
                        Yii::$app->getResponse()->getHeaders()
                            ->set('Pragma', 'public')
                            ->set('Expires', '0')
                            ->set('Cache-Control', 'must-revalidate, post-check=0, pre-check=0')
                            ->set('Content-Transfer-Encoding', 'binary')
                            ->set('Content-type', FileHelper::getMimeTypeByExtension($imagePath));
                        return ($image->get($format));

                    }
                } catch (\Exception $e) {
                    throw new BadRequestHttpException();
                }

            } else {
                throw new NotFoundHttpException();
            }
        }

        throw new BadRequestHttpException('Wrong url format!');
    }
}