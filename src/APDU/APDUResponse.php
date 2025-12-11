<?php

namespace YourVendor\LedgerManager\APDU;

class APDUResponse
{
    protected string $data;
    protected string $statusWord;
    protected const SW_OK = '9000';

    public function __construct(string $responseHex)
    {
        $len = strlen($responseHex);
        if ($len < 4) {
            throw new \Exception('Invalid APDU response');
        }

        $this->data = substr($responseHex, 0, -4);
        $this->statusWord = substr($responseHex, -4);
    }

    public function isSuccess(): bool
    {
        return $this->statusWord === self::SW_OK;
    }

    public function getStatusWord(): string
    {
        return $this->statusWord;
    }

    public function getData(): string
    {
        return $this->data;
    }

    public function getDataAsBytes(): string
    {
        return hex2bin($this->data);
    }

    public function getErrorMessage(): string
    {
        return match($this->statusWord) {
            '6E00' => 'Class not supported',
            '6D00' => 'Instruction not supported',
            '6A82' => 'File not found',
            '6985' => 'Conditions not satisfied',
            '6A86' => 'Incorrect parameters P1 P2',
            default => "Unknown error: {$this->statusWord}",
        };
    }
}

