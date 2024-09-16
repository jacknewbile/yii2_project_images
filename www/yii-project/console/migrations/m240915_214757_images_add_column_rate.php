<?php

use yii\db\Migration;

/**
 * Class m240915_214757_images_add_column_rate
 */
class m240915_214757_images_add_column_rate extends Migration
{

    // Use up()/down() to run migration code without a transaction.
    public function up()
    {
		$this->addColumn('images', 'rate', $this->string(64)->after('id'));
    }

    public function down()
    {
        echo "m240915_214757_images_add_column_rate cannot be reverted.\n";

        return false;
    }

}
