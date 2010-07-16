<?php

class Zend_Epp_Registry_ItNic extends Zend_Epp_Registry
{
    /**
     * URI
     *
     * @var string
     */
    protected $uri = 'https://epp-gtl.nic.it';

    /**
     * EPP registry name
     *
     * @var string
     */
    protected $name = 'NIC Italia';

    protected function init()
    {
        $this->transport = new Zend_Epp_Transport_Http($this);
        $this->registerExtension('Zend_Epp_Extension_Ietf_Contact');
        $this->registerExtension('Zend_Epp_Extension_Ietf_Domain');
        $this->registerExtension('Zend_Epp_Extension_Ietf_GracePeriod');
        $this->registerExtension('Zend_Epp_Extension_ItNic_Contact');
        $this->registerExtension('Zend_Epp_Extension_ItNic_Domain');
        $this->registerExtension('Zend_Epp_Extension_ItNic_Epp');
    }
}

/*

NIC.it sendet als MIME-Typ text/xml, RFC4930 schreibt aber application/epp+xml vor

*/

