<?php

namespace KG\DigiDoc\Exception;

/**
 * Gets thrown, if the current session is locked (another request with this
 * session is already in progress).
 */
class SessionLockedException extends ApiException
{

}
