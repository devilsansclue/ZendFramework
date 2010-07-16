<?php

class Zend_Epp_Command extends Zend_Epp_Request
{
    // TODO: Init, checken ob ns, name usw

    /**
     * Command name
     *
     * @var string
     */
    protected $name;
    protected $object;

    public function getName()
    {
        return $this->name;
    }

    protected function init($params = array())
    {
        $command = $this->addCommand($this->getName());
        if (isset($params[0]) && $params[0] instanceof Zend_Epp_Object) {
            $command->appendChild($this->addObject($params[0]));
        }
        $this->initCommand($params);
    }

    protected function initCommand($params)
    {
    }

    protected function appendCommandNode($name, $element = null)
    {
        return $this->appendElement($this->command_node, $name, $element);
    }

    public function addObject(Zend_Epp_Object $object)
    {
        $node = $this->doc->createElementNS(
            // 'urn:ietf:params:xml:ns:domain-1.0',
            $object->getNamespace(),
            sprintf('%s:%s', $object->getName(), $this->getName())
        );
        $this->command->appendChild($node);
        $schemaLocation = $node->setAttributeNS(
            'http://www.w3.org/2001/XMLSchema-instance',
            'schemaLocation',
            $object->getSchema()

            // 'urn:ietf:params:xml:ns:domain-1.0 domain-1.0.xsd'
        );
        $this->appendElement(
            $node,
            sprintf('%s:%s', $object->getName(), $object->getKey()),
            $object->getId()
        );
        return $node;
    }

}

