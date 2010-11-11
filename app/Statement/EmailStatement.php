<?php
/**
 * FakturoidPairing.
 *
 * @author Michal Wiglasz <michal.wiglasz@gmail.com>
 * @copyright Copyright (c) 2010 Michal Wiglasz
 */

/**
 * Abstract class which allows fetch payments from emails
 *
 * @author Michal Wiglasz <michal.wiglasz@gmail.com>
 */
abstract class EmailStatement extends Statement {

    public function __construct($configuration)
    {
        parent::construct($configuration);
        //$serverReference, $mailbox, $username, $password
    }
    
    public function getPayments() {

    }
}


