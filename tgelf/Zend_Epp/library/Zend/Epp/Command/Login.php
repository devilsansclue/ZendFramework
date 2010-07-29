<?php

class Zend_Epp_Command_Login extends Zend_Epp_Command
{
    // protected $use_trid = false;
    protected $name = 'login';

    protected function initCommand($params)
    {
        $this->appendCommandNode('clID', $this->registry->getUsername());
        $this->appendCommandNode('pw',   $this->registry->getPassword());

        // TODO: sicher so?
        if (isset($params['newpassword']))
        {
            $this->appendCommandNode('newPW', $params['newpassword']);
        }

        $options = $this->appendCommandNode('options');
        $this->appendElement($options, 'version', '1.0');
        // TODO: multiple language support
        $this->appendElement($options, 'lang',    'en');
        $svcs = $this->appendCommandNode('svcs');
        foreach ($this->registry->getBaseExtensions() as $urn) {
            $this->appendElement($svcs, 'objURI', $urn);
            $this->registry->loadExtension($urn);
        }

        $svce = $this->appendElement($svcs, 'svcExtension');
        foreach ($this->registry->getExtensions() as $urn) {
            $this->registry->loadExtension($urn);
            $this->appendElement($svce, 'extURI', $urn);
        }
    }
}

