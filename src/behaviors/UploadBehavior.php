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
	const EVENT_AFTER_FILE_SAVE = 'afterFileSave';
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
	public $validator = [];

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

		$this->addValidator();
	}

	/**
	 * @inheritdoc
	 */
	public function detach()
	{
		parent::detach();

		$this->removeValidator();
	}

	/**
	 * @param \yii\base\Event $event
	 */
	public function beforeValidate($event)
	{
		\yii\helpers\VarDumper::dump($this->owner->{$this->attribute});
		exit();
		$this->owner->{$this->attribute} = \yii\web\UploadedFile::getInstance($this->owner, $this->attribute);
	}

	/**
	 * Evaluates the attribute value and assigns it to the current attributes.
	 *
	 * @param \yii\base\Event $event
	 */
	public function afterValidate($event)
	{
		if (!$this->owner->hasErrors()) {
			$value = $this->getValue($event);
			$this->owner->{$this->attribute} = $value;
		}
	}

	/**
	 * Returns the value of the current attributes.
	 * This method is called by [[evaluateAttributes()]]. Its return value will be assigned
	 * to the attributes corresponding to the triggering event.
	 *
	 * @param \yii\base\Event $event the event that triggers the current attribute updating.
	 *
	 * @return mixed the attribute value
	 */
	protected function getValue($event)
	{
		$file = $this->owner->{$this->attribute};
		if (!$this->getValidator()->isEmpty($file)) {

			$oldFileId = null;
			\metalguardian\fileProcessor\helpers\FPM::deleteFile($oldFileId);

			$fileId = $this->saveUploadedFile($file);

			return $fileId;
		}

		return null;
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

	/**
	 *
	 */
	protected function removeValidator()
	{
		$this->owner->getValidators()->offsetUnset($this->getValidatorIndex());
	}

	/**
	 * @return integer
	 */
	public function getValidatorIndex()
	{
		$offset = self::VALIDATOR_OFFSET;
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

	/**
	 * @param \yii\web\UploadedFile $file
	 *
	 * @return int
	 */
	public function saveUploadedFile(\yii\web\UploadedFile $file)
	{
		$id = $this->saveData($file);

		$directory = \metalguardian\fileProcessor\helpers\FPM::getUploadDirectory()
			. DIRECTORY_SEPARATOR
			. floor($id / \metalguardian\fileProcessor\helpers\FPM::getFilesPerDirectory());

		\yii\helpers\FileHelper::createDirectory($directory, 0777, true);

		$realName = $file->getBaseName();
		$ext = $file->getExtension();
		$ext = $ext ? '.' . $ext : null;
		$fileName = $directory . DIRECTORY_SEPARATOR . $id . '-' . $realName . $ext;

		$file->saveAs($fileName);

		$this->owner->trigger(static::EVENT_AFTER_FILE_SAVE);

		return $id;
	}

	/**
	 * @param \yii\web\UploadedFile $file
	 *
	 * @return int
	 */
	public function saveData(\yii\web\UploadedFile $file)
	{
		$ext = $file->getExtension();
		$baseName = $file->getBaseName();

		$model = new \metalguardian\fileProcessor\models\File();
		$model->extension = $ext;
		$model->real_name = $baseName;
		$model->save(false);

		return $model->id;
	}
}
