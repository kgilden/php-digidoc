<?php

/*
 * This file is part of the DigiDoc package.
 *
 * (c) Kristen Gilden <kristen.gilden@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KG\DigiDoc\Soap;

use KG\DigiDoc\Exception\ApiException;

/**
 * Converts SoapFault exceptions to ApiExceptions
 */
class Client extends \SoapClient
{
    const WSDL_PROD = 'https://digidocservice.sk.ee/';
    const WSDL_TEST = 'https://www.openxades.org:9443/?wsdl';

    private static $classmap = array(
        'DataFileAttribute' => '\KG\DigiDoc\Soap\Wsdl\DataFileAttribute',
        'DataFileInfo' => '\KG\DigiDoc\Soap\Wsdl\DataFileInfo',
        'SignerRole' => '\KG\DigiDoc\Soap\Wsdl\SignerRole',
        'SignatureProductionPlace' => '\KG\DigiDoc\Soap\Wsdl\SignatureProductionPlace',
        'CertificatePolicy' => '\KG\DigiDoc\Soap\Wsdl\CertificatePolicy',
        'CertificateInfo' => '\KG\DigiDoc\Soap\Wsdl\CertificateInfo',
        'SignerInfo' => '\KG\DigiDoc\Soap\Wsdl\SignerInfo',
        'ConfirmationInfo' => '\KG\DigiDoc\Soap\Wsdl\ConfirmationInfo',
        'TstInfo' => '\KG\DigiDoc\Soap\Wsdl\TstInfo',
        'RevokedInfo' => '\KG\DigiDoc\Soap\Wsdl\RevokedInfo',
        'CRLInfo' => '\KG\DigiDoc\Soap\Wsdl\CRLInfo',
        'Error' => '\KG\DigiDoc\Soap\Wsdl\Error',
        'SignatureInfo' => '\KG\DigiDoc\Soap\Wsdl\SignatureInfo',
        'SignedDocInfo' => '\KG\DigiDoc\Soap\Wsdl\SignedDocInfo',
        'DataFileData' => '\KG\DigiDoc\Soap\Wsdl\DataFileData',
        'SignatureModule' => '\KG\DigiDoc\Soap\Wsdl\SignatureModule',
        'SignatureModulesArray' => '\KG\DigiDoc\Soap\Wsdl\SignatureModulesArray',
        'DataFileDigest' => '\KG\DigiDoc\Soap\Wsdl\DataFileDigest',
        'DataFileDigestList' => '\KG\DigiDoc\Soap\Wsdl\DataFileDigestList',
    );

    /**
     * @param array  $options
     * @param string $wsdl
     */
    public function __construct(array $options = array(), $wsdl = 'https://www.openxades.org:9443/?wsdl')
    {
        if (!isset($options['classmap'])) {
            $options['classmap'] = array();
        }

        $options['classmap'] = array_merge(self::$classmap, $options['classmap']);

        if ($wsdl === self::WSDL_TEST && !isset($options['stream_context'])) {
            // In newer PHP versions peer verification is enabled by default,
            // but the test wsdl url does not pass verification so we disable it.
            $options['stream_context'] = stream_context_create(array('ssl' => array(
                'verify_peer'      => false,
                'verify_peer_name' => false,
            )));
        }

        parent::__construct($wsdl, $options);
    }

    /**
     * {@inheritDoc}
     */
    public function __soapCall($function_name, $arguments, $options = array(), $input_headers = null, &$output_headers = null)
    {
        try {

            $result = parent::__soapCall($function_name, $arguments, $options, $input_headers, $output_headers);

            // Some requests may return the status as a simple string result ...
            if (is_string($result)) {
                $result = array('Status' => $result);
            }

            // ... and some return an array.
            if ('OK' !== $result['Status']) {
                throw ApiException::createIncorrectStatus($result['Status']);
            }

        } catch (\SoapFault $e) {
            throw ApiException::createFromSoapFault($e);
        }

        return $result;
    }
}
