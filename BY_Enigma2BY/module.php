<?
/* 2do **************************************************************************************************************************
> Verfügbare Tonspuren + Infos auslesen (Beschreibung, TrackID, PID, Status) >> (Vars Erw. Features)
> Bestimmte Tonspur aktivieren/setzen
> PIcons (Bild-URL) 
> Bildinformationen der aktuellen Sendung/Sender >> (Vars Erw. Features)
> ...

> MultiEPG
> EPG Similar
> EPG Next (mit Angabe vom Sender oder alle Sender)
> EPGSearch
> EPG Kram allgemein
/web/epgbouquet?bRef=&time= 
/web/epgmulti?bRef=&time=&endTime= 
/web/epgnext?bRef= 
/web/epgnow?bRef= 
/web/epgnownext?bRef= 
/web/epgsearch.rss?search= 
/web/epgsearch?search= 
/web/epgservice?sRef=&time=&endTime= 
/web/epgservicenext?sRef= 
/web/epgservicenow?sRef= 
/web/epgsimilar?sRef=&eventid=  

*********************************************************************************************************************************/
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
        $this->RegisterPropertyString("IntervallRefresh", "60");
        $this->RegisterPropertyString("RCUdefault", "advanced");
        $this->RegisterPropertyString("KeyDropDown", "");
        $this->RegisterPropertyString("SenderZapTo", "");
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
        $this->RegisterProfileStringEx("E2BY.inaktiv.aktiv", "Information", "", "", Array(
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
        $this->RegisterVariableInteger("AktSendunsdauerVar", "Akt. Sendungsdauer Min.", "E2BY.Minuten");
        $this->RegisterVariableInteger("AktSendunsdauerRestVar", "Akt. Sendungsdauer Rest Min.", "E2BY.Minuten");
        $this->RegisterVariableString("NextSendungsnameVar", "Next Sendungstitel");
        $this->RegisterVariableString("NextSendungsBeschrKurzVAR", "Next Sendungsbeschreibung kurz");
        $this->RegisterVariableString("NextSendungsBeschrLangVAR", "Next Sendungsbeschreibung lang");
        $this->RegisterVariableString("NextSendungsStartVAR", "Next Sendung Startzeit");
        $this->RegisterVariableInteger("NextSendungsdauerVAR", "Next Sendungsdauer Min.", "E2BY.Minuten");
        $this->RegisterVariableInteger("VolumeVAR", "Volume", "E2BY.Volume");
        $this->RegisterVariableBoolean("MuteVAR", "Mute");
        $this->RegisterVariableInteger("SenderAnzahlVAR", "Sender-Anzahl");  
        $this->RegisterVariableInteger("TimerAnzahlVAR", "Timer-Anzahl");
		    $this->RegisterVariableString("TimerlisteVAR", "Timerliste", "~HTMLBox");
		    $this->RegisterVariableInteger("AufnahmenAnzahlVAR", "Aufnahmen-Anzahl");
		    $this->RegisterVariableString("AufnahmenlisteVAR", "Aufnahmenliste", "~HTMLBox");
        $this->RegisterVariableString("EnigmaVersionVAR", "Enigma-Version");
        $this->RegisterVariableString("ImageVersionVAR", "Image-Version");
        $this->RegisterVariableString("WebIfVersionVAR", "WebIf-Version");
        $this->RegisterVariableString("BoxModelVAR", "Receiver Modell");
        
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
		        $this->RegisterVariableFloat("SignalSnrDbVAR", "Signal - SNR db", "E2BY.SNRdb");
		        $this->RegisterVariableInteger("SignalSnrVAR", "Signal - SNR");
		        $this->RegisterVariableInteger("SignalBerVAR", "Signal - BER");
		        $this->RegisterVariableInteger("SignalAvgVAR", "Signal - ACG");
		        $this->RegisterVariableString("LanIpVAR", "LAN - IP");
		        $this->RegisterVariableString("LanMacVAR", "LAN - MAC");
		        $this->RegisterVariableBoolean("LanDhcpVAR", "LAN - DHCP", "E2BY.inaktiv.aktiv");
		        $this->RegisterVariableString("LanGwVAR", "LAN - Gateway");
		        $this->RegisterVariableString("LanNetzmaskeVAR", "LAN - Netzmaske");
      	}
      	else
      	{
		      	$this->UnregisterVariable("SignalSnrDbVAR");
						$this->UnregisterVariable("SignalSnrVAR");
						$this->UnregisterVariable("SignalBerVAR");
						$this->UnregisterVariable("SignalBerVAR");
						$this->UnregisterVariable("LanIpVAR");
						$this->UnregisterVariable("LanMacVAR");
						$this->UnregisterVariable("LanDhcpVAR");
						$this->UnregisterVariable("LanGwVAR");
						$this->UnregisterVariable("LanNetzmaskeVAR");
      	}
      	
      	
      	//Timer einstellen
      	$this->SetTimerInterval("Refresh_All", $this->ReadPropertyInteger("IntervallRefresh"));
      	
      	//Daten in Variablen aktualisieren
      	if (strlen($this->ReadPropertyString("Enigma2IP")) > 7)
      	{
						$this->UpdateAll();
		    }
		}
    
    public function UpdateAll()
    {
    		if (strlen($IP = $this->ReadPropertyString("Enigma2IP")) > 7)
      	{
      			if (Sys_Ping($IP, 2000) == true)
      			{
      					$this->GetSystemInfos();
				    		$this->GetEPGInfos();
				    		$this->GetVolume();
				    		$this->GetPowerState();
				    		$this->GetTimerliste();
				    		$this->GetAufnahmenliste();
				    		$this->GetSenderliste();
				    		if ($this->ReadPropertyBoolean("ErwInformationen") == true)
								{
				    				$this->GetSignalInfos();
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
    		if ($result[1] === true)
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
    		$VolSOLL = $VolIST[0] - 5;
    		if ($VolSOLL < 0)
    		{
    				$VolSOLL = 0;
    		}
    		$result = $this->SetVolume($VolSOLL);
    		$echoText = "Die Lautstärke des Receiver wurde auf ".$result[0]."% gestellt.";
 				echo $echoText;
    }
    
    public function TestVolUp5()
    {
    		$VolIST = $this->GetVolume();
    		$VolSOLL = $VolIST[0] + 5;
    		if ($VolSOLL > 100)
    		{
    				$VolSOLL = 100;
    		}
    		$result = $this->SetVolume($VolSOLL);
    		$echoText = "Die Lautstärke des Receiver wurde auf ".$result[0]."% gestellt.";
 				echo $echoText;
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
    
    public function SendMsg($Text, $Type, $Timeout)
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
										if (trim($xml->e2statetext) == "Answer is NO!")
										{
												$AntwortINT = 0;
										}
										elseif (trim($xml->e2statetext) == "Answer is YES!")
										{
												$AntwortINT = 1;
										}
										elseif (trim($xml->e2statetext) == "No answer in time")
										{
												$AntwortINT = 2;
												$this->SendKey("Exit", "short");
										}
										$this->SetValueInteger("FrageAntwortVAR", $AntwortINT);
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
    
    public function SendKey($Key, $LongShort)
    {
    		if ($this->GetPowerState() == 1)
    		{
		    		$IP = $this->ReadPropertyString("Enigma2IP");
		    		$WebPort = $this->ReadPropertyInteger("Enigma2WebPort");
		    		$CommandArray = array("Power" => "Power", "1" => "2", "2" => "3", "4" => "5", "5" => "6", "6" => "7", "7" => "8", "8" => "9", "9" => "10", "0" => "11", "VolumeUp" => "115", "VolumeDown" => "114", "MUTE" => "113", "Previous" => "412", "Next" => "407", "BouquetUp" => "402", "BouquetDown" => "403", "ArrowUp" => "103", "ArrowDown" => "108", "ArrowLeft" => "105", "ArrowRight" => "106", "Menu" => "139", "OK" => "352", "Info" => "358", "Audio" => "392", "Video" => "393", "RED" => "398", "GREEN" => "399", "YELLOW" => "400", "BLUE" => "401", "TV" => "377", "Radio" => "385", "Text" => "388", "Help" => "138", "Exit" => "174");
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
						$E2_CurSendungsname = (string)trim($xml->e2eventlist->e2event[0]->e2eventname);
						$E2_CurSendungsBeschrKurz = (string)trim($xml->e2eventlist->e2event[0]->e2eventdescription);
						$E2_CurSendungsBeschrLang = (string)trim($xml->e2eventlist->e2event[0]->e2eventdescriptionextended);
						$E2_CurSendungsdauerSek = (int)trim($xml->e2eventlist->e2event[0]->e2eventduration);
						$E2_CurSendungsrestdauerSek = (int)trim($xml->e2eventlist->e2event[0]->e2eventremaining);
						$E2_CurSendungEventID = (int)trim($xml->e2eventlist->e2event[0]->e2eventid);
						$E2_NextSendungsname = (string)trim($xml->e2eventlist->e2event[1]->e2eventname);
						$E2_NextSendungsBeschrKurz = (string)trim($xml->e2eventlist->e2event[1]->e2eventdescription);
						$E2_NextSendungsBeschrLang = (string)trim($xml->e2eventlist->e2event[1]->e2eventdescriptionextended);
						$E2_NextSendungStart = (int)trim($xml->e2eventlist->e2event[1]->e2eventstart);
						$E2_NextSendungsdauerSek = (int)trim($xml->e2eventlist->e2event[1]->e2eventduration);
						$E2_NextSendungEventID = (int)trim($xml->e2eventlist->e2event[1]->e2eventid);
						//Return-Array befüllen
						$E2_EPGInfo["AktSendername"] = $E2_CurSendername;
						$E2_EPGInfo["AktSendungsname"] = $E2_CurSendungsname;
						$E2_EPGInfo["AktSendungsBeschrKurz"] = $E2_CurSendungsBeschrKurz;
						$E2_EPGInfo["AktSendungsBeschrLang"] = $E2_CurSendungsBeschrLang;
						$E2_EPGInfo["AktSendunsdauer"] = $E2_CurSendungsdauerSek;
						$E2_EPGInfo["AktSendunsdauerRest"] = $E2_CurSendungsrestdauerSek;
						$E2_EPGInfo["AktSendungsEventID"] = $E2_CurSendungEventID;
						$E2_EPGInfo["NextSendungsname"] = $E2_NextSendungsname;
						$E2_EPGInfo["NextSendungsBeschrKurz"] = $E2_NextSendungsBeschrKurz;
						$E2_EPGInfo["NextSendungsBeschrLang"] = $E2_NextSendungsBeschrLang;
						$E2_EPGInfo["NextSendungsStart"] = $E2_NextSendungStart;
						$E2_EPGInfo["NextSendungsdauer"] = $E2_NextSendungsdauerSek;
						$E2_EPGInfo["NextSendungsEventID"] = $E2_NextSendungEventID;
						//Variablen befüllen
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
						$E2_NextSendungStart = date("H:i", intval($E2_NextSendungStart))." Uhr";
						$this->SetValueString("NextSendungsStartVAR", $E2_NextSendungStart);
						$E2_NextSendungsdauerMin = $E2_NextSendungsdauerSek / 60;
						$this->SetValueInteger("NextSendungsdauerVAR", $E2_NextSendungsdauerMin);
						return $E2_EPGInfo;
				}
				else
				{
							$this->SetValueString("AktSendernameVAR", "");
							$this->SetValueString("AktSendungsnameVAR", "");
							$this->SetValueString("AktSendungsBeschrKurzVAR", "");
							$this->SetValueString("AktSendungsBeschrLangVAR", "");
							$this->SetValueInteger("AktSendunsdauerVar", 0);
							$this->SetValueInteger("AktSendunsdauerRestVar", 0);
							$this->SetValueString("NextSendungsnameVar", "");
							$this->SetValueString("NextSendungsBeschrKurzVAR", "");
							$this->SetValueString("NextSendungsBeschrLangVAR", "");
							$this->SetValueString("NextSendungsStartVAR", "");
							$this->SetValueInteger("NextSendungsdauerVAR", 0);
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
								$E2_SysInfo["HDDModell"] = (string)trim($xml->e2about->e2hddinfo->model);
								$E2_SysInfo["HDDKapazitaetGB"] = (int)trim($xml->e2about->e2hddinfo->capacity);
								$E2_SysInfo["HDDKapazitaetFreiGB"] = (int)trim($xml->e2about->e2hddinfo->free);
								$this->SetValueString("HDDModelVAR", $E2_SysInfo["HDDModell"]);
								$this->SetValueInteger("HDDCapaVAR", $E2_SysInfo["HDDKapazitaetGB"]);
								$this->SetValueInteger("HDDCapaFreeVAR", $E2_SysInfo["HDDKapazitaetFreiGB"]);
						}
						if ($this->ReadPropertyBoolean("ErwInformationen") == true)
						{
								$E2_SysInfo["LanIP"] = (string)trim($xml->e2about->e2lanip);
								$E2_SysInfo["LanMAC"] = (string)trim($xml->e2about->e2lanmac);
								$E2_SysInfo["LanDHCP"] = (string)trim($xml->e2about->e2landhcp);
								$E2_SysInfo["LanGW"] = (string)trim($xml->e2about->e2langw);
								$E2_SysInfo["LanNETZMASKE"] = (string)trim($xml->e2about->e2lanmask);
								$this->SetValueString("HDDModelVAR", $E2_SysInfo["LanIP"]);
								$this->SetValueString("HDDModelVAR", $E2_SysInfo["LanMAC"]);
								$this->SetValueBoolean("HDDModelVAR", $E2_SysInfo["LanDHCP"]);
								$this->SetValueString("HDDModelVAR", $E2_SysInfo["LanGW"]);
								$this->SetValueString("HDDModelVAR", $E2_SysInfo["LanNETZMASKE"]);
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
    
    public function GetPowerState()
    {
    		$IP = $this->ReadPropertyString("Enigma2IP");
    		$WebPort = $this->ReadPropertyInteger("Enigma2WebPort");
    		if (Sys_Ping($IP, 2000) == false)
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
    
    public function SetVolume($Parameter)
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
						else {
								return "Unbekannter Befehl für die Funktion -SetVolume-";
						}
						$url = "http://".$IP.":".$WebPort."/web/vol?set=".$Befehl;
						$xml = @simplexml_load_file($url);
						$result = $this->ResultAuswerten($xml->e2ismuted);
						$E2_VolReturn["Volume"] = (int)trim($xml->e2current);
						$E2_VolReturn["Mute"] = $this->ResultAuswerten($xml->e2ismuted);
						$this->SetValueBoolean("MuteVAR", $E2_VolReturn[1]);
						return $E2_VolReturn;						
				}
				else
				{
						return false;
				}
    }
    
    public function SetPowerState($PowerStateNr)
    {
    		$IP = $this->ReadPropertyString("Enigma2IP");
    		$WebPort = $this->ReadPropertyInteger("Enigma2WebPort");
    		if ($this->GetPowerState() != 0)
    		{
		    		$url = "http://".$IP.":".$WebPort."/web/powerstate?newstate=".$PowerStateNr; // 0=ToggleStandby,1=Deepstandby,2=Reboot,3=RestartGUI
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
    
    public function ZapTo($Sendername)
    {
    		if ($this->GetPowerState() == 1)
    		{
		    		$ServicesAR = $this->GetSenderliste();
		    		$ServiceRef = @$ServicesAR[$Sendername];
		    		if ($ServiceRef != NULL)
						{
								$IP = $this->ReadPropertyString("Enigma2IP");
					    	$url = "http://".$IP.":".$WebPort."/web/zap?sRef=".$ServiceRef;
								$xml = @simplexml_load_file($url);
								$result = $this->ResultAuswerten($xml->e2state);
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
        
        $this->RegisterProfileInteger($Name, $Icon, $Prefix, $Suffix, $MinValue, $MaxValue, 0);
        
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