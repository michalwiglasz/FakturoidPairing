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
        echo "Unpaid invoices: " . count($invoices) . LF;
        
        if(count($invoices) == 0)
        {
            echo "Nothing to do." . LF;
            return;
        }

        $payments = $statement->getPayments();
        echo "Payments on statement: " . count($payments) . LF;

        if(count($payments) == 0)
        {
            echo "Nothing to do." . LF;
            return;
        }
        

        foreach($invoices as $inv)
        {
            foreach($payments as $pay)
            {
                if($pay->{'variable-symbol'} === intval($inv->{'variable-symbol'}) /* && $pay->amount === floatval($inv->total) */)
                {
                    echo "Invoice $inv->number: {$inv->{'client-name'}}, $inv->total CZK" . LF;
                    echo "  issued on " . date('d.m.Y', strtotime($inv->{'issued-on'})) . ", paid on " . date('d.m.Y', $pay->date) . LF;
                    
                    try
                    {
                        $this->getFakturoidModel()->MarkAsPaid($inv->id);
                        echo "  OK: Invoice succesfully marked as paid.\n" . LF;
                    }
                    catch(Exception $ex)
                    {
                        echo "  ERR: Invoice $inv->number cannot be marked as paid: $ex->message\n" . LF;
                    }

                    break; // next invoice
                }
            }
        }

        echo "Done." . LF;
    }

}


