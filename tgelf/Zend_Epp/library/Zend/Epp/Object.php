<?php

class Zend_Epp_Object
{
    protected $id;
    protected $key;
    protected $name;
    protected $ns;
    
    /* Hä ?  */
    protected $schema = 'urn:ietf:params:xml:ns:epp-1.0 epp-1.0.xsd';

    // TODO: Init, checken ob key, ns, name usw

    public function __construct($id, $properties = array())
    {
        $this->id = $id;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getKey()
    {
        return $this->key;
    }

    public function getNamespace()
    {
        return $this->ns;
    }

    public function getSchema()
    {
        return $this->schema;
    }

    public function getName()
    {
        return $this->name;
    }
/*
    public function prepareForCommand(Zend_Epp_Command $command)
    {
        $node = $this->doc->createElementNS(
            $this->ns,
            $this->name . ':' . $command->getName()
        );
        $parent->appendChild($node);
        $schemaLocation = $node->setAttributeNS(
            'http://www.w3.org/2001/XMLSchema-instance',
            'schemaLocation',
            $this->schema
        );
        return $node;
    }*/
}

