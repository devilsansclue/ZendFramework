<?php

class Zend_Epp_Extension_ItNic_Domain extends Zend_Epp_Extension
{
    protected $urn = 'http://www.nic.it/ITNIC-EPP/extdom-1.0';

    protected function dnsErrorMsgData($xml)
    {
        /*
        <extdom:responseId>ff62ee11-2f64-47ed-aa7e-9e078a41234e2</extdom:responseId>
        <extdom:validationDate>2010-04-07T12:17:39+02:00</extdom:validationDate>
        <extdom:report><extdom:domain name="example.com." status="FAILED">
        <extdom:test name="NameserversResolvableTest" status="FAILED">
        <extdom:dns name="dns.example.com." status="FAILED">
        <extdom:dnsreport level="debug">
        <![CDATA[
        */

        $response_id = (string) $xml->responseId;
        $validation_date = (string) $xml->validationDate;
        $domain = rtrim($xml->report->domain->attributes()->name, '.');
        $test = (string) $xml->report->domain->test->attributes()->name;
        $test_result = (string) $xml->report->domain->test->attributes()->status;
        printf("%s for %s: %s\n", $test, $domain, $test_result);
        foreach ($xml->report->domain->test->dns as $dns) {
            $server = rtrim($dns->attributes()->name, '.');
            $server_result = (string) $dns->attributes()->status;
            printf("- DNS-Server %s: %s\n", $server, $server_result);
            // echo (string) "\n===\n" . $dnstest->dnsreport . "\n===\n";
        }
    }

    protected function infData($xml)
    {
    	// <extdom:ownStatus s="dnsHold" lang="en" />
    	$status = (string) $xml->ownStatus->attributes()->s;
        printf("NIC.it status: %s\n", $status);
    }

    protected function simpleMsgData($xml)
    {
        /*
		<extdom:name>example.com</extdom:name>
        */
        $domain = (string) $xml->name;
        printf("simpleMsgData? NIC.it domain: %s\n", $domain);
    }

    protected function infNsToValidateData($xml)
    {
        /*
		<extdom:nsToValidate>
			<domain:hostAttr xmlns:domain="urn:ietf:params:xml:ns:domain-1.0">
				<domain:hostName>dns.example.com</domain:hostName>
			</domain:hostAttr>
			<domain:hostAttr xmlns:domain="urn:ietf:params:xml:ns:domain-1.0">
				<domain:hostName>dns2.example.com</domain:hostName>
			</domain:hostAttr>
		</extdom:nsToValidate>
        */
        $nsToValidate = array();
        foreach ($xml->nsToValidate->children('urn:ietf:params:xml:ns:domain-1.0')->hostAttr as $hostAttr) {
            $nsToValidate[] = (string) $hostAttr->hostName;
        }
        foreach ($nsToValidate as $ns) {
            printf("NS validation required: %s\n", $ns);
        }
    }
}

