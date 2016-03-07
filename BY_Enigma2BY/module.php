<?
class Enigma2BY extends IPSModule
{

    public function Create()
    {
        //Never delete this line!
        parent::Create();
        
        //These lines are parsed on Symcon Startup or Instance creation
        //You cannot use variables here. Just static values.
        $this->RegisterPropertyString("Enigma2IP", "");
        $this->RegisterPropertyInteger("Enigma2WebPort", 80);
        $this->RegisterPropertyBoolean("HDDverbaut", false);
        $this->RegisterPropertyBoolean("ErwInformationen", false);
		$this->RegisterPropertyBoolean("AufnahmenListeAuslesen", false);
        $this->RegisterPropertyInteger("IntervallRefresh", "60");
        $this->RegisterPropertyString("RCUdefault", "advanced");
        $this->RegisterPropertyString("KeyDropDown", "");
        $this->RegisterPropertyString("SenderZapTo", "");
        $this->RegisterPropertyString("EPGSuchstring", "");
        $this->RegisterTimer("Refresh_All", 0, 'Enigma2BY_UpdateAll($_IPS[\'TARGET\']);');
    }

    public function Destroy()
    {
    	//Timer entfernen
    	$this->UnregisterTimer("Refresh_All");
    		
        //Never delete this line!!
        parent::Destroy();
    }

    public function ApplyChanges()
    {
        //Never delete this line!
        parent::ApplyChanges();
        
        //Variablenprofile erstellen
        $this->RegisterProfileInteger("E2BY.Minuten", "Clock", "", " Min.",  "0", "300", 1);
		$this->RegisterProfileString("E2BY.Uhr", "Clock", "", " Uhr",  "0", "0", 0);
        if ($this->ReadPropertyBoolean("HDDverbaut") == true)
		{
        	$this->RegisterProfileInteger("E2BY.GB", "Information", "", " GB",  "0", "10240000", 1);
      	}
        $this->RegisterProfileString("E2BY.Info", "Information", "", "",  "0", "2", 0);
        $this->RegisterProfileIntegerEx("E2BY.JaNeinKA", "Information", "", "", Array(
                                             Array(0, "Nein",  "", -1),
                                             Array(1, "Ja",  "", -1),
                                             Array(2, "Keine Antwort",  "", -1)
        ));
        $this->RegisterProfileIntegerEx("E2BY.PowerState", "Information", "", "", Array(
                                             Array(0, "ausgeschaltet",  "", -1),
                                             Array(1, "eingeschaltet",  "", -1),
                                             Array(2, "Standby",  "", -1)
        ));
        $this->RegisterProfileBooleanEx("E2BY.inaktiv.aktiv", "Information", "", "", Array(
                                             Array(false, "inaktiv",  "", -1),
                                             Array(true, "aktiv",  "", -1)
        ));
        $this->RegisterProfileInteger("E2BY.Volume", "Speaker", "", " %",  "0", "100", 1);
        $this->RegisterProfileFloat("E2BY.SNRdb", "Speaker", "", " db",  "0", "100", 0.01);

        //Variablen erstellen
        $this->RegisterVariableInteger("PowerStateVAR", "Power-State", "E2BY.PowerState");
        $this->RegisterVariableInteger("FrageAntwortVAR", "Frage-Antwort", "E2BY.JaNeinKA");
        $this->RegisterVariableString("AktSendernameVAR", "Akt. Sendername");
        $this->RegisterVariableString("AktSendungsnameVAR", "Akt. Sendungstitel");
        $this->RegisterVariableString("AktSendungsBeschrKurzVAR", "Akt. Sendungsbeschreibung kurz");
        $this->RegisterVariableString("AktSendungsBeschrLangVAR", "Akt. Sendungsbeschreibung lang");
        $this->RegisterVariableInteger("AktSendungsdauerVAR", "Akt. Sendungsdauer Min.", "E2BY.Minuten");
        $this->RegisterVariableInteger("AktSendungsdauerRestVAR", "Akt. Sendungsdauer Rest Min.", "E2BY.Minuten");
		$this->RegisterVariableString("AktSendungsStartVAR", "Akt. Sendung Startzeit", "E2BY.Uhr");
		$this->RegisterVariableString("AktSendungsEndeVAR", "Akt. Sendung Endzeit", "E2BY.Uhr");
		$this->RegisterVariableInteger("AktSendungsfortschrittProzVAR", "Akt. Sendung Fortschritt", "~Intensity.100");
		$this->RegisterVariableInteger("AktSendungsvergangenedauerVAR", "Akt. Sendung vergangene Min.", "E2BY.Minuten");  
        $this->RegisterVariableString("NextSendungsnameVAR", "Next Sendungstitel");
        $this->RegisterVariableString("NextSendungsBeschrKurzVAR", "Next Sendungsbeschreibung kurz");
        $this->RegisterVariableString("NextSendungsBeschrLangVAR", "Next Sendungsbeschreibung lang");
        $this->RegisterVariableString("NextSendungsStartVAR", "Next Sendung Startzeit", "E2BY.Uhr");
		$this->RegisterVariableString("NextSendungsEndeVAR", "Next Sendung Endzeit", "E2BY.Uhr");
        $this->RegisterVariableInteger("NextSendungsdauerVAR", "Next Sendungsdauer Min.", "E2BY.Minuten");
        $this->RegisterVariableInteger("VolumeVAR", "Volume", "E2BY.Volume");
        $this->RegisterVariableBoolean("MuteVAR", "Mute");
        $this->RegisterVariableInteger("SenderAnzahlVAR", "Sender-Anzahl");  
        $this->RegisterVariableInteger("TimerAnzahlVAR", "Timer-Anzahl");
		$this->RegisterVariableString("TimerlisteVAR", "Timerliste", "~HTMLBox");
        $this->RegisterVariableString("EnigmaVersionVAR", "Enigma-Version");
        $this->RegisterVariableString("ImageVersionVAR", "Image-Version");
        $this->RegisterVariableString("WebIfVersionVAR", "WebIf-Version");
        $this->RegisterVariableString("BoxModelVAR", "Receiver Modell");
        $this->RegisterVariableInteger("EPGSucheErgebnisAnzahlVAR", "EPGSuchergebnis-Anzahl");
		$this->RegisterVariableString("EPGSucheErgebnisVAR", "EPGSuchergebnis", "~HTMLBox");
        
		if ($this->ReadPropertyBoolean("AufnahmenListeAuslesen") == true)
		{
			$this->RegisterVariableInteger("AufnahmenAnzahlVAR", "Aufnahmen-Anzahl");
			$this->RegisterVariableString("AufnahmenlisteVAR", "Aufnahmenliste", "~HTMLBox");
		}
		else
		{
			$this->UnregisterVariable("AufnahmenAnzahlVAR");
			$this->UnregisterVariable("AufnahmenlisteVAR");
		}
		
        if ($this->ReadPropertyBoolean("HDDverbaut") == true)
		{
			$this->RegisterVariableString("HDDModelVAR", "HDD Modell");
			$this->RegisterVariableInteger("HDDCapaVAR", "HDD Kapazität (gesamt)", "E2BY.GB");
			$this->RegisterVariableInteger("HDDCapaFreeVAR", "HDD Kapazität (frei)", "E2BY.GB");
      	}
      	else
      	{
			$this->UnregisterVariable("HDDModelVAR");
			$this->UnregisterVariable("HDDCapaVAR");
			$this->UnregisterVariable("HDDCapaFreeVAR");	
      	}
      	
      	if ($this->ReadPropertyBoolean("ErwInformationen") == true)
		{
			$this->RegisterVariableString("AktSenderSRefVAR", "Akt. Sender SRef");
			$this->RegisterVariableString("AktSenderPRefVAR", "Akt. Sender PRef");
			$this->RegisterVariableFloat("SignalSnrDbVAR", "Signal - SNR db", "E2BY.SNRdb");
			$this->RegisterVariableInteger("SignalSnrVAR", "Signal - SNR");
			$this->RegisterVariableInteger("SignalBerVAR", "Signal - BER");
			$this->RegisterVariableInteger("SignalAcgVAR", "Signal - ACG");
			$this->RegisterVariableString("LanIpVAR", "LAN - IP");
			$this->RegisterVariableString("LanMacVAR", "LAN - MAC");
			$this->RegisterVariableBoolean("LanDhcpVAR", "LAN - DHCP", "E2BY.inaktiv.aktiv");
			$this->RegisterVariableString("LanGwVAR", "LAN - Gateway");
			$this->RegisterVariableString("LanNetzmaskeVAR", "LAN - Netzmaske");
			$this->RegisterVariableInteger("VideoBreiteVAR", "Video - Breite");
			$this->RegisterVariableInteger("VideoHoeheVAR", "Video - Höhe");
			$this->RegisterVariableString("VideoBreiteHoeheVAR", "Video - Breite x Höhe");
			$this->RegisterVariableInteger("TonspurenAnzahlVAR", "Tonspuren-Anzahl");
			$this->RegisterVariableString("TonspurAktivVAR", "Tonspur-Aktiv");
			if ($this->FeaturePreCheck("downmix") === true)
			{
				$this->RegisterVariableBoolean("AC3DownmixStatusVAR", "AC3-Downmix", "E2BY.inaktiv.aktiv");
			}
			if ($this->FeaturePreCheck("sleeptimer") === true)
			{
				$this->RegisterVariableBoolean("SleeptimerAktiviertVAR", "Sleeptimer-Status", "E2BY.inaktiv.aktiv");
				$this->RegisterVariableInteger("SleeptimerMinutenVAR", "Sleeptimer-Minuten", "E2BY.Minuten");
				$this->RegisterVariableString("SleeptimerAktionVAR", "Sleeptimer-Aktion");
			}
      	}
      	else
      	{
			$this->UnregisterVariable("AktSenderSRefVAR");
			$this->UnregisterVariable("AktSenderPRefVAR");
			$this->UnregisterVariable("SignalSnrDbVAR");
			$this->UnregisterVariable("SignalSnrVAR");
			$this->UnregisterVariable("SignalBerVAR");
			$this->UnregisterVariable("SignalAcgVAR");
			$this->UnregisterVariable("LanIpVAR");
			$this->UnregisterVariable("LanMacVAR");
			$this->UnregisterVariable("LanDhcpVAR");
			$this->UnregisterVariable("LanGwVAR");
			$this->UnregisterVariable("LanNetzmaskeVAR");
			$this->UnregisterVariable("VideoBreiteVAR");
			$this->UnregisterVariable("VideoHoeheVAR");
			$this->UnregisterVariable("VideoBreiteHoeheVAR");
			$this->UnregisterVariable("TonspurenAnzahlVAR");
			$this->UnregisterVariable("TonspurAktivVAR");
			if ($this->FeaturePreCheck("downmix") === true)
			{
				$this->UnregisterVariable("AC3DownmixStatusVAR");
			}
			if ($this->FeaturePreCheck("sleeptimer") === true)
			{
				$this->UnregisterVariable("SleeptimerAktiviertVAR");
				$this->UnregisterVariable("SleeptimerMinutenVAR");
				$this->UnregisterVariable("SleeptimerAktionVAR");
			}
      	}
      	
      	//Timer einstellen
      	$this->SetTimerInterval("Refresh_All", $this->ReadPropertyInteger("IntervallRefresh"));
      	
      	//Daten in Variablen aktualisieren
      	if (strlen($this->ReadPropertyString("Enigma2IP")) != "")
      	{
			$this->UpdateAll();
	    }
	}
    
    public function UpdateAll()
    {
    	if (strlen($IP = $this->ReadPropertyString("Enigma2IP")) != "")
      	{
			if (@Sys_Ping($IP, 2000) === true)
			{
				$this->GetPowerState();
				$this->GetSystemInfos();
				$this->GetEPGInfos();
				$this->GetVolume();
				$this->GetTimerliste();
				$this->GetSenderliste();
				if ($this->ReadPropertyBoolean("AufnahmenListeAuslesen") == true)
				{
					$this->GetAufnahmenliste();  // dabei wird die HDD aus dem Standby geholt
				}
				if ($this->ReadPropertyBoolean("ErwInformationen") === true)
				{
					$this->GetSignalInfos();
					$this->GetTonspuren();
					if ($this->FeaturePreCheck("ac3downmix"))
					{
						$this->GetAC3DownmixInfo();
					}
					if ($this->FeaturePreCheck("sleeptimer"))
					{
						$this->GetSleeptimerInfos();
					}
				}
			}
      	}
    }

    public function TestMsg()
    {
		$Text_Test = "Das ist ein Test!";
		$Type_Test = 1;
		$Timeout_Test = 5;
		$result = $this->SendMsg($Text_Test, $Type_Test, $Timeout_Test);
		if ($result)
		{
			echo "Test-Nachricht wurde erfolgreich gesendet.";
		}
		else 
		{
			echo "Test-Nachricht konnte nicht gesendet werden!";
		}
    }
    
    public function TestKey()
    {
		$Key_Test = $this->ReadPropertyString("KeyDropDown");
		$LongShort_Test = "short";
		$result = $this->SendKey($Key_Test, $LongShort_Test);
		if ($result)
		{
			echo "Taste wurde erfolgreich gesendet.";
		}
		else 
		{
			echo "Taste konnte nicht gesendet werden!";
		}
    }
    
    public function TestMute()
    {
		$result = $this->SetVolume("MUTE");
		if ($result["Mute"] === true)
		{
			echo "Der Receiver hat jetzt den Mute-Status AKTIV.";
		}
		else
		{
			echo "Der Receiver hat jetzt den Mute-Status INAKTIV.";
		}
    }
    
    public function TestVolDown5()
    {
		$VolIST = $this->GetVolume();
		$VolSOLL = $VolIST["Volume"] - 5;
		if ($VolSOLL < 0)
		{
			$VolSOLL = 0;
		}
		$result = $this->SetVolume($VolSOLL);
		$echoText = "Die Lautstärke des Receiver wurde auf ".$result["Volume"]."% gestellt.";
		echo $echoText;
    }
    
    public function TestVolUp5()
    {
		$VolIST = $this->GetVolume();
		$VolSOLL = $VolIST["Volume"] + 5;
		if ($VolSOLL > 100)
		{
			$VolSOLL = 100;
		}
		$result = $this->SetVolume($VolSOLL);
		$echoText = "Die Lautstärke des Receiver wurde auf ".$result["Volume"]."% gestellt.";
		echo $echoText;
    }
    
    public function TestEPGSuche()
    {
		$Suchstring = $this->ReadPropertyString("EPGSuchstring");
		$result = $this->EPGSuche($Suchstring);
		if ($result)
		{
			$echoText = "EPG-Suche erfolgreich ausgeführt! Das Ergebnis der Suche ist in der Variable 'EPGSuchergebnis' zu finden.";
			echo $echoText;
		}
		else 
		{
			$echoText = "Im EPG wurde nichts passendes zum Suchbegriff '".$Suchstring."' gefunden!";
			echo $echoText;
		}
    }
    
    public function TestZap()
    {
		$Sendername = $this->ReadPropertyString("SenderZapTo");
		$result = $this->ZapTo($Sendername);
		if ($result)
		{
			$echoText = "Der Receiver wurde erfolgreich auf den Sender '".$Sendername."' geschaltet.";
			echo $echoText;
		}
		else 
		{
			$echoText = "Der Receiver konnte nicht auf den Sender '".$Sendername."' geschaltet werden! (Tippfehler? Ausgeschaltet?)";
			echo $echoText;
		}
    }    
    
    public function SendMsg(string $Text, integer $Type, integer $Timeout)
    {
		if ($this->GetPowerState() == 1)
		{
			if (IPS_SemaphoreEnter("Enigma2BY_SendMsg", 20000))
			{
			$IP = $this->ReadPropertyString("Enigma2IP");
			$WebPort = $this->ReadPropertyInteger("Enigma2WebPort");
			$Text = urlencode(trim($Text));
			$Text = str_replace('%A7', '%0A', $Text);
			$url = "http://".$IP.":".$WebPort."/web/message?text=".$Text."&type=".$Type."&timeout=".$Timeout;
			$xml = @simplexml_load_file($url);
			$result = $this->ResultAuswerten($xml->e2state);

			if ($Type == 0)
			{
				$this->SendKey("ArrowDown", "short");
				IPS_Sleep($Timeout * 1000 + 1000);
				$xml = @simplexml_load_file("http://".$IP.":".$WebPort."/web/messageanswer?getanswer=now");
				if ((trim($xml->e2statetext) == "Answer is NO!") OR (trim($xml->e2statetext) == "Antwort lautet NEIN!"))
				{
					$AntwortINT = 0;
				}
				elseif ((trim($xml->e2statetext) == "Answer is YES!") OR (trim($xml->e2statetext) == "Antwort lautet JA!"))
				{
					$AntwortINT = 1;
				}
				elseif ((trim($xml->e2statetext) == "No answer in time") OR (trim($xml->e2statetext) == "Keine rechtzeitige Antwort"))
				{
					$AntwortINT = 2;
					if ($this->DistroCheck() == true)
					{
						$this->SendKey("Exit", "short");
					}
					else
					{
						$this->SendKey("OK", "short");
					}
				}
				$this->SetValueInteger("FrageAntwortVAR", $AntwortINT);
				IPS_SemaphoreLeave("Enigma2BY_SendMsg");
				return $AntwortINT;
				}
			IPS_SemaphoreLeave("Enigma2BY_SendMsg");
			return $result;
			}
			else
			{
			return false;
			}
		}
		else
		{
			return false;
		}
    }
    
    public function SendKey(string $Key, string $LongShort)
    {
		if ($this->GetPowerState() == 1)
		{
			$IP = $this->ReadPropertyString("Enigma2IP");
			$WebPort = $this->ReadPropertyInteger("Enigma2WebPort");
			$CommandArray = array("Power" => "116", "1" => "2", "2" => "3", "3" => "4", "4" => "5", "5" => "6", "6" => "7", "7" => "8", "8" => "9", "9" => "10", "0" => "11", "VolumeUp" => "115", "VolumeDown" => "114", "MUTE" => "113", "Previous" => "412", "Next" => "407", "BouquetUp" => "402", "BouquetDown" => "403", "ArrowUp" => "103", "ArrowDown" => "108", "ArrowLeft" => "105", "ArrowRight" => "106", "Menu" => "139", "OK" => "352", "Info" => "358", "Audio" => "392", "Video" => "393", "RED" => "398", "GREEN" => "399", "YELLOW" => "400", "BLUE" => "401", "TV" => "377", "Radio" => "385", "Text" => "388", "Help" => "138", "Exit" => "174", "Rewind" => "168", "Play" => "207", "Stop" => "128", "Forward" => "159", "Record" => "167");
			$Command = $CommandArray[$Key];
			if ($Command != NULL)
			{
				if (($LongShort == "long") OR ($LongShort == "Long"))
				{
					$LongShort = "long";
				}
				elseif (($LongShort == "short") OR ($LongShort == "Short"))
				{
					$LongShort = "short";
				}
				$RCU = $this->ReadPropertyString("RCUdefault");
				$url = "http://".$IP.":".$WebPort."/web/remotecontrol?command=".$Command."&type=".$LongShort."&rcu=".$RCU;
				$xml = @simplexml_load_file($url);
				$result = $this->ResultAuswerten($xml->e2result);
				return $result;
			}
			else
			{
				return false;
			}
		}
		else
		{
		return false;
		}
    }
    
    public function GetEPGInfos()
    {
		if ($this->GetPowerState() == 1)
		{
			$IP = $this->ReadPropertyString("Enigma2IP");
			$WebPort = $this->ReadPropertyInteger("Enigma2WebPort");
			$url = "http://".$IP.":".$WebPort."/web/getcurrent";
			$xml = @simplexml_load_file($url);
			$E2_CurSendername = (string)trim($xml->e2service->e2servicename);
			$E2_SenderSRef = (string)trim($xml->e2service->e2servicereference);
			$E2_SenderSRef = substr($E2_SenderSRef, 0, -1);
			$E2_SenderPRef = str_replace(':', '_', $E2_SenderSRef); 
			$E2_CurSendungsname = (string)trim($xml->e2eventlist->e2event[0]->e2eventname);
			$E2_CurSendungsBeschrKurz = (string)trim($xml->e2eventlist->e2event[0]->e2eventdescription);
			$E2_CurSendungsBeschrLang = (string)trim($xml->e2eventlist->e2event[0]->e2eventdescriptionextended);
			$E2_CurSendungsStart_TS = (int)trim($xml->e2eventlist->e2event[0]->e2eventstart);
			$E2_CurSendungsdauerSek = (int)trim($xml->e2eventlist->e2event[0]->e2eventduration);
			$E2_CurSendungsrestdauerSek = (int)trim($xml->e2eventlist->e2event[0]->e2eventremaining);
			$E2_CurSendungEventID = (int)trim($xml->e2eventlist->e2event[0]->e2eventid);
			$E2_NextSendungsname = (string)trim($xml->e2eventlist->e2event[1]->e2eventname);
			$E2_NextSendungsBeschrKurz = (string)trim($xml->e2eventlist->e2event[1]->e2eventdescription);
			$E2_NextSendungsBeschrLang = (string)trim($xml->e2eventlist->e2event[1]->e2eventdescriptionextended);
			$E2_NextSendungsStart_TS = (int)trim($xml->e2eventlist->e2event[1]->e2eventstart);
			$E2_NextSendungsdauerSek = (int)trim($xml->e2eventlist->e2event[1]->e2eventduration);
			$E2_NextSendungEventID = (int)trim($xml->e2eventlist->e2event[1]->e2eventid);
			
			//Return-Array befüllen
			$E2_EPGInfo["AktSendername"] = $E2_CurSendername;
			$E2_EPGInfo["AktSendungsname"] = $E2_CurSendungsname;
			$E2_EPGInfo["AktSendungsBeschrKurz"] = $E2_CurSendungsBeschrKurz;
			$E2_EPGInfo["AktSendungsBeschrLang"] = $E2_CurSendungsBeschrLang;
			$E2_CurSendungsStart = date("H:i", $E2_CurSendungsStart_TS);
			$E2_CurSendungsEnde_TS = $E2_CurSendungsStart_TS + ($E2_CurSendungsdauerSek);
			$E2_CurSendungsEnde = date("H:i", $E2_CurSendungsEnde_TS);
			$E2_EPGInfo["AktSendungsStart"] = $E2_CurSendungsStart;
			$E2_EPGInfo["AktSendungsEnde"] = $E2_CurSendungsEnde;
			$E2_CurSendungsdauerMin = (int)($E2_CurSendungsdauerSek / 60);
			$E2_CurSendungsrestdauerMin = (int)($E2_CurSendungsrestdauerSek / 60);
			$E2_CurSendungsvergangenedauerSek = $E2_CurSendungsdauerSek - $E2_CurSendungsrestdauerSek;
			$E2_CurSendungsvergangenedauerMin = (int)(($E2_CurSendungsdauerSek - $E2_CurSendungsrestdauerSek) / 60);
			$E2_CurSendungsfortschritt_Proz = 100 - (int)($E2_CurSendungsrestdauerSek * 100 / $E2_CurSendungsdauerSek);
			$E2_EPGInfo["AktSendungsdauerSek"] = $E2_CurSendungsdauerSek;
			$E2_EPGInfo["AktSendungsdauerMin"] = $E2_CurSendungsdauerMin;
			$E2_EPGInfo["AktSendungsdauerRestSek"] = $E2_CurSendungsrestdauerSek;
			$E2_EPGInfo["AktSendungsdauerRestMin"] = $E2_CurSendungsrestdauerMin;
			$E2_EPGInfo["AktSendungsvergangenedauerSek"] = $E2_CurSendungsvergangenedauerSek;
			$E2_EPGInfo["AktSendungsvergangenedauerMin"] = $E2_CurSendungsvergangenedauerMin;
			$E2_EPGInfo["AktSendungsfortschrittProz"] = $E2_CurSendungsfortschritt_Proz;
			$E2_EPGInfo["AktSendungsEventID"] = $E2_CurSendungEventID;
			$E2_EPGInfo["NextSendungsname"] = $E2_NextSendungsname;
			$E2_EPGInfo["NextSendungsBeschrKurz"] = $E2_NextSendungsBeschrKurz;
			$E2_EPGInfo["NextSendungsBeschrLang"] = $E2_NextSendungsBeschrLang;
			$E2_NextSendungsStart = date("H:i", $E2_NextSendungsStart_TS);
			$E2_NextSendungsEnde_TS = $E2_NextSendungsStart_TS + ($E2_NextSendungsdauerSek);
			$E2_NextSendungsEnde = date("H:i", $E2_NextSendungsEnde_TS);
			$E2_EPGInfo["NextSendungsStart"] = $E2_NextSendungsStart;
			$E2_EPGInfo["NextSendungsEnde"] = $E2_NextSendungsEnde;
			$E2_NextSendungsdauerMin = (int)($E2_NextSendungsdauerSek / 60);
			$E2_EPGInfo["NextSendungsdauerSek"] = $E2_NextSendungsdauerSek;
			$E2_EPGInfo["NextSendungsdauerMin"] = $E2_NextSendungsdauerMin;
			$E2_EPGInfo["NextSendungsEventID"] = $E2_NextSendungEventID;
			$E2_EPGInfo["SenderSRef"] = $E2_SenderSRef;
			$E2_EPGInfo["SenderPRef"] = $E2_SenderPRef;
			
			//Variablen befüllen
			$this->SetValueString("AktSendernameVAR", $E2_CurSendername);
			$this->SetValueString("AktSendungsnameVAR", $E2_CurSendungsname);
			$this->SetValueString("AktSendungsBeschrKurzVAR", $E2_CurSendungsBeschrKurz);
			$this->SetValueString("AktSendungsBeschrLangVAR", $E2_CurSendungsBeschrLang);
			$this->SetValueString("AktSendungsStartVAR", $E2_CurSendungsStart);
			$this->SetValueString("AktSendungsEndeVAR", $E2_CurSendungsEnde);
			$this->SetValueInteger("AktSendungsfortschrittProzVAR", $E2_CurSendungsfortschritt_Proz);
			$this->SetValueInteger("AktSendungsvergangenedauerVAR", $E2_CurSendungsvergangenedauerMin);
			$this->SetValueInteger("AktSendungsdauerVAR", $E2_CurSendungsdauerMin);
			$this->SetValueInteger("AktSendungsdauerRestVAR", $E2_CurSendungsrestdauerMin);
			$this->SetValueString("NextSendungsnameVAR", $E2_NextSendungsname);
			$this->SetValueString("NextSendungsBeschrKurzVAR", $E2_NextSendungsBeschrKurz);
			$this->SetValueString("NextSendungsBeschrLangVAR", $E2_NextSendungsBeschrLang);
			$this->SetValueString("NextSendungsStartVAR", $E2_NextSendungsStart);
			$this->SetValueString("NextSendungsEndeVAR", $E2_NextSendungsEnde);
			$this->SetValueInteger("NextSendungsdauerVAR", $E2_NextSendungsdauerMin);
			if ($this->ReadPropertyBoolean("ErwInformationen") == true)
			{
				$this->SetValueString("AktSenderSRefVAR", $E2_SenderSRef);
				$this->SetValueString("AktSenderPRefVAR", $E2_SenderPRef);
			}
			return $E2_EPGInfo;
		}
		else
		{
			$this->SetValueString("AktSendernameVAR", "");
			$this->SetValueString("AktSendungsnameVAR", "");
			$this->SetValueString("AktSendungsBeschrKurzVAR", "");
			$this->SetValueString("AktSendungsBeschrLangVAR", "");
			$this->SetValueString("AktSendungsStartVAR", "");
			$this->SetValueString("AktSendungsEndeVAR", "");
			$this->SetValueInteger("AktSendungsfortschrittProzVAR", "");
			$this->SetValueInteger("AktSendungsvergangenedauerVAR", "");
			$this->SetValueInteger("AktSendungsdauerVAR", 0);
			$this->SetValueInteger("AktSendungsdauerRestVAR", 0);
			$this->SetValueString("NextSendungsnameVAR", "");
			$this->SetValueString("NextSendungsBeschrKurzVAR", "");
			$this->SetValueString("NextSendungsBeschrLangVAR", "");
			$this->SetValueString("NextSendungsStartVAR", "");
			$this->SetValueString("NextSendungsEndeVAR", "");
			$this->SetValueInteger("NextSendungsdauerVAR", 0);
			if ($this->ReadPropertyBoolean("ErwInformationen") == true)
			{
				$this->SetValueString("AktSenderSRefVAR", "");
				$this->SetValueString("AktSenderPRefVAR", "");
			}
			return false;
		}
	}
    
    public function GetSystemInfos()
    {
		$IP = $this->ReadPropertyString("Enigma2IP");
		$WebPort = $this->ReadPropertyInteger("Enigma2WebPort");
		if ($this->GetPowerState() != 0)
		{
			$url = "http://".$IP.":".$WebPort."/web/about";
			$xml = @simplexml_load_file($url);
			$E2_Enigmaversion = (string)trim($xml->e2about->e2enigmaversion);
			$E2_Imageversion = (string)trim($xml->e2about->e2imageversion);
			$E2_WebIfversion = (string)trim($xml->e2about->e2webifversion);
			$E2_BoxModel = (string)trim($xml->e2about->e2model);
			$this->SetValueString("EnigmaVersionVAR", $E2_Enigmaversion);
			$this->SetValueString("ImageVersionVAR", $E2_Imageversion);
			$this->SetValueString("WebIfVersionVAR", $E2_WebIfversion);
			$this->SetValueString("BoxModelVAR", $E2_BoxModel);
			$E2_SysInfo["EnigmaVersion"] = $E2_Enigmaversion;
			$E2_SysInfo["ImageVersion"] = $E2_Imageversion;
			$E2_SysInfo["WebIfVersion"] = $E2_WebIfversion;
			$E2_SysInfo["ReceiverModell"] = $E2_BoxModel;
			if ($this->ReadPropertyBoolean("HDDverbaut") == true)
			{
				//HDD Kapazität in welcher Größe angegeben?
				$HDDkapa = trim($xml->e2about->e2hddinfo->capacity);
				preg_match('|TB|', $HDDkapa, $matchTB);
				if ($matchTB)
				{
					$HDDkapa = $HDDkapa * 1024;
				}
				preg_match('|GB|', $HDDkapa, $matchGB);
				if ($matchGB)
				{
					$HDDkapa = $HDDkapa;
				}
				preg_match('|MB|', $HDDkapa, $matchMB);
				if ($matchMB)
				{
					$HDDkapa = $HDDkapa / 1024;
				}
				//HDD freie Kapazität in welcher Größe angegeben?
				$HDDkapafree = trim($xml->e2about->e2hddinfo->free);
				preg_match('|TB|', $HDDkapafree, $matchfreeTB);
				if ($matchfreeTB)
				{
					$HDDkapafree = $HDDkapafree * 1024;
				}
				preg_match('|GB|', $HDDkapafree, $matchfreeGB);
				if ($matchfreeGB)
				{
					$HDDkapafree = $HDDkapafree;
				}
				preg_match('|MB|', $HDDkapafree, $matchfreeMB);
				if ($matchfreeMB)
				{
					$HDDkapafree = $HDDkapafree / 1024;
				}
				//Variablen füllen und result zusammenstellen
				$E2_SysInfo["HDDModell"] = (string)trim($xml->e2about->e2hddinfo->model);
				$E2_SysInfo["HDDKapazitaetGB"] = $HDDkapa;
				$E2_SysInfo["HDDKapazitaetFreiGB"] = $HDDkapafree;
				$this->SetValueString("HDDModelVAR", $E2_SysInfo["HDDModell"]);
				$this->SetValueInteger("HDDCapaVAR", $E2_SysInfo["HDDKapazitaetGB"]);
				$this->SetValueInteger("HDDCapaFreeVAR", $E2_SysInfo["HDDKapazitaetFreiGB"]);
			}
			if ($this->ReadPropertyBoolean("ErwInformationen") == true)
			{
				$E2_SysInfo["LanIP"] = (string)trim($xml->e2about->e2lanip);
				$E2_SysInfo["LanMAC"] = (string)trim($xml->e2about->e2lanmac);
				$E2_SysInfo["LanDHCP"] = $this->ResultAuswerten(trim($xml->e2about->e2landhcp));
				$E2_SysInfo["LanGW"] = (string)trim($xml->e2about->e2langw);
				$E2_SysInfo["LanNETZMASKE"] = (string)trim($xml->e2about->e2lanmask);
				$E2_SysInfo["VideoBreite"] = (int)trim($xml->e2about->e2videowidth);
				$E2_SysInfo["VideoHoehe"] = (int)trim($xml->e2about->e2videoheight);
				$E2_SysInfo["VideoBreiteHoehe"] = (string)trim($xml->e2about->e2servicevideosize);
				$this->SetValueString("LanIpVAR", $E2_SysInfo["LanIP"]);
				$this->SetValueString("LanMacVAR", $E2_SysInfo["LanMAC"]);
				$this->SetValueBoolean("LanDhcpVAR", $E2_SysInfo["LanDHCP"]);
				$this->SetValueString("LanGwVAR", $E2_SysInfo["LanGW"]);
				$this->SetValueString("LanNetzmaskeVAR", $E2_SysInfo["LanNETZMASKE"]);
				$this->SetValueInteger("VideoBreiteVAR", $E2_SysInfo["VideoBreite"]);
				$this->SetValueInteger("VideoHoeheVAR", $E2_SysInfo["VideoHoehe"]);
				$this->SetValueString("VideoBreiteHoeheVAR", $E2_SysInfo["VideoBreiteHoehe"]);
			}
			return $E2_SysInfo;
		}
		else
		{
			return false;
		}
    }
    
    public function GetSignalInfos()
    {
		$IP = $this->ReadPropertyString("Enigma2IP");
		$WebPort = $this->ReadPropertyInteger("Enigma2WebPort");
		if ($this->GetPowerState() != 0)
		{
			$url = "http://".$IP.":".$WebPort."/web/signal?";
			$xml = @simplexml_load_file($url);
			$E2_SignalSNRdb = (string)trim($xml->e2snrdb);
			$E2_SignalSNR = (string)trim($xml->e2snr);
			$E2_SignalBER = (string)trim($xml->e2ber);
			$E2_SignalACG = (string)trim($xml->e2acg);
			$E2_SignalInfo["SignalSNRdb"] = $E2_SignalSNRdb;
			$E2_SignalInfo["SignalSNR"] = $E2_SignalSNR;
			$E2_SignalInfo["SignalBER"] = $E2_SignalBER;
			$E2_SignalInfo["SignalACG"] = $E2_SignalACG;
			if ($this->ReadPropertyBoolean("ErwInformationen") == true)
			{
				$this->SetValueFloat("SignalSnrDbVAR", $E2_SignalSNRdb);
				$this->SetValueInteger("SignalSnrVAR", $E2_SignalSNR);
				$this->SetValueInteger("SignalBerVAR", $E2_SignalBER);
				$this->SetValueInteger("SignalAcgVAR", $E2_SignalACG);
			}
			return $E2_SignalInfo;
		}
		else
		{
			return false;
		}
    }
    
    public function GetAC3DownmixInfo()
    {
		if ($this->GetPowerState() != 0)
		{
			if ($this->FeaturePreCheck("downmix") === true)
			{
				$IP = $this->ReadPropertyString("Enigma2IP");
				$WebPort = $this->ReadPropertyInteger("Enigma2WebPort");
				$url = "http://".$IP.":".$WebPort."/web/downmix";
				$xml = @simplexml_load_file($url);
				$E2_AC3DownmixStatus = $this->ResultAuswerten(trim($xml->e2state));
				$E2_AC3DownmixText = (string)trim($xml->e2statetext);
				$E2_AC3DownmixInfo["AC3DownmixStatus"] = $E2_AC3DownmixStatus;
				$E2_AC3DownmixInfo["AC3DownmixText"] = $E2_AC3DownmixText;
				if ($this->ReadPropertyBoolean("ErwInformationen") == true)
				{
					$this->SetValueBoolean("AC3DownmixStatusVAR", $E2_AC3DownmixStatus);
				}
				return $E2_AC3DownmixInfo;
			}
			else
			{
				IPS_LogMessage("ENIGMA2BY", "Diese Funktion ist mit diesem Receiver/Image nicht verfügbar!");
			}
		}
		else
		{
			return false;
		}
    }
    
    public function GetSleeptimerInfos()
    {
		if ($this->GetPowerState() == 1)
		{
			if ($this->FeaturePreCheck("sleeptimer") === true)
			{
				$IP = $this->ReadPropertyString("Enigma2IP");
				$WebPort = $this->ReadPropertyInteger("Enigma2WebPort");
				$url = "http://".$IP.":".$WebPort."/web/sleeptimer";
				$xml = @simplexml_load_file($url);
				$E2_SleeptimerEnabled = $this->ResultAuswerten(trim($xml->e2enabled));
				$E2_SleeptimerMinuten = (int)trim($xml->e2minutes);
				$E2_SleeptimerAktion = (string)trim($xml->e2action);
				$E2_SleeptimerText = (string)trim($xml->e2text);
				$E2_SleeptimerInfo["SleeptimerAktiviert"] = $E2_SleeptimerEnabled;
				$E2_SleeptimerInfo["SleeptimerMinuten"] = $E2_SleeptimerMinuten;
				$E2_SleeptimerInfo["SleeptimerAktion"] = $E2_SleeptimerAktion;
				$E2_SleeptimerInfo["SleeptimerText"] = $E2_SleeptimerText;
				if ($this->ReadPropertyBoolean("ErwInformationen") == true)
				{
					$this->SetValueBoolean("SleeptimerAktiviertVAR", $E2_SleeptimerEnabled);
					$this->SetValueInteger("SleeptimerMinutenVAR", $E2_SleeptimerMinuten);
					$this->SetValueString("SleeptimerAktionVAR", $E2_SleeptimerAktion);
				}
				return $E2_SleeptimerInfo;
			}
			else
			{
				IPS_LogMessage("ENIGMA2BY", "Diese Funktion ist mit diesem Receiver/Image nicht verfügbar!");
			}
		}
		else
		{
		return false;
		}
    }
    
    public function SetSleeptimer(integer $Minuten, string $Aktion, boolean $Aktiv)
    {
		if ($this->FeaturePreCheck("sleeptimer") === true)
		{
			$IP = $this->ReadPropertyString("Enigma2IP");
			$WebPort = $this->ReadPropertyInteger("Enigma2WebPort");
			if ($this->GetPowerState() == 1)
			{
				if ($Aktiv === true)
				{
					$Aktiv = "True";
				}
				else
				{
					$Aktiv = "False";
				}		    		
				$url = "http://".$IP.":".$WebPort."/web/sleeptimer?cmd=set&time=".$Minuten."&action=".$Aktion."&enabled=".$Aktiv;
				$xml = @simplexml_load_file($url);
				$E2_SleeptimerEnabled = $this->ResultAuswerten(trim($xml->e2enabled));
				$E2_SleeptimerMinuten = (int)trim($xml->e2minutes);
				$E2_SleeptimerAktion = (string)trim($xml->e2action);
				$E2_SleeptimerText = (string)trim($xml->e2text);
				$E2_SleeptimerInfo["SleeptimerAktiviert"] = $E2_SleeptimerEnabled;
				$E2_SleeptimerInfo["SleeptimerMinuten"] = $E2_SleeptimerMinuten;
				$E2_SleeptimerInfo["SleeptimerAktion"] = $E2_SleeptimerAktion;
				$E2_SleeptimerInfo["SleeptimerText"] = $E2_SleeptimerText;
				if ($this->ReadPropertyBoolean("ErwInformationen") == true)
				{
						$this->SetValueBoolean("SleeptimerAktiviertVAR", $E2_SleeptimerEnabled);
						$this->SetValueInteger("SleeptimerMinutenVAR", $E2_SleeptimerMinuten);
						$this->SetValueString("SleeptimerAktionVAR", $E2_SleeptimerAktion);
				}
				return $E2_SleeptimerInfo;
			}
			else
			{
				return false;
			}
		}
		else
		{
			IPS_LogMessage("ENIGMA2BY", "Diese Funktion ist mit diesem Receiver/Image nicht verfügbar!");
		}
    }
    
    public function GetPowerState()
    {
		$IP = $this->ReadPropertyString("Enigma2IP");
		$WebPort = $this->ReadPropertyInteger("Enigma2WebPort");
		if ((@Sys_Ping($IP, 2000) == false) AND ($this->ReadPropertyString("Enigma2IP") != ""))
		{
			$PowerStateIST = 0;
			$this->SetValueInteger("PowerStateVAR", 0); // AUS
		}
		else
		{
			$url = "http://".$IP.":".$WebPort."/web/powerstate";
			$xml = @simplexml_load_file($url);
			if (($this->ResultAuswerten(@$xml->e2instandby) == "false") OR (trim(@$xml->e2instandby) == "false"))
			{
				$PowerStateIST = 1;
				$this->SetValueInteger("PowerStateVAR", 1); // AN
			}
			else
			{
				$PowerStateIST = 2;
				$this->SetValueInteger("PowerStateVAR", 2); // STANDBY
			}
		}
		return $PowerStateIST;
    }
    
    public function SetTonspur(integer $TonspurID)
    {
		$IP = $this->ReadPropertyString("Enigma2IP");
		$WebPort = $this->ReadPropertyInteger("Enigma2WebPort");
		if ($this->GetPowerState() == 1)
		{
			$url = "http://".$IP.":".$WebPort."/web/selectaudiotrack?id=".$TonspurID;
			$xml = @simplexml_load_file($url);
			$result = (string)trim($xml[0]);
			if ($result == "Success")
			{
				return true;
			}
			else {
				return false;
			}
			$this->GetTonspuren();
		}
		else
		{
			return false;
		}
    }
    
    public function GetTonspuren()
    {
		$IP = $this->ReadPropertyString("Enigma2IP");
		$WebPort = $this->ReadPropertyInteger("Enigma2WebPort");
		if ($this->GetPowerState() == 1)
		{
			$url = "http://".$IP.":".$WebPort."/web/getaudiotracks";
			$xml = @simplexml_load_file($url);
			$TonspurenCount = count($xml->e2audiotrack);  // Anzahl der verfügbaren Tonspuren
			$this->SetValueInteger("TonspurenAnzahlVAR", $TonspurenCount);
			if ($TonspurenCount > 0)
			{
				$i = 0;
				foreach ($xml->e2audiotrack as $xmlnode)
				{
					$TonspurenAR[$i]["TonspurBeschreibung"] = (string)$xmlnode->e2audiotrackdescription;
					$TonspurenAR[$i]["TonspurID"] = (string)$xmlnode->e2audiotrackid;
					$TonspurenAR[$i]["TonspurPID"] = (string)$xmlnode->e2audiotrackpid;
					$TonspurenAR[$i]["TonspurAktiv"] = $this->ResultAuswerten(trim($xmlnode->e2audiotrackactive));
					if ($TonspurenAR[$i]["TonspurAktiv"] === true)
					{
						$this->SetValueString("TonspurAktivVAR", $TonspurenAR[$i]["TonspurBeschreibung"]);
					}
					$i++;
				}
				return $TonspurenAR;
			}
			else
			{
				return false;
			}
		}
		else
		{
			return false;
		}
    }
    
    public function GetVolume()
    {
		$IP = $this->ReadPropertyString("Enigma2IP");
		$WebPort = $this->ReadPropertyInteger("Enigma2WebPort");
		if ($this->GetPowerState() == 1)
		{
			$url = "http://".$IP.":".$WebPort."/web/vol";
			$xml = @simplexml_load_file($url);
			$E2_VolumeWert = (int)$xml->e2current;
			$this->SetValueInteger("VolumeVAR", $E2_VolumeWert);
			$E2_VolReturn["Volume"] = (int)trim($xml->e2current);
			$E2_VolReturn["Mute"] = (string)trim($xml->e2ismuted);
			$result = $this->ResultAuswerten($xml->e2ismuted);
			$this->SetValueBoolean("MuteVAR", $result);
			return $E2_VolReturn;
		}
		else
		{
			return false;
		}
    }
    
    public function SetVolume(string $Parameter)
    {
		$IP = $this->ReadPropertyString("Enigma2IP");
		$WebPort = $this->ReadPropertyInteger("Enigma2WebPort");
		if ($this->GetPowerState() == 1)
		{
			if (is_int($Parameter))
			{
				if (($Parameter < 0) OR ($Parameter > 100))
				{
					return "Ungültiger Wert für die Lautstärke! Erlaubte Werte sind 0 bis 100.";
				}
				else
				{
					$Befehl = "set".$Parameter;
				}
			}
			elseif (($Parameter == "+") OR ($Parameter == "up"))
			{
				$Befehl = "up";
			}
			elseif (($Parameter == "-") OR ($Parameter == "down"))
			{
				$Befehl = "down";
			}
			elseif (($Parameter == "MUTE") OR ($Parameter == "mute") OR ($Parameter == "Mute"))
			{
				$Befehl = "mute";
			}
			else
			{
				return "Unbekannter Befehl für die Funktion -SetVolume-";
			}
			$url = "http://".$IP.":".$WebPort."/web/vol?set=".$Befehl;
			$xml = @simplexml_load_file($url);
			$result = $this->ResultAuswerten($xml->e2ismuted);
			$E2_VolReturn["Volume"] = (int)trim($xml->e2current);
			$E2_VolReturn["Mute"] = $this->ResultAuswerten($xml->e2ismuted);
			$this->SetValueBoolean("MuteVAR", $E2_VolReturn["Mute"]);
			$this->GetVolume();
			return $E2_VolReturn;						
		}
		else
		{
			return false;
		}
    }
    
    public function AddTimerByEventID(string $sRef, integer $EventID, string $AufnahmePfad)
    {
		$IP = $this->ReadPropertyString("Enigma2IP");
		$WebPort = $this->ReadPropertyInteger("Enigma2WebPort");
		if ($this->GetPowerState() != 0)
		{
			$url = "http://".$IP.":".$WebPort."/web/timeraddbyeventid?sRef=".$sRef."&eventid=".$EventID."&dirname=".$AufnahmePfad;
			$xml = @simplexml_load_file($url);
			if ($xml === false)
			{
				return false;
			}
			$result = $this->ResultAuswerten($xml->e2state);
			$this->GetTimerliste();
			return $result;
		}
		else
		{
			return false;
		}
    }
    
    public function DelTimer(string $sRef, integer $TimerStartzeit, integer $TimerEndzeit)
    {
		$IP = $this->ReadPropertyString("Enigma2IP");
		$WebPort = $this->ReadPropertyInteger("Enigma2WebPort");
		if ($this->GetPowerState() != 0)
		{
			$url = "http://".$IP.":".$WebPort."/web/timerdelete?sRef=".$sRef."&begin=".$TimerStartzeit."&end=".$TimerEndzeit;
			$xml = @simplexml_load_file($url);
			if ($xml === false)
			{
				return false;
			}
			$result = $this->ResultAuswerten($xml->e2state);
			$this->GetTimerliste();
			return $result;
		}
		else
		{
			return false;
		}
    }
    
    public function SetPowerState(integer $PowerStateNr) 
	{ 
		$IP = $this->ReadPropertyString("Enigma2IP"); 
		$WebPort = $this->ReadPropertyInteger("Enigma2WebPort"); 
		if ($this->GetPowerState() != 0) 
		{ 
			$url = "http://".$IP.":".$WebPort."/web/powerstate?newstate=".$PowerStateNr; // 0=ToggleStandby,1=Deepstandby,2=Reboot,3=RestartGUI,4=Wakeup,5=Standby 
			$xml = @simplexml_load_file($url); 
			$E2_PowerstateStandby = (int)trim($xml->e2instandby); 

			switch ($PowerStateNr) 
			{ 
				case 0: 
					if ($E2_PowerstateStandby == true) 
					{ 
						$this->SetValueInteger("PowerStateVAR", 2); // STANDBY 
						return true; 
					} 
					else 
					{ 
						$this->SetValueInteger("PowerStateVAR", 1); // AN 
						return true;
					} 
				break; 

				case 1: 
					$this->SetValueInteger("PowerStateVAR", 0); // AUS 
					return true; 
				break; 

				case 2: 
					$this->SetValueInteger("PowerStateVAR", 0); // AUS 
					return true; 
				break; 

				case 3: 
					$this->SetValueInteger("PowerStateVAR", 1); // AN 
					return true; 
				break; 

				case 4: 
					$this->SetValueInteger("PowerStateVAR", 1); // AN 
					return true; 
				break; 

				case 5: 
					$this->SetValueInteger("PowerStateVAR", 2); // STANDBY 
					return true; 
				break; 
			} 
		} 
		else 
		{ 
			return false; 
		} 
	}  
				
	public function GetTimerliste()
    {
		$IP = $this->ReadPropertyString("Enigma2IP");
		$WebPort = $this->ReadPropertyInteger("Enigma2WebPort");
		if ($this->GetPowerState() != 0)
		{
			$url = "http://".$IP.":".$WebPort."/web/timerlist";
			$xml = @simplexml_load_file($url);
			$i = 0;
			foreach ($xml->e2timer as $xmlnode)
			{
				$TimerAR[$i]["ServiceReference"] = (string)$xmlnode->e2servicereference;
				$TimerAR[$i]["Sendername"] = (string)$xmlnode->e2servicename;
				$TimerAR[$i]["EventID"] = (int)$xmlnode->e2eit;
				$TimerAR[$i]["Sendungsname"] = (string)$xmlnode->e2name;
				$TimerAR[$i]["SendungsbeschreibungKurz"] = (string)$xmlnode->e2description;
				$TimerAR[$i]["SendungsbeschreibungLang"] = (string)$xmlnode->e2descriptionextended;
				$TimerAR[$i]["Sendungsbeginn"] = (int)$xmlnode->e2timebegin;
				$TimerAR[$i]["Sendungsende"] = (int)$xmlnode->e2timeend;
				$TimerAR[$i]["SendungsdauerSek"] = (int)$xmlnode->e2duration;
				$TimerAR[$i]["TimerArt"] = (int)$xmlnode->e2justplay; // TimerArt (0=Aufnahme,1=Umschalten,...)
				$TimerAR[$i]["Aufnahmeverzeichnis"] = (string)$xmlnode->e2location;
				$i++;
			}
			$TimerCount = count($xml->e2timer);
			$this->SetValueInteger("TimerAnzahlVAR", $TimerCount);
			
			if ($TimerCount > 0)
			{
				// HTML Ausgabe generieren
				$TitelAR = array("Sendername","Sendungstitel","Beschreibung","Beginn","Ende","Dauer","Art");
				$HTMLTimerliste = '<html><table>';
				$HTMLTimerliste .= '<tr><th>'.$TitelAR[3].'</th><th>'.$TitelAR[4].'</th><th>'.$TitelAR[0].'</th><th>'.$TitelAR[1].'</th><th colspan="2">'.$TitelAR[2].'</th><th>'.$TitelAR[5].'</th><th>'.$TitelAR[6].'</th></tr>';
				for ($h=0; $h<count($TimerAR); $h++)
				{
					// Timerbeginn-Anpassung
					$t = date("w", $TimerAR[$h]["Sendungsbeginn"]);
					$wochentage = array('So.','Mo.','Di.','Mi.','Do.','Fr.','Sa.');
					$TimerEintragSendungsbeginn = $wochentage[$t];
					$TimerEintragSendungsbeginn .= " ".date("j.m.Y H:i", $TimerAR[$h]["Sendungsbeginn"]);
					// Timerende-Anpassung
					$t = date('w', $TimerAR[$h]["Sendungsende"]);
					$wochentage = array('So.','Mo.','Di.','Mi.','Do.','Fr.','Sa.');
					$TimerEintragSendungsende = $wochentage[$t];
					$TimerEintragSendungsende .= " ".date("j.m.Y H:i", $TimerAR[$h]["Sendungsende"]);
					// Sendungsbeschreibung-Anpassung
					if ((strlen($TimerAR[$h]["SendungsbeschreibungKurz"]) != 0) AND (strlen($TimerAR[$h]["SendungsbeschreibungLang"]) != 0))
					{
						$TimerEintragBeschreibung = $TimerAR[$h]["SendungsbeschreibungKurz"].' || '.$TimerAR[$h]["SendungsbeschreibungLang"];
					}
					elseif ((strlen($TimerAR[$h]["SendungsbeschreibungKurz"]) == 0) AND (strlen($TimerAR[$h]["SendungsbeschreibungLang"]) != 0))
					{
						$TimerEintragBeschreibung = $TimerAR[$h]["SendungsbeschreibungLang"];
					}
					elseif ((strlen($TimerAR[$h]["SendungsbeschreibungKurz"]) != 0) AND (strlen($TimerAR[$h]["SendungsbeschreibungLang"]) == 0))
					{
						$TimerEintragBeschreibung = $TimerAR[$h]["SendungsbeschreibungKurz"];
					}
					else
					{
						$TimerEintragBeschreibung = "";
					}
					
					// Sendungsdauer-Anpassung
					$TimerEintragSendungsdauerMin = $TimerAR[$h]["SendungsdauerSek"] / 60;
					
					// TimerArt-Anpassung
					switch ($TimerAR[$h]["TimerArt"])
					{
						case 0:
							  $TimerEintragArt = "Aufnahme";
						break;
						case 1:
							  $TimerEintragArt = "Umschalten";
						break;
					}
					$HTMLTimerliste .= '<tr><th>'.$TimerEintragSendungsbeginn.' Uhr</th><th>'.$TimerEintragSendungsende.' Uhr</th><th>'.$TimerAR[$h]["Sendername"].'</th><th>'.$TimerAR[$h]["Sendungsname"].'</th><th colspan="2">'.$TimerEintragBeschreibung.'</th><th>'.$TimerEintragSendungsdauerMin.' Min.</th><th>'.$TimerEintragArt.'</th></tr>';
				}
				$HTMLTimerliste .= '</table></html>';
				$this->SetValueString("TimerlisteVAR", $HTMLTimerliste);
				return $TimerAR;
			}
			else
			{
				$HTMLTimerliste = '<html><b>Keine Timer vorhanden!</b></html>';
				$this->SetValueString("TimerlisteVAR", $HTMLTimerliste);
				return false;
			}
		}
		else
		{
			return false;
		}
    }
    
    public function GetAufnahmenliste()
    {
		$IP = $this->ReadPropertyString("Enigma2IP");
		$WebPort = $this->ReadPropertyInteger("Enigma2WebPort");
		if ($this->GetPowerState() != 0)
		{
			$url = "http://".$IP.":".$WebPort."/web/movielist";
			$xml = @simplexml_load_file($url);
			$i = 0;
			foreach ($xml->e2movie as $xmlnode)
			{
				$AufnahmenAR[$i]["Sendername"] = (string)$xmlnode->e2servicename; // Sendername
				$AufnahmenAR[$i]["Sendungstitel"] = (string)$xmlnode->e2title; // Titel
				$AufnahmenAR[$i]["SendungsbeschreibungLang"] = (string)$xmlnode->e2descriptionextended; // Sendungsbeschreibung lang
				$AufnahmenAR[$i]["SendungsdauerMin"] = (int)$xmlnode->e2length; // Sendungsdauer Min.
				$AufnahmenAR[$i]["SendungsDateigroesse"] = (int)$xmlnode->e2filesize; // Dateigröße der Sendung in Byte
				$i++;
			}
			$AufnahmenCount = count($xml->e2movie);  // Anzahl der Aufnahmen
			$this->SetValueInteger("AufnahmenAnzahlVAR", $AufnahmenCount);						
			
			if ($AufnahmenCount > 0)
			{
				// HTML Ausgabe generieren
				$TitelAR = array("Sendername","Sendungstitel","Beschreibung","Dauer","Dateigröße");
				$HTMLAufnahmenliste = '<html><table>';
				$HTMLAufnahmenliste .= '<tr><th>'.$TitelAR[0].'</th><th>'.$TitelAR[1].'</th><th>'.$TitelAR[2].'</th><th>'.$TitelAR[3].'</th><th>'.$TitelAR[4].'</th></tr>';
				
				for ($h=0; $h<count($AufnahmenAR); $h++)
				{
					// Dateigröße-Anpassung
					$AufnahmeEintragDateigroesseGB = round((float)$AufnahmenAR[$h]["SendungsDateigroesse"] / 1024 / 1024 / 1024, 2);
					$HTMLAufnahmenliste .= '<tr><th>'.$AufnahmenAR[$h]["Sendername"].'</th><th>'.$AufnahmenAR[$h]["Sendungstitel"].'</th><th>'.$AufnahmenAR[$h]["SendungsbeschreibungLang"].'</th><th>'.$AufnahmenAR[$h]["SendungsdauerMin"].' Min.</th><th>'.$AufnahmeEintragDateigroesseGB.' GB</th></tr>';
				}
				$HTMLAufnahmenliste .= '</table></html>';
				$this->SetValueString("AufnahmenlisteVAR", $HTMLAufnahmenliste);
				return $AufnahmenAR;
			}
			else
			{
				$HTMLAufnahmenliste = '<html><b>Keine Aufnahmen vorhanden!</b></html>';
				$this->SetValueString("AufnahmenlisteVAR", $HTMLAufnahmenliste);
				return false;
			}						
		}
		else
		{
			return false;
		}
    }
    
    public function GetSenderliste()
    {
		if ($this->GetPowerState() != 0)
		{
			$IP = $this->ReadPropertyString("Enigma2IP");
			$WebPort = $this->ReadPropertyInteger("Enigma2WebPort");
			$url = "http://".$IP.":".$WebPort."/web/getallservices";
			$xml = simplexml_load_file($url);
			foreach ($xml->e2bouquet as $xmlnode1)
			{
				foreach ($xmlnode1->e2servicelist->e2service as $xmlnode2)
				{
					$Sendername = (string)$xmlnode2->e2servicename; // Sendername
					$ServicesAR[$Sendername] = (string)$xmlnode2->e2servicereference; // SenderReference
				}
			}
			$SenderCount = count($ServicesAR);  // Anzahl der Sender
			if ($SenderCount > 0)
			{
				$this->SetValueInteger("SenderAnzahlVAR", $SenderCount);
				return $ServicesAR;
			}
			else
			{
				return false;
			}
		}
		else
		{
			return false;
		}
    }
    
    public function EPGSuche(string $Suchstring)
    {
		$IP = $this->ReadPropertyString("Enigma2IP");
		$WebPort = $this->ReadPropertyInteger("Enigma2WebPort");
		if ($this->GetPowerState() != 0)
		{
			$url = "http://".$IP."/web/epgsearch?search=".$Suchstring;
			$xml = simplexml_load_file($url);
			
			$i = 0;
			foreach ($xml->e2event as $xmlnode)
			{
				$EPGSucheAR[$i]["Sendername"] = (string)$xmlnode->e2eventservicename; // Sendername
				$EPGSucheAR[$i]["ServiceReference"] = (string)$xmlnode->e2eventservicereference; // ServiceReference
				$EPGSucheAR[$i]["EventID"] = (int)$xmlnode->e2eventid; // EventID
				$EPGSucheAR[$i]["Sendungsname"] = (string)$xmlnode->e2eventtitle; // Sendungsname
				$EPGSucheAR[$i]["SendungsbeschreibungKurz"] = (string)$xmlnode->e2eventdescription; // Sendungsbeschreibung kurz
				$EPGSucheAR[$i]["SendungsbeschreibungLang"] = (string)$xmlnode->e2eventdescriptionextended; // Sendungsbeschreibung lang
				$EPGSucheAR[$i]["Sendungsbeginn"] = (int)$xmlnode->e2eventstart; // Sendungsbeginn
				$EPGSucheAR[$i]["SendungsdauerSek"] = (int)$xmlnode->e2eventduration; // Sendungsdauer Sek.
				$EPGSucheAR[$i]["Sendungsende"] = $EPGSucheAR[$i]["Sendungsbeginn"] + $EPGSucheAR[$i]["SendungsdauerSek"]; // Sendungsende
				$i++;
			}
			$SuchCount = count($xml->e2event);  // Anzahl der Timer in der Liste
			$this->SetValueInteger("EPGSucheErgebnisAnzahlVAR", $SuchCount);
			
			// HTML Ausgabe generieren
			$TitelAR = array("Sendername","Sendungsname","Beschreibung","Beginn","Ende","Dauer");
			$HTMLEPGSuchergebnisse = '<html><table>';
			$HTMLEPGSuchergebnisse .= '<tr><th>'.$TitelAR[0].'</th><th>'.$TitelAR[1].'</th><th colspan="2">'.$TitelAR[2].'</th><th>'.$TitelAR[3].'</th><th>'.$TitelAR[4].'</th><th>'.$TitelAR[5].'</th></tr>';
			
			for ($h=0; $h<count($EPGSucheAR); $h++)
			{
				// Timerbeginn-Anpassung
				$t = date("w", $EPGSucheAR[$h]["Sendungsbeginn"]);
				$wochentage = array('So.','Mo.','Di.','Mi.','Do.','Fr.','Sa.');
				$EPGEintragSendungsbeginn = $wochentage[$t];
				$EPGEintragSendungsbeginn .= " ".date("j.m.Y H:i", $EPGSucheAR[$h]["Sendungsbeginn"]);
				// Timerende-Anpassung
				$t = date('w', $EPGSucheAR[$h]["Sendungsende"]);
				$wochentage = array('So.','Mo.','Di.','Mi.','Do.','Fr.','Sa.');
				$EPGEintragSendungsende = $wochentage[$t];
				$EPGEintragSendungsende .= " ".date("j.m.Y H:i", $EPGSucheAR[$h]["Sendungsende"]);
				// Sendungsbeschreibung-Anpassung
				if ((strlen($EPGSucheAR[$h]["SendungsbeschreibungKurz"]) > 2) AND (strlen($EPGSucheAR[$h]["SendungsbeschreibungLang"]) > 2))
				{
					$EPGEintragBeschreibung = $EPGSucheAR[$h]["SendungsbeschreibungKurz"].' || '.$EPGSucheAR[$h]["SendungsbeschreibungLang"];
				}
				elseif ((strlen($EPGSucheAR[$h]["SendungsbeschreibungKurz"]) < 2) AND (strlen($EPGSucheAR[$h]["SendungsbeschreibungLang"]) > 2))
				{
					$EPGEintragBeschreibung = $EPGSucheAR[$h]["SendungsbeschreibungLang"];
				}
				elseif ((strlen($EPGSucheAR[$h]["SendungsbeschreibungKurz"]) > 2) AND (strlen($EPGSucheAR[$h]["SendungsbeschreibungLang"]) < 2))
				{
					$EPGEintragBeschreibung = $EPGSucheAR[$h]["SendungsbeschreibungKurz"];
				}
				else
				{
					$EPGEintragBeschreibung = "";
				}
				// Sendungsdauer-Anpassung
				$EPGEintragSendungsdauerMin = $EPGSucheAR[$h]["SendungsdauerSek"] / 60;
				$HTMLEPGSuchergebnisse .= '<tr><th>'.$EPGSucheAR[$h]["Sendername"].'</th><th>'.$EPGSucheAR[$h]["Sendungsname"].'</th><th colspan="2">'.$EPGEintragBeschreibung.'</th><th>'.$EPGEintragSendungsbeginn.'</th><th>'.$EPGEintragSendungsende.'</th><th>'.$EPGEintragSendungsdauerMin.' Min.</th></tr>';
			}
			$HTMLEPGSuchergebnisse .= '</table></html>';
			$this->SetValueString("EPGSucheErgebnisVAR", $HTMLEPGSuchergebnisse);
			return $EPGSucheAR;
		}
		else
		{
			return false;
		}
    }
    
    public function ZapTo(string $Sendername)
    {
		if ($this->GetPowerState() == 1)
		{
			$ServicesAR = $this->GetSenderliste();
			$ServiceRef = @$ServicesAR[$Sendername];
			if ($ServiceRef != NULL)
			{
				$IP = $this->ReadPropertyString("Enigma2IP");
				$WebPort = $this->ReadPropertyInteger("Enigma2WebPort");
				$url = "http://".$IP.":".$WebPort."/web/zap?sRef=".$ServiceRef;
				$xml = @simplexml_load_file($url);
				$result = $this->ResultAuswerten($xml->e2state);
				$this->GetEPGInfos();
				return $result;
			}
			else
			{
				return false;
			}
		}
		else
		{
			return false;
		}
    }
    
    private function DistroCheck()
    {
		$IP = $this->ReadPropertyString("Enigma2IP");
		$WebPort = $this->ReadPropertyInteger("Enigma2WebPort");
		if ($this->GetPowerState() != 0)
		{
			$url = "http://".$IP.":".$WebPort."/web/about";
			$xml = @simplexml_load_file($url);
			$E2_Distroversion = (string)trim(@$xml->e2about->e2distroversion);
			if ($E2_Distroversion == "openatv")
			{
				return false;
			}
			else
			{
				return true;
			}
		}
    }
    
    private function FeaturePreCheck($feature)
    {
		$IP = $this->ReadPropertyString("Enigma2IP");
		$WebPort = $this->ReadPropertyInteger("Enigma2WebPort");
		if ($this->GetPowerState() != 0)
		{
			$url = "http://".$IP.":".$WebPort."/web/".$feature;
			$check = @Sys_GetURLContent($url);
			if ($check === false)
			{
				return false;
			}
			else
			{
				return true;
			}
		}
    }
    
    private function ResultAuswerten($result)
    {
			switch ($result)
			{
				case "True":
					return true;
				break;
				
				case "False":
					return false;
				break;
			}
		}
    
    private function SetValueInteger($Ident, $Value)
    {
		$ID = $this->GetIDForIdent($Ident);
		if (GetValueInteger($ID) <> $Value)
		{
			SetValueInteger($ID, intval($Value));
			return true;
		}
		return false;
    }
    
    private function SetValueFloat($Ident, $Value)
    {
        $ID = $this->GetIDForIdent($Ident);
        if (GetValueFloat($ID) <> $Value)
        {
            SetValueFloat($ID, intval($Value));
            return true;
        }
        return false;
    }
    
    private function SetValueString($Ident, $Value)
    {
        $ID = $this->GetIDForIdent($Ident);
        if (GetValueString($ID) <> $Value)
        {
            SetValueString($ID, strval($Value));
            return true;
        }
        return false;
    }
    
    private function SetValueBoolean($Ident, $Value)
    {
        $ID = $this->GetIDForIdent($Ident);
        if (GetValueBoolean($ID) <> $Value)
        {
            SetValueBoolean($ID, boolval($Value));
            return true;
        }
        return false;
    }
    
    protected function RegisterProfileBoolean($Name, $Icon, $Prefix, $Suffix, $MinValue, $MaxValue, $StepSize) {
		if(!IPS_VariableProfileExists($Name))
		{
			IPS_CreateVariableProfile($Name, 0);
		}
		else
		{
			$profile = IPS_GetVariableProfile($Name);
			if($profile['ProfileType'] != 0)
			throw new Exception("Variable profile type does not match for profile ".$Name);
		}
		IPS_SetVariableProfileIcon($Name, $Icon);
		IPS_SetVariableProfileText($Name, $Prefix, $Suffix);
		IPS_SetVariableProfileValues($Name, $MinValue, $MaxValue, $StepSize);
    }
    
    protected function RegisterProfileBooleanEx($Name, $Icon, $Prefix, $Suffix, $Associations) {
        if ( sizeof($Associations) === 0 ){
            $MinValue = 0;
            $MaxValue = 0;
        } else {
            $MinValue = $Associations[0][0];
            $MaxValue = $Associations[sizeof($Associations)-1][0];
        }
        
        $this->RegisterProfileBoolean($Name, $Icon, $Prefix, $Suffix, $MinValue, $MaxValue, 0);
        
        foreach($Associations as $Association) {
            IPS_SetVariableProfileAssociation($Name, $Association[0], $Association[1], $Association[2], $Association[3]);
        }
        
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
    
    protected function RegisterProfileStringEx($Name, $Icon, $Prefix, $Suffix, $Associations) {
        if ( sizeof($Associations) === 0 ){
            $MinValue = 0;
            $MaxValue = 0;
        } else {
            $MinValue = $Associations[0][0];
            $MaxValue = $Associations[sizeof($Associations)-1][0];
        }
        
        $this->RegisterProfileString($Name, $Icon, $Prefix, $Suffix, $MinValue, $MaxValue, 0);
        
        foreach($Associations as $Association) {
            IPS_SetVariableProfileAssociation($Name, $Association[0], $Association[1], $Association[2], $Association[3]);
        }
        
    }
    
    protected function RegisterProfileInteger($Name, $Icon, $Prefix, $Suffix, $MinValue, $MaxValue, $StepSize)
		{
				if (!IPS_VariableProfileExists($Name))
      	{
      			IPS_CreateVariableProfile($Name, 1);
      	}
      	else
      	{
      			$profile = IPS_GetVariableProfile($Name);
            if ($profile['ProfileType'] != 1)
            		throw new Exception("Variable profile type does not match for profile " . $Name);
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
    
    protected function RegisterProfileFloat($Name, $Icon, $Prefix, $Suffix, $MinValue, $MaxValue, $StepSize)
		{
				if (!IPS_VariableProfileExists($Name))
      	{
      			IPS_CreateVariableProfile($Name, 2);
      	}
      	else
      	{
      			$profile = IPS_GetVariableProfile($Name);
            if ($profile['ProfileType'] != 2)
            		throw new Exception("Variable profile type does not match for profile " . $Name);
      	}
      	IPS_SetVariableProfileIcon($Name, $Icon);
      	IPS_SetVariableProfileText($Name, $Prefix, $Suffix);
      	IPS_SetVariableProfileValues($Name, $MinValue, $MaxValue, $StepSize);
		}
    
    protected function RegisterTimer($Name, $Interval, $Script)
    {
        $id = @IPS_GetObjectIDByIdent($Name, $this->InstanceID);
        if ($id === false)
            $id = 0;


        if ($id > 0)
        {
            if (!IPS_EventExists($id))
                throw new Exception("Ident with name " . $Name . " is used for wrong object type", E_USER_WARNING);

            if (IPS_GetEvent($id)['EventType'] <> 1)
            {
                IPS_DeleteEvent($id);
                $id = 0;
            }
        }

        if ($id == 0)
        {
            $id = IPS_CreateEvent(1);
            IPS_SetParent($id, $this->InstanceID);
            IPS_SetIdent($id, $Name);
        }
        IPS_SetName($id, $Name);
        IPS_SetHidden($id, true);
        IPS_SetEventScript($id, $Script);
        if ($Interval > 0)
        {
            IPS_SetEventCyclic($id, 0, 0, 0, 0, 1, $Interval);

            IPS_SetEventActive($id, true);
        } else
        {
            IPS_SetEventCyclic($id, 0, 0, 0, 0, 1, 1);

            IPS_SetEventActive($id, false);
        }
    }

    protected function UnregisterTimer($Name)
    {
        $id = @IPS_GetObjectIDByIdent($Name, $this->InstanceID);
        if ($id > 0)
        {
            if (!IPS_EventExists($id))
                throw new Exception('Timer not present', E_USER_NOTICE);
            IPS_DeleteEvent($id);
        }
    }
    
    protected function UnregisterVariable($Name)
    {
        $id = @IPS_GetObjectIDByIdent($Name, $this->InstanceID);
        if ($id > 0)
        {
            if (!IPS_VariableExists($id))
                throw new Exception('Variable not present', E_USER_NOTICE);
            IPS_DeleteVariable($id);
        }
    }

    protected function SetTimerInterval($Name, $Interval)
    {
        $id = @IPS_GetObjectIDByIdent($Name, $this->InstanceID);
        if ($id === false)
            throw new Exception('Timer not present', E_USER_WARNING);
        if (!IPS_EventExists($id))
            throw new Exception('Timer not present', E_USER_WARNING);

        $Event = IPS_GetEvent($id);

        if ($Interval < 1)
        {
            if ($Event['EventActive'])
                IPS_SetEventActive($id, false);
        }
        else
        {
            if ($Event['CyclicTimeValue'] <> $Interval)
                IPS_SetEventCyclic($id, 0, 0, 0, 0, 1, $Interval);
            if (!$Event['EventActive'])
                IPS_SetEventActive($id, true);
        }
    }
}
?>