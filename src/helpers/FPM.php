<?php
/**
 * Author: metal
 * Email: metal
 */

namespace metalguardian\fileProcessor\helpers;

/**
 * Class FPM
 * @package metalguardian\fileProcessor\helpers
 */
class FPM
{
	public static $moduleName = 'fileProcessor';
	/**
	 * @param string $module
	 *
	 * @throws \yii\base\InvalidConfigException
	 * @return \metalguardian\fileProcessor\Module
	 */
	public static function m($module = null)
	{
		if (is_null($module)) {
			$module = self::$moduleName;
		}
		if (!\Yii::$app->hasModule($module)) {
			throw new \yii\base\InvalidConfigException(
				\metalguardian\fileProcessor\Module::t(
					'exception',
					'Wrong module name! You need call this method with right fileProcessor module name.'
				)
			);
		}
		return \Yii::$app->getModule($module);
	}

	/**
	 * @param $id
	 *
	 * @return null
	 */
	public static function deleteFile($id)
	{
		if (!(int)$id) {
			return null;
		}
		// TODO: implement delete file
		return true;
	}

	/**
	 * Get host for the image with $id
	 *
	 * @param null $id
	 *
	 * @return string
	 * @throws \yii\base\InvalidConfigException
	 */
	public static function getHost($id = null)
	{
		if (is_string(self::m()->host)) {
			$host = \Yii::getAlias(self::m()->host);
		} else {
			$count = count(self::m()->host);
			$host = \Yii::getAlias(self::m()->host[$id % $count]);
		}
		return $host;
	}

	/**
	 * @return string
	 * @throws \yii\base\InvalidConfigException
	 */
	public static function getTableName()
	{
		return self::m()->tableName;
	}

	/**
	 * @return bool|string
	 * @throws \yii\base\InvalidConfigException
	 */
	public static function getBaseUploadDirectory()
	{
		return \Yii::getAlias(self::m()->baseUploadDirectory);
	}

	/**
	 * @return string
	 * @throws \yii\base\InvalidConfigException
	 */
	public static function getUploadDirectory()
	{
		return self::getBaseUploadDirectory() . self::m()->originalDirectory;
	}

	/**
	 * @return int
	 * @throws \yii\base\InvalidConfigException
	 */
	public static function getFilesPerDirectory()
	{
		return self::m()->filesPerDir;
	}
}
