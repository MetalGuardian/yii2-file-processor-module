<?php

namespace metalguardian\fileProcessor\models;

use metalguardian\fileProcessor\Module;

/**
 * This is the model class for table "fpm_file".
 *
 * @property integer $id
 * @property string $extension
 * @property string $real_name
 * @property integer $created_at
 * @property integer $updated_at
 */
class File extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return Module::getTableName();
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            [
                'class' => \yii\behaviors\TimestampBehavior::className(),
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['extension'], 'required'],
            [['extension'], 'string', 'max' => 10],
            [['real_name'], 'string', 'max' => 250]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Module::t('model', 'ID'),
            'extension' => Module::t('model', 'Extension'),
            'real_name' => Module::t('model', 'Base name'),
        ];
    }
}
