<?php
/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_Filter
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: IniTest.php 20166 2010-01-09 19:00:17Z bkarwin $
 */

/**
 * @see Zend_Filter_Encode_Entity
 */
require_once 'Zend/Filter/Encode/Entity.php';

/**
 * @category   Zend
 * @package    Zend_Filter
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Filter_Encode_EntityTest extends PHPUnit_Framework_TestCase
{
    protected $_filter;

    public function setUp()
    {
        if (!extension_loaded('iconv')) {
            $this->setExpectedException('Zend\Filter\Exception');
            new Zend_Filter_Encode_Entity();

            $this->markTestSkipped("Missing needed ext/iconv");
        }

        $this->_filter = new Zend_Filter_Encode_Entity();
    }

    public function testSetUnknownEntityReferenceThrowException()
    {
        $this->setExpectedException('Zend_Filter_Exception');
        $this->_filter->setEntityReference('unknown');
    }

    public function testSetInvalidCharUnknownActionException()
    {
        $this->setExpectedException('Zend_Filter_Exception');
        $this->_filter->setInvalidCharAction('unknown');
    }

    public function testSetInvalidEntityUnknownActionException()
    {
        $this->setExpectedException('Zend_Filter_Exception');
        $this->_filter->setInvalidCharAction('unknown');
    }

    // encode

    public function testEncodeSpecialCharsToNumericEntities()
    {
        $this->_filter->setHex(false);

        $text     = '<>&"\'';
        $expected = '&#60;&#62;&#38;&#34;&#39;';
        $actual   = $this->_filter->encode($text);
        $this->assertEquals($expected, $actual);
    }

    public function testEncodeSpecialCharsToHexEntities()
    {
        $this->_filter->setHex(true);

        $text     = '<>&"\'';
        $expected = '&#x3c;&#x3e;&#x26;&#x22;&#x27;';
        $actual   = $this->_filter->encode($text);
        $this->assertEquals($expected, $actual);
    }

    public function testEncodeSpecialCharsToXmlNamedEntities()
    {
        $this->_filter->setEntityReference('xml');

        $text     = '<>&"\'';
        $expected = '&lt;&gt;&amp;&quot;&apos;';
        $actual   = $this->_filter->encode($text);
        $this->assertEquals($expected, $actual);
    }

    public function testEncodeSpecialCharsToHtmlNamedEntities()
    {
        $this->_filter->setHex(false);
        $this->_filter->setEntityReference('html');

        $text     = '<>&"\'';
        $expected = '&lt;&gt;&amp;&quot;&#39;';
        $actual   = $this->_filter->encode($text);
        $this->assertEquals($expected, $actual);
    }

    public function testEncodeUtf8ToUtf8()
    {
        $this->_filter->setInputCharset('UTF-8');
        $this->_filter->setOutputCharset('UTF-8');

        //           auml       euro
        $text     = "\xC3\xA4 - \xE2\x82\xAC - <>&\"'";
        $expected = "\xC3\xA4 - \xE2\x82\xAC - &#60;&#62;&#38;&#34;&#39;";
        $actual   = $this->_filter->encode($text);
        $this->assertEquals($expected, $actual);
    }

    public function testEncodeUtf8ToIso88591()
    {
        $this->_filter->setInputCharset('UTF-8');
        $this->_filter->setOutputCharset('ISO-8859-1');

        //           auml       euro
        $text     = "\xC3\xA4 - \xE2\x82\xAC - <>&\"'";
        $expected = "\xE4 - &#8364; - &#60;&#62;&#38;&#34;&#39;";
        $actual   = $this->_filter->encode($text);
        $this->assertEquals($expected, $actual);
    }

    // decode

    public function testDecodeHexEntities()
    {
        $input    = '&#x3c;a&#x3e; &#x3c;&#x62;&#x3e; &#x3c;c&#x3e;';
        $expected = '<a> <b> <c>';
        $actual   = $this->_filter->decode($input);
        $this->assertEquals($expected, $actual);
    }

    public function testDecodeNumEntities()
    {
        $input    = '&#60;a&#62; &#60;&#98;&#62; &#60;c&#62;';
        $expected = '<a> <b> <c>';
        $actual   = $this->_filter->decode($input);
        $this->assertEquals($expected, $actual);
    }

    public function testDecodeKeepSpecial()
    {
        $this->_filter->setKeepSpecial(true);

        $input    = '&#x3c;a&#x3e; &#60;&#98;&#62; &#60;c&#62;';
        $expected = '&#x3c;a&#x3e; &#60;b&#62; &#60;c&#62;';
        $actual   = $this->_filter->decode($input);
        $this->assertEquals($expected, $actual);
    }

    public function testDecodeUtf8ToUtf8()
    {
        $this->_filter->setInputCharset('UTF-8');
        $this->_filter->setOutputCharset('UTF-8');

        // euro in different scopes
        $input    = "\xE2\x82\xAC - &#8364; - &#x20AC; - &#x20ac;";
        $expected = "\xE2\x82\xAC - \xE2\x82\xAC - \xE2\x82\xAC - \xE2\x82\xAC";
        $actual   = $this->_filter->decode($input);
        $this->assertEquals($expected, $actual);
    }

    public function testDecodeUtf8ToISO885915()
    {
        $this->_filter->setInputCharset('UTF-8');
        $this->_filter->setOutputCharset('ISO-8859-15');

        // euro in different scopes
        $input    = "\xE2\x82\xAC - &#8364; - &#x20AC; - &#x20ac;";
        $expected = "\xA4 - \xA4 - \xA4 - \xA4";
        $actual   = $this->_filter->decode($input);
        $this->assertEquals($expected, $actual);
    }

    public function testDecodeOnInvalidCharException()
    {
        $this->_filter->setInputCharset('UTF-8');
        $this->_filter->setOutputCharset('ISO-8859-1');
        $this->_filter->setInvalidCharAction(Zend_Filter_Encode_Entity::ACTION_EXCEPTION);

        $this->setExpectedException('Zend_Filter_Exception');
        $this->_filter->decode('&#x20AC;'); // euro
    }

    public function testDecodeOnInvalidCharCallback()
    {
        $this->_filter->setInputCharset('UTF-8');
        $this->_filter->setOutputCharset('ISO-8859-1');
        $this->_filter->setInvalidCharAction(Zend_Filter_Encode_Entity::ACTION_CALLBACK);
        $this->_filter->setInvalidCharCallback(array($this, 'callback_testDecodeOnInvalidCharCallback'));

        $expected = '[e282ac]';
        $actual   = $this->_filter->decode('&#x20AC;'); // euro
        $this->assertEquals($expected, $actual);
    }
    public function callback_testDecodeOnInvalidCharCallback($char)
    {
        $tmp = unpack('H*', $char);
        return '[' . $tmp[1] . ']';
    }

    public function testDecodeOnInvalidCharSubstitute()
    {
        $this->_filter->setInputCharset('UTF-8');
        $this->_filter->setOutputCharset('ISO-8859-1');
        $this->_filter->setInvalidCharAction(Zend_Filter_Encode_Entity::ACTION_SUBSTITUTE);
        $this->_filter->setSubstitute('XYZ');

        $expected = 'XYZ';
        $actual   = $this->_filter->decode('&#x20AC;'); // euro
        $this->assertEquals($expected, $actual);
    }

    public function testDecodeOnInvalidCharIgnore()
    {
        $this->_filter->setInputCharset('UTF-8');
        $this->_filter->setOutputCharset('ISO-8859-1');
        $this->_filter->setInvalidCharAction(Zend_Filter_Encode_Entity::ACTION_IGNORE);

        $expected = '';
        $actual   = $this->_filter->decode('&#x20AC;'); // euro
        $this->assertEquals($expected, $actual);
    }

    public function testDecodeOnInvalidCharEntity()
    {
        $this->_filter->setInputCharset('UTF-8');
        $this->_filter->setOutputCharset('ISO-8859-1');
        $this->_filter->setInvalidCharAction(Zend_Filter_Encode_Entity::ACTION_ENTITY);

        // numeric entity
        $this->_filter->setHex(false);
        $expected = '&#8364;';
        $actual   = $this->_filter->decode('&#x20AC;'); // euro
        $this->assertEquals($expected, $actual);

        // hex entity
        $this->_filter->setHex(true);
        $expected = '&#x20ac;';
        $actual   = $this->_filter->decode('&#x20AC;'); // euro
        $this->assertEquals($expected, $actual);
    }

    public function testDecodeOnInvalidCharTranslit()
    {
        $this->_filter->setInputCharset('UTF-8');
        $this->_filter->setOutputCharset('ISO-8859-1');
        $this->_filter->setInvalidCharAction(Zend_Filter_Encode_Entity::ACTION_TRANSLIT_IGNORE);

        $expected = 'EUR';
        $actual   = $this->_filter->decode('&#x20AC;'); // euro
        $this->assertEquals($expected, $actual);
    }

    public function testDecodeOnInvalidCharTranslitException()
    {
        $this->_filter->setInputCharset('UTF-8');
        $this->_filter->setOutputCharset('ISO-8859-1');
        $this->_filter->setInvalidCharAction(Zend_Filter_Encode_Entity::ACTION_TRANSLIT_EXCEPTION);

        $this->setExpectedException('Zend_Filter_Exception');
        $this->_filter->decode('&#x2021;'); // DOUBLE DAGGER
    }

    public function testDecodeOnInvalidCharTranslitCallback()
    {
        $this->_filter->setInputCharset('UTF-8');
        $this->_filter->setOutputCharset('ISO-8859-1');
        $this->_filter->setInvalidCharAction(Zend_Filter_Encode_Entity::ACTION_TRANSLIT_CALLBACK);
        $this->_filter->setInvalidCharCallback(array($this, 'callback_testDecodeOnInvalidCharTranslitCallback'));

        $expected = '[e280a1]';
        $actual   = $this->_filter->decode('&#x2021;'); // DOUBLE DAGGER
        $this->assertEquals($expected, $actual);
    }
    public function callback_testDecodeOnInvalidCharTranslitCallback($char)
    {
        $tmp = unpack('H*', $char);
        return '[' . $tmp[1] . ']';
    }

    public function testDecodeOnInvalidCharTranslitSubstitute()
    {
        $this->_filter->setInputCharset('UTF-8');
        $this->_filter->setOutputCharset('ISO-8859-1');
        $this->_filter->setInvalidCharAction(Zend_Filter_Encode_Entity::ACTION_TRANSLIT_SUBSTITUTE);
        $this->_filter->setSubstitute('XYZ');

        $expected = 'XYZ';
        $actual   = $this->_filter->decode('&#x2021;'); // DOUBLE DAGGER
        $this->assertEquals($expected, $actual);
    }

    public function testDecodeOnInvalidCharTranslitIgnore()
    {
        $this->_filter->setInputCharset('UTF-8');
        $this->_filter->setOutputCharset('ISO-8859-1');
        $this->_filter->setInvalidCharAction(Zend_Filter_Encode_Entity::ACTION_TRANSLIT_IGNORE);

        $expected = '';
        $actual   = $this->_filter->decode('&#x2021;'); // DOUBLE DAGGER
        $this->assertEquals($expected, $actual);
    }

    public function testDecodeOnInvalidCharTranslitEntity()
    {
        $this->_filter->setInputCharset('UTF-8');
        $this->_filter->setOutputCharset('ISO-8859-1');
        $this->_filter->setInvalidCharAction(Zend_Filter_Encode_Entity::ACTION_TRANSLIT_ENTITY);

        $expected = '&#8225;';
        $actual   = $this->_filter->decode('&#x2021;'); // DOUBLE DAGGER
        $this->assertEquals($expected, $actual);
    }



}
