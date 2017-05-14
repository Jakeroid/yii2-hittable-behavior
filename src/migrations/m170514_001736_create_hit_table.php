<?php

use yii\db\Migration;

/**
 * Handles the creation of table `hit`.
 */
class m170514_001736_create_hit_table extends Migration
{
    private $tableName = '{{%hit}}';

    public function up() {

        $tableOptions = null;

        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';
        }

        $this->createTable($this->tableName, [
            'hit_id' => $this->primaryKey()->unsigned(),
            'user_agent' => $this->string()->notNull(),
            'ip' => $this->string()->notNull(),
            'target_group' => $this->string()->notNull(),
            'target_pk' => $this->string()->notNull(),
            'created_at' => $this->integer()->notNull(),
        ], $tableOptions);

        $this->createIndex('hits_uigp_idx', $this->tableName, ['user_agent', 'ip', 'target_group', 'target_pk']);
    }

    public function down() {
        $this->dropTable($this->tableName);
    }
}
