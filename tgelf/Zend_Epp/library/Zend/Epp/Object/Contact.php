<?php

class Zend_Epp_Object_Contact extends Zend_Epp_Object
{
    protected $key    = 'id';
    protected $name   = 'contact';
    protected $ns     = 'urn:ietf:params:xml:ns:contact-1.0';
    protected $schema = 'urn:ietf:params:xml:ns:contact-1.0 contact-1.0.xsd';
    /*
    id
    roid
    postalInfo (...)
    voice
    email
    */
}
/*
			<contact:infData xmlns:contact="urn:ietf:params:xml:ns:contact-1.0" xsi:schemaLocation="urn:ietf:params:xml:ns:contact-1.0 contact-1.0.xsd">
				<contact:id>XYZ-ID123</contact:id>
				<contact:roid>ITNIC-123414</contact:roid>
				<contact:status s="ok" lang="en"/>
				<contact:postalInfo type="loc">
					<contact:name>John Doe</contact:name>
					<contact:org>John Doe</contact:org>
					<contact:addr>
						<contact:street>via Whatever, 1</contact:street>
						<contact:city>Pisa</contact:city>
						<contact:sp>PI</contact:sp>
						<contact:pc>56124</contact:pc>
						<contact:cc>IT</contact:cc>
					</contact:addr>
				</contact:postalInfo>
				<contact:voice x="">+39.050123456</contact:voice>
				<contact:email>john.doe@example.com</contact:email>
				<contact:clID>XYZ-REG</contact:clID>
				<contact:crID>XYZ-REG</contact:crID>
				<contact:crDate>2010-04-06T23:40:44+02:00</contact:crDate>
			</contact:infData>
*/
class Zend_Epp_Contact_PostalInfo
{
    /*
    name
    org
    addr
    */
}

class Zend_Epp_Contact_Address
{
    /*
    street
    city
    province
    postcode
    */
}

