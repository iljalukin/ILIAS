<?php declare(strict_types=1);

/* Copyright (c) 1998-2021 ILIAS open source, Extended GPL, see docs/LICENSE */

class ilMailMimeTransportFactory
{
    protected ilSetting $settings;
    private ilAppEventHandler $eventHandler;

    /**
     * ilMailMimeTransportFactory constructor.
     */
    public function __construct(ilSetting $settings, ilAppEventHandler $eventHandler)
    {
        $this->settings = $settings;
        $this->eventHandler = $eventHandler;
    }

    
    public function getTransport() : ilMailMimeTransport
    {
        if (!(bool) $this->settings->get('mail_allow_external')) {
            return new ilMailMimeTransportNull();
        }

        if ($this->settings->get('mail_smtp_status')) {
            return new ilMailMimeTransportSmtp($this->settings, $this->eventHandler);
        }

        return new ilMailMimeTransportSendmail($this->settings, $this->eventHandler);
    }
}
