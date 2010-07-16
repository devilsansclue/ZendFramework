<?php

abstract class Zend_Epp_Extension
{
    protected $registry;
    protected $urn;
/*
    public static function factory($registry, $name)
    {
        $class = 'Zend_Epp_Extension_' . $name;
        $extension = new $class;
        $extension->registry = $registry;
        return $extension;
    }
*/

    public function register()
    {
        // printf("Loading %s\n", $this->urn);
    }

    // TODO: besser machen, evtl __call oder noch besser: hooks bzw observer
    public function call($key, $xml)
    {
        if (method_exists($this, $key)) {
            $this->$key($xml);
        } else {
            $this->_generic($key, $xml);
        }
    }

    public function getUrn()
    {
        return $this->urn;
    }

    protected function _generic($key, $xml)
    {
        printf("EXTENSION %s, unsupported element: %s\n", get_class($this), $key);
        print_r($xml);
    }

    // TODO: Workaround destruct -> registry
}

