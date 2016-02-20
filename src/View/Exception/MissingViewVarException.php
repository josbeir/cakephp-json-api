<?php
namespace JsonApi\View\Exception;

use Cake\Core\Exception\Exception;

/**
 * Used when a required view variable was not set
 *
 */
class MissingViewVarException extends Exception
{
    /**
     * Message template
     *
     * @var string
     */
    protected $_messageTemplate = 'Required view variable "%s" is not set or is empty.';
}
