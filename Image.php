<?php
/**
 * @copyright Copyright (c) 2014 Vitaliy Syrchikov
 * @link http://syrchikov.name
 */

namespace maddoger\imagecache;

use Yii;
use yii\base\InvalidConfigException;
use yii\base\InvalidParamException;
use yii\base\UnknownMethodException;
use yii\helpers\ArrayHelper;

use Imagine\Image\ImagineInterface;
use Imagine\Image\ImageInterface;
use Imagine\Image\Box;
use Imagine\Image\ManipulatorInterface;
use Imagine\Image\Point;
use Imagine\Image\Color;

/**
 * Image
 *
 * @author Vitaliy Syrchikov <maddoger@gmail.com>
 * @link http://syrchikov.name
 * @package maddoger/yii2-imagecache
 */
class Image
{
    /**
     * GD2 driver definition for Imagine implementation using the GD library.
     */
    const DRIVER_GD2 = 'gd2';
    /**
     * imagick driver definition.
     */
    const DRIVER_IMAGICK = 'imagick';
    /**
     * gmagick driver definition.
     */
    const DRIVER_GMAGICK = 'gmagick';

    /**
     * @var array|string the driver to use. This can be either a single driver name or an array of driver names.
     * If the latter, the first available driver will be used.
     */
    public static $driver = [self::DRIVER_GMAGICK, self::DRIVER_IMAGICK, self::DRIVER_GD2];

    /**
     * @var ImagineInterface instance.
     */
    private static $_imagine;

    /**
     * @var ImageInterface
     */
    public $image;

    /**
     * Returns the `Imagine` object that supports various image manipulations.
     * @return ImagineInterface the `Imagine` object
     */
    public static function getImagine()
    {
        if (self::$_imagine === null) {
            self::$_imagine = static::createImagine();
        }

        return self::$_imagine;
    }

    /**
     * @param ImagineInterface $imagine the `Imagine` object.
     */
    public static function setImagine($imagine)
    {
        self::$_imagine = $imagine;
    }

    /**
     * Creates an `Imagine` object based on the specified [[driver]].
     * @return ImagineInterface the new `Imagine` object
     * @throws InvalidConfigException if [[driver]] is unknown or the system doesn't support any [[driver]].
     */
    protected static function createImagine()
    {
        foreach ((array)static::$driver as $driver) {
            switch ($driver) {
                case self::DRIVER_GMAGICK:
                    if (class_exists('Gmagick', false)) {
                        return new \Imagine\Gmagick\Imagine();
                    }
                    break;
                case self::DRIVER_IMAGICK:
                    if (class_exists('Imagick', false)) {
                        return new \Imagine\Imagick\Imagine();
                    }
                    break;
                case self::DRIVER_GD2:
                    if (function_exists('gd_info')) {
                        return new \Imagine\Gd\Imagine();
                    }
                    break;
                default:
                    throw new InvalidConfigException("Unknown driver: $driver");
            }
        }
        throw new InvalidConfigException("Your system does not support any of these drivers: " . implode(',', (array)static::$driver));
    }

    /**
     * @param $filePath
     */
    public function __construct($filePath = null)
    {
        if ($filePath) {
            $this->open($filePath);
        }
    }

    /**
     * @param $filePath
     * @return $this
     */
    public function open($filePath)
    {
        if ($this->image) {

            $this->image = null;
        }
        $this->image = static::getImagine()->open($filePath);
        return $this;
    }

    /**
     * @param $functions
     * @return $this
     */
    public function process($functions)
    {
        if (!$this->image) {
            return $this;
        }
        foreach ($functions as $func => $args) {

            if ($args instanceof \Closure) {
                //User function
                call_user_func_array($args, [$this]);

            } elseif (method_exists($this, $func)) {
                //Image method
                call_user_func_array([$this, $func], $args);

            } /*elseif (method_exists($this->image, $func)) {
                //ImageInterface method
                call_user_func_array([$this->image, $func], $args);
            }*/ else {
                throw new UnknownMethodException($func);
            }
        }
        return $this;
    }

    /**
     * @return bool
     */
    public function isValid()
    {
        return ($this->image !== null);
    }

    /**
     * Show image to browser
     * @param $format
     * @param array $options
     * @return $this
     */
    public function get($format, $options = [])
    {
        if ($this->image) {
            return $this->image->get($format, $options);
        }
        return null;
    }

    /**
     * Save image to file
     * @param $filePath
     * @param array $options
     * @return $this
     */
    public function save($filePath, $options = [])
    {
        if ($this->image) {
            $this->image->save($filePath, $options);
        }
        return $this;
    }

    /**
     * Crops an image.
     *
     * ~~~
     *
     * @param integer $width the crop width
     * @param integer $height the crop height
     * @param array $start the starting point. This must be an array with two elements representing `x` and `y` coordinates.
     * @return ImageInterface
     * @throws InvalidParamException if the `$start` parameter is invalid
     */
    public function crop($width, $height, array $start = [0, 0])
    {
        if (!isset($start[0], $start[1])) {
            throw new InvalidParamException('$start must be an array of two elements.');
        }

        $this->image->crop(new Point($start[0], $start[1]), new Box($width, $height));

        return $this;
    }

    /**
     * Resize an image.
     *
     * @param integer $width the new width
     * @param integer $height the new height
     * @param string $filter
     * @return static
     * @throws InvalidParamException if the `$start` parameter is invalid
     */
    public function resize($width, $height, $filter = ImageInterface::FILTER_UNDEFINED)
    {
        $this->image->resize(new Box($width, $height), $filter);

        return $this;
    }

    /**
     * Creates a thumbnail image.
     * @param integer $width the width in pixels to create the thumbnail
     * @param integer $height the height in pixels to create the thumbnail
     * @param string $mode
     * @return static
     */
    public function thumbnail($width, $height, $mode = ManipulatorInterface::THUMBNAIL_INSET)
    {
        $box = new Box($width, $height);
        $this->image = $this->image->thumbnail($box, $mode);
        return $this;
    }


    /**
     * Fit image to area
     * @param integer $width the width in pixels to create the thumbnail
     * @param integer $height the height in pixels to create the thumbnail
     * @param string $mode
     * @return static
     */
    public function fit($width, $height, $mode = ManipulatorInterface::THUMBNAIL_INSET)
    {
        $box = new Box($width, $height);

        $this->image = $this->image->thumbnail($box, $mode);

        // create empty image to preserve aspect ratio of thumbnail
        $thumb = static::getImagine()->create($box, new Color('FFF', 100));

        // calculate points
        $size = $this->image->getSize();

        $startX = 0;
        $startY = 0;
        if ($size->getWidth() < $width) {
            $startX = ceil($width - $size->getWidth()) / 2;
        }
        if ($size->getHeight() < $height) {
            $startY = ceil($height - $size->getHeight()) / 2;
        }

        $thumb->paste($this->image, new Point($startX, $startY));
        $this->image = $thumb;

        return $this;
    }

    /**
     * Adds a watermark to an existing image.
     * @param string $watermarkFilename the file path or path alias of the watermark image.
     * @param array $start the starting point. This must be an array with two elements representing `x` and `y` coordinates.
     * @return static
     * @throws InvalidParamException if `$start` is invalid
     */
    public function watermark($watermarkFilename, array $start = [0, 0])
    {
        if (!isset($start[0], $start[1])) {
            throw new InvalidParamException('$start must be an array of two elements.');
        }

        $watermark = static::getImagine()->open(Yii::getAlias($watermarkFilename));
        $this->image->paste($watermark, new Point($start[0], $start[1]));

        return $this;
    }

    /**
     * Draws a text string on an existing image.
     * @param string $text the text to write to the image
     * @param string $fontFile the file path or path alias
     * @param array $start the starting position of the text. This must be an array with two elements representing `x` and `y` coordinates.
     * @param array $fontOptions the font options. The following options may be specified:
     *
     * - color: The font color. Defaults to "fff".
     * - size: The font size. Defaults to 12.
     * - angle: The angle to use to write the text. Defaults to 0.
     *
     * @return static
     * @throws InvalidParamException if `$fontOptions` is invalid
     */
    public function text($text, $fontFile, array $start = [0, 0], array $fontOptions = [])
    {
        if (!isset($start[0], $start[1])) {
            throw new InvalidParamException('$start must be an array of two elements.');
        }

        $fontSize = ArrayHelper::getValue($fontOptions, 'size', 12);
        $fontColor = ArrayHelper::getValue($fontOptions, 'color', 'fff');
        $fontAngle = ArrayHelper::getValue($fontOptions, 'angle', 0);

        $font = static::getImagine()->font(Yii::getAlias($fontFile), $fontSize, new Color($fontColor));

        $this->image->draw()->text($text, $font, new Point($start[0], $start[1]), $fontAngle);

        return $this;
    }

    /**
     * Adds a frame around of the image. Please note that the image size will increase by `$margin` x 2.
     * @param integer $margin the frame size to add around the image
     * @param string $color the frame color
     * @param integer $alpha the alpha value of the frame.
     * @return static
     */
    public function frame($margin = 20, $color = '666', $alpha = 100)
    {
        $size = $this->image->getSize();

        $pasteTo = new Point($margin, $margin);
        $padColor = new Color($color, $alpha);

        $box = new Box($size->getWidth() + ceil($margin * 2), $size->getHeight() + ceil($margin * 2));

        $image = static::getImagine()->create($box, $padColor);

        $image->paste($this->image, $pasteTo);

        return $this;
    }
}