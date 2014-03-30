<?php

namespace KG\DigiDoc;

/**
 * Maintains a communication interchange between the system and the external
 * service. Most of the api calls require the session. Over the course of
 * serveral HTTP requests (between the end user and this system) you should
 * store this in PHP's session.
 */
class Session
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @param integer $id
     */
    public function __construct($id)
    {
        $this->id = $id;
    }

    /**
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }
}
