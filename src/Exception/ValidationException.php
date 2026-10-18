<?php

namespace Mithridatem\Validation\Exception;

use Exception;

/**
 * @deprecated Validation errors are now returned as arrays by Validator::validate().
 */
class ValidationException extends Exception
{
}
