<?php

class Zend_Epp_Extension_Ietf_Contact extends Zend_Epp_Extension
{
    protected $urn = 'urn:ietf:params:xml:ns:contact-1.0';

    protected function infData($xml)
    {
        $contact_id = $xml->id;
        printf("Got contact info for %s\n", $contact_id);
    }
}

