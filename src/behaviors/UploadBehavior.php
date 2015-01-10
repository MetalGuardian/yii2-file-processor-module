<?php
/**
 * Author: metal
 * Email: metal
 */

namespace metalguardian\fileProcessor\behaviors;

use yii\helpers\VarDumper;

/**
 * Class UploadBehavior
 *
 * @package metalguardian\fileProcessor\behaviors
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
	public $validator = [
		'extensions' => ['xml', 'jpg'],
	];

	/**
	 * @var bool use [[ImageValidator]] instead of [[FileValidator]]
	 */
	public $image = false;

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

	public function attach($owner)
	{
		parent::attach($owner);

		$this->addValidator();
	}

	public function detach()
	{
		parent::detach();

		$this->removeValidator();
	}

	public function beforeValidate($event)
	{
		$this->owner->{$this->attribute} = \yii\web\UploadedFile::getInstance($this->owner, $this->attribute);
	}

	/**
	 * Evaluates the attribute value and assigns it to the current attributes.
	 *
	 * @param \yii\base\Event $event
	 */
	public function afterValidate($event)
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
		return null;
	}

	protected function addValidator()
	{
		/* @var $validator \yii\validators\FileValidator|\yii\validators\ImageValidator */
		/* @var $model \yii\base\Model */
		$validator = \Yii::createObject(
			array_merge(
				[
					'class' => $this->image ? \yii\validators\ImageValidator::className()
						: \yii\validators\FileValidator::className(),
					'attributes' => $this->attribute,
				],
				$this->validator
			)
		);

		$this->owner->validators[] = $validator;
	}

	protected function removeValidator()
	{
		// TODO: implement remove file validator
	}
}
