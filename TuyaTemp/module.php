<?php

declare(strict_types=1);
require_once __DIR__ . '/../libs/TuyaModule.php';

class TuyaSwitch extends TuyaModule
{
    public static $Variables = [
        ['Tuya_Temperature', 'Temperature', VARIABLETYPE_FLOAT, '~Temperature', [], '', false, true], 
		['Tuya_Humidity', 'Humidity', VARIABLETYPE_FLOAT, '~Humidity.F', [], '', false, true],
		['Tuya_Battery', 'Battery', VARIABLETYPE_INTEGER, '~Battery.100', [], '', false, true],
    ];

    public function RequestAction($Ident, $Value)
    {
       
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
                if (fnmatch('*/DP1', $Buffer->Topic)) {
                    SetValue($this->GetIDForIdent('Tuya_Temperature'), $Buffer->Payload /10.0);
                
                }
				if (fnmatch('*/DP2', $Buffer->Topic)) {
                    SetValue($this->GetIDForIdent('Tuya_Humidity'), $Buffer->Payload);
                
                }
                if (fnmatch('*/DP3', $Buffer->Topic)) {
                    switch ($Buffer->Payload) {
                        case 'low':
                            SetValue($this->GetIDForIdent('Tuya_Battery'), 5);
                            break;
                        case 'middle':
                            SetValue($this->GetIDForIdent('Tuya_Battery'), 50);
                            break;
						case 'high':
                            SetValue($this->GetIDForIdent('Tuya_Battery'), 100);
                            break;
                    }
				
                }
            }
        }
    }

}