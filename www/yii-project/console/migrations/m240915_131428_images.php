<?php

use yii\db\Migration;

/**
 * Class m240915_131428_images
 */
class m240915_131428_images extends Migration
{


    // Use up()/down() to run migration code without a transaction.
    public function up()
    {
		$this->createTable('images', [
			'id' => $this->primaryKey(),
			'source_name' => $this->string()->notNull(),
			'source_uuid' => $this->string()->notNull(),
		]);

    }

    public function down()
    {
        echo "m240915_131428_images cannot be reverted.\n";

        return false;
    }

}
