<?php

namespace Xe\Xecd\Client\Rates\Exception;

class XecdRatesException extends \Exception
{
    /**
     * @var string
     */
    protected $documentation;

    public function __construct($message = '', $code = 0, \Exception $previous = null)
    {
        // Need to re-define this constructor to make $previous optional.
        parent::__construct($message, $code, $previous);
    }

    /**
     * @param int $code
     */
    public function setCode($code)
    {
        $this->code = $code;
    }

    /**
     * @param string $message
     */
    public function setMessage($message)
    {
        $this->message = $message;
    }

    /**
     * @return string
     */
    public function getDocumentation()
    {
        return $this->documentation;
    }

    /**
     * @param string $documentation
     */
    public function setDocumentation($documentation)
    {
        $this->documentation = $documentation;
    }
}
