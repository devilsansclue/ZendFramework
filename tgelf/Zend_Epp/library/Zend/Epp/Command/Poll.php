<?php

class Zend_Epp_Command_Poll extends Zend_Epp_Command
{
    protected $name = 'poll';

    protected function initCommand($params)
    {
        $this->command_node->setAttribute('op', 'req');
        // ACK: $this->command_node->setAttribute('msgID', $params['message_id']);
    }
}

