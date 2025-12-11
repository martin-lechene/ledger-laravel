<?php

namespace MartinLechene\LedgerManager\APDU;

class APDUCommand
{
    protected string $cla;
    protected string $ins;
    protected string $p1;
    protected string $p2;
    protected ?string $data;
    protected ?int $le;

    public function __construct(
        string $cla,
        string $ins,
        string $p1 = '00',
        string $p2 = '00',
        ?string $data = null,
        ?int $le = null
    ) {
        $this->cla = $cla;
        $this->ins = $ins;
        $this->p1 = $p1;
        $this->p2 = $p2;
        $this->data = $data;
        $this->le = $le;
    }

    public function toBytes(): string
    {
        $bytes = hex2bin($this->cla . $this->ins . $this->p1 . $this->p2);

        if ($this->data) {
            $dataLen = strlen(hex2bin($this->data));
            $bytes .= chr($dataLen);
            $bytes .= hex2bin($this->data);
        } else {
            $bytes .= chr(0x00);
        }

        if ($this->le !== null) {
            $bytes .= chr($this->le);
        }

        return $bytes;
    }

    public function toHex(): string
    {
        return bin2hex($this->toBytes());
    }
}

