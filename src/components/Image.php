<?php
/**
 * Author: metalguardian
 * Email: metalguardian
 */

namespace metalguardian\fileProcessor\components;

use Imagine\Image\Box;
use Imagine\Image\ImageInterface;
use Imagine\Image\ImagineInterface;
use Imagine\Image\ManipulatorInterface;
use Imagine\Image\Palette\RGB;
use Imagine\Image\Point;
use Yii;
use yii\base\InvalidConfigException;
use yii\base\InvalidParamException;

/**
 * Class Image
 * @package metalguardian\fileProcessor\components
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
        foreach ((array) static::$driver as $driver) {
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
        throw new InvalidConfigException("Your system does not support any of these drivers: " . implode(',', (array) static::$driver));
    }

    /**
     * Crops an image.
     *
     * For example,
     *
     * ~~~
     * $obj->crop('path\to\image.jpg', 200, 200, [5, 5]);
     *
     * $point = new \Imagine\Image\Point(5, 5);
     * $obj->crop('path\to\image.jpg', 200, 200, $point);
     * ~~~
     *
     * @param string $filename the image file path
     * @param integer $width the crop width
     * @param integer $height the crop height
     * @param array $start the starting point. This must be an array with two elements representing `x` and `y` coordinates.
     * @return ImageInterface
     * @throws InvalidParamException if the `$start` parameter is invalid
     */
    public static function crop($filename, $width, $height, $startX = 0, $startY = 0)
    {
        return static::getImagine()
            ->open($filename)
            ->copy()
            ->crop(new Point($startX, $startY), new Box($width, $height));
    }
    /**
     * Creates a thumbnail image. The function differs from `\Imagine\Image\ImageInterface::thumbnail()` function that
     * it keeps the aspect ratio of the image.
     * @param string $filename the image file path or path alias.
     * @param integer $width the width in pixels to create the thumbnail
     * @param integer $height the height in pixels to create the thumbnail
     * @param string $mode
     * @return ImageInterface
     */
    public static function thumbnail($filename, $width, $height, $mode = ManipulatorInterface::THUMBNAIL_OUTBOUND, $filter = ImageInterface::FILTER_UNDEFINED)
    {
        return static::getImagine()
            ->open($filename)
            ->thumbnail(new Box($width, $height), $mode, $filter);
    }

    /**
     * Adds a frame around of the image. Please note that the image size will increase by `$margin` x 2.
     * @param string $filename the full path to the image file
     * @param integer $margin the frame size to add around the image
     * @param string $color the frame color
     * @param integer $alpha the alpha value of the frame.
     * @return ImageInterface
     */
    public static function frame($filename, $margin, $color, $alpha = 100)
    {
        $img = static::getImagine()->open($filename);
        $size = $img->getSize();
        $pasteTo = new Point($margin, $margin);
        $padColor = (new RGB())->color($color, $alpha);
        $box = new Box($size->getWidth() + ceil($margin * 2), $size->getHeight() + ceil($margin * 2));
        $image = static::getImagine()->create($box, $padColor);
        $image->paste($img, $pasteTo);
        return $image;
    }

    /**
     * Creates a thumbnail image. The function differs from `\Imagine\Image\ImageInterface::thumbnail()` function that
     * it keeps the aspect ratio of the image.
     * @param string $filename the image file path or path alias.
     * @param integer $width the width in pixels to create the thumbnail
     * @param integer $height the height in pixels to create the thumbnail
     * @param integer $alpha
     * @return ImageInterface
     */
    public static function canvasThumbnail($filename, $width, $height, $alpha = null)
    {
        $box = new Box($width, $height);

        $img = static::getImagine()->open($filename);
        /** @var ImageInterface $img */
        $img = $img->thumbnail($box);

        $thumb = static::getImagine()->create($box, (new RGB())->color('FFF', $alpha));

        // calculate points
        $size = $img->getSize();
        $startX = 0;
        $startY = 0;
        if ($size->getWidth() < $width) {
            $startX = ceil($width - $size->getWidth()) / 2;
        }
        if ($size->getHeight() < $height) {
            $startY = ceil($height - $size->getHeight()) / 2;
        }
        $thumb->paste($img, new Point($startX, $startY));
        return $thumb;
    }
}
