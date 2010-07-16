<?php

class Zend_Epp_Extension_Ietf_Domain extends Zend_Epp_Extension
{
    protected $urn = 'urn:ietf:params:xml:ns:domain-1.0';

    protected function infData($xml)
    {
        /*
		<domain:name>example.com</domain:name>
		<domain:roid>ITNIC-13241324</domain:roid>
		<domain:status s="inactive" lang="en" />
		<domain:registrant>XYZ-TG2</domain:registrant>
		<domain:contact type="admin">XYZ-TG2</domain:contact>
		<domain:contact type="tech">XYZ-TG1</domain:contact>
		<domain:clID>XYZ-REG</domain:clID>
		<domain:crID>XYZ-REG</domain:crID>
		<domain:crDate>2010-04-07T12:17:37+02:00</domain:crDate>
		<domain:upID>XYZ-REG</domain:upID>
		<domain:upDate>2010-04-07T12:17:39+02:00</domain:upDate>
		<domain:exDate>2011-04-07T23:59:59+02:00</domain:exDate>
		<domain:authInfo>
			<domain:pw>UmUAp75yu</domain:pw>
		</domain:authInfo>
        */
        $domain     = (string) $xml->name;
        $roid       = (string) $xml->roid;
        $status     = (string) $xml->status->attributes()->s;
        $registrant = (string) $xml->registrant;
        $contacts   = array();
        foreach ($xml->contact as $contact) {
            $contacts[(string) $contact->attributes()->type] = (string) $contact;
        }
        $clID       = (string) $xml->clID; // ??
        $created_by = (string) $xml->crID;
        $created    = (string) $xml->crDate;
        $updated_by = (string) $xml->upID;
        $updated    = (string) $xml->upDate;
        $expires    = (string) $xml->exDate;
        $auth_pw    = (string) $xml->authInfo->pw;
        printf("%s [%s] for %s is %s\n", $domain, $roid, $registrant, $status);
    }
}

