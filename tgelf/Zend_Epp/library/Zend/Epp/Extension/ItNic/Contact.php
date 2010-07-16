<?php

class Zend_Epp_Extension_ItNic_Contact extends Zend_Epp_Extension
{
    protected $urn = 'http://www.nic.it/ITNIC-EPP/extcon-1.0';

    protected function infData($xml)
    {
        /*
		<extcon:consentForPublishing>true</extcon:consentForPublishing>
		<extcon:registrant>
			<extcon:nationalityCode>IT</extcon:nationalityCode>
			<extcon:entityType>1</extcon:entityType>
			<extcon:regCode>JHNDOE74R15B519U</extcon:regCode>
		</extcon:registrant>
        */
        $consent = (bool) $xml->consentForPublishing;
        if (isset($xml->registrant)) {
            $nationalityCode = (string) $xml->registrant->nationalityCode;
            $entityType = (int) $xml->registrant->entityType;
            $regCode = (string) $xml->registrant->regCode;
            printf(
                "Got a registrant from %s, type %d, code %s\n",
                $nationalityCode,
                $entityType,
                $regCode
            );
        }
    }
}

