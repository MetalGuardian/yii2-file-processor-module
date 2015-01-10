<?php

namespace metalguardian\fileProcessor;

class Module extends \yii\base\Module
{
    public $controllerNamespace = 'metalguardian\fileProcessor\controllers';

    public function init()
    {
        parent::init();

        // custom initialization code goes here
    }

    /**
     * Default table name for files
     *
     * @var string
     */
    protected static $tableName = '{{%fpm_file}}';

    /**
     * Default table name for table for related files
     *
     * @var string
     */
    public $relatedTableName = '{{%fpm_file_related}}';

    /**
     * If array - files will be divided between hosts by their ids
     *
     * @var string|array host for the images.
     */
    public $host = '/';

    /**
     * Do not change this param when you already reach your files fer dir limit
     * If changed - generated path to old files would be broken
     * and you have to move files to their right locations
     *
     * @var integer max images count per dir.
     */
    public $filesPerDir = 5000;

    /**
     * Base path. If non default application structure
     *
     * @var bool|string
     */
    public $baseDir = false;

    /**
     * @var string original files base dir
     */
    public $originalBaseDir = 'uploads';

    /**
     * @var string cached images base dir
     */
    public $cachedImagesBaseDir = 'uploads/thumb';

    /**
     * @var array all project images definition.
     *
     * Example:
     * array(
     *        'user' => array(
     *            'avatar' => array(
     *                'small' => array(
     *                    'width' => '151',
     *                    'height' => '157',
     *                    'quality' => 80,
     *                    'do' => 'resize', // resize|adaptiveResize
     *                ),
     *                'medium' => array(
     *                    'width' => '500',
     *                    'height' => '500',
     *                    'quality' => 80,
     *                    'do' => 'resize', // resize|adaptiveResize
     *                ),
     *            ),
     *        )
     * )
     */
    public $imageSections = array();

    /**
     * Get host for the image with $id
     *
     * @param null $id image id
     *
     * @return string
     */
    public function getHost($id = null)
    {
        $host = '/';
        if (is_array($this->host)) {
            $count = count($this->host);
            $host = $this->host[$id % $count];
        } else {
            $host = $this->host;
        }
        return $host;
    }

    /**
     * @return string
     */
    public static function getTableName()
    {
        return self::$tableName;
    }
}
