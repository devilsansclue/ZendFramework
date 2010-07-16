<?php

class Zend_Epp_Command_Logout extends Zend_Epp_Command
{
    protected $use_trid = false;
    protected $name = 'logout';

    protected function initCommand($params)
    {
        printf("Logging out\n");
    }
}

