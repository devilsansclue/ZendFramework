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
 * @package    Doctrine
 * @subpackage Import
 * @copyright  Copyright (c) 2005-2009 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

class Zend_Doctrine_Import_Schema extends Doctrine_Import_Schema
{
    /**
     * @var Doctrine_Import_Builder
     */
    protected $_builder = null;

    /**
     * @var array
     */
    protected $_modules = array();

    /**
     * @var string
     */
    protected $_defaultModule = null;

    /**
     * @var array
     */
    protected $_listener = null;

    /**
     * importSchema
     *
     * A method to import a Schema and translate it into a Doctrine_Record object
     *
     * @param  string $schema       The file containing the XML schema
     * @param  string $format       Format of the schema file
     * @param  string $directory    The directory where the Doctrine_Record class will be written
     * @param  array  $models       Optional array of models to import
     *
     * @return void
     */
    public function importSchema($schema, $format = 'yml', $directory = null, $models = array())
    {
        $manager = Doctrine_Manager::getInstance();
        $modelLoading = $manager->getAttribute(Doctrine_Core::ATTR_MODEL_LOADING);

        if ($modelLoading !== Zend_Doctrine_Core::MODEL_LOADING_ZEND) {
            throw new Zend_Doctrine_DoctrineException(
                "Can't use Zend_Doctrine_Schema with Doctrine_Core::ATTR_MODEL_LOADING not equal to 4 (Zend)."
            );
        }

        $schema = (array) $schema;
        $records = $this->buildSchema($schema, $format);

        if (count($records) == 0) {
            throw new Doctrine_Import_Exception(
                sprintf('No records found for schema "' . $format . '" found in ' . implode(", ", $schema))
            );
        }
        $builder = $this->_getBuilder();
        $builder->setOptions($this->getOptions());

        $this->_initModules();
            
        foreach ($records as $name => $definition) {
            if (!empty($models) && !in_array($definition['className'], $models)) {
                continue;
            }

            $this->_buildRecord($builder, $definition);
        }

        if ($this->_listener) {
            $this->_listener->notifyImportCompleted();
        }
    }

    /**
     * Modify options for Zend Framework model compliant code-generation.
     *
     * @return array
     */
    public function getOptions()
    {
        $options = parent::getOptions();
        $options['pearStyle'] = false;
        $options['baseClassesDirectory'] = 'Base';
        $options['baseClassPrefix'] = '';
        $options['classPrefix'] = '';
        $options['classPrefixFiles'] = true;
        return $options;
    }

    /**
     * @param Doctrine_Import_Builder $builder
     * @return void
     */
    public function setBuilder(Doctrine_Import_Builder $builder)
    {
        $this->_builder = $builder;
    }

    /**
     * @return Doctrine_ImportBuilder
     */
    protected function _getBuilder()
    {
        if ($this->_builder == null) {
            $this->_builder = new Zend_Doctrine_Import_Builder();
        }
        return $this->_builder;
    }

    /**
     * @return void
     */
    protected function _initModules()
    {
        $this->_modules = Zend_Doctrine_Core::getAllModelDirectories();
        $this->_defaultModule = Zend_Controller_Front::getInstance()->getDefaultModule();
    }

    /**
     * @param Doctrine_Import_Builder $builder
     * @param array $definition
     */
    protected function _buildRecord($builder, $definition)
    {
        $className = $definition['className'];
        if (strpos($className, "Model_") === false) {
            throw Zend_Doctrine_DoctrineException::invalidZendModel($className);
        }

        $classPrefix = substr($className, 0, strpos($className, 'Model_')+strlen('Model_'));
        if (strpos($className, "Model_") === 0) {
            $moduleName = $this->_defaultModule;
        } else {
            $camelCaseToDash = new Zend_Filter_Word_CamelCaseToDash();

            $moduleName = current(explode("_", $className));
            $moduleName = strtolower($camelCaseToDash->filter($moduleName));
        }

        if (!isset($this->_modules[$moduleName])) {
            throw Zend_Doctrine_DoctrineException::unknownModule($moduleName, $className);
        }

        $definition['modulePrefix'] = $classPrefix;

        $moduleModelsDirectory = $this->_modules[$moduleName];

        $builder->setTargetPath($moduleModelsDirectory);

        $builder->buildRecord($definition);

        if ($this->_listener) {
            $this->_listener->notifyRecordBuilt($className, $moduleName);
        }
    }

    /**
     * @param Zend_Doctrine_Import_ImportListener $listener
     */
    public function setListener(Zend_Doctrine_Import_ImportListener $listener)
    {
        $this->_listener = $listener;
    }
}