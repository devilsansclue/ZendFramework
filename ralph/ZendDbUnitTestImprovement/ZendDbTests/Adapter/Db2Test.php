<?php
/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_Db
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2005-2008 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

/**
 * @see Zend_Db_Adapter_AbstractTestCase
 */
require_once 'Zend/Db/Adapter/AbstractTestCase.php';

PHPUnit_Util_Filter::addFileToFilter(__FILE__);

class Zend_Db_Adapter_Db2Test extends Zend_Db_Adapter_AbstractTestCase
{

    protected $_numericDataTypes = array(
        Zend_Db::INT_TYPE    => Zend_Db::INT_TYPE,
        Zend_Db::BIGINT_TYPE => Zend_Db::BIGINT_TYPE,
        Zend_Db::FLOAT_TYPE  => Zend_Db::FLOAT_TYPE,
        'INTEGER'            => Zend_Db::INT_TYPE,
        'SMALLINT'           => Zend_Db::INT_TYPE,
        'BIGINT'             => Zend_Db::BIGINT_TYPE,
        'DECIMAL'            => Zend_Db::FLOAT_TYPE,
        'NUMERIC'            => Zend_Db::FLOAT_TYPE
    );

    public function testAdapterDescribeTablePrimaryAuto()
    {
        $desc = $this->sharedFixture->dbAdapter->describeTable('zf_bugs');

        $this->assertTrue($desc['bug_id']['PRIMARY']);
        $this->assertEquals(1, $desc['bug_id']['PRIMARY_POSITION']);
        $this->assertTrue($desc['bug_id']['IDENTITY']);
    }

    public function testAdapterDescribeTableAttributeColumn()
    {
        $desc = $this->sharedFixture->dbAdapter->describeTable('zf_products');

        $this->assertEquals('zf_products',        $desc['product_name']['TABLE_NAME'], 'Expected table name to be zf_products');
        $this->assertEquals('product_name',      $desc['product_name']['COLUMN_NAME'], 'Expected column name to be product_name');
        $this->assertEquals(2,                   $desc['product_name']['COLUMN_POSITION'], 'Expected column position to be 2');
        $this->assertRegExp('/varchar/i',        $desc['product_name']['DATA_TYPE'], 'Expected data type to be VARCHAR');
        $this->assertEquals('',                  $desc['product_name']['DEFAULT'], 'Expected default to be empty string');
        $this->assertTrue(                       $desc['product_name']['NULLABLE'], 'Expected product_name to be nullable');
        if (!$this->sharedFixture->dbAdapter->isI5()) {
        	$this->assertEquals(0,                   $desc['product_name']['SCALE'], 'Expected scale to be 0');
        } else {
        	$this->assertNull(                   $desc['product_name']['SCALE'], 'Expected scale to be 0');
        }
        $this->assertEquals(0,                   $desc['product_name']['PRECISION'], 'Expected precision to be 0');
        $this->assertFalse(                      $desc['product_name']['PRIMARY'], 'Expected product_name not to be a primary key');
        $this->assertNull(                       $desc['product_name']['PRIMARY_POSITION'], 'Expected product_name to return null for PRIMARY_POSITION');
        $this->assertFalse(                      $desc['product_name']['IDENTITY'], 'Expected product_name to return false for IDENTITY');
    }

    public function testAdapterDescribeTablePrimaryKeyColumn()
    {
        $desc = $this->sharedFixture->dbAdapter->describeTable('zf_products');

        $this->assertEquals('zf_products',        $desc['product_id']['TABLE_NAME'], 'Expected table name to be zf_products');
        $this->assertEquals('product_id',        $desc['product_id']['COLUMN_NAME'], 'Expected column name to be product_id');
        $this->assertEquals(1,                   $desc['product_id']['COLUMN_POSITION'], 'Expected column position to be 1');
        $this->assertEquals('',                  $desc['product_id']['DEFAULT'], 'Expected default to be empty string');
        $this->assertFalse(                      $desc['product_id']['NULLABLE'], 'Expected product_id not to be nullable');
        $this->assertEquals(0,                   $desc['product_id']['SCALE'], 'Expected scale to be 0');
        $this->assertEquals(0,                   $desc['product_id']['PRECISION'], 'Expected precision to be 0');
        $this->assertTrue(                       $desc['product_id']['PRIMARY'], 'Expected product_id to be a primary key');
        $this->assertEquals(1,                   $desc['product_id']['PRIMARY_POSITION']);
    }

    /**
     * Used by _testAdapterOptionCaseFoldingNatural()
     * DB2 and Oracle return identifiers in uppercase naturally,
     * so those test suites will override this method.
     */
    protected function _testAdapterOptionCaseFoldingNaturalIdentifier()
    {
        return 'CASE_FOLDED_IDENTIFIER';
    }

    public function testAdapterTransactionCommit()
    {
        $bugs = $this->sharedFixture->dbAdapter->quoteIdentifier('zf_bugs');
        $bug_id = $this->sharedFixture->dbAdapter->quoteIdentifier('bug_id');

        // use our default connection as the Connection1
        $dbConnection1 = $this->sharedFixture->dbAdapter;
        
        // create a second connection to the same database
        $clonedUtility = $this->_getClonedUtility();
        $dbAdapter2 = $clonedUtility->getDbAdapter();
        $dbAdapter2->getConnection();
        if ($dbAdapter2->isI5()) {
        	$dbAdapter2->query('SET TRANSACTION ISOLATION LEVEL SERIALIZABLE');
        } else {
            $dbAdapter2->query('SET ISOLATION LEVEL = UR');
        }
        
        // notice the number of rows in connection 2
        $count = $dbAdapter2->fetchOne("SELECT COUNT(*) FROM $bugs");
        $this->assertEquals(4, $count, 'Expecting to see 4 rows in bugs table (step 1)');

        // start an explicit transaction in connection 1
        $dbConnection1->beginTransaction();

        // delete a row in connection 1
        $rowsAffected = $dbConnection1->delete(
            'zf_bugs',
            "$bug_id = 1"
        );
        $this->assertEquals(1, $rowsAffected);

        // we should see one less row in connection 2
        // because it is doing an uncommitted read
        $count = $dbAdapter2->fetchOne("SELECT COUNT(*) FROM $bugs");
        $this->assertEquals(3, $count, 'Expecting to see 3 rows in bugs table (step 2) because conn2 is doing an uncommitted read');

        // commit the DELETE
        $dbConnection1->commit();

        // now we should see one fewer rows in connection 2
        $count = $dbAdapter2->fetchOne("SELECT COUNT(*) FROM $bugs");
        $this->assertEquals(3, $count, 'Expecting to see 3 rows in bugs table after DELETE (step 3)');

        // delete another row in connection 1
        $rowsAffected = $dbConnection1->delete(
            'zf_bugs',
            "$bug_id = 2"
        );
        $this->assertEquals(1, $rowsAffected);

        // we should see results immediately, because
        // the db connection returns to auto-commit mode
        $count = $dbAdapter2->fetchOne("SELECT COUNT(*) FROM $bugs");
        $this->assertEquals(2, $count);
    }

    public function testAdapterTransactionRollback()
    {
        $bugs = $this->sharedFixture->dbAdapter->quoteIdentifier('zf_bugs');
        $bug_id = $this->sharedFixture->dbAdapter->quoteIdentifier('bug_id');

        // use our default connection as the Connection1
        $dbConnection1 = $this->sharedFixture->dbAdapter;

        // create a second connection to the same database
        $clonedUtility = $this->_getClonedUtility();
        $dbAdapter2 = $clonedUtility->getDbAdapter();
        $dbAdapter2->getConnection();
        if ($dbAdapter2->isI5()) {
        	$dbAdapter2->query('SET TRANSACTION ISOLATION LEVEL SERIALIZABLE');
        } else {
            $dbAdapter2->query('SET ISOLATION LEVEL = UR');
        }
        
        // notice the number of rows in connection 2
        $count = $dbAdapter2->fetchOne("SELECT COUNT(*) FROM $bugs");
        $this->assertEquals(4, $count, 'Expecting to see 4 rows in bugs table (step 1)');

        // start an explicit transaction in connection 1
        $dbConnection1->beginTransaction();

        // delete a row in connection 1
        $rowsAffected = $dbConnection1->delete(
            'zf_bugs',
            "$bug_id = 1"
        );
        $this->assertEquals(1, $rowsAffected);

        // we should see one less row in connection 2
        // because it is doing an uncommitted read
        $count = $dbAdapter2->fetchOne("SELECT COUNT(*) FROM $bugs");
        $this->assertEquals(3, $count, 'Expecting to see 3 rows in bugs table (step 2) because conn2 is doing an uncommitted read');

        // rollback the DELETE
        $dbConnection1->rollback();

        // now we should see the same number of rows
        // because the DELETE was rolled back
        $count = $dbAdapter2->fetchOne("SELECT COUNT(*) FROM $bugs");
        $this->assertEquals(4, $count, 'Expecting to still see 4 rows in bugs table after DELETE is rolled back (step 3)');

        // delete another row in connection 1
        $rowsAffected = $dbConnection1->delete(
            'zf_bugs',
            "$bug_id = 2"
        );
        $this->assertEquals(1, $rowsAffected);

        // we should see results immediately, because
        // the db connection returns to auto-commit mode
        $count = $dbAdapter2->fetchOne("SELECT COUNT(*) FROM $bugs");
        $this->assertEquals(3, $count, 'Expecting to see 3 rows in bugs table after DELETE (step 4)');
    }

    public function testAdapterAlternateStatement()
    {
        require_once dirname(__FILE__) . '/_files/Test/Db2Statement.php';
        $this->_testAdapterAlternateStatement('Test_Db2Statement');
    }

    /**
     * OVERRIDDEN COMMON TEST CASE
     *
     * This test case will produce a value with two internally set values,
     * autocommit = 1
     * DB2_ATTR_CASE = 0
     */
    public function testAdapterZendConfigEmptyDriverOptions()
    {
        Zend_Loader::loadClass('Zend_Config');
        $params = $this->sharedFixture->dbUtility->getDriverConfigurationAsParams();
        $params['driver_options'] = '';
        $params = new Zend_Config($params);

        $db = Zend_Db::factory($this->sharedFixture->dbUtility->getDriverName(), $params);
        $db->getConnection();
        
        $config = $db->getConfig();
        
        $expectedValue = array(
            'autocommit' => 1,
            'DB2_ATTR_CASE' => 0
            );
        $this->assertEquals($expectedValue, $config['driver_options']);
        $db->closeConnection();
        unset($db);
    }
    
    /**
     * OVERRIDDEN COMMON TEST CASE
     * 
     * Test that quote() takes an array and returns
     * an imploded string of comma-separated, quoted elements.
     */
    public function testAdapterQuoteArray()
    {
        $array = array("it's", 'all', 'right!');
        $value = $this->sharedFixture->dbAdapter->quote($array);
        $this->assertEquals("'it''s', 'all', 'right!'", $value);
    }
    
    /**
     * OVERRRIDEEN COMMON TEST CASE
     * 
     * test that quote() escapes a double-quote
     * character in a string.
     */
    public function testAdapterQuoteDoubleQuote()
    {
        $string = 'St John"s Wort';
        $value = $this->sharedFixture->dbAdapter->quote($string);
        $this->assertEquals("'St John\"s Wort'", $value);
    }
    
    /**
     * OVERRIDDEN FROM COMMON TEST CASE
     * 
     * test that quote() escapes a single-quote
     * character in a string.
     */
    public function testAdapterQuoteSingleQuote()
    {
        $string = "St John's Wort";
        $value = $this->sharedFixture->dbAdapter->quote($string);
        $this->assertEquals("'St John''s Wort'", $value);
    }
    
    /**
     * OVERRIDDEN FROM COMMON TEST CASE
     * 
     * test that quoteInto() escapes a double-quote
     * character in a string.
     */
    public function testAdapterQuoteIntoDoubleQuote()
    {
        $string = 'id=?';
        $param = 'St John"s Wort';
        $value = $this->sharedFixture->dbAdapter->quoteInto($string, $param);
        $this->assertEquals("id='St John\"s Wort'", $value);
    }

    /**
     * OVERRIDDEN FROM COMMON TEST CASE
     * 
     * test that quoteInto() escapes a single-quote
     * character in a string.
     */
    public function testAdapterQuoteIntoSingleQuote()
    {
        $string = 'id = ?';
        $param = 'St John\'s Wort';
        $value = $this->sharedFixture->dbAdapter->quoteInto($string, $param);
        $this->assertEquals("id = 'St John''s Wort'", $value);
    }
    
    /**
     * This is "related" to the issue.  It appears the fix for
     * describeTable is relatively untestable due to the fact that
     * its primary focus is to reduce the query time, not the result
     * set.
     * 
     * @group ZF-5169
     */
    public function testAdapterSchemaOptionInListTables()
    {
        $params = $this->sharedFixture->dbUtility->getDriverConfigurationAsParams();
        unset($params['schema']);
        
        $clonedUtility1 = $this->_getClonedUtility(true, $params);
        $clonedDbAdapter1 = $clonedUtility1->getDbAdapter();
        
        // list tables without schema
        $tableCountNoSchema = count($clonedDbAdapter1->listTables());
        
        $clonedDbConfig1 = $this->sharedFixture->dbAdapter->getConfig();
        
        if ($clonedDbAdapter1->isI5()) {
            if (isset($clonedDbConfig1['driver_options']['i5_lib'])) {
                $schema = $clonedDbConfig1['driver_options']['i5_lib'];
            }
        } elseif (!$clonedDbAdapter1->isI5()) {
            $schema = $clonedUtility1->getSchema();
        } else {
            $this->markTestSkipped('No valid schema to test against.');
            return;
        }
        
        $params = $this->sharedFixture->dbUtility->getDriverConfigurationAsParams();
        $params['schema'] = $schema;
        $clonedUtility2 = $this->_getClonedUtility(true, $params);
        $clonedDbAdpater2 = $clonedUtility2->getDbAdapter();
        
        // list tables with schema
        $tableCountSchema = count($clonedDbAdpater2->listTables());
        
        $this->assertGreaterThan(0, $tableCountNoSchema, 'Adapter without schema should produce large result');
        $this->assertGreaterThan(0, $tableCountSchema, 'Adapter with schema should produce large result');

        $this->assertTrue(($tableCountNoSchema > $tableCountSchema), 'Table count with schema provided should be less than without.');
    }

}
