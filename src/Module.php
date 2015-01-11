<?php

namespace metalguardian\fileProcessor;

class Module extends \yii\base\Module
{
    public $controllerNamespace = 'metalguardian\fileProcessor\controllers';

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
    public static $host = '@web/';

    /**
     * Do not change this param when you already reach your files fer dir limit
     * If changed - generated path to old files would be broken
     * and you have to move files to their right locations
     *
     * @var integer max images count per dir.
     */
    public static $filesPerDir = 5000;

    /**
     * Base path
     *
     * @var bool|string
     */
    public static $baseUploadDirectory = '@webroot/';

    /**
     * @var string original files base dir
     */
    public static $originalDirectory = 'uploads';

    /**
     * @var string cached images base dir
     */
    public $thumbnailDirectory = 'uploads/thumb';

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

    public function init()
    {
        parent::init();

        $this->registerTranslations();
    }

    public function registerTranslations()
    {
        \Yii::$app->i18n->translations['modules/fileProcessor/*'] = [
            'class' => 'yii\i18n\PhpMessageSource',
            'sourceLanguage' => 'en-US',
            'basePath' => '@metalguardian/fileProcessor/messages',
            'fileMap' => [
                'modules/fileProcessor/model' => 'model.php',
            ],
        ];
    }

    public static function t($category, $message, $params = [], $language = null)
    {
        return \Yii::t('modules/fileProcessor/' . $category, $message, $params, $language);
    }

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

    public static function getBaseUploadDirectory()
    {
        return \Yii::getAlias(self::$baseUploadDirectory);
    }

    public static function getUploadDirectory()
    {
        return self::getBaseUploadDirectory() . self::$originalDirectory;
    }

    public static function getFilesPerDirectory()
    {
        return self::$filesPerDir;
    }
}
