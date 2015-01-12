<?php

use yii\db\Schema;
use yii\db\Migration;

/**
 * Class m150110_172045_create_file_table
 */
class m150110_172045_create_file_table extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable(
            \metalguardian\fileProcessor\helpers\FPM::getTableName(),
            [
                'id' => Schema::TYPE_PK,
                'extension' => Schema::TYPE_STRING . '(10) NOT NULL COMMENT "File extension"',
                'base_name' => Schema::TYPE_STRING . '(250) NULL DEFAULT NULL COMMENT "File base name"',
                'created_at' => Schema::TYPE_INTEGER . ' NOT NULL',
            ],
            $tableOptions
        );
    }

    public function down()
    {
        $this->dropTable(\metalguardian\fileProcessor\helpers\FPM::getTableName());
    }
}
