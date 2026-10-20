<?php

namespace Mithridatem\Validation\Exception;

use Exception;

/**
 * @deprecated Validator::validate() now returns ValidationError objects grouped by property.
 */
class ValidationException extends Exception
{
}
