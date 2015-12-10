<?
/* 2do *************************************************************************************
> Antwort auswerten bei Frage und in Bool-Variable schreiben.
> Frage z.B. nutzen für > Haustür klingelt, an der Dreambox kommt Frage "Tür öffnen?" und bei JA wird die Tür geöffnet, Timeout X Sekunden

> Funktionen einbauen für "VolUp, VolDown, NextSender, PrevSender, Aktuelles Programm, Aktuelles EPG, ....)
> VolumeVAR fuer aktuelle Lautstaerke in Variable (bedienbar machen wenn ins WebFront verlinkt - ActionSkript)
> SenderVar fuer aktuellen Sender (nicht bedienbar)
> SenderEPGInfoVAR fuer Detailinfos zur aktuellen Sendung (nicht bedienbar)

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
        $this->RegisterProfileIntegerEx("E2Nachr.JaNeinKA", "Information", "", "", Array(
                                             Array(0, "Nein",  "", -1),
                                             Array(1, "Ja",  "", -1),
                                             Array(2, "Keine Antwort",  "", -1)
        ));
        $this->RegisterProfileIntegerEx("E2Nachr.PowerState", "Information", "", "", Array(
                                             Array(0, "DeepStandby",  "", -1),
                                             Array(1, "Standby",  "", -1),
                                             Array(2, "AN",  "", -1)
        ));

        //Variablen erstellen
        $this->RegisterVariableInteger("FrageAntwortVar", "Frage-Antwort", "E2BY.JaNeinKA");
        $this->RegisterVariableInteger("PowerStateVar", "Power-State", "E2BY.PowerState");
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
    
    public function SendKey($IP, $Key, $LongShort)
    {
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