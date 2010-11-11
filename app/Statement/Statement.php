<?php
/**
 * FakturoidPairing.
 *
 * @author Michal Wiglasz <michal.wiglasz@gmail.com>
 * @copyright Copyright (c) 2010 Michal Wiglasz
 */

/**
 * Provides access to bank statement
 *
 * @author Michal Wiglasz <michal.wiglasz@gmail.com>
 */
abstract class Statement {

    protected $cfg;

    public function construct($configuration)
    {
        $this->cfg = $configuration;
    }

    abstract public function getPayments();
    
}


