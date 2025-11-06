<?php

namespace webdna\commerce\bundles\migrations;

use Craft;
use craft\db\Migration;

/**
 * m250106_000000_add_display_fields migration.
 *
 * Adds displayStyles and displayInitially columns to bundles_bundles table
 * and migrates any existing data from old custom fields in elements_sites.content JSON.
 */
class m250106_000000_add_display_fields extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp(): bool
    {
        // Add displayStyles column if it doesn't exist
        if (!$this->db->columnExists('{{%bundles_bundles}}', 'displayStyles')) {
            $this->addColumn('{{%bundles_bundles}}', 'displayStyles', $this->boolean()->defaultValue(true)->notNull());
        }

        // Add displayInitially column if it doesn't exist
        if (!$this->db->columnExists('{{%bundles_bundles}}', 'displayInitially')) {
            $this->addColumn('{{%bundles_bundles}}', 'displayInitially', $this->boolean()->defaultValue(true)->notNull());
        }

        // Migrate existing data from old custom fields in elements_sites.content JSON
        // Old field UIDs:
        // - bundleDisplayStyles: 7220009a-090e-47cd-b00f-cd75f360d3c9
        // - bundleDisplayInitially: 7ad9ba6b-7fbc-4679-9480-9b71c2dc6d8e

        $this->execute("
            UPDATE {{%bundles_bundles}} bb
            INNER JOIN {{%elements_sites}} es ON es.elementId = bb.id
            SET bb.displayStyles = COALESCE(
                CAST(JSON_UNQUOTE(JSON_EXTRACT(es.content, '$.\"7220009a-090e-47cd-b00f-cd75f360d3c9\"')) AS UNSIGNED),
                1
            ),
            bb.displayInitially = COALESCE(
                CAST(JSON_UNQUOTE(JSON_EXTRACT(es.content, '$.\"7ad9ba6b-7fbc-4679-9480-9b71c2dc6d8e\"')) AS UNSIGNED),
                1
            )
            WHERE JSON_EXTRACT(es.content, '$.\"7220009a-090e-47cd-b00f-cd75f360d3c9\"') IS NOT NULL
               OR JSON_EXTRACT(es.content, '$.\"7ad9ba6b-7fbc-4679-9480-9b71c2dc6d8e\"') IS NOT NULL
        ");

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        echo "m250106_000000_add_display_fields cannot be reverted.\n";
        return false;
    }
}
