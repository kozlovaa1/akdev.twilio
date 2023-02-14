<?php

class akdev_twilio extends CModule
{
    public $MODULE_ID = "akdev.twilio";
    public $MODULE_NAME = "Сообщения в Whatsapp через Twilio";
	public $MODULE_VERSION = '1.0.0';

    public function DoInstall()
    {
        RegisterModule($this->MODULE_ID);
    }

    public function DoUninstall()
    {
        UnRegisterModule($this->MODULE_ID);
    }
}
