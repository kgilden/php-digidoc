<?php

/*
 * This file is part of the DigiDoc package.
 *
 * (c) Kristen Gilden <kristen.gilden@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KG\DigiDoc\Ocsp;

use KG\DigiDoc\Exception\FileNotFoundException;
use KG\DigiDoc\Exception\OcspRequestException;
use KG\DigiDoc\X509\Cert;
use Symfony\Component\Process\Process;

/**
 * A wrapper to make OCSP requests using openssl as an external program since
 * the OpenSSL PHP module doesn't cover OCSP requests.
 */
class Responder
{
    /**
     * @var string
     */
    private $url;

    /**
     * @var string
     */
    private $pathToCert;

    /**
     * @var Process
     */
    private $process;

    /**
     * @var string
     */
    private $tempDir;

    /**
     * @param string       $url
     * @param string       $pathToCert
     * @param null|string  $tempDir    Path to a directory where temporary files can be written (system temp by default)
     * @param null|Process $process    The Process object to use (i.e. for testing)
     */
    public function __construct($url, $pathToCert, $tempDir = null, Process $process = null)
    {
        if (!is_file($pathToCert)) {
            throw new FileNotFoundException();
        }

        $this->url = $url;
        $this->pathToCert = $pathToCert;
        $this->tempDir = $tempDir ?: sys_get_temp_dir();
        $this->process = $process ?: new Process('');
    }

    /**
     * @param Request $request
     *
     * @return Response
     */
    public function handle(Request $request)
    {
        $response = $this->makeRequest($request);

        if (!$response->isSignedBy(new Cert(file_get_contents($this->pathToCert)))) {
            throw new \RuntimeException('The signature does not match.');
        }

        return $response;
    }

    /**
     * @param Request $request
     *
     * @return Response
     */
    private function makeRequest(Request $request)
    {
        $pathToResponse = tempnam($this->tempDir, 'php-digidoc');

        $process = $this->createProcess($request, $pathToResponse);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new OcspRequestException('OCSP Verification failed: ' . $process->getErrorOutput());
        }

        return new Response(file_get_contents($pathToResponse));
    }

    /**
     * @param Request $request
     * @param string  $pathToResponse
     *
     * @return Process
     */
    private function createProcess(Request $request, $pathToResponse)
    {
        $commandLine = sprintf(
            'openssl ocsp -issuer %s -cert %s -url %s -VAfile %s -respout %s',
            escapeshellarg($request->getPathToIssuerCert()),
            escapeshellarg($request->getPathToClientCert()),
            escapeshellarg($this->url),
            escapeshellarg($this->pathToCert),
            escapeshellarg($pathToResponse)
        );

        $process = $this->process;
        $process->setCommandLine($commandLine);

        return $process;
    }
}
