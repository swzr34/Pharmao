<?php
namespace Pharmao\Delivery\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{
	public function upgrade( SchemaSetupInterface $setup, ModuleContextInterface $context ) 
	{
		$installer = $setup;

		$installer->startSetup();

		if (version_compare($context->getVersion(), '1.1.0', '<')) {
			$installer->getConnection()->addColumn(
				$installer->getTable( 'sales_order' ),
				'job_id',
				[
					'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
					'nullable' => true,
					'comment' => 'Job Id',
					'after' => 'paypal_ipn_customer_notified'
				]
			);
			
			$installer->getConnection()->addColumn(
				$installer->getTable( 'sales_invoice' ),
				'job_id',
				[
					'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
					'nullable' => true,
					'comment' => 'Job Id',
					'after' => 'customer_note_notify'
				]
			);
		}

		$installer->endSetup();
	}
}