<?php
namespace Drupal\cr_eddirect_ws\Services;

use Drupal\Core\Cache\CacheBackendInterface;

class EdDirectService
{
    private $username = "comic";
    private $passowrd = "BUspuc63buZu";

    private $authenticateWSDL = "http://webapi.education.co.uk/webservices/spiritdataservice/ServiceAdmin.asmx?WSDL";

    private $establishmentWSDL = "http://webapi.education.co.uk/webservices/spiritdataservice/EstablishmentProvider.asmx?WSDL";

    private $cacheHandle;

    public function __construct($cacheHandle)
    {
        $this ->cacheHandle = $cacheHandle;
    }

    /**
     * @param bool $revalidateKey
     * @return mixed
     * @throws \SoapFault
     */
    public function authenticate($revalidateKey = false)
    {
        xdebug_disable();

        try {
            $authenticatedKey = $this ->cacheHandle ->get("cr_eddirect_ws_key", false);

            if ($authenticatedKey || false === $revalidateKey || REQUEST_TIME <= $authenticatedKey ->expire) {
                return $authenticatedKey;
            } else {

                $soapClient = new \SoapClient($this ->authenticateWSDL, array("trace" => 1, "exception" => 1));
                $soapResponse = $soapClient ->Authenticate(array(
                        'username' => $this ->username,
                        'password' => $this ->passowrd)
                );

                if (strcmp($soapResponse ->sessionTime, "0") == 0) {
                    throw new \SoapFault("Could not authenticate");
                }

                $this ->cacheHandle ->set(
                    "cr_eddirect_ws_key",
                    $authenticatedKey ->AuthenticateResult,
                    REQUEST_TIME + $soapResponse ->sessionTime * 60
                );
            }

        } catch (\SoapFault $e) {
            throw new \SoapFault($e ->getCode(), $e ->getMessage());
        } finally {
            xdebug_enable();
        }
    }
}