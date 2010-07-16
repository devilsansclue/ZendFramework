<?php

/**
 * EPP allows using different transports. This abstract class provides their
 * common base functionality.
 */
abstract class Zend_Epp_Transport
{
    /**
     * EPP registry
     *
     * @var Zend_Epp_Registry
     */
    protected $registry;

    /**
     * Each transport MUST implement the send() function
     *
     * @param Zend_Epp_Request 
     */
    abstract public function send(Zend_Epp_Request $request);

    /**
     * The init() function can be overriden by specific transports, it is being
     * called once in the constructor
     *
     * @return void
     */
    protected function init()
    {
    }

    public function isConnected()
    {
        return false;
    }

    /**
     * Constructor
     *
     * @param Zend_Epp_Registry
     */
    final public function __construct(Zend_Epp_Registry $registry)
    {
        $this->registry = $registry;
        $this->init();
    }

    /**
     * Destructor
     */
    public function __destruct()
    {
        // Workaround for memory leaks in PHP when using circular references
        // before 5.3 (PHP bug #33595)
        unset($this->registry);
    }
}

