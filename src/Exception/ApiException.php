<?php

/*
 * This file is part of the DigiDoc package.
 *
 * (c) Kristen Gilden <kristen.gilden@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KG\DigiDoc\Exception;

use KG\DigiDoc\Container;

/**
 * ApiException is a generic exception for failed api calls.
 */
class ApiException extends DigiDocException
{
    public static $messages = array(
        100 => 'Unspecified fault caused by client.',
        101 => 'Some of the arguments are invalid.',
        102 => 'A mandatory argument is missing.',
        103 => 'Access denied to OCSP - see https://www.sk.ee/en/services/validity-confirmation-services/auth-ocsp/ for more.',
        200 => 'Unspecified fault caused by server.',
        201 => 'User certificate not found.',
        202 => 'Not possible to check user certificate.',
        203 => 'This session is locked by another SOAP request.',
        //300 => @todo phone exception
        //301 => @todo phone exception
        302 => 'User certificate is invalid (OCSP status REVOKED).',
        //303 => @todo phone exception
        304 => 'User certificate is revoked.',
        305 => 'User certificate is expired.',
        413 => 'Request is too long for the service.',
        503 => 'You have too many concurrent requests.',
    );

    public static $exceptions = array(
        103 => 'AccessDeniedException',
        203 => 'SessionLockedException',
        302 => 'CertificateException',
        304 => 'CertificateException',
        305 => 'CertificateException',
        413 => 'RequestTooLongException',
        503 => 'LimitReachedException',
    );

    /**
     * Creates a new ApiException (or an extending exception) based on the
     * error code given by the soap fault.
     *
     * The exact exception type can be found out from ApiException::$exceptions.
     * ApiException is thrown, if the message code is not found or a specific
     * exception type is not defined.
     *
     * @param SoapFault $e
     *
     * @return ApiException
     */
    public static function createFromSoapFault(\SoapFault $e)
    {
        $code = (int) $e->getMessage();

        $message = 'Request failed';

        if (isset(self::$messages[$code])) {
            $message = self::$messages[$code];
            $class   = isset(self::$exceptions[$code]) ? self::$exceptions[$code] : null;

            if ($class) {
                $class = 'KG\\DigigDoc\\Exception\\'.$class;

                return new $class($message, $code, $e);
            }
        }

        return new static($message, $code, $e);
    }

    /**
     * Creates a new ApiException for incorrect response statuses.
     *
     * @param string          $status
     * @param \Exception|null $e
     *
     * @return ApiException
     */
    public static function createIncorrectStatus($status, \Exception $e = null)
    {
        $message = 'Expected server status to be "OK", got "%s" instead';

        return new static(sprintf($message, $status), null, $e);
    }

    /**
     * Creates a new ApiException for non-merged DigiDoc containers.
     *
     * @param Container       $container
     * @param \Exception|null $e
     *
     * @return ApiException
     */
    public static function createNotMerged(Container $contaienr, \Exception $e = null)
    {
        $message = 'The given DigiDoc container must be merged with Api (using Api::merge) before calling this method.';

        return new static($message, null, $e);
    }
}
