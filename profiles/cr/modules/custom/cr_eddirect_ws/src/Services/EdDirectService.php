<?php
namespace Drupal\cr_eddirect_ws\Services;

use Drupal\Core\Cache\CacheBackendInterface;

class EdDirectService
{
    /**
     * @var string $username
     */
    private $username = "comic";

    /**
     * @var string password
     */
    private $password = "BUspuc63buZu";

    /**
     * @var array Options we always pass to the SoapClient
     */
    private $soapOptions = array(
        "exceptions" => 1,
        "features" => SOAP_SINGLE_ELEMENT_ARRAYS
    );

    /**
     * @var string Authentication Endpoint
     */
    private $authenticateWSDL = "http://webapi.education.co.uk/webservices/spiritdataservice/ServiceAdmin.asmx?WSDL";

    /**
     * @var string Establishment/Search Endpoint
     */
    private $establishmentWSDL = "http://webapi.education.co.uk/webservices/spiritdataservice/EstablishmentProvider.asmx?WSDL";

    /**
     * @var array Parameter keys that we need to always send to search endpoint
     */
    private $minimalSearchOptions = array(
        'name' => '',
        'town' => '',
        'postcode' => '',
        'key' => '',
        'typeFilter' => 'BA,BM,BP,BS,DN,I6,IA,IB,IC,IE,IF,IJ,IN,IP,IS,IT,JH,JN,LE,MP,MS,PF,PI,PJ,PP,PS,S6,S8,SG,SH,SL,SN,SP,SR,ST,SU,BN,CM,NM,PN,PT,PU,U1'
    );

    /**
     * @var CacheBackendInterface Handle cache implementation
     */
    private $cacheHandle;

    /**
     * EdDirectService constructor.
     * @param $cacheHandle
     */
    public function __construct($cacheHandle)
    {
        $this ->cacheHandle = $cacheHandle;
    }

    /**
     * Proxy function to _search
     *
     * @param string $postcode Postcode of School to search for
     * @return mixed
     * @throws \SoapFault
     */
    public function searchByPostcode($postcode)
    {
        return $this ->_search('postcode', $postcode);
    }

    /**
     * Proxy function to _search
     *
     * @param string $name  Name of School to search for
     * @return mixed
     * @throws \SoapFault
     */
    public function searchByName($name)
    {
        return $this ->_search('name', $name);
    }

    /**
     * Attempt to authenticate with the service. If successful cache the key
     *
     * @param bool $revalidateKey Whether to revalidate the key or not
     * @return string
     * @throws \SoapFault
     */
    public function authenticate($revalidateKey = false)
    {
        if (true == $revalidateKey) {
            $this ->cacheHandle ->delete("cr_eddirect_ws_key");
        }

        try {
            $authenticatedKey = $this ->cacheHandle ->get("cr_eddirect_ws_key");

            if (!$authenticatedKey) {
                $soapClient = new \SoapClient($this ->authenticateWSDL, $this ->soapOptions);

                $soapResponse = $soapClient ->Authenticate(array(
                        'username' => $this ->username,
                        'password' => $this ->password)
                );

                if (strcmp($soapResponse ->sessionTime, "0") == 0) {
                    throw new \SoapFault("Could not authenticate");
                }

                $stashKey = (string)$soapResponse ->AuthenticateResult;
                $stashUntil = REQUEST_TIME + $soapResponse ->sessionTime;
                $this ->cacheHandle ->set("cr_eddirect_ws_key", $stashKey, $stashUntil);

                return $stashKey;
            } else {
                return $authenticatedKey ->data;
            }
        } catch (\SoapFault $e) {
            throw new \SoapFault($e ->getCode(), $e ->getMessage());
        }
    }
    /**
     * Attempt a search for a school given parameters.
     *
     * @param string $searchType Type of search to make (postcode,name)
     * @param string $searchValue Value for the searchType
     * @return mixed
     * @throws \SoapFault
     */
    private function _search($searchType, $searchValue)
    {
        try {
            $authenticatedKey = $this ->authenticate();
        } catch (\SoapFault $e) {
            return false;
        }

        $searchOptions = array($searchType => $searchValue) + array('key' => $authenticatedKey) + $this ->minimalSearchOptions;

        try {
            $soapClient = new \SoapClient($this ->establishmentWSDL, $this ->soapOptions);
            $soapResponse = $soapClient ->Search($searchOptions);

            return $soapResponse ->SearchResult ->Establishment;
        } catch (\SoapFault $e) {
            throw new \SoapFault($e ->getCode(), $e ->getMessage());
        }
    }
}