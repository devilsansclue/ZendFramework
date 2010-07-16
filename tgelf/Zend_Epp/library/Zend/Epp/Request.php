<?php

class Zend_Epp_Request
{
    protected $doc;
    protected $root;
    protected $command;
    protected $command_node; // oans drunter, z.B. <login/>
    protected $required_params = array();
    protected $use_trid = true;
    protected $registry;
    protected $response;

    public function __construct($registry, $params = array())
    {
        $this->registry = $registry;
        $this->doc = new DOMDocument('1.0', 'utf-8');
        $this->doc->formatOutput = true;
        $this->doc->xmlStandalone = false;
        $this->root = $this->addEppNode();
        foreach ($this->required_params as $param)
        {
            if (! array_key_exists($param, $params))
                throw new Zend_Epp_Exception(sprintf(
                    '%s requires %s',
                    get_class($this),
                    $param
                ));
        }
        $this->init($params);
    }

    public function setTrId($id)
    {
        $this->appendElement($this->command, 'clTRID', $id);
    }

    public function send()
    {
        if ($this->use_trid) {
            $this->setTrId($this->registry->getNextTrId());
        }
        // TODO: remove
        if (true) {
            echo "SENDING REQUEST:\n===\n";
            $this->dump();
        }

        $this->response = $this->registry->getTransport()->send($this);
        /*
        if (! $response->succeeded()) {
            // $this->logout();
        }
        */

        if ($this->process() === true) {
            var_dump($this->response->getResponseCode());
            var_dump($this->response->getResponseMessage());

            $this->response->setSuccess(true);
        } else {
            new Zend_Epp_Exception(sprintf(
                // TODO: command name, object id?
                'EPP request (%s): processing "XXX" failed: [%s] %s',
                $this->registry->getName(),
                $this->response->getResponseCode(),
                $this->response->getResponseMessage()
            ));
        }
        return $this->response;
    }

    public function getRegistry()
    {
        return $this->registry;
    }

    public function getResponse()
    {
        if ($this->response === null) {
            throw new Zend_Epp_Exception('Did not yet get any response');
        }
        return $this->response;
    }

    protected function process()
    {
        echo "No processing\n";
        $this->response->dump();
        return false;
    }

    public function dump($return = false)
    {
        if ($return) {
            return $this->doc->saveXML();
        } else {
            echo $this->doc->saveXML();
        }
    }

    protected function init($params = array())
    {
    }

    protected function addCommand($name)
    {
        if ($this->command === null)
        {
            $this->command = $this->root->appendChild(
                $this->doc->createElement('command')
            );
            $this->command_node = $this->appendElement($this->command, $name);
        }
        return $this->command_node;
    }

    protected function appendElement($parent, $name, $value = null)
    {
        if ($value === null)
        {
            $element = $this->doc->createElement($name);
        } else {
            $element = $this->doc->createElement($name, $value);
        }
        $parent->appendChild($element);
        return $element;
    }

    protected function addEppNode($parent = null)
    {
        if ($parent === null) $parent = $this->doc;
        $node = $parent->createElementNS('urn:ietf:params:xml:ns:epp-1.0', 'epp');
        $node->setAttributeNS(
            'http://www.w3.org/2000/xmlns/',
            'xmlns:xsi',
            'http://www.w3.org/2001/XMLSchema-instance'
        );
        $schemaLocation = $node->setAttributeNS(
            'http://www.w3.org/2001/XMLSchema-instance',
            'schemaLocation',
            'urn:ietf:params:xml:ns:epp-1.0 epp-1.0.xsd'
        );
        $parent->appendChild($node);
        return $node;
    }

    public function __destruct()
    {
        // Workaround for memory leaks in PHP when using circular references
        // before 5.3 (PHP bug #33595)
        unset($this->registry);
    }
}

