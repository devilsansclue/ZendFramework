<?php

class Zend_Epp_Response
{
    protected $succeeded = false;
    protected $code;
    protected $msg;
    protected $trid_server;
    protected $trid_client;
    public $xml;
    protected $queued_messages = array();

    protected function __construct(Zend_Epp_Request $request, $xml)
    {
        $dom = new DOMDocument;
        $dom->loadXML($xml);
        if (! $dom instanceof DOMDocument || ! $dom->hasChildNodes())
        {
            $this->code = 0;
            $this->msg = 'XML Parse error';
            return;
        }
        $simple = simplexml_import_dom($dom);
        $root = $dom->documentElement->tagName;
        // TODO: result kann mehrmals vorkommen, evtl mit unterschiedlichen Codes
        if (isset($simple->response->result)) {
            $this->code = (int) $simple->response->result->attributes()->code;
            if ($this->code >= 1000 && $this->code < 2000) {
                $this->succeeded = true;
            }
            $this->msg  = (string) $simple->response->result->msg;
        }
        if (isset($simple->response->msgQ)) {
            foreach ($simple->response->msgQ as $msg) {
                $this->queued_messages[] = $msg;
            }
        }
        foreach (array('resData', 'extension') as $what) {
            if (isset($simple->response->$what)) {
                $namespaces = $simple->response->$what->getNamespaces(true);
                foreach ($namespaces as $ns_prefix => $urn) {
                    foreach ($simple->response->$what->children($urn) as $key => $extension) {
                        $request->getRegistry()->callExtension($urn, $key, $extension);
                        // $this->request?
                    }
                }
            }
        }

        foreach ($simple->result as $nix) {}
        if ($simple->response->trID)
        {
            $this->trid_server = (string) $simple->response->trID->svTRID;
            $this->trid_client = (string) $simple->response->trID->clTRID;
        }
        $this->xml = $simple;
    }

    public function getResponseCode()
    {
        return $this->code;
    }

    public function hasQueuedMessages()
    {
        return ! empty($this->queued_messages);
    }

    public function getResponseMessage()
    {
        return $this->msg;
    }

    public function succeeded()
    {
        return $this->succeeded;
    }

    public function setSuccess($success)
    {
        $this->succeeded = $success;
    }

    // nur test
    public function getQueuedMessages()
    {
        var_dump($this->queued_messages);
    }

    public function dump($return = false)
    {
        if ($return) {
            return $this->xml->saveXML();
        } else {
            echo $this->xml->saveXML();
        }
    }

    public static function fromXml(Zend_Epp_Request $request, $xml)
    {
        $response = new Zend_Epp_Response($request, $xml);
        return $response;
    }

    public function __destruct()
    {
        // Workaround for memory leaks in PHP when using circular references
        // before 5.3 (PHP bug #33595)
        unset($this->request);
    }
}

