<?
/* 2do *************************************************************************************
> Antwort auswerten bei Frage und in Bool-Variable schreiben.
> Frage z.B. nutzen für > Haustür klingelt, an der Dreambox kommt Frage "Tür öffnen?" und bei JA wird die Tür geöffnet, Timeout X Sekunden

> TimerIntervall für Funktion in Form + Modul (GetSenderInfos)
+ bei JEDER Funktion die Infos mit abfragen lassen!!! Also am besten eine "Gruppenfunktion" erstellen die dann überall mit drin steht

> Funktionen einbauen für "VolUp, VolDown, NextSender, PrevSender, Aktuelles Programm, Aktuelles EPG, ....)

> VolumeVAR fuer aktuelle Lautstaerke in Variable (bedienbar machen wenn ins WebFront verlinkt - ActionSkript)
Get current Volume: http://dreambox/web/vol oder http://dreambox/web/vol?set=state
Set Volume to 23: http://dreambox/web/vol?set=set23
Increase Volume: http://dreambox/web/vol?set=up
Decrease Volume: http://dreambox/web/vol?set=down
Switch Mute: http://dreambox/web/vol?set=mute

> Box Infos auslesen (http://192.168.10.111/web/about) > e2imageversion, e2model, e2hddinfo/model, e2hddinfo/free

> SetPowerstate > http://192.168.10.111/web/powerstate?newstate={powerstate_number} - (0 bis 5) toggle standby, deepstandby, reboot, restart gui, wakeup from standby, standby

> Timerliste in HTMLBox stecken (http://192.168.10.111/web/timerlist)

> Favoriten-Sender in Form eintragen lassen (sRef), damit man zu diesen direkt umschalten kann
Hier gibt es alle Sendernamen + sRef (http://192.168.10.111/web/getallservices)

> Umschalten > http://192.168.10.111/web/zap?sRef={servicereference}

>>> Detail-Infos >> http://dream.reichholf.net/wiki/Enigma2:WebInterface#Message
********************************************************************************************/

class Enigma2BY extends IPSModule
{

    public function Create()
    {
        //Never delete this line!
        parent::Create();
        
        //These lines are parsed on Symcon Startup or Instance creation
        //You cannot use variables here. Just static values.
        $this->RegisterPropertyString("Enigma2IP", "");  
        $this->RegisterPropertyString("RCUdefault", "advanced");
        $this->RegisterPropertyString("KeyDropDown", "Info");
    }

    public function Destroy()
    {
        //Never delete this line!
        parent::Destroy();
    }

    public function ApplyChanges()
    {
        //Never delete this line!
        parent::ApplyChanges();
        
        //Variablenprofile erstellen
        $this->RegisterProfileInteger("E2BY.Minuten", "Clock", "", " Min.",  "", "", 0);
        $this->RegisterProfileString("E2BY.Info", "Information", "", "",  "", "", 0);
        $this->RegisterProfileIntegerEx("E2BY.JaNeinKA", "Information", "", "", Array(
                                             Array(0, "Nein",  "", -1),
                                             Array(1, "Ja",  "", -1),
                                             Array(2, "Keine Antwort",  "", -1)
        ));

        //Variablen erstellen
        $this->RegisterVariableInteger("FrageAntwortVAR", "Frage-Antwort", "E2BY.JaNeinKA");
        $this->RegisterVariableString("AktSendernameVAR", "Akt. Sendername");
        $this->RegisterVariableString("AktSendungsnameVAR", "Akt. Sendungsname");
        $this->RegisterVariableString("AktSendungsBeschrKurzVAR", "Akt. Sendungsbeschreibung kurz");
        $this->RegisterVariableString("AktSendungsBeschrLangVAR", "Akt. Sendungsbeschreibung lang");
        $this->RegisterVariableInteger("AktSendunsdauerVar", "Akt. Sendungsdauer Min.", "E2BY.Minuten");
        $this->RegisterVariableInteger("AktSendunsdauerRestVar", "Akt. Sendungsdauer Rest Min.", "E2BY.Minuten");
        $this->RegisterVariableString("NextSendungsnameVar", "Next Sendungsname");
        $this->RegisterVariableString("NextSendungsBeschrKurzVAR", "Next Sendungsbeschreibung kurz");
        $this->RegisterVariableString("NextSendungsBeschrLangVAR", "Next Sendungsbeschreibung lang");
        $this->RegisterVariableString("NextSendungsStartVAR", "Next Sendung Startzeit");
        $this->RegisterVariableInteger("NextSendungsdauerVAR", "Next Sendungsdauer Min.", "E2BY.Minuten");
    }

    public function MsgTest()
    {
    		$IP_Test = $this->ReadPropertyString("Enigma2IP");
    		$Text_Test = "Das ist ein Test!";
    		$Type_Test = 1;
    		$Timeout_Test = 5;
    		$result = $this->Enigma2BY_SendMsg($IP_Test, $Text_Test, $Type_Test, $Timeout_Test);
    		if ($result)
    		{
    				echo "Test-Nachricht wurde erfolgreich gesendet.";
    		}
    		else 
    		{
    				echo "Test-Nachricht konnte nicht gesendet werden!";
    		}
    }
    
    public function KeyTest()
    {
    		$IP_Test = $this->ReadPropertyString("Enigma2IP");
    		$Key_Test = $this->ReadPropertyString("KeyDropDown");
    		$LongShort_Test = "short";
    		$result = $this->SendKey($IP_Test, $Key_Test, $LongShort_Test);
    		if ($result)
    		{
    				echo "Test-Taste wurde erfolgreich gesendet.";
    		}
    		else 
    		{
    				echo "Test-Taste konnte nicht gesendet werden!";
    		}
    }
    
    public function Frage($Text, $Timeout)
    {
    		$IP = $this->ReadPropertyString("Enigma2IP");
    		$Type = 0;
    		$result = $this->Enigma2BY_SendMsg($IP, $Text, $Type, $Timeout);
    		return $result;
    }
    
    public function Info($Text, $Timeout)
    {
    		$IP = $this->ReadPropertyString("Enigma2IP");
    		$Type = 1;
    		$result = $this->Enigma2BY_SendMsg($IP, $Text, $Type, $Timeout);
    		return $result;
    }
    
    public function Message($Text, $Timeout)
    {
    		$IP = $this->ReadPropertyString("Enigma2IP");
    		$Type = 2;
    		$result = $this->Enigma2BY_SendMsg($IP, $Text, $Type, $Timeout);
    		return $result;
    }
    
    public function Attention($Text, $Timeout)
    {
    		$IP = $this->ReadPropertyString("Enigma2IP");
    		$Type = 3;
    		$result = $this->Enigma2BY_SendMsg($IP, $Text, $Type, $Timeout);
    		return $result;
    }
    
    private function Enigma2BY_SendMsg($IP, $Text, $Type, $Timeout)
    {
    		if (Sys_Ping($IP, 2000) == true)
    		{
    				$Text = urlencode(trim($Text));
    				$Text = str_replace('%A7', '%0A', $Text);
 						$url = "http://".$IP."/web/message?text=".$Text."&type=".$Type."&timeout=".$Timeout;
    				$result = Sys_GetURLContent($url);
    				preg_match('|True|', $result, $resultmatch);
    				
    				if ($Type == 0)
    				{
    						$this->SendKey($IP, "ArrowDown", "short");
    						IPS_Sleep($Timeout * 1000 + 1000);
								$result = @Sys_GetURLContent("http://".$IP."/web/messageanswer?getanswer=now");
								preg_match('|Answer is.(.*)!.*|', $result, $antwortmatch);
								if ($antwortmatch[1] == "NO")
								{
										$AntwortINT = 0;
								}
								elseif ($antwortmatch[1] == "YES")
								{
										$AntwortINT = 1;
								}
								else
								{
										$AntwortINT = 2;
										$this->SendKey($IP, "Exit", "short");
								}
								$this->SetValueInteger("FrageAntwortVar", $AntwortINT);
    				}
    				return $result;
    		}
    }
    
    public function SendKey($Key, $LongShort)
    {
    		$IP = $this->ReadPropertyString("Enigma2IP");
    		$CommandArray = array("Power" => "Power", "1" => "2", "2" => "3", "4" => "5", "5" => "6", "6" => "7", "7" => "8", "8" => "9", "9" => "10", "0" => "11", "VolumeUp" => "115", "VolumeDown" => "114", "MUTE" => "113", "Previous" => "412", "Next" => "407", "BouquetUp" => "402", "BouquetDown" => "403", "ArrowUp" => "103", "ArrowDown" => "108", "ArrowLeft" => "105", "ArrowRight" => "106", "Menu" => "139", "OK" => "352", "Info" => "358", "Audio" => "392", "Video" => "393", "RED" => "398", "GREEN" => "399", "YELLOW" => "400", "BLUE" => "401", "TV" => "377", "Radio" => "385", "Text" => "388", "Help" => "138", "Exit" => "174");
    		$Command = $CommandArray[$Key];
    		if (($LongShort == "long") OR ($LongShort == "Long"))
    		{
    				$LongShort = "long";
    		}
    		elseif (($LongShort == "short") OR ($LongShort == "Short"))
    		{
    				$LongShort = "short";
    		}
    		$RCU = $this->ReadPropertyString("RCUdefault");
    		$url = "http://".$IP."/web/remotecontrol?command=".$Command."&type=".$LongShort."&rcu=".$RCU;
    		$result = Sys_GetURLContent($url);
				
    }
    
    public function GetSenderInfos()
    {
    		$IP = $this->ReadPropertyString("Enigma2IP");
    		$url = "http://".$IP."/web/getcurrent";
				$xml = simplexml_load_file($url);
				$E2_CurSendername = $xml->e2service->e2servicename;
				$E2_CurSendungsname = $xml->e2eventlist->e2event[0]->e2eventname;
				$E2_CurSendungsBeschrKurz = $xml->e2eventlist->e2event[0]->e2eventdescription;
				$E2_CurSendungsBeschrLang = $xml->e2eventlist->e2event[0]->e2eventdescriptionextended;
				$E2_CurSendungsdauerSek = $xml->e2eventlist->e2event[0]->e2eventduration;
				$E2_CurSendungsrestdauerSek = $xml->e2eventlist->e2event[0]->e2eventremaining;
				$E2_NextSendungsname = $xml->e2eventlist->e2event[1]->e2eventname;
				$E2_NextSendungsBeschrKurz = $xml->e2eventlist->e2event[1]->e2eventdescription;
				$E2_NextSendungsBeschrLang = $xml->e2eventlist->e2event[1]->e2eventdescriptionextended;
				$E2_NextSendungStart = $xml->e2eventlist->e2event[1]->e2eventstart;
				$E2_NextSendungsdauerSek = $xml->e2eventlist->e2event[1]->e2eventduration;
				$this->SetValueString("AktSendernameVAR", $E2_CurSendername);
				$this->SetValueString("AktSendungsnameVAR", $E2_CurSendungsname);
				$this->SetValueString("AktSendungsBeschrKurzVAR", $E2_CurSendungsBeschrKurz);
				$this->SetValueString("AktSendungsBeschrLangVAR", $E2_CurSendungsBeschrLang);
				$E2_CurSendungsdauerMin = $E2_CurSendungsdauerSek / 60;
				$this->SetValueInteger("AktSendunsdauerVar", $E2_CurSendungsdauerMin);
				$E2_CurSendungsrestdauerMin = $E2_CurSendungsrestdauerSek / 60;
				$this->SetValueInteger("AktSendunsdauerRestVar", $E2_CurSendungsrestdauerMin);
				$this->SetValueString("NextSendungsnameVar", $E2_NextSendungsname);
				$this->SetValueString("NextSendungsBeschrKurzVAR", $E2_NextSendungsBeschrKurz);
				$this->SetValueString("NextSendungsBeschrLangVAR", $E2_NextSendungsBeschrLang);
				$E2_NextSendungStart = date("H:i", $E2_NextSendungStart)." Uhr";
				$this->SetValueString("NextSendungsStartVAR", $E2_NextSendungStart);
				$E2_NextSendungsdauerMin = $E2_NextSendungsdauerSek / 60;
				$this->SetValueInteger("NextSendungsdauerVAR", $E2_NextSendungsdauerMin);
    }
    
    private function SetValueInteger($Ident, $Value)
    {
        $ID = $this->GetIDForIdent($Ident);
        if (GetValueInteger($ID) <> $Value)
        {
            SetValueInteger($ID, $Value);
            return true;
        }
        return false;
    }
    
    private function SetValueString($Ident, $Value)
    {
        $ID = $this->GetIDForIdent($Ident);
        if (GetValueString($ID) <> $Value)
        {
            SetValueString($ID, $Value);
            return true;
        }
        return false;
    }
    
    protected function RegisterProfileString($Name, $Icon, $Prefix, $Suffix, $MinValue, $MaxValue, $StepSize) {
        
        if(!IPS_VariableProfileExists($Name)) {
            IPS_CreateVariableProfile($Name, 3);
        } else {
            $profile = IPS_GetVariableProfile($Name);
            if($profile['ProfileType'] != 3)
            throw new Exception("Variable profile type does not match for profile ".$Name);
        }
        
        IPS_SetVariableProfileIcon($Name, $Icon);
        IPS_SetVariableProfileText($Name, $Prefix, $Suffix);
        IPS_SetVariableProfileValues($Name, $MinValue, $MaxValue, $StepSize);
        
    }
    
    protected function RegisterProfileInteger($Name, $Icon, $Prefix, $Suffix, $MinValue, $MaxValue, $StepSize) {
        
        if(!IPS_VariableProfileExists($Name)) {
            IPS_CreateVariableProfile($Name, 1);
        } else {
            $profile = IPS_GetVariableProfile($Name);
            if($profile['ProfileType'] != 1)
            throw new Exception("Variable profile type does not match for profile ".$Name);
        }
        
        IPS_SetVariableProfileIcon($Name, $Icon);
        IPS_SetVariableProfileText($Name, $Prefix, $Suffix);
        IPS_SetVariableProfileValues($Name, $MinValue, $MaxValue, $StepSize);
        
    }
    
    protected function RegisterProfileIntegerEx($Name, $Icon, $Prefix, $Suffix, $Associations) {
        if ( sizeof($Associations) === 0 ){
            $MinValue = 0;
            $MaxValue = 0;
        } else {
            $MinValue = $Associations[0][0];
            $MaxValue = $Associations[sizeof($Associations)-1][0];
        }
        
        $this->RegisterProfileInteger($Name, $Icon, $Prefix, $Suffix, $MinValue, $MaxValue, 0);
        
        foreach($Associations as $Association) {
            IPS_SetVariableProfileAssociation($Name, $Association[0], $Association[1], $Association[2], $Association[3]);
        }
        
    }
}
?>