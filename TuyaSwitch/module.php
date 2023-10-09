<?php

declare(strict_types=1);
require_once __DIR__ . '/../libs/TuyaModule.php';

class TuyaSwitch extends TuyaModule
{
    public static $Variables = [
        ['Tuya_State', 'State', VARIABLETYPE_BOOLEAN, '~Switch', [], '', true, true] 
    ];

    public function RequestAction($Ident, $Value)
    {
        switch ($Ident) {
            case 'Tuya_State':
                $this->SwitchMode($Value);
                break;
            }
    }

    public function ReceiveData($JSONString)
    {
        if (!empty($this->ReadPropertyString('MQTTTopic'))) {
            $Buffer = json_decode($JSONString);
            $this->SendDebug('JSON', $Buffer, 0);

            //Für MQTT Fix in IPS Version 6.3
            if (IPS_GetKernelDate() > 1670886000) {
                $Buffer->Payload = utf8_decode($Buffer->Payload);
            }

            if (property_exists($Buffer, 'Topic')) {
                if (fnmatch('*/DP0', $Buffer->Topic)) {
                    switch ($Buffer->Payload) {
                        case 'False':
                            SetValue($this->GetIDForIdent('Tuya_State'), 0);
                            break;
                        case 'True':
                            SetValue($this->GetIDForIdent('Tuya_State'), 1);
                            break;
                    }
                }
                
            }
        }
    }

    private function SwitchMode(bool $Value)
    {
        $Topic = $this->ReadPropertyString('MQTTTopic') . '/DP1/command';
        if ($Value) {
            $Payload = 'true';
        } else {
            $Payload = 'false';
        }
        $this->sendMQTT($Topic, $Payload);
    }
}