<?php
/**
 * @copyright Copyright (c) 2014 Vitaliy Syrchikov
 * @link http://syrchikov.name
 */

namespace maddoger\imagecache;
use Yii;
use yii\base\Component;

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
     * Path to protected static files
     * @var string
     */
    public $staticProtectedPath = '@staticProtected';

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
     * @var bool saving file to cache or generate only
     */
    public $saveFile = true;

    public $presets = [

    ];

    public function getRawImageByPreset($preset, $image_path)
    {

    }
}