<?php

class Zend_Epp_Hello extends Zend_Epp_Command
{
    protected $use_trid = false;

    protected function init($params = array())
    {
        $this->appendElement($this->root, 'hello');
    }

    /**
     * @return bool
     */
    protected function process()
    {
        if (! isset($this->response->xml->greeting)) {
            return;
        }
        // echo "Server: " . $this->response->xml->greeting->svID . "\n";
        foreach ($this->response->xml->greeting->svcMenu->objURI as $urn) {
            $this->registry->addBaseExtension((string) $urn);
        }
        // TODO: is there any difference between those two extension types??
        if (isset($this->response->xml->greeting->svcMenu->svcExtension)) {
            foreach ($this->response->xml->greeting->svcMenu->svcExtension->extURI as $urn) {
                $this->registry->addExtension((string) $urn);
            }
        }
        $this->response->dump();
        return true;
    }
}

