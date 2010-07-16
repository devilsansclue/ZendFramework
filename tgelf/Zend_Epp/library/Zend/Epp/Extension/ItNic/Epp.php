<?php

class Zend_Epp_Extension_ItNic_Epp extends Zend_Epp_Extension
{
    protected $urn = 'http://www.nic.it/ITNIC-EPP/extepp-1.0';

    protected function creditMsgData($xml)
    {
        printf("Remaining credit: %s\n", $xml->credit);
    }
}

