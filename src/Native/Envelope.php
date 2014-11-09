<?php

/*
 * This file is part of the DigiDoc package.
 *
 * (c) Kristen Gilden <kristen.gilden@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KG\DigiDoc\Native;

use DOMDocument;

class Envelope
{
    const BDOC21 = 0;
    const SHA256 = 'sha256';

    private $files;

    private $stamps;

    private $options;

    public function __construct(array $files, array $options = array())
    {
        $this->files = $files;
        $this->stamps = array();
        $this->options = array_merge(array(
            'format' => self::BDOC21,
            'algo' => self::SHA256,
        ), $options);
    }

    public function signBy(Signer $signer)
    {
        // @todo what other stuff should be added to the view and where
        //       should they be added?
        return $this->stamps[] = new Stamp($signer, $this->createView($signer));
    }

    public function write($path)
    {
        throw new \Exception('Implement me!');
    }

    private function createView(Signer $signer)
    {
        $version = $this->options['format'];

        if ($version !== self::BDOC21) {
            throw new \Exception('Unsupported format');
        }

        return BDocView::fromSignerAndFiles($signer, $this->files);
    }
}
