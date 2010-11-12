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

    /**
     *
     * @var resource IMAP connection
     */
    protected $connection;

    /**
     *
     * @var timestamp Check emails only since this date
     */
    protected $checkToDate;

    /**
     * @param array $configuration
     */
    public function __construct($configuration)
    {
        parent::construct($configuration);
        $this->serverReference = $configuration['mail_server'];

        $this->connection = imap_open(
                '{' . $configuration['mail_server'] . '}' . $configuration['mail_mailbox'],
                $configuration['mail_username'],
                $configuration['mail_password']
        );

        if(!$this->connection)
                throw new Exception('Nepovedlo se připojit k poštovnímu serveru.');

        $this->checkToDate = strtotime($configuration['mail_checktodate']);
    }

    /**
     * Return list of payments on the statement
     *
     * @return array
     */
    public function getPayments() {
        $payments = array();

        $messageCount = imap_num_msg($this->connection);

        for($i = 1; $i <= $messageCount; $i++)
        {
            $headers = imap_header($this->connection, $i);
            
            if(strtotime($headers->date) < $this->checkToDate) continue;

            if(($p = $this->processEmail($i, $headers)))
            {
                if(is_array($p))
                    $payments = array_merge($payments, $p);
                else
                    $payments[] = $p;
            }
        }

        return $payments;
    }

    /**
     * Fetches email body
     *
     * @param int $msgno Message number
     * @return string
     */
    protected function fetchBody($msgno)
    {
        $body = imap_fetchbody($this->connection, $msgno, '1', FT_PEEK);

        $structure = imap_fetchstructure($this->connection, $msgno);
        $encoding = $structure->parts[0]->encoding;
        $charset = null;
        foreach($structure->parts[0]->parameters as $param)
        {
            if($param->attribute == 'CHARSET')
            {
                $charset = $param->value;
                break;
            }
        }
        
        if($encoding == ENCBASE64)
            $body = imap_base64($body);
        elseif ($encoding == ENCQUOTEDPRINTABLE)
            $body = imap_qprint($body);

        if($charset)
        {
            $body = iconv($charset, "UTF-8", $body);
        }

        return $body;
    }

    /**
     * Checks email for payment
     */
    abstract function processEmail($msgno, $headers);
}


