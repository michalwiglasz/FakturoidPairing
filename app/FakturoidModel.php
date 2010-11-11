<?php
/**
 * FakturoidPairing.
 *
 * @author Michal Wiglasz <michal.wiglasz@gmail.com>
 * @copyright Copyright (c) 2010 Michal Wiglasz
 */


/**
 * Provides Fakturoid data.
 * 
 * Based on FakturoidCalc by Jan Javorek <honza@javorek.net>
 *
 * @author Michal Wiglasz <michal.wiglasz@gmail.com>
 * 
 */
class FakturoidModel
{
	/**
	 * @var string
	 */
	protected $username;

	/**
	 * @var string
	 */
	protected $apiKey;

	/**
	 * @var DOMXPath[]
	 */
	private $fileCache = array();

	/**
	 * @param string $username
	 * @param string $apiKey
	 */
	public function __construct($username, $apiKey)
	{
		$this->username = $username;
		$this->apiKey = $apiKey;
	}

	/**
	 * Remote XML XPath provider.
	 *
	 * @param $fileName
	 * @return DOMXPath
	 */
	protected function getFile($fileName)
	{
		if (!empty($this->fileCache[$fileName])) {
			return $this->fileCache[$fileName];
		}
		$xml = $this->fetch($fileName);

		$doc = new DOMDocument();
		$doc->loadXML($xml);
		$this->fileCache[$fileName] = (object)array(
			'doc' => $doc,
			'xpath' => new DOMXPath($doc),
		);
		return $this->fileCache[$fileName];
	}

	/**
	 * Fetches wanted file from server.
	 *
	 * Uses HTTPS authorization and certificate check. See these tutorials:
	 *  - http://www.electrictoolbox.com/php-curl-sending-username-password/
	 *  - http://unitstep.net/blog/2009/05/05/using-curl-in-php-to-access-https-ssltls-protected-sites/
	 *  - http://www.php.net/manual/en/function.curl-error.php#87212
	 *
	 * @param string $file
	 * @return string Response, should be XML.
	 */
	private function fetch($fileName)
	{
		$username = $this->username;
		$apiKey = $this->apiKey;

		if (!$username || !$apiKey) {
			throw new Exception('Chybí uživatelské jméno nebo API klíč.');
		}

		$error = NULL;

		$c = curl_init();
		curl_setopt_array($c, array(
			CURLOPT_URL => "https://$username.fakturoid.cz/$fileName", // url
			CURLOPT_RETURNTRANSFER => TRUE, // return response
			CURLOPT_FAILONERROR => TRUE, // HTTP errors

			CURLOPT_USERPWD => "vera.pohlova:$apiKey", // auth
			CURLOPT_HTTPAUTH => CURLAUTH_BASIC,

                        //FIXME: SSL verification fails for some reason
			CURLOPT_SSL_VERIFYPEER => FALSE /*TRUE*/, // HTTPS, certificate
			CURLOPT_SSL_VERIFYHOST => 2,
			CURLOPT_CAINFO => dirname(__FILE__) . '/fakturoid.crt',
		));
		$response = curl_exec($c);
		if ($response === FALSE) {
			$error = curl_error($c);
		}
		curl_close($c);
		if ($error) {
			throw new Exception($error);
		}
		return $response;
	}

	/**
	 * Fetches all unpaid invoices.
	 *
	 * @return array
	 */
	public function getUnpaidInvoices()
	{
		$list = array();
		$page = 1;
		while($this->getFile("invoices.xml?page=$page")->xpath->evaluate("count(//invoice)")) {
			$nodes = $this->getFile("invoices.xml?page=$page")->xpath->evaluate("//invoice[status!='paid']");
                        foreach($nodes as $n)
                            $list[] = $this->DOMElementToStdClass($n);
			$page++;
		}
		return $list;
	}

        private function DOMElementToStdClass(DOMNode $el)
        {
            $obj = new StdClass;

            if($el instanceof DOMText)
            {
                return mb_trim($el->textContent);
            }

            if(($el->firstChild === $el->lastChild) && (($child = $el->childNodes->item(0)) instanceof DOMText))
            {
                return mb_trim($el->childNodes->item(0)->textContent);
            }

            $hasChildren = FALSE;
            foreach($el->childNodes as $child)
            {
                $hasChildren = TRUE;
                $contents = $this->DOMElementToStdClass($child);
                if($child instanceof DOMText && $contents === '') continue;
                
                if(isset($obj->{$child->nodeName}))
                {
                    if(is_array($obj->{$child->nodeName}))
                    {
                        $obj->{$child->nodeName}[] = $contents;
                    }
                    else
                    {
                        $obj->{$child->nodeName} = array($obj->{$child->nodeName}, $contents);
                    }
                }
                else
                {
                    $obj->{$child->nodeName} = $contents;
                }
            }

            return $hasChildren ? $obj : NULL;
        }
}
