<?php

class Zend_Epp_Registry_Eurid extends Zend_Epp_Registry
{
    /**
     * URI
     *
     * @var string
     */
    protected $uri = 'epp://epp.registry.eu:33128';

    /**
     * EPP registry name
     *
     * @var string
     */
    protected $name = 'EURid';

    protected function init()
    {
        $this->transport = new Zend_Epp_Transport_Tcp($this);
        $this->registerExtension('Zend_Epp_Extension_Eurid_Contact');
        $this->registerExtension('Zend_Epp_Extension_Eurid_Domain');
        $this->registerExtension('Zend_Epp_Extension_Eurid_Nsgroup');
        $this->registerExtension('Zend_Epp_Extension_Eurid_Registrar');
        $this->registerExtension('Zend_Epp_Extension_Eurid_Build20051003');
        $this->registerExtension('Zend_Epp_Extension_Ietf_Dnssec');
        $this->registerExtension('Zend_Epp_Extension_Eurid_Keygroup');
        $this->registerExtension('Zend_Epp_Extension_Eurid_Idn');
/*
        <objURI>http://www.eurid.eu/xml/epp/contact-1.0</objURI>
      <objURI>http://www.eurid.eu/xml/epp/domain-1.0</objURI>
      <svcExtension>
        <extURI>http://www.eurid.eu/xml/epp/nsgroup-1.0</extURI>
        <extURI>http://www.eurid.eu/xml/epp/registrar-1.0</extURI>
        <extURI>http://www.eurid.eu/xml/epp/build/20051003</extURI>
        <extURI>urn:ietf:params:xml:ns:secDNS-1.0</extURI>
        <extURI>http://www.eurid.eu/xml/epp/keygroup-1.0</extURI>
        <extURI>http://www.eurid.eu/xml/epp/idn-1.0</extURI>
*/
    }
}

