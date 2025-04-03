<?php

declare(strict_types=1);

namespace Infrangible\CatalogProductOptionDefault\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * @author      Andreas Knollmann
 * @copyright   2014-2025 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * @throws \Exception
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context): void
    {
        $setup->startSetup();

        $connection = $setup->getConnection();

        $tableName = $connection->getTableName('catalog_product_option_type_value');

        if (! $connection->tableColumnExists(
            $tableName,
            'default'
        )) {
            $connection->addColumn(
                $tableName,
                'default',
                [
                    'type'     => Table::TYPE_SMALLINT,
                    'length'   => 5,
                    'nullable' => true,
                    'unsigned' => true,
                    'comment'  => 'Default'
                ]
            );
        }

        $setup->endSetup();
    }
}
