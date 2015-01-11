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
			\yii\base\Model::EVENT_AFTER_VALIDATE => 'evaluateAttributes',
		];
	}

	/**
	 * Evaluates the attribute value and assigns it to the current attributes.
	 *
	 * @param \yii\base\Event $event
	 */
	public function evaluateAttributes($event)
	{
		$value = $this->getValue($event);
		$this->owner->{$this->attribute} = $value;
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
		$file = \yii\web\UploadedFile::getInstance($this->owner, $this->attribute);
		if ($this->validateFile($file)) {

		}
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
		$this->owner->{$this->attribute} = $file;

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

		$validator->validateAttribute($this->owner, $this->attribute);

		if ($this->owner->hasErrors($this->attribute)) {
			return false;
		}

		return true;
	}
}
