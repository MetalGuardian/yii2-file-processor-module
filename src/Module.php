<?php

namespace metalguardian\fileProcessor;

/**
 * Class Module
 *
 * @package metalguardian\fileProcessor
 */
class Module extends \yii\base\Module implements \yii\base\BootstrapInterface
{

    public $controllerNamespace = 'metalguardian\fileProcessor\controllers';

    /**
     * Default table name for files
     *
     * @var string
     */
    public $tableName = '{{%fpm_file}}';

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
    public $host = '@web/';

    /**
     * Do not change this param when you already reach your files fer dir limit
     * If changed - generated path to old files would be broken
     * and you have to move files to their right locations
     *
     * @var integer max images count per dir.
     */
    public $filesPerDir = 5000;

    /**
     * Base path
     *
     * @var bool|string
     */
    public $baseUploadDirectory = '@webroot/';

    /**
     * @var string original files base dir
     */
    public $originalDirectory = 'uploads';

    /**
     * @var string cached images base dir
     */
    public $thumbnailDirectory = 'uploads/thumb';

    public $originalDirectoryNameTemplate = '{id}';

    public $originalFileNameTemplate = '{id}-{baseName}.{extension}';

    public $thumbnailDirectoryNameTemplate = '{id}/{module}_{size}';

    public $thumbnailFileNameTemplate = '{id}-{baseName}.{extension}';

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
     *                    'action' => 'thumbnail', // thumbnail|adaptiveThumbnail
     *                ),
     *                'medium' => array(
     *                    'width' => '500',
     *                    'height' => '500',
     *                    'quality' => 80,
     *                    'action' => 'thumbnail', // thumbnail|adaptiveThumbnail
     *                ),
     *            ),
     *        )
     * )
     */
    public $imageSections = [];

    public $symLink = false;

    public function init()
    {
        parent::init();

        \Yii::setAlias('@metalguardian', __DIR__);

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
                'modules/fileProcessor/exception' => 'exception.php',
            ],
        ];
    }

    /**
     * @param $category
     * @param $message
     * @param array $params
     * @param null $language
     *
     * @return string
     */
    public static function t($category, $message, $params = [], $language = null)
    {
        return \Yii::t('modules/fileProcessor/' . $category, $message, $params, $language);
    }

    /**
     * @inheritdoc
     */
    public function bootstrap($app)
    {
        $app->getUrlManager()->addRules([
            'GET ' . $this->thumbnailDirectory . '/<sub:\d+>/<module:\w+>_<size:\w+>/<id:\d+>-<baseName>.<extension>' => $this->id . '/file/process',
        ], false);
    }
}
