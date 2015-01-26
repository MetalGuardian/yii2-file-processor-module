<?php
/**
 * Author: metal
 * Email: metal
 */

namespace metalguardian\fileProcessor\behaviors;

/**
 * Class UploadBehavior
 *
 * @package metalguardian\fileProcessor\behaviors
 *
 * @property \yii\base\Model $owner
 */
class UploadBehavior extends \yii\base\Behavior
{
    const VALIDATOR_OFFSET = 100;

    /**
     * @var string the attribute that will receive the fileId value
     */
    public $attribute = 'file_id';

    /**
     * @var array configuration for file validator. Parameter 'class' may be omitted - by default
     * [[FileValidator]] will be used (or [[ImageValidator]] if image parameter is true).
     * @see FileValidator
     * @see ImageValidator
     */
    public $validator = [
        'extensions' => []
    ];

    /**
     * @var bool use [[ImageValidator]] instead of [[FileValidator]]
     */
    public $image = false;

    public $required = true;

    protected $validatorIndex;

    /**
     * @inheritdoc
     */
    public function events()
    {
        return [
            \yii\base\Model::EVENT_BEFORE_VALIDATE => 'beforeValidate',
            \yii\base\Model::EVENT_AFTER_VALIDATE => 'afterValidate',
        ];
    }

    /**
     * @inheritdoc
     */
    public function attach($owner)
    {
        parent::attach($owner);
        //$this->addValidator();
    }

    /**
     * @inheritdoc
     */
    public function detach()
    {
        parent::detach();
        //$this->removeValidator();
    }

    /**
     * Evaluates the attribute value and assigns it to the current attributes.
     *
     * @param \yii\base\Event $event
     */
    public function beforeValidate($event)
    {
        //$this->removeValidator();
    }

    /**
     * Validate file, and delete old file
     *
     * @param \yii\base\Event $event
     *
     * @return bool
     */
    public function afterValidate($event)
    {
        $file = \yii\web\UploadedFile::getInstance($this->owner, $this->attribute);

        if (!$file && !$this->required) {
            return false;
        }

        $oldFileId = $this->owner->{$this->attribute};
        if ($this->validateFile($file)) {
            $this->owner->{$this->attribute} = $this->getValue($file);

            \metalguardian\fileProcessor\helpers\FPM::deleteFile($oldFileId);

            return true;
        }

        return false;
    }

    /**
     *
     * @param $file
     *
     * @return int
     */
    protected function getValue($file)
    {
        $fileId = \metalguardian\fileProcessor\helpers\FPM::transfer()->saveUploadedFile($file);

        return $fileId;
    }

    /**
     * Checks if given slug value is unique.
     *
     * @param \yii\web\UploadedFile $file slug value
     *
     * @return boolean whether slug is unique.
     */
    protected function validateFile(\yii\web\UploadedFile $file = null)
    {
        unset($this->validator['maxFiles']);
        /* @var $validator \yii\validators\FileValidator|\yii\validators\ImageValidator */
        $validator = \Yii::createObject(
            array_merge(
                [
                    'class' => $this->image ? \yii\validators\ImageValidator::className()
                        : \yii\validators\FileValidator::className(),
                    'attributes' => [$this->attribute],
                    'skipOnEmpty' => $this->required ? false : true,
                ],
                $this->validator
            )
        );

        $model = clone $this->owner;
        $model->clearErrors();
        $model->{$this->attribute} = $file;
        $validator->validateAttribute($model, $this->attribute);
        if ($model->hasErrors()) {
            $this->owner->addErrors($model->getErrors());
            return false;
        }

        return true;
    }

    /**
     * @throws \yii\base\InvalidConfigException
     */
    protected function addValidator()
    {
        unset($this->validator['maxFiles']);

        /* @var $validator \yii\validators\FileValidator|\yii\validators\ImageValidator */
        $validator = \Yii::createObject(
            array_merge(
                [
                    'class' => $this->image ? \yii\validators\ImageValidator::className()
                        : \yii\validators\FileValidator::className(),
                    'attributes' => [$this->attribute],
                    'skipOnEmpty' => $this->required ? false : true,
                ],
                $this->validator
            )
        );

        $this->owner->getValidators()->offsetSet($this->getValidatorIndex(), $validator);
    }

    protected function removeValidator()
    {
        if ($this->owner->getValidators()->offsetExists($this->getValidatorIndex())) {
            // TODO: add checking if it is a current validator
            $this->owner->getValidators()->offsetUnset($this->getValidatorIndex());
        }
    }

    /**
     * @return integer
     */
    public function getValidatorIndex()
    {
        $offset = static::VALIDATOR_OFFSET;
        while (!$this->validatorIndex) {
            if (!$this->owner->getValidators()->offsetExists($offset)) {
                $this->validatorIndex = $offset;
            }
            $offset++;
        }

        return $this->validatorIndex;
    }

    /**
     * @return \yii\validators\FileValidator|\yii\validators\ImageValidator
     */
    public function getValidator()
    {
        return $this->owner->getValidators()->offsetGet($this->getValidatorIndex());
    }
}
