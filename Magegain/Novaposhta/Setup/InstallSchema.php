<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magegain\Novaposhta\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * @codeCoverageIgnore
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
      

    $installer = $setup;
        $installer->startSetup();

        /**
         * Create table 'customer_entity'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('novaposhta_cities')
        )->addColumn(
            'id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Entity Id'
        )->addColumn(
            'city_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['nullable' => false],
            'api Id'
        )
        ->addColumn(
            'city_name',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            50,
            [],
            'name of the city'
        )->addColumn(
            'city_name_ru',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            50,
            [],
            'name of the city in ukraine'
        )->addColumn(
            'ref',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            50,
            [''],
            'ref'
        )->setComment(
            'cities from api'
        );
        $installer->getConnection()->createTable($table);


        $table = $installer->getConnection()->newTable(
            $installer->getTable('novaposhta_warhouse')
        )->addColumn(
            'id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Entity Id'
        )->addColumn(
            'city_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            [ 'nullable' => false, ],
            'api Id'
        )
        ->addColumn(
            'warhouse_name',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            [],
            'name of the city'
        )->addColumn(
            'warhouse_name_ru',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            [],
            'name ru'
        )->addColumn(
            'ref',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            [],
            'ref'
        )->setComment(
            'warhouses from api'
        );
        $installer->getConnection()->createTable($table);
        $installer->endSetup();
    }
}
