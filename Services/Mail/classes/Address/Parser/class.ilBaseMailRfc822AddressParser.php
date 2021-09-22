<?php declare(strict_types=1);
/* Copyright (c) 1998-2021 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilBaseMailRfc822AddressParser
 * @author Michael Jansen <mjansen@databay.de>
 */
abstract class ilBaseMailRfc822AddressParser implements ilMailRecipientParser
{
    protected string $addresses = '';
    protected string $installationHost = '';

    /**
     * @param string $addresses A comma separated list of email addresses
     */
    public function __construct(string $addresses, string $installationHost = ilMail::ILIAS_HOST)
    {
        $this->addresses = $addresses;
        $this->installationHost = $installationHost;
    }

    
    public function getAddresses() : string
    {
        return $this->addresses;
    }

    /**
     * A comma separated list of email addresses
     */
    public function setAddresses(string $addresses) : void
    {
        $this->addresses = $addresses;
    }

    /**
     * @param string $addresses A comma separated list of email addresses
     * @return ilMailAddress[]
     */
    abstract protected function parseAddressString(string $addresses) : array;

    /**
     * @inheritdoc
     */
    public function parse() : array
    {
        $addresses = preg_replace('/;/', ',', trim($this->addresses));

        return $this->parseAddressString($addresses);
    }
}
