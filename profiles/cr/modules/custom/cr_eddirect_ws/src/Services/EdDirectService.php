<?php
/**
 * @file
 * EdDirect Symfony Service.
 */

namespace Drupal\cr_eddirect_ws\Services;

use Symfony\Component\Serializer\Serializer;

/**
 * Implementation.
 */
class EdDirectService {
  /**
   * Username to authenticate with.
   *
   * @var string $username
   */
  private $username = "comic";

  /**
   * Password to authenticate with.
   *
   * @var string password
   */
  private $password = "BUspuc63buZu";

  /**
   * Options we always pass to the SoapClient.
   *
   * @var array
   */
  private $soapOptions = array(
    "trace" => 1,
    "exceptions" => 1,
    "features" => SOAP_SINGLE_ELEMENT_ARRAYS,
  );

  /**
   * Authentication Endpoint.
   *
   * @var string
   */
  private $authenticateWsdl = "http://webapi.education.co.uk/webservices/spiritdataservice/ServiceAdmin.asmx?WSDL";

  /**
   * Establishment/Search Endpoint.
   *
   * @var string
   */
  private $establishmentWsdl = "http://webapi.education.co.uk/webservices/spiritdataservice/EstablishmentProvider.asmx?WSDL";

  /**
   * Parameter keys that we need to always send to search endpoint.
   *
   * @var array
   */
  private $minimalSearchOptions = array(
    'name' => '',
    'town' => '',
    'postcode' => '',
    'key' => '',
    'typeFilter' => 'BA,BM,BP,BS,DN,I6,IA,IB,IC,IE,IF,IJ,IN,IP,IS,IT,JH,JN,LE,MP,MS,PF,PI,PJ,PP,PS,S6,S8,SG,SH,SL,SN,SP,SR,ST,SU,BN,CM,NM,PN,PT,PU,U1',
  );

  /**
   * Handle to configured cache implementation.
   *
   * @var CacheBackendInterface
   */
  private $cacheHandle;

  /**
   * The serializer service.
   *
   * @var \Symfony\Component\Serializer\Serializer
   */
  protected $serializer;

  /**
   * EdDirectService constructor.
   *
   * @param mixed $cache_handle
   *    The Cache Implementation.
   */
  public function __construct($cache_handle) {
    if (!extension_loaded('soap')) {
      die('SOAP extension not found.');
    }

    $this->cacheHandle = $cache_handle;
  }

  /**
   * Proxy function to _search.
   *
   * @param string $postcode
   *   Postcode of School to search for.
   *
   * @return mixed
   *    Search Results.
   *
   * @throws \SoapFault
   */
  public function searchByPostcode($postcode) {

    return $this->search('postcode', $postcode);
  }

  /**
   * Proxy function to _search.
   *
   * @param string $name
   *   Name of School to search for.
   *
   * @return mixed
   *    Search Results.
   *
   * @throws \SoapFault
   */
  public function searchByName($name) {

    return $this->search('name', $name);
  }

  /**
   * Attempt to authenticate with the service. If successful cache the key.
   *
   * @param bool $revalidate_key
   *   Whether to revalidate the key or not.
   *
   * @return string
   *    Authentication Key.
   *
   * @throws \SoapFault
   */
  public function authenticate($revalidate_key = FALSE) {

    if (TRUE == $revalidate_key) {
      $this->cacheHandle->delete("cr_eddirect_ws_key");
    }

    try {
      $authenticated_key = $this->cacheHandle->get("cr_eddirect_ws_key");

      if (!$authenticated_key) {
        $soap_client = new \SoapClient($this->authenticateWsdl, $this->soapOptions);

        $soap_response = $soap_client->Authenticate(array(
          'username' => $this->username,
          'password' => $this->password,
        )
        );

        if (strcmp($soap_response->sessionTime, "0") == 0) {
          throw new \SoapFault("Could not authenticate");
        }

        $stash_key = (string) $soap_response->AuthenticateResult;
        $stash_until = REQUEST_TIME + $soap_response->sessionTime;
        $this->cacheHandle->set("cr_eddirect_ws_key", $stash_key, $stash_until);

        return $stash_key;
      }
      else {
        return $authenticated_key->data;
      }
    }
    catch (\SoapFault $e) {
      throw new \SoapFault($e->getCode(), $e->getMessage());
    }
  }
  /**
   * Attempt a search for a school given parameters.
   *
   * @param string $search_type
   *   Type of search to make (postcode,name).
   * @param string $search_value
   *   Value for the searchType.
   *
   * @return mixed
   *    Search Result.
   *
   * @throws \SoapFault
   */
  private function search($search_type, $search_value) {

    try {
      $authenticated_key = $this->authenticate();
    }
    catch (\SoapFault $e) {
      return FALSE;
    }

    $search_options = array($search_type => $search_value) + array('key' => $authenticated_key) + $this->minimalSearchOptions;

    try {
      $soap_client = new \SoapClient($this->establishmentWsdl, $this->soapOptions);
      $soap_response = $soap_client->Search($search_options);

      $response = $soap_response->SearchResult->Establishment;

      return $response;
    }
    catch (\SoapFault $e) {
      throw new \SoapFault($e->getCode(), $e->getMessage());
    }
  }

}
