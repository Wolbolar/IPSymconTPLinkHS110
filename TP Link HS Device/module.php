<?

	class TPLinkHS110 extends IPSModule
	{
		
		public function Create()
		{
			//Never delete this line!
			parent::Create();
			$this->RegisterPropertyString("Host", "");
            $this->RegisterPropertyInteger("model", 1);
            $this->RegisterPropertyInteger("stateinterval", 0);
            $this->RegisterPropertyInteger("systeminfointerval", 0);
            $this->RegisterTimer('StateUpdate', 0, 'TPLHS_StateTimer('.$this->InstanceID.');');
            $this->RegisterTimer('SystemInfoUpdate', 0, 'TPLHS_SystemInfoTimer('.$this->InstanceID.');');
		}
	
		public function ApplyChanges()
		{
			//Never delete this line!
			parent::ApplyChanges();
            $this->RegisterVariableBoolean("State", "Status", "~Switch", 1);
            $this->EnableAction("State");
			$model = $this->ReadPropertyInteger("model");
			if($model == 2)
            {
                $this->RegisterVariableFloat("Voltage", "Spannung", "~Volt.230", 2);
                $this->EnableAction("Voltage");
                $this->RegisterVariableFloat("Power", "Leistung", "~Power", 3);
                // ~Electricity
                $this->EnableAction("Power");
                $this->RegisterVariableFloat("Current", "Aktuell", "~Watt.3680", 4);
                $this->EnableAction("Current");
            }
			$this->ValidateConfiguration();	
		}
		
		private function ValidateConfiguration()
		{
            // Types HS100, HS105, HS110, HS200
            $host = $this->ReadPropertyString('Host');

            //IP TP Link check
            if (!filter_var($host, FILTER_VALIDATE_IP) === false)
            {
                //IP ok
                $ipcheck = true;
            }
            else
            {
                $ipcheck = false;
            }

            //Domain TP Link Device check
            if(!$this->is_valid_localdomain($host) === false)
            {
                //Domain ok
                $domaincheck = true;
            }
            else
            {
                $domaincheck = false;
            }

            if ($domaincheck === true || $ipcheck === true)
            {
                $hostcheck = true;
                $this->SetStatus(102);
            }
            else
            {
                $hostcheck = false;
                $this->SetStatus(203); //IP Adresse oder Host ist ungültig
            }
            $this->SetStateInterval($hostcheck);
            $this->SetSystemInfoInterval($hostcheck);
		}

        public function StateTimer()
        {
            $this->GetSystemInfo();
        }

        public function SystemInfoTimer()
        {
            $this->GetRealtimeCurrent();
        }

        protected function SetStateInterval($hostcheck)
        {
            if($hostcheck)
            {
                $devicetype = $this->ReadPropertyInteger("model");
                $stateinterval = $this->ReadPropertyInteger("stateinterval");
                $interval = $stateinterval * 1000;
                if($devicetype == 2)
                {
                    $this->SetTimerInterval("StateUpdate", $interval);
                }
                else
                {
                    $this->SetTimerInterval("StateUpdate", $interval);
                }
            }
        }

        protected function SetSystemInfoInterval($hostcheck)
        {
            if($hostcheck)
            {
                $devicetype = $this->ReadPropertyInteger("model");
                $infointerval = $this->ReadPropertyInteger("systeminfointerval");
                $interval = $infointerval * 60 * 1000;
                if($devicetype == 2)
                {
                    $this->SetTimerInterval("SystemInfoUpdate", $interval);
                }
                else
                {
                    $this->SetTimerInterval("SystemInfoUpdate", 0);
                }
            }
        }

        protected function decrypt($cypher_text, $first_key = 0xAB)
        {
            $header        = substr($cypher_text, 0, 4);
            $header_length = unpack('N*', $header)[1];
            $cypher_text   = substr($cypher_text, 4);
            $buf           = unpack('c*', $cypher_text );
            $key           = $first_key;
            //$nextKey = "";
            for ($i = 1; $i < count($buf)+1; $i++)
                {
                    $nextKey = $buf[$i];
                    $buf[$i] = $buf[$i] ^ $key;
                    $key     = $nextKey;
                }
            $array_map     = array_map('chr', $buf);
            $clear_text    = implode('', $array_map);
            $cypher_length = strlen($clear_text);
            if ($header_length !== $cypher_length)
                {
                    trigger_error("Length in header ({$header_length}) doesn't match actual message length ({$cypher_length}).");
                }
            return $clear_text;
        }

        protected function encrypt ( $clear_text , $first_key = 0xAB )
        {
            $buf = unpack('c*', $clear_text );
            $key = $first_key;
            for ($i = 1; $i < count($buf)+1; $i++)
            {
                $buf[$i] = $buf[$i] ^ $key;
                $key = $buf[$i];
            }
            $array_map  = array_map('chr', $buf);
            $clear_text = implode('', $array_map);
            $length     = strlen($clear_text);
            $header     = pack('N*', $length);
            return $header . $clear_text;
        }

        protected function connectToSocket()
        {
            $host = $this->ReadPropertyString('Host');
            if(!($sock1 = socket_create(AF_INET, SOCK_STREAM, SOL_TCP)))
                {
                    $errorcode = socket_last_error();
                    $errormsg  = socket_strerror($errorcode);
                    die("Couldn't create socket: [$errorcode] $errormsg \n");
                }
            $this->SendDebug("TP Link:","Create Socket",0);

            //Connect socket to remote server
            if(!socket_connect($sock1 , $host ,9999))
                {
                    $errorcode = socket_last_error();
                    $errormsg  = socket_strerror($errorcode);
                    die("Could not connect: [$errorcode] $errormsg \n");
                }
            $this->SendDebug("TP Link:","Connection established",0);
            return $sock1;
        }

        protected function sendToSocket($messageToSend, $sock)
        {
            $message = $this->encrypt($messageToSend);

            //Send the message to the server
            if(!socket_send ($sock , $message , strlen($message) , 0)){
                $errorcode = socket_last_error();
                $errormsg  = socket_strerror($errorcode);
                die("Could not send data: [$errorcode] $errormsg \n");
            }
            $this->SendDebug("TP Link:","Message send successfully",0);
        }

        protected function getResultFromSocket($sock)
        {
            //Now receive reply from server
            $buf = "";
            if(socket_recv ( $sock , $buf , 2048 , MSG_WAITALL ) === FALSE){
                $errorcode = socket_last_error();
                $errormsg = socket_strerror($errorcode);

                die("Could not receive data: [$errorcode] $errormsg \n");
            }
            return $buf;
        }


        protected function SendToTPLink($command)
        {
            $sock   = $this->connectToSocket();
            $this->sendToSocket($command, $sock);
            $buf    = $this->getResultFromSocket($sock);
            $result = json_decode($this->decrypt($buf));
            socket_close($sock);
            return $result;
        }

        //System Commands
        //========================================

        // Get System Info (Software & Hardware Versions, MAC, deviceID, hwID etc.)
        public function GetSystemInfo()
        {
            #########################################################################################################################################
            # Command to Send: {"system":{"get_sysinfo":{"err_code":0,"sw_ver":"1.1.4 Build 170417
            # Expexted answer: Rel.145118","hw_ver":"1.0","type":"IOT.SMARTPLUGSWITCH","model":"HS110(EU)","mac":"70:4F:57:1B:DD:C1","deviceId":"8006E91F9E48110F07356B2A68FBF29018EC5159","hwId":"45E29DA8382494D2E82688B52A0B2EB5","fwId":"851E8C7225C3220531D5A3AFDACD9098","oemId":"3D341ECE302C0642C99E31CE2430544B","alias":"Egon","dev_name":"Wi-Fi Smart Plug With Energy Monitoring","icon_hash":"","relay_state":1,"on_time":22954,"active_mode":"schedule","feature":"TIM:ENE","updating":0,"rssi":-45,"led_off":0,"latitude":48.123456,"longitude":11.123456}}}

            $command = '{"system":{"get_sysinfo":{}}}';
            $result = $this->SendToTPLink($command);
            /*
            if($debug)
            {
                echo " --> Message received successfully - Lenght: " . strlen($buf) . " - decrypt:\n" . decrypt($buf) . "\n";
                print_r((array) json_decode(decrypt($buf)));
                $result->system->get_sysinfo->model . " - ". $result->system->get_sysinfo->alias . " - " . $result->system->get_sysinfo->relay_state . " (1 = on / 0 = 0ff) - Values: ";
            }
            */
            SetValueBoolean($this->GetIDForIdent("State"), boolval($result->system->get_sysinfo->relay_state));
            return $result;
        }

        // Reboot
        public function Reboot()
        {
            $command = '{"system":{"reboot":{"delay":1}}}';
            $result = $this->SendToTPLink($command);
            return $result;
        }

        // Power On
        public function PowerOn()
        {
            $command = '{"system":{"set_relay_state":{"state":1}}}';
            $result = $this->SendToTPLink($command);
            return $result;
        }

        // Power Off
        public function PowerOff()
        {
            $command = '{"system":{"set_relay_state":{"state":0}}}';
            $result = $this->SendToTPLink($command);
            return $result;
        }

        // Reset (To Factory Settings)
        public function Reset()
        {
            $command = '{"system":{"reset":{"delay":1}}}';
            $result = $this->SendToTPLink($command);
            return $result;
        }

        // Turn Off Device LED (Night mode)
        public function NightMode()
        {
            $command = '{"system":{"set_led_off":{"off":1}}}';
            $result = $this->SendToTPLink($command);
            return $result;
        }

        // Set Device Alias
        public function SetDeviceAlias(string $alias)
        {
            $command = '{"system":{"set_dev_alias":{"alias":"'.$alias.'"}}}';
            $result = $this->SendToTPLink($command);
            return $result;
        }

        // Set MAC Address
        public function SetMACAddress(string $mac)
        {
            // {"system":{"set_mac_addr":{"mac":"50-C7-BF-01-02-03"}}}
            $command = '{"system":{"set_mac_addr":{"mac":"'.$mac.'"}}}';
            $result = $this->SendToTPLink($command);
            return $result;
        }

        // Set Device ID
        public function SetDeviceID(string $deviceid)
        {
            $command = '{"system":{"set_device_id":{"deviceId":"'.$deviceid.'"}}}';
            $result = $this->SendToTPLink($command);
            return $result;
        }

        // Set Hardware ID
        public function SetHardwareID(string $hardwareid)
        {
            $command = '{"system":{"set_hw_id":{"hwId":"'.$hardwareid.'"}}}';
            $result = $this->SendToTPLink($command);
            return $result;
        }

        // Set Location
        public function SetLocation(float $longitude, float $latitude)
        {
            // {"system":{"set_dev_location":{"longitude":6.9582814,"latitude":50.9412784}}}
            $command = '{"system":{"set_dev_location":{"longitude":'.$longitude.',"latitude":'.$latitude.'}}}';
            $result = $this->SendToTPLink($command);
            return $result;
        }

        // Perform uBoot Bootloader Check
        public function BootloaderCheck()
        {
            $command = '{"system":{"test_check_uboot":null}}';
            $result = $this->SendToTPLink($command);
            return $result;
        }

        // Get Device Icon
        public function GetDeviceIcon()
        {
            $command = '{"system":{"get_dev_icon":null}}';
            $result = $this->SendToTPLink($command);
            return $result;
        }

        // Set Device Icon
        public function SetDeviceIcon(string $icon, string $hash)
        {
            $command = '{"system":{"set_dev_icon":{"icon":"'.$icon.'","hash":"'.$hash.'"}}}';
            $result = $this->SendToTPLink($command);
            return $result;
        }

        // Set Test Mode (command only accepted coming from IP 192.168.1.100)
        /*
        public function SetTestMode()
        {
            $command = '{"system":{"set_test_mode":{"enable":1}}}';
            $result = $this->SendToTPLink($command);
            return $result;
        }
        */

        // Download Firmware from URL
        public function DownloadFirmware(string $url)
        {
            $command = '{"system":{"download_firmware":{"url":"http://'.$url.'"}}}';
            $result = $this->SendToTPLink($command);
            return $result;
        }

        // Get Download State
        public function GetDownloadState()
        {
            $command = '{"system":{"get_download_state":{}}}';
            $result = $this->SendToTPLink($command);
            return $result;
        }

        // Flash Downloaded Firmware
        public function FlashDownloadedFirmware()
        {
            $command = '{"system":{"flash_firmware":{}}}';
            $result = $this->SendToTPLink($command);
            return $result;
        }

        // Check Config
        public function CheckConfig()
        {
            $command = '{"system":{"check_new_config":null}}';
            $result = $this->SendToTPLink($command);
            return $result;
        }

        // WLAN Commands
        // ========================================

        // Scan for list of available APs
        public function ScanAP()
        {
            $command = '{"netif":{"get_scaninfo":{"refresh":1}}}';
            $result = $this->SendToTPLink($command);
            return $result;
        }

        // Connect to AP with given SSID and Password
        public function ConnectAP(string $ssid, string $password)
        {
            $command = '{"netif":{"set_stainfo":{"ssid":"'.$ssid.'","password":"'.$password.'","key_type":3}}}';
            $result = $this->SendToTPLink($command);
            return $result;
        }

        // Cloud Commands
        // ========================================

        // Get Cloud Info (Server, Username, Connection Status)
        public function GetCloudInfo()
        {
            $command = '{"cnCloud":{"get_info":null}}';
            $result = $this->SendToTPLink($command);
            return $result;
        }

        // Get Firmware List from Cloud Server
        public function GetFirmwareList()
        {
            $command = '{"cnCloud":{"get_intl_fw_list":{}}}';
            $result = $this->SendToTPLink($command);
            return $result;
        }

        // Set Server URL
        public function SetServerURL(string $url)
        {
            // {"cnCloud":{"set_server_url":{"server":"devs.tplinkcloud.com"}}}
            $command = '{"cnCloud":{"set_server_url":{"server":"'.$url.'"}}}';
            $result = $this->SendToTPLink($command);
            return $result;
        }

        // Connect with Cloud username & Password
        public function ConnectCloud(string $user, string $password)
        {
            // {"cnCloud":{"bind":{"username":"your@email.com", "password":"secret"}}}
            $command = '{"cnCloud":{"bind":{"username":"'.$user.'", "password":"'.$password.'"}}}';
            $result = $this->SendToTPLink($command);
            return $result;
        }

        // Unregister Device from Cloud Account
        public function UnregisterFromCloud()
        {
            $command = '{"cnCloud":{"unbind":null}}';
            $result = $this->SendToTPLink($command);
            return $result;
        }

        // Time Commands
        // ========================================

        // Get Time
        public function GetTime()
        {
            $command = '{"time":{"get_time":null}}';
            $result = $this->SendToTPLink($command);
            return $result;
        }

        // Get Timezone
        public function GetTimezone()
        {
            $command = '{"time":{"get_timezone":null}}';
            $result = $this->SendToTPLink($command);
            return $result;
        }

        // Set Timezone
        public function SetTimezone()
        {
            $command = '{"time":{"set_timezone":{"year":2016,"month":1,"mday":1,"hour":10,"min":10,"sec":10,"index":42}}}';
            $result = $this->SendToTPLink($command);
            return $result;
        }

        // EMeter Energy Usage Statistics Commands
        // (for TP-Link HS110)
        // ========================================

        // Get Realtime Current and Voltage Reading
        public function GetRealtimeCurrent()
        {
            #########################################################################################################################################
            # Command to Send: '{"emeter":{"get_realtime":{}}}'
            # Expexted answer: {"emeter":{"get_realtime":{"current":0.151818,"voltage":231.747099,"power":20.172881,"total":2.597000,"err_code":0}}}

            $command = '{"emeter":{"get_realtime":{}}}';
            $result = $this->SendToTPLink($command);
            /*
             if($debug)
             {
                    echo " --> Message received successfully - Lenght: " . strlen($buf) . " - decrypt:\n" . decrypt($buf) . "\n";;
                    print_r((array) json_decode(decrypt($buf)));
                    echo $result->emeter->get_realtime->voltage . "V / " . $result->emeter->get_realtime->current . "A / " . $result->emeter->get_realtime->power . "W";
                }
             */
            SetValueFloat($this->GetIDForIdent("Voltage"), floatval($result->emeter->get_realtime->voltage));
            SetValueFloat($this->GetIDForIdent("Current"), floatval($result->emeter->get_realtime->current));
            SetValueFloat($this->GetIDForIdent("Power"), floatval($result->emeter->get_realtime->power));
            return array("voltage" => floatval($result->emeter->get_realtime->voltage), "current" => floatval($result->emeter->get_realtime->current), "power" => floatval($result->emeter->get_realtime->power));
        }

        // Get EMeter VGain and IGain Settings
        public function GetEMeterVGain()
        {
            $command = '{"emeter":{"get_vgain_igain":{}}}';
            $result = $this->SendToTPLink($command);
            return $result;
        }

        // Set EMeter VGain and Igain
        public function SetEMeterVGain(int $vgain, int $igain)
        {
            // {"emeter":{"set_vgain_igain":{"vgain":13462,"igain":16835}}}
            $command = '{"emeter":{"set_vgain_igain":{"vgain":'.$vgain.',"igain":'.$igain.'}}}';
            $result = $this->SendToTPLink($command);
            return $result;
        }

        // Start EMeter Calibration
        public function StartEMeterCalibration(int $vgain, int $igain)
        {
            // {"emeter":{"start_calibration":{"vtarget":13462,"itarget":16835}}}
            $command = '{"emeter":{"start_calibration":{"vtarget":'.$vgain.',"itarget":'.$igain.'}}}';
            $result = $this->SendToTPLink($command);
            return $result;
        }

        // Get Daily Statistic for given Month
        public function GetDailyStatistic(int $year)
        {
            $command = '{"emeter":{"get_daystat":{"month":1,"year":'.$year.'}}}';
            $result = $this->SendToTPLink($command);
            return $result;
        }

        // Get Montly Statistic for given Year
        public function GetMontlyStatistic(int $year)
        {
            $command = '{"emeter":{""get_monthstat":{"year":'.$year.'}}}';
            $result = $this->SendToTPLink($command);
            return $result;
        }

        // Erase All EMeter Statistics
        public function EraseAllEMeterStatistics()
        {
            $command = '{"emeter":{"erase_emeter_stat":null}}';
            $result = $this->SendToTPLink($command);
            return $result;
        }

        // Schedule Commands
        // (action to perform regularly on given weekdays)
        // ========================================

        // Get Next Scheduled Action
        public function GetNextScheduledAction()
        {
            $command = '{"schedule":{"get_next_action":null}}';
            $result = $this->SendToTPLink($command);
            return $result;
        }

        // Get Schedule Rules List
        public function GetScheduleRulesList()
        {
            $command = '{"schedule":{"get_rules":null}}';
            $result = $this->SendToTPLink($command);
            return $result;
        }

        // Add New Schedule Rule
        /*
        public function AddNewScheduleRule()
        {
            // {"schedule":{"add_rule":{"stime_opt":0,"wday":[1,0,0,1,1,0,0],"smin":1014,"enable":1,"repeat":1,"etime_opt":-1,"name":"lights on","eact":-1,"month":0,"sact":1,"year":0,"longitude":0,"day":0,"force":0,"latitude":0,"emin":0},"set_overall_enable":{"enable":1}}}
            $command = '{"schedule":{"add_rule":{"stime_opt":0,"wday":[1,0,0,1,1,0,0],"smin":1014,"enable":1,"repeat":1,"etime_opt":-1,"name":"lights on","eact":-1,"month":0,"sact":1,"year":0,"longitude":0,"day":0,"force":0,"latitude":0,"emin":0},"set_overall_enable":{"enable":1}}}';
            $result = $this->SendToTPLink($command);
            return $result;
        }

        // Edit Schedule Rule with given ID
        public function EditScheduleRule(string $id)
        {
            // {"schedule":{"edit_rule":{"stime_opt":0,"wday":[1,0,0,1,1,0,0],"smin":1014,"enable":1,"repeat":1,"etime_opt":-1,"id":"4B44932DFC09780B554A740BC1798CBC","name":"lights on","eact":-1,"month":0,"sact":1,"year":0,"longitude":0,"day":0,"force":0,"latitude":0,"emin":0}}}
            $command = '{"schedule":{"edit_rule":{"stime_opt":0,"wday":[1,0,0,1,1,0,0],"smin":1014,"enable":1,"repeat":1,"etime_opt":-1,"id":"'.$id.'","name":"lights on","eact":-1,"month":0,"sact":1,"year":0,"longitude":0,"day":0,"force":0,"latitude":0,"emin":0}}}';
            $result = $this->SendToTPLink($command);
            return $result;
        }

        // Delete Schedule Rule with given ID
        public function DeleteScheduleRule(string $id)
        {
            // {"schedule":{"delete_rule":{"id":"4B44932DFC09780B554A740BC1798CBC"}}}
            $command = '{"schedule":{"delete_rule":{"id":"'.$id.'"}}}';
            $result = $this->SendToTPLink($command);
            return $result;
        }

        // Delete All Schedule Rules and Erase Statistics
        public function DeleteAllScheduleRules()
        {
            // {"schedule":{"delete_all_rules":null,"erase_runtime_stat":null}}
            $command = '{"schedule":{"delete_all_rules":null,"erase_runtime_stat":null}}';
            $result = $this->SendToTPLink($command);
            return $result;
        }
        */

        // Countdown Rule Commands
        // (action to perform after number of seconds)

        // Get Rule (only one allowed)
        public function GetRule()
        {
            $command = '{"count_down":{"get_rules":null}}';
            $result = $this->SendToTPLink($command);
            return $result;
        }

        // Add New Countdown Rule
        public function AddNewCountdownRule(int $delay, string $name)
        {
            // {"count_down":{"add_rule":{"enable":1,"delay":1800,"act":1,"name":"turn on"}}}
            $command = '{"count_down":{"add_rule":{"enable":1,"delay":'.$delay.',"act":1,"name":"'.$name.'"}}}';
            $result = $this->SendToTPLink($command);
            return $result;
        }

        // Edit Countdown Rule with given ID
        public function EditCountdownRule(string $id, int $delay, string $name)
        {
            // {"count_down":{"edit_rule":{"enable":1,"id":"7C90311A1CD3227F25C6001D88F7FC13","delay":1800,"act":1,"name":"turn on"}}}
            $command = '{"count_down":{"edit_rule":{"enable":1,"id":"'.$id.'","delay":'.$delay.',"act":1,"name":"'.$name.'"}}}';
            $result = $this->SendToTPLink($command);
            return $result;
        }

        // Delete Countdown Rule with given ID
        public function DeleteCountdownRule(string $id)
        {
            // {"count_down":{"delete_rule":{"id":"7C90311A1CD3227F25C6001D88F7FC13"}}}
            $command = '{"count_down":{"delete_rule":{"id":"'.$id.'"}}}';
            $result = $this->SendToTPLink($command);
            return $result;
        }

        // Delete All Coundown Rules
        public function DeleteAll()
        {
            // {"count_down":{"delete_all_rules":null}}
            $command = '{"count_down":{"delete_all_rules":null}}';
            $result = $this->SendToTPLink($command);
            return $result;
        }

        // Anti-Theft Rule Commands (aka Away Mode)
        // (period of time during which device will be randomly turned on and off to deter thieves)
        // ========================================

        // Get Anti-Theft Rules List
        public function GetAntiTheftRules()
        {
            $command = '{"anti_theft":{"get_rules":null}}';
            $result = $this->SendToTPLink($command);
            return $result;
        }

        // Delete All Anti-Theft Rules
        public function DeleteAllAntiTheftRules()
        {
            $command = '{"anti_theft":{"delete_all_rules":null}}';
            $result = $this->SendToTPLink($command);
            return $result;
        }

        // Add New Anti-Theft Rule
        /*
        public function AddNewAntiTheftRule()
        {
            // {"anti_theft":{"add_rule":{"stime_opt":0,"wday":[0,0,0,1,0,1,0],"smin":987,"enable":1,"frequency":5,"repeat":1,"etime_opt":0,"duration":2,"name":"test","lastfor":1,"month":0,"year":0,"longitude":0,"day":0,"latitude":0,"force":0,"emin":1047},"set_overall_enable":1}}
            $command = '{"anti_theft":{"add_rule":{"stime_opt":0,"wday":[0,0,0,1,0,1,0],"smin":987,"enable":1,"frequency":5,"repeat":1,"etime_opt":0,"duration":2,"name":"test","lastfor":1,"month":0,"year":0,"longitude":0,"day":0,"latitude":0,"force":0,"emin":1047},"set_overall_enable":1}}';
            $result = $this->SendToTPLink($command);
            return $result;
        }

        // Edit Anti-Theft Rule with given ID
        public function EditAntiTheftRule()
        {
            $command = '{"anti_theft":{"edit_rule":{"stime_opt":0,"wday":[0,0,0,1,0,1,0],"smin":987,"enable":1,"frequency":5,"repeat":1,"etime_opt":0,"id":"E36B1F4466B135C1FD481F0B4BFC9C30","duration":2,"name":"test","lastfor":1,"month":0,"year":0,"longitude":0,"day":0,"latitude":0,"force":0,"emin":1047},"set_overall_enable":1}}';
            $result = $this->SendToTPLink($command);
            return $result;
        }
        */

        // Delete Anti-Theft Rule with given ID
        public function DeleteAntiTheftRule(string $id)
        {
            $command = '{"anti_theft":{"delete_rule":{"id":"'.$id.'"}}}';
            $result = $this->SendToTPLink($command);
            return $result;
        }

		public function ReceiveData($JSONString)
		{
			$data = json_decode($JSONString);
			$objectid = $data->Buffer->objectid;
			$values = $data->Buffer->values;
			$valuesjson = json_encode($values);
			if (($this->InstanceID) == $objectid)
			{
				//Parse and write values to our variables
				//$this->WriteValues($valuesjson);
			}
		}

        protected function is_valid_localdomain($url)
        {

            $validation = FALSE;
            /*Parse URL*/
            $urlparts = parse_url(filter_var($url, FILTER_SANITIZE_URL));
            /*Check host exist else path assign to host*/
            if(!isset($urlparts['host'])){
                $urlparts['host'] = $urlparts['path'];
            }

            if($urlparts['host']!=''){
                /*Add scheme if not found*/
                if (!isset($urlparts['scheme'])){
                    $urlparts['scheme'] = 'http';
                }
                /*Validation*/
                if(checkdnsrr($urlparts['host'], 'A') && in_array($urlparts['scheme'],array('http','https')) && ip2long($urlparts['host']) === FALSE){
                    $urlparts['host'] = preg_replace('/^www\./', '', $urlparts['host']);
                    $url = $urlparts['scheme'].'://'.$urlparts['host']. "/";

                    if (filter_var($url, FILTER_VALIDATE_URL) !== false && @get_headers($url)) {
                        $validation = TRUE;
                    }
                }
            }

            if(!$validation)
            {
                //echo $url." Its Invalid Domain Name.";
                $domaincheck = false;
                return $domaincheck;
            }
            else
            {
                //echo $url." is a Valid Domain Name.";
                $domaincheck = true;
                return $domaincheck;
            }

        }

		public function RequestAction($Ident, $Value)
		{	
			switch($Ident) {
				case "State":
                    $varid = $this->GetIDForIdent("State");
					SetValue($varid, $Value);
                    if($Value)
                    {
                        $this->PowerOn();
                    }
                    else
                    {
                        $this->PowerOff();
                    }
					break;
				default:
					throw new Exception("Invalid ident");
			}
		}

		
		//Profile
		protected function RegisterProfile($Name, $Icon, $Prefix, $Suffix, $MinValue, $MaxValue, $StepSize, $Digits, $Vartype)
		{
			
			if(!IPS_VariableProfileExists($Name))
			{
				IPS_CreateVariableProfile($Name, $Vartype); // 0 boolean, 1 int, 2 float, 3 string,
			}
			else
			{
				$profile = IPS_GetVariableProfile($Name);
				if($profile['ProfileType'] != $Vartype)
				throw new Exception("Variable profile type does not match for profile ".$Name);
			}
			
			IPS_SetVariableProfileIcon($Name, $Icon);
			IPS_SetVariableProfileText($Name, $Prefix, $Suffix);
			IPS_SetVariableProfileDigits($Name, $Digits); //  Nachkommastellen
			IPS_SetVariableProfileValues($Name, $MinValue, $MaxValue, $StepSize); // string $ProfilName, float $Minimalwert, float $Maximalwert, float $Schrittweite
		}
		
		protected function RegisterProfileAssociation($Name, $Icon, $Prefix, $Suffix, $MinValue, $MaxValue, $Stepsize, $Digits, $Vartype, $Associations)
		{
			if ( sizeof($Associations) === 0 ){
				$MinValue = 0;
				$MaxValue = 0;
			} 
			/*
			else {
				//undefiened offset
				$MinValue = $Associations[0][0];
				$MaxValue = $Associations[sizeof($Associations)-1][0];
			}
			*/
			$this->RegisterProfile($Name, $Icon, $Prefix, $Suffix, $MinValue, $MaxValue, $Stepsize, $Digits, $Vartype);
			
			//boolean IPS_SetVariableProfileAssociation ( string $ProfilName, float $Wert, string $Name, string $Icon, integer $Farbe )
			foreach($Associations as $Association) {
				IPS_SetVariableProfileAssociation($Name, $Association[0], $Association[1], $Association[2], $Association[3]);
			}
			
		}

        //Configuration Form
        public function GetConfigurationForm()
        {
            $formhead = $this->FormHead();
            $formactions = $this->FormActions();
            $formelementsend = '{ "type": "Label", "label": "__________________________________________________________________________________________________" }';
            $formstatus = $this->FormStatus();
            return	'{ '.$formhead.$formelementsend.'],'.$formactions.$formstatus.' }';
        }

        protected function FormHead()
        {
            $form = '"elements":
            [
                { "type": "Label", "label": "TP Link HS type"},
                { "type": "Select", "name": "model", "caption": "model",
					"options": [
						{ "label": "HS100", "value": 1 },
						{ "label": "HS110", "value": 2 }
					]
				},
                { "type": "Label", "label": "TP Link HS device ip address"},
				{
                    "name": "Host",
                    "type": "ValidationTextBox",
                    "caption": "IP adress"
                },
                { "type": "Label", "label": "TP Link HS device state update interval"},
                { "type": "IntervalBox", "name": "stateinterval", "caption": "seconds" },';
            $model = $this->ReadPropertyInteger("model");
            if($model == 2)
            {
                $form .= '{ "type": "Label", "label": "TP Link HS device system info update interval"},
                { "type": "IntervalBox", "name": "systeminfointerval", "caption": "seconds" },';
            }

            return $form;
        }

        protected function FormActions()
        {
            $form = '"actions":
			[
				{ "type": "Label", "label": "TP Link HS device" },
				{ "type": "Label", "label": "TP Link HS Power On" },
				{ "type": "Button", "label": "On", "onClick": "TPLHS_PowerOn($id);" },
				{ "type": "Label", "label": "TP Link HS Power Off" },
				{ "type": "Button", "label": "Off", "onClick": "TPLHS_PowerOff($id);" }
			],';
            return  $form;
        }

        protected function FormStatus()
        {
            $form = '"status":
            [
                {
                    "code": 101,
                    "icon": "inactive",
                    "caption": "Creating instance."
                },
				{
                    "code": 102,
                    "icon": "active",
                    "caption": "instance created."
                },
                {
                    "code": 104,
                    "icon": "inactive",
                    "caption": "interface closed."
                },
                {
                    "code": 202,
                    "icon": "error",
                    "caption": "special errorcode."
                },
                {
                    "code": 203,
                    "icon": "error",
                    "caption": "IP Address is not valid."
                }
            ]';
            return $form;
        }
    }

?>
