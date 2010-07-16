<?php

/**
 *
 */

abstract class Zend_Epp_Registry
{
    /**
     * EPP registry name
     *
     * @var string
     */
    protected $name;

    /**
     * EPP username
     *
     * @var string
     */
    protected $user;

    /**
     * EPP password
     *
     * @var string
     */
    protected $pass;

    /**
     * URI
     *
     * @var string
     */
    protected $uri;

    protected $trid = 900;
    
    protected $tr_prefix = 'ZF';

    protected $base_extensions = array();
    protected $extensions = array();

    protected $connected = false;

    /**
     * Extensions supported by this Registry
     *
     * @var array
     */
    protected $supported_extensions = array();

    protected $loaded_extensions = array();

    /**
     * EPP Transport
     *
     * @var Zend_Epp_Transport
     */
    protected $transport;

    /**
     * Registry name
     *
     * @var string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Registry username
     *
     * @var string
     */
    public function getUsername()
    {
        return $this->user;
    }

    /**
     * Registry password
     *
     * @var string
     */
    public function getPassword()
    {
        return $this->pass;
    }

    /**
     * Transport URI
     *
     * @var string
     */
    public function getUri()
    {
        return $this->uri;
    }

    // TODO: replace $arguments with just one epp_object
    public function __call($func, $arguments)
    {
        $func[0] = strtoupper($func[0]);
        if ($func === 'Hello') {
            $class = 'Zend_Epp_' . $func;
        } else {
            $class = 'Zend_Epp_Command_' . $func;
        }
        printf("Calling %s\n", $class);
        $request = new $class($this, $arguments);
        $response = $this->send($request);
        if ($func === 'Login') {
            if ($response->succeeded())
            {
                $this->connected = true;
            } else {
                throw new Zend_Epp_Exception('Login failed: ' . $response->getResponseMessage());
            }
        }
        return $response;
    }

    /**
     * Constructor is declared final, each registry class however needs to
     * implement an init() function to create at least a valid transport object
     */
    final public function __construct($user, $pass)
    {
        $this->user = $user;
        $this->pass = $pass;
        $this->init();
        if (! $this->transport instanceof Zend_Epp_Transport)
        {
            throw new Zend_Epp_Exception(
                'Each registry requires a valid transport'
            );
        }
        if ($this->name === null) {
            throw new Zend_Epp_Exception('Each registry requires a valid name');
        }
    }

    protected function isConnected()
    {
        return $this->connected;
    }

    /**
     * ..
     *
     * @param  Zend_Epp_Request
     * @return Zend_Epp_Response
     */
    public function send(Zend_Epp_Request $request)
    {
        if (! $request instanceof Zend_Epp_Command_Login
            && ! $request instanceof Zend_Epp_Hello) {
            if (! $this->isConnected()
                && ! $request instanceof Zend_Epp_Command_Logout)
            {
                $this->hello();
                // TODO: commands shall accept only one param, an epp_object
                $this->login(array(
                    'username' => $this->user,
                    'password' => $this->pass
                ));
            }
        }

        return $request->send();
    }

    protected function registerExtension($class)
    {
        $ext = new $class($this);
        if (isset($this->supported_extensions[$ext->getUrn()])) {
            throw new Zend_Epp_Exception(sprintf(
                'Cannot register the same extension twice (%s | %s)',
                $class,
                $ext->getUrn()
            ));
        }
        $this->supported_extensions[$ext->getUrn()] = $ext;
    }

    /**
     * @return Zend_Epp_Transport
     */
    public function getTransport()
    {
        return $this->transport;
    }

    /**
     * @return string
     */
    public function getNextTrId()
    {
        $this->trid++;
        return sprintf(
            '%s%14d%05d%05d',
            $this->tr_prefix,
            date('YmdHis'),
            posix_getpid(),
            $this->trid
        );
    }

    /**
     * Initialization function, has to create at least a valid transport object
     * and load (register?) all supported extensions
     */
    abstract protected function init();

    public function fetchQueuedMessage()
    {
        exit;
        // TODO: not here
        $request = new Zend_Epp_Command_Poll($this);
        $response = $request->send(true);
        if ($this->storePolledMessage($response)) {
            // $this->ackQueuedMessage($response->
        }
        /*
        // TODO:
        if ($this->processPolledMessage($response)) {
            // $this->poll(array('message_id' => $response->..id)); -> ACK!
        }
        */
        return $response->hasQueuedMessages();
    }

    // TODO: replace with (external) callback function
    protected function storePolledMessage(Zend_Epp_Response $response)
    {
        exit;
        $db = Zend_Epp_Db::getDb();
        $response->getQueuedMessages();
        return $db->insert('epp_polling', array(
            'registrar' => $this->name,
            'retrieved_at' => date('Y-m-d H:i:s'),
            'message' => $response->getResponseMessage(),
            'xml' => $response->dump(true)
        ));
    }

    // TODO: remove
    public function ackQueuedMessage($id)
    {
        $request = new Zend_Epp_Request_Poll($this, array('message_id' => $id));
        $response = $request->send();
        return $response;
    }

    // sollte passen
    public function extensionIsSupported($urn)
    {
        return isset($this->supported_extensions[(string) $urn]);
    }

    // nope. sollen sich selbst registrieren
    public function callExtension($urn, $key, $xml)
    {
        return $this->loaded_extensions[(string) $urn]->call($key, $xml);
    }

    public function loadExtension($urn)
    {
        $urn = (string) $urn;
        if (! $this->extensionIsSupported($urn)) {
            throw new Zend_Epp_Exception(sprintf(
                'Trying to load unsupported extension: %s',
                $urn
            ));
        }
        $this->loaded_extensions[$urn] = $this->supported_extensions[$urn];
        $this->loaded_extensions[$urn]->register();
        return;
    }

    public function getExtensions()
    {
        return $this->extensions;
    }

    public function addExtension($extension)
    {
        $this->extensions[] = (string) $extension;
    }

    public function getBaseExtensions()
    {
        return $this->base_extensions;
    }

    public function addBaseExtension($extension)
    {
        $this->base_extensions[] = (string) $extension;
    }

    public function __destruct()
    {
        if ($this->isConnected()) {
            $this->logout();
        }
    }
}

