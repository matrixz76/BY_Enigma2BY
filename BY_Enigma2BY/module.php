<?
/* 2do *************************************************************************************
> Antwort auswerten bei Frage und in Bool-Variable schreiben.
> Frage z.B. nutzen für > Haustür klingelt, an der Dreambox kommt Frage "Tür öffnen?" und bei JA wird die Tür geöffnet, Timeout X Sekunden
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
        //$this->RegisterPropertyInteger("TimeoutDefault", 5);
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
        
        //Variablenprofil erstellen
        $this->RegisterProfileIntegerEx("E2Nachr.JaNeinKA", "Information", "", "", Array(
                                             Array(0, "Nein",  "", -1),
                                             Array(1, "Ja",  "", -1),
                                             Array(2, "Keine Antwort",  "", -1)
        ));

        //Variablen erstellen
        $this->RegisterVariableInteger("FrageAntwort", "Frage-Antwort", "E2Nachr.JaNeinKA");
    }

    public function MsgTest()
    {
    		$IP_Test = $this->ReadPropertyString("Enigma2IP");
    		$Text_Test = "Das ist ein Test!";
    		$Type_Test = 1;
    		$Timeout_Test = 5;
    		$result = $this->Enigma2BY_SEND($IP_Test, $Text_Test, $Type_Test, $Timeout_Test);
    		if ($result)
    		{
    				echo "Test-Nachricht wurde erfolgreich gesendet.";
    		}
    		else 
    		{
    				echo "Test-Nachricht konnte nicht gesendet werden!";
    		}
    }
    
    public function Frage($Text, $Timeout)
    {
    		$IP = $this->ReadPropertyString("Enigma2IP");
    		$Type = 0;
    		$result = $this->Enigma2BY_SEND($IP, $Text, $Type, $Timeout);
    		return $result;
    }
    
    public function Info($Text, $Timeout)
    {
    		$IP = $this->ReadPropertyString("Enigma2IP");
    		$Type = 1;
    		$result = $this->Enigma2BY_SEND($IP, $Text, $Type, $Timeout);
    		return $result;
    }
    
    public function Message($Text, $Timeout)
    {
    		$IP = $this->ReadPropertyString("Enigma2IP");
    		$Type = 2;
    		$result = $this->Enigma2BY_SEND($IP, $Text, $Type, $Timeout);
    		return $result;
    }
    
    public function Attention($Text, $Timeout)
    {
    		$IP = $this->ReadPropertyString("Enigma2IP");
    		$Type = 3;
    		$result = $this->Enigma2BY_SEND($IP, $Text, $Type, $Timeout);
    		return $result;
    }
    
    private function Enigma2BY_SEND($IP, $Text, $Type, $Timeout)
    {
    		if (Sys_Ping($IP, 2000) == true)
    		{
    				$Text = urlencode(trim($Text));
    				$Text = str_replace('%A7', '%0A', $Text);
    				$url = "http://".$IP."/web/message?text=".$Text."&type=".$Type."&timeout=".$Timeout;
    				$result = Sys_GetURLContent($url);
    				preg_match('|True|', $result, $resultmatch);
						$result = (bool)$resultmatch;
    				if (!$result)
    				{
    						IPS_Sleep(2000);
    						$result = Sys_GetURLContent($url);
    						preg_match('|True|', $result, $resultmatch);
								$result = (bool)$resultmatch;
    				}
    				
    				if ($Type == 0)
    				{
    						IPS_Sleep($Timeout * 1000 + 1000);
								$result = Sys_GetURLContent("http://".$IP."/web/messageanswer?getanswer=now");
								preg_match('|Answer is.(.*)!.*|', $result, $antwortmatch);
								
								if ($antwortmatch[1] == "YES")
								{
									$AntwortBOOL = true;
								}
								elseif ($antwortmatch[1] == "NO")
								{
									$AntwortBOOL = false;
								}
								//$this->SetValueBoolean("FrageAntwort", $AntwortBOOL);
    				}
    				return $result;
    		}
    }
    
    private function Enigma2BY_SendKey($IP, $Key, $LongShort)
    {
    		//$CommandArray noch sinnvoll sortieren, damit es im DropDown leicht zu bedienen ist
    		$CommandArray = array("Power" => "Power", "1" => "2", "2" => "3", "4" => "5", "5" => "6", "6" => "7", "7" => "8", "8" => "9", "9" => "10", "0" => "11", "Previous" => "412", "Next" => "407", "VolumeUp" => "115", "MUTE" => "113", "BouquetUp" => "402", "VolumeDown" => "114", "Lame" => "174", "BouquetDown" => "403", "Info" => "358", "ArrowUp" => "103", "Menu" => "139", "ArrowLeft" => "105", "OK" => "352", "ArrowRight" => "106", "Audio" => "392", "ArrowDown" => "108", "Video" => "393", "RED" => "398", "GREEN" => "399", "YELLOW" => "400", "BLUE" => "401", "TV" => "377", "Radio" => "385", "Text" => "388", "Help" => "138");
    		
    		// im $CommandArray den Key finden und dann die zugehörige Nummer in die Var $Command schreiben
    		
    		// $LongShort einbauen für langen und kurzen Tastendruck
    }

    private function SetValueBoolean($Ident, $value)
    {
        $id = $this->GetIDForIdent($Ident);
        if (GetValueBoolean($id) <> $value)
        {
            SetValueBoolean($id, $value);
            return true;
        }
        return false;
    }
    
    protected function RegisterProfileBooleanEx($Name, $Icon, $Prefix, $Suffix, $Associations) {
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