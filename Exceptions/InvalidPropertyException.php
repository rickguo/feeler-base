<?php
/**
 * @link http://www.feeler.top/
 * @copyright Copyright (c) 2019 Rick Guo
 * @license http://www.feeler.top/license/
 */

namespace Feeler\Base\Exceptions;

use Feeler\Base\Errno;

/**
 * Exception thrown if a property is invalid.
 */
class InvalidPropertyException extends LogicException {
    public function __construct($message = "", $code = Errno::UNSPECIFIED, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}