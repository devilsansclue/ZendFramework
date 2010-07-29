<?php

class Zend_Epp_Transport_Tcp extends Zend_Epp_Transport
{
	protected $socket;

	public function init()
	{
		$timeout = 10;
		if (! preg_match('~^epp://(.+)\:(\d+)~', $this->registry->getUri(), $match))
		{
			throw new Zend_Epp_Transport_Exception('Got invalid URI for TCP Transport');
		}
		$addr = 'ssl://' . $match[1];
		$port = $match[2];
		$this->socket = fsockopen($addr, $port, $errno, $errstr, $timeout);
		$this->readBinaryResponse();
	}
	
	public function isConnected()
	{
		return $this->socket !== null;
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
		$xml = $request->dump(true);
    	fwrite($this->socket, pack('N', strlen($xml) + 4));
    	$bytes = fwrite($this->socket, $xml);
    	$xml = $this->readBinaryResponse();
    	$response = Zend_Epp_Response::fromXml($request, $xml);
        return $response;
    }

    protected function readBinaryResponse()
    {
    	$len = fread($this->socket, 4);
    	if (strlen($len) < 4) {
    		throw new Zend_Epp_Transport_Exception('Got invalid string length');
    	}
    	list(, $len) = unpack('N', $len);
    	$len -= 4;
    	$left = $len;
    	$xml = '';
    	while ($left > 0 && ! feof($this->socket)) {
    		$chunk = fread($this->socket, ($left > 4096) ? 4096 : $left);
    		$xml .= $chunk;
    		$left = $left - strlen($chunk);
    	}
    	echo "*** GOT RESULT ***\n";
    	echo $xml;
    	echo "\n***\n";
    	return $xml;
    }

    public function disconnect()
    {
        fclose($this->socket);
    }
    
    /**
     * Destructor
     */
    public function __destruct()
    {
    	$this->disconnect();
        parent::__destruct();
    }
}
