<?php

/**
 * TODO: Use Zend_Http_Client once it #ZF-4838 is fixed
 *
 */
class Zend_Epp_Transport_Http extends Zend_Epp_Transport
{
    /**
     * cURL handle
     *
     * @var resource
     */
    protected $ch;


    /**
     * Session ID
     *
     * @var string
     */
    protected $sid;

    /**
     * EPP client, user-agent identifier
     *
     * @var string
     */
    protected $useragent = 'Zend Framework EPP Library';

    public function isConnected()
    {
        return $this->sid !== null;
    }

    /**
     * Init function, prepares curl resource handle
     *
     * @return void
     */
    protected function init()
    {
        // TODO: how to make sure we always have an URL?
        $this->ch = curl_init($this->registry->getUri());
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($this->ch, CURLOPT_HEADER, 1);
        curl_setopt($this->ch, CURLOPT_POST, 1);
        curl_setopt($this->ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($this->ch, CURLOPT_USERAGENT, $this->useragent);
        curl_setopt($this->ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/epp+xml; charset=utf-8'
        ));
    }

    /**
     * Send EPP request to registry
     *
     * @param  Zend_Epp_Request  $request
     * @return Zend_Epp_Response
     * @throws Zend_Epp_Transport_Exception
     */
    public function send(Zend_Epp_Request $request)
    {
        curl_setopt($this->ch, CURLOPT_POSTFIELDS, $request->dump(true));
        $response = curl_exec($this->ch);
        if (curl_errno($this->ch)) {
            throw new Zend_Epp_Transport_Exception(sprintf(
                'EPP request to registry (%s) failed: %s',
                $this->registry->name,
                curl_error($this->ch)
            ));
        }

        // TODO: remove from final version
        $debug = false;
        
        list($header, $xml) = explode("\r\n\r\n", $response, 2);

        // TODO: Muss besser werden
        if (preg_match('~^HTTP/1.1\s100\s~', $header)) {
            list($header, $xml) = explode("\r\n\r\n", $xml, 2);
        }

        // TODO: remove from final version
        if ($debug) {
            echo "\n\nGOT HEADER:\n===\n";
            echo $header;
        }

        // TODO: Alle(!) Cookies auslesen und wieder senden
        if ($this->sid === null) {
            $pattern = '~^Set-Cookie:\s*JSESSIONID=([^\^\s]+);~';
            $cookies = array();
            foreach (preg_split('~\r\n~', $header, -1, PREG_SPLIT_NO_EMPTY)
                     as $line)
            {
                if (preg_match($pattern, $line, $match)) {
                    $this->sid = $match[1];
                    echo "GOT CONNECTED\n";
                    curl_setopt(
                        $this->ch,
                        CURLOPT_COOKIE,
                        'JSESSIONID=' . $this->sid
                    );
                }
            }
        }

        // TODO: remove from final version
        if ($debug) {
            echo "\n\nGOT RESPONSE:\n===\n";
            echo $xml;
        }

        $response = Zend_Epp_Response::fromXml($request, $xml);
        return $response;
    }

    /**
     * Destructor
     */
    public function __destruct()
    {
        curl_close($this->ch);
        parent::__destruct();
    }
}

