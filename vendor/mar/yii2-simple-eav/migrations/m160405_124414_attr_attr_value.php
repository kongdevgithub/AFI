y<?php

use yii\db\Migration;
use \yii\db\Schema;

class m160405_124414_attr_attr_value extends Migration
{
    public function up()
    {
        $options = $this->db->driverName == 'mysql'
            ? 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB'
            : null;
        $this->createTable('{{%eav_attribute}}', [
            'id' => Schema::TYPE_PK,
            'alias' => Schema::TYPE_STRING . ' NOT NULL COMMENT \'Алиас класса, к которому атрибут привязан\'',
            'name' => Schema::TYPE_STRING . ' NOT NULL COMMENT \' Имя атрибута \'',
            'label' => Schema::TYPE_STRING . ' NOT NULL COMMENT \' Label ( название ) атрибута\'',
            'searchable' => Schema::TYPE_BOOLEAN . ' NOT NULL DEFAULT 1 COMMENT \'Поиск по полю\'',
            'is_numeric' => Schema::TYPE_BOOLEAN . ' NOT NULL DEFAULT 0 COMMENT \'Числовое поле\'',
        ], $options);
        $this->createTable('{{%eav_attribute_value}}', [
            'id' => Schema::TYPE_PK,
            'object_id' => Schema::TYPE_INTEGER . ' NOT NULL COMMENT \'Id объекта \'',
            'attribute_id' => Schema::TYPE_INTEGER . ' NOT NULL COMMENT \' Атрибут \'',
            'value' => Schema::TYPE_TEXT . ' COMMENT \'Значение\'',
        ], $options);
        $this->addForeignKey('FK_Value_attribute_id', '{{%eav_attribute_value}}', 'attribute_id', '{{%eav_attribute}}', 'id', "CASCADE", "NO ACTION");
    }

    public function down()
    {
        echo "m160405_124414_attr_attr_value cannot be reverted.\n";

        return false;
    }

    /*
    // Use safeUp/safeDown to run migration code within a transaction
    public function safeUp()
    {
    }

    public function safeDown()
    {
    }
    */
}
