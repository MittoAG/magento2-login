<?php

namespace Mitto\Login\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Zend_Db_Exception;

/**
 * Class InstallSchema
 * @package Mitto\Login\Setup
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     *
     *
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @throws Zend_Db_Exception
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();

        $tableName = 'mitto_login_otp';
        $installer->getConnection()->dropTable($tableName);
        if (!$installer->tableExists($tableName)) {
            $table = $installer->getConnection()->newTable(
                $installer->getTable($tableName)
            )->addColumn(
                'id',
                Table::TYPE_INTEGER,
                null,
                [
                    'identity' => true,
                    'nullable' => false,
                    'primary'  => true,
                    'unsigned' => true,
                ],
                'ID'
            )->addColumn(
                'phone',
                Table::TYPE_TEXT,
                255,
                ['nullable' => false]
            )->addColumn(
                'code',
                Table::TYPE_TEXT,
                255
            )->addColumn(
                'verification_token',
                Table::TYPE_TEXT,
                255
            )->addColumn(
                'generated_at',
                Table::TYPE_TIMESTAMP,
                null,
                ['default' => Table::TIMESTAMP_INIT],
                'Generated At'
            );
            $installer->getConnection()->createTable($table);
        }
        $tableName = 'mitto_login_customer_phone';
        $installer->getConnection()->dropTable($tableName);
        if (!$installer->tableExists($tableName)) {
            $table = $installer->getConnection()->newTable(
                $installer->getTable($tableName)
            )->addColumn(
                'id',
                Table::TYPE_INTEGER,
                null,
                [
                    'identity' => true,
                    'nullable' => false,
                    'primary'  => true,
                    'unsigned' => true,
                ],
                'ID'
            )->addColumn(
                'customer_id',
                Table::TYPE_INTEGER,
                null,
                ['nullable => false', 'unsigned' => true]
            )->addColumn(
                'phone',
                Table::TYPE_TEXT,
                255,
                ['nullable => false']
            )->addColumn(
                'is_verified',
                Table::TYPE_SMALLINT,
                null,
                [
                    'nullable' => false,
                    'default'  => 0,
                    'unsigned' => true,
                ]
            )->addForeignKey(
                $installer->getFkName(
                    $installer->getTable('mitto_login_customer_phone'),
                    'customer_id',
                    'customer_entity',
                    'entity_id'
                ),
                'customer_id',
                $installer->getTable('customer_entity'),
                'entity_id',
                Table::ACTION_CASCADE
            );
            $installer->getConnection()->createTable($table);
        }

        $installer->endSetup();
    }
}
