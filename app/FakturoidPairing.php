<?php
/**
 * FakturoidPairing.
 *
 * @author Michal Wiglasz <michal.wiglasz@gmail.com>
 * @copyright Copyright (c) 2010 Michal Wiglasz
 */

/**
 * 
 *
 * @author Michal Wiglasz <michal.wiglasz@gmail.com>
 */
class FakturoidPairing {
    protected $cfg = array();

    /**
     * @var FakturoidModel
     */
    protected $model;

    public function __construct($configurationFile)
    {
            $file = dirname(__FILE__) . '/../data/' . $configurationFile;
            if (!is_file($file)) {
                    throw new Exception('Soubor s konfigurací neexistuje.');
            }
            $this->cfg = parse_ini_file($file, TRUE);
    }

    /**
     * Lazy model provider.
     *
     * @return FakturoidModel
     */
    protected function getFakturoidModel()
    {
        if (!$this->model) {
            if (empty($this->cfg['username']) || empty($this->cfg['api_key'])) {
                throw new Exception('V konfiguraci chybí uživatelské jméno nebo API klíč.');
            }
            $this->model = new FakturoidModel($this->cfg['username'], $this->cfg['api_key']);
        }
        return $this->model;
    }

    public function run()
    {
        $cls = $this->cfg['statement_type'] . 'Statement';
        if(!class_exists($cls)) throw new Exception ('Neplatný typ zdroje plateb (' . $this->cfg['statement_type'] . '.');

        $statement = new $cls($this->cfg);
        
        $invoices = $this->getFakturoidModel()->getUnpaidInvoices();
        $payments = $statement->getPayments();

        dump($payments);
    }

}


