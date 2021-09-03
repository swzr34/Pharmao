<?php 
namespace Pharmao\Delivery\Setup;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\DB\Ddl\Table;
class InstallSchema implements \Magento\Framework\Setup\InstallSchemaInterface{
    public function install(SchemaSetupInterface $setup,ModuleContextInterface $context){
        $setup->startSetup();
        $conn = $setup->getConnection();
        $tableName = $setup->getTable('pharmao_cache_addresses');
        if($conn->isTableExists($tableName) != true){
            $table = $conn->newTable($tableName)
                            ->addColumn(
                                'id',
                                Table::TYPE_INTEGER,
                                null,
                                ['identity'=>true,'unsigned'=>true,'nullable'=>false,'primary'=>true]
                                )
                            ->addColumn(
                                'customer_id',
                                Table::TYPE_INTEGER,
                                11,
                                ['nullable'=>false]
                                )
                            ->addColumn(
                                'email',
                                Table::TYPE_TEXT,
                                '255',
                                ['nullbale'=>false,'default'=>'']
                                )
                            ->addColumn(
                                'address_id',
                                Table::TYPE_INTEGER,
                                '11',
                                ['nullbale'=>false]
                                )
                            ->addColumn(
                                'street1',
                                Table::TYPE_TEXT,
                                '255',
                                ['nullbale'=>false,'default'=>'']
                                )
                            ->addColumn(
                                'street2',
                                Table::TYPE_TEXT,
                                '255',
                                ['nullbale'=>false,'default'=>'']
                                )
                            ->addColumn(
                                'street3',
                                Table::TYPE_TEXT,
                                '255',
                                ['nullbale'=>false,'default'=>'']
                                )
                            ->addColumn(
                                'city',
                                Table::TYPE_TEXT,
                                '255',
                                ['nullbale'=>false,'default'=>'']
                                )
                            ->addColumn(
                                'postcode',
                                Table::TYPE_TEXT,
                                '255',
                                ['nullbale'=>false,'default'=>'']
                                )
                            ->addColumn(
                                'country',
                                Table::TYPE_TEXT,
                                '255',
                                ['nullbale'=>false,'default'=>'']
                                )
                            ->setOption('charset','utf8');
            $conn->createTable($table);
        }
        $tableJobName = $setup->getTable('pharmao_job');
        if($conn->isTableExists($tableJobName) != true){
            $table_job = $conn->newTable($tableJobName)
                            ->addColumn(
                                'id',
                                Table::TYPE_INTEGER,
                                null,
                                ['identity'=>true,'unsigned'=>true,'nullable'=>false,'primary'=>true]
                                )
                            ->addColumn(
                                'order_id',
                                Table::TYPE_INTEGER,
                                11,
                                ['nullable'=>false]
                                )
                            ->addColumn(
                                'job_id',
                                Table::TYPE_INTEGER,
                                '11',
                                ['nullbale'=>false]
                                )
                            ->setOption('charset','utf8');
            $conn->createTable($table_job);
        }
        $setup->endSetup();
    }
}
 ?>