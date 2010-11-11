<?php
/**
 * FakturoidPairing.
 *
 * @author Michal Wiglasz <michal.wiglasz@gmail.com>
 * @copyright Copyright (c) 2010 Michal Wiglasz
 */

/**
 * Provides access to bank statement via emails sent by CSOB <www.csob.cz>
 *
 * @author Michal Wiglasz <michal.wiglasz@gmail.com>
 */
class CsobEmailStatement extends EmailStatement {

    const REGEX = '#dne\s+([0-9]{1,2})\.([0-9]{1,2})\.(2[0-9]{3}).+(.|\n)+částka ([+-]?[0-9]+(,[0-9]+)?)(.|\n)+VS\s+([0-9]+)#ui';

    function processEmail($msgno, $headers)
    {
        $from = $headers->from[0];

        if($from->mailbox != 'administrator' || $from->host != 'tbs.csob.cz')
                return NULL;

        $body = $this->fetchBody($msgno);


        $payments = array();
        if(preg_match_all(self::REGEX, $body, $matches, PREG_SET_ORDER))
        {
            foreach($matches as $m)
            {
                $ammount = floatval(str_replace(',', '.', $m[5]));
                if($ammount > 0)
                {
                    $payments[] = (object)array(
                        'variable-symbol' => intval($m[8]),
                        'date' => mktime(0,0,0, $m[2], $m[1], $m[3]),
                        'ammount' => $ammount,
                    );
                }
            }
        }
        
        return count($payments) ? $payments : NULL;
    }
}


