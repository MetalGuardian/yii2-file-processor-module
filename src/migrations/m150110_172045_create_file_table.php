<?php

use yii\db\Schema;
use yii\db\Migration;

class m150110_172045_create_file_table extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable(
            \metalguardian\fileProcessor\Module::getTableName(),
            [
                'id' => Schema::TYPE_PK,

                'extension' => Schema::TYPE_STRING . '(10) NOT NULL COMMENT "extension of the file"',
                'real_name' => Schema::TYPE_STRING . '(250) NULL DEFAULT NULL COMMENT "real name of the file"',

                'created_at' => Schema::TYPE_INTEGER . ' NOT NULL',
                'updated_at' => Schema::TYPE_INTEGER . ' NOT NULL',
            ],
            $tableOptions
        );
    }

    public function down()
    {
        $this->dropTable(\metalguardian\fileProcessor\Module::getTableName());
    }
}
