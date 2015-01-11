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

	/**
	 * @inheritdoc
	 */
	public function events()
	{
		return [
			\yii\base\Model::EVENT_AFTER_VALIDATE => 'afterValidate',
		];
	}

	/**
	 * Evaluates the attribute value and assigns it to the current attributes.
	 *
	 * @param \yii\base\Event $event
	 */
	public function afterValidate($event)
	{
		$file = \yii\web\UploadedFile::getInstance($this->owner, $this->attribute);
		if ($this->validateFile($file)) {
			$value = $this->getValue($file);
			$this->owner->{$this->attribute} = $value;
		}
	}

	protected function getValue($file)
	{
		return 1;
		return null;
	}

	/**
	 * Checks if given slug value is unique.
	 *
	 * @param \yii\web\UploadedFile $file slug value
	 *
	 * @return boolean whether slug is unique.
	 */
	private function validateFile(\yii\web\UploadedFile $file = null)
	{
		unset($this->validator['maxFiles']);
		/* @var $validator \yii\validators\FileValidator|\yii\validators\ImageValidator */
		$validator = \Yii::createObject(
			array_merge(
				[
					'class' => $this->image ? \yii\validators\ImageValidator::className()
						: \yii\validators\FileValidator::className(),
				],
				$this->validator
			)
		);

		$model = clone $this->owner;
		$model->clearErrors();
		$validator->validateAttribute($model, $this->attribute);
		if ($model->hasErrors()) {
			$this->owner->addErrors($model->getErrors());
			return false;
		}

		return true;
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
