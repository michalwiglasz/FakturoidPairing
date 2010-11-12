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

    /**
     *
     * @var array configuration
     */
    protected $cfg;

    /**
     * @param array $configuration
     */
    public function construct($configuration)
    {
        $this->cfg = $configuration;
    }

    /**
     * Return list of payments on the statement
     *
     * @return array
     */
    abstract public function getPayments();
    
}


