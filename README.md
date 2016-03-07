### IP-Symcon Modul // Enigma2BY
---

## Dokumentation

**Inhaltsverzeichnis**

1. [Funktionsumfang](#1-funktionsumfang) 
2. [Systemanforderungen](#2-systemanforderungen)
3. [Installation](#3-installation)
4. [Befehlsreferenz](#4-befehlsreferenz)
5. [Changelog](#5-changelog)


## 1. Funktionsumfang
Dieses Modul stellt viele praktische Funktionen zur Verfügung, damit ein Receiver mit
Enigma2 System gesteuert und Informationen ausgelesen werden können. Für jeden
Receiver muss eine eigene Modul-Instanz angelegt werden.
Da manche Images nicht alle Informationen/Funkionen bieten (z.B. OpenATV), wird vorher geprüft, ob die Funktion mit dem Image möglich ist.

###### Übersicht der Features (die Funktionen findet ihr in der Befehlsreferenz):
**Nachrichten an Receiver senden:**
- Info (Nachricht mit Info-Symbol)
- Message (Nachricht mit Message-Symbol)
- Attention (Nachricht mit Attention-Symbol)<br>
- Ja/Nein Frage (Am TV kann mit Ja/Nein geantwortet werden und die Antwort wird in eine Variable geschrieben und von der Funktion zurückgegeben)

    > Alle Nachrichten haben einen einstellbaren Timeout und werden automatisch nach den gewählten
    > X Sekunden wieder ausgeblendet. Wird als Timeout eine "0" angegeben, dann ist dieser inaktiv
    > und die Nachricht muss aktiv am Receiver weg gedrückt werden.

    > Ein Zeilenumbruch in der Nachricht kann mit dem Zeichen **§** erzeugt werden. Dieses Zeichen
    > wird vom Modul erkannt und automatisch in einen Zeilenumbruch umgewandelt.

**Tasten an den Receiver senden**
- Funktion zum Senden verschiedener Taste an den Receiver<br>
    Verfügbare Tasten:
    *Power,0,1,2,3,4,5,6,7,8,9,VolumeUp,VolumeDown,MUTE,Previous,Next,BouquetUp,BouquetDown,ArrowUp,ArrowDown,ArrowLeft,ArrowRight,Menu,OK,Info,Audio,Video,RED,GREEN,YELLOW,BLUE,TV,Radio,Text,Help,Exit,Rewind,Play,Stop,Rewind,Record*<br>

**Informationen über das Receiver-System auslesen (einstellbarer Intervall):**  
- Enigma-Version
- Image-Version
- WebIf-Version
- Festplatte > Modell    *wenn HDD verbaut*
- Festplatte > Kapazität (in MB)    *wenn HDD verbaut*
- Festplatte > Freie Kapazität (in MB)    *wenn HDD verbaut*
- Netzwerk-Infos > IP, Mac, GW, Netzmaske, DHCP    *nur in Variablen, wenn "Erw. Informationen" aktiv*
- Bildinformationen (Breite x Höhe in Pixel)    *nur in Variablen, wenn "Erw. Informationen" aktiv*

**Informationen über den aktuellen/nächsten Sender/Sendung (einstellbarer Intervall):**
- Aktueller Sendername, Sendungsname, Sendungsbeschreibung kurz, Sendungsbeschreibung lang, Sendungsdauer,
  Sendungsrestdauer, EventID
- Nächster Sendungsname, Sendungsbeschreibung kurz, Sendungsbeschreibung lang, Sendungsstart, Sendungsdauer, EventID

**Timerliste auslesen (einstellbarer Intervall)**
- Liest die Timerliste aus dem Receiver aus, gibt diese als Array zurück und speichert die Daten in eine Variable (HTMLBox)

**Timer bearbeiten**
- Hinzufügen eines Aufnahme-Timer
- Entfernen eines Timer

**Aufnahmenliste auslesen (einstellbarer Intervall)**
- Liest die Aufnahmenliste aus dem Receiver aus, gibt diese als Array zurück und speichert die Daten in eine Variable (HTMLBox)

**EPG Suche**
- Durchsucht das EPG anhand des/der angegebenen Suchbegriff/e, gibt das Ergebnis als Array zurück und stellt es in einer Variable
  als HTMLBox dar. Damit kann man sich eine Sendungen-Suchmaske in seine Visualisierung einbauen.

**Eingestellte Lautstärke vom Receiver auslesen und setzen**
- Liest die aktuelle Receiver-Lautstärke aus (Volume 0-100 und Mute aktiv/inaktiv)
- Steuert die Lautstärke des Receiver (bestimmter Wert, leiser, lauter, Toggle Mute)<br>
    > !Achtung! Lautstärke kann nur geändert werden, wenn AC3-Ton im Receiver nicht als Default gewählt wurde!

**Power-Zustand des Receiver auslesen und steuern**
- Liest den Power-Status des Receiver aus (eingeschaltet, ausgeschaltet, Standby)
- Steuert den Power-Status des Receiver (Toggle Standby, Deep Standby, Reboot, Restart GUI)

**Senderliste auslesen**
- Liest alle Sender der Senderliste mit ServiceReferenznummer aus und gibt es in einem Array zurück

**Sender umschalten**
- Schaltet auf dem Receiver auf den angegeben Sender um

**Signalstärke auslesen**
- Liest die Signalstärke aus (SNR db, SNR, BER, ACG), schreibt sie ggf. in die Variablen und gibt alles in einem Array zurück.
  *nur in Variablen, wenn "Erw. Informationen" aktiv*
  
**Tonspuren auslesen**
- Liest die verfügbaren Tonspuren der Sendung aus, gibt die Infos als Array zurück und speichert die Infos in Variablen.
  *nur in Variablen, wenn "Erw. Informationen" aktiv*

**AC3 Downmix Einstellungen auslesen**
- Liest die Einstellungen zu AC3-Downmix aus, gibt die Infos als Array zurück und speichert die Infos in Variablen.
  *nur in Variablen, wenn "Erw. Informationen" aktiv* // *Bei OpenATV Images nicht verfügbar*

**Sleeptimer auslesen**
- Liest die Einstellungen des Sleeptimer aus, gibt die Infos als Array zurück und speichert die Infos in Variablen.
  *nur in Variablen, wenn "Erw. Informationen" aktiv* // *Bei OpenATV Images nicht verfügbar*

**Sleeptimer**
- Liest die Einstellungen des Sleeptimer (Aktiviert,Minuten,Aktion,Bestaetigt,Text) aus, gibt die Infos als Array zurück
  und speichert die Infos in Variablen (auch beim Setzen/Aktivieren des Sleeptimer - zur Kontrolle).
  *nur in Variablen, wenn "Erw. Informationen" aktiv*
- Setzen/Aktivieren des Sleeptimer mit den gewählten Einstellungen (Minuten [0-999], Aktion [Standby/Shutdown], Aktiviert [true/false]).
  *Bei OpenATV Images nicht verfügbar*


## 2. Systemanforderungen
- IP-Symcon ab Version 4.x


## 3. Installation
Über die Kern-Instanz "Module Control" folgende URL hinzufügen:

`git://github.com/BayaroX/BY_Enigma2BY.git`

Die neue Instanz findet ihr in der IPS-Console, in dem Ordner in dem ihr die Instanz erstellt habt.


## 4. Befehlsreferenz
```php
  Enigma2BY_SendMsg(integer $InstanzID, string $Text, integer $Type, integer $TimeoutSekunden);
```
Schickt eine Nachricht an den Receiver und dieser zeigt diese dann auf TV/Beamer an.
Liefert ein Array mit Informationen zurück, ob die Nachricht erfolgreich gesendet wurde oder nicht.
Diese Funktion läuft durch eine Semaphore mit max. 20 Sekunden, falls 2 Fragen gleichzeitig an den
Receiver gesendet werden. Das muss bei der Wahl der Timeout-Länge beachtet werden!<br>
$Type:<br>
1 = Schickt eine Nachricht mit einem "Info-Symbol" an den Receiver.<br>
2 = Schickt eine Nachricht ohne Symbol an den Receiver.<br>
3 = Schickt eine Nachricht mit einem "Achtung-Symbol" an den Receiver.<br>
0 = Schickt eine Nachricht/Frage mit Ja/Nein als Antwortmöglichkeit an den Receiver.<br>
Die Antwort steht dann in der Integer-Variable "Frage-Antwort" und in der Rückmeldung der Funktion.<br>
Antwort-Bedeutungen: 0 = Nein // 1 = Ja // 2 = Keine Antwort innerhalb Timeout-Zeit

```php
  Enigma2BY_SendKey(integer $InstanzID, string $Key, string $LongShort);
```
Löst einen virtuellen Tastendruck, der gewählten Taste, am Receiver aus. Eine Liste
der verfügbaren Tasten ist in der Modul-Instanz zu finden (DropDown-Auswahl).
Bei $LongShort muss entweder "long" oder "short" angegeben werden (langer/kurzer Tastendruck).
Liefert true/false zurück, ob die Taste gesendet wurde oder nicht.

```php
  Enigma2BY_GetEPGInfos(integer $InstanzID);
```
Liest EPG-Infos vom Receiver aus (siehe Funktionsumfang), speichert diese in Variablen und
gibt die Daten als Array zurück.

```php
  Enigma2BY_GetSystemInfos(integer $InstanzID);
```
Liest Systeminfos vom Receiver aus (siehe Funktionsumfang), speichert diese in Variablen und
gibt die Daten als Array zurück.

```php
  Enigma2BY_GetTimerliste(integer $InstanzID);
```
Liest alle Daten aus dem Receiver aus, aktualisiert die Variable und gibt die Daten als Array zurück.

```php
  Enigma2BY_GetPowerState(integer $InstanzID);
```
Liefert den Power-Zustand des Receiver zurück (0 = ausgeschaltet // 1 = eingeschaltet // 2 = Standby)

```php
  Enigma2BY_SetPowerState(integer $InstanzID, integer $PowerState);
```
Steuert den Power-Zustand des Receiver (0 = Toggle Standby // 1 = Deep Standby // 2 = Reboot // 3 = Restart GUI // 4 = Wakeup from Standby // 5 = Standby).
Liefert true/false zurück, ob der Power-State an den Receiver gesendet werden konnte oder nicht.

```php
  Enigma2BY_GetVolume(integer $InstanzID);
```
Liefert ein Array mit Volume-Informationen zurück (Volume 0-100, Mute true/false).

```php
  Enigma2BY_SetVolume(integer $InstanzID, integer $Volume);
  Enigma2BY_SetVolume(integer $InstanzID, string "+");
  Enigma2BY_SetVolume(integer $InstanzID, string "-");
  Enigma2BY_SetVolume(integer $InstanzID, string "MUTE");
```
Setzt die Lautstärke des Receiver auf den angegebenen Wert (0-100). Die anderen Parameter steuern
lauter/leiser/Mute Toggle. Liefert jeweils ein Array mit Volume-Informationen zurück (Volume 0-100, Mute true/false).
> !Achtung! Lautstärke kann nur geändert werden, wenn AC3-Ton im Receiver nicht als Default gewählt wurde!

```php
  Enigma2BY_GetTimerliste(integer $InstanzID);
```
Liefert ein Array mit allen Timern + Details zurück und schreibt die Daten in eine
Variable (als HTML-Tabelle).

```php
  Enigma2BY_AddTimerByEventID(integer $InstanzID, string $sRef, integer $EventID, string $AufnahmePfad);
```
Erstellt einen Aufnahme-Timer für die angegebene Sendung. sRef und EventID einer Sendung können über die
EPGSuche-Funktion ermittelt werden. Der Aufnahmepfad bei einer eingebauten Festplatte ist "/hdd/movie/".
Liefert true/false zurück, je nachdem, ob der Timer erstellt wurde oder nicht.

```php
  Enigma2BY_DelTimer(integer $InstanzID, string $sRef, integer $Sendungsbeginn, integer $Sendungsende);
```
Entfernt einen Timer aus der Timerliste. Die erforderlichen Daten können mit der "GetTimerliste"-Funktion
ermittelt werden. Sendungsbeginn und -Ende müssen als Unix-Timestamp angegeben werden. Liefert true/false zurück,
je nachdem, ob der Timer entfernt werden konnte oder nicht.

```php
  Enigma2BY_GetAufnahmenliste(integer $InstanzID);
```
Liefert ein Array mit allen Aufnahmen + Details zu allen aufgenommenen Sendungen
zurück und schreibt die Daten in eine Variable (als HTML-Tabelle).

```php
  Enigma2BY_GetSenderliste(integer $InstanzID);
```
Liefert ein Array mit allen Sendernamen + ServiceReferenznummer zurück.

```php
  Enigma2BY_ZapTo(integer $InstanzID, string $Sendername);
```
Schaltet den Receiver auf den gewählten Sender. Der Sender muss 1:1 so geschrieben werden, wie
er im Receiver gespeichert ist. Der genaue Name kann auch über die Funktion "Enigma2BY_GetSenderliste"
ermittelt werden.

```php
  Enigma2BY_GetSignalInfos(integer $InstanzID);
```
Liest die aktuellen Signalstärken aus dem Receiver aus (SNR db, SNR, BER, ACG), speichert die Daten ggf.
in Variablen und gibt die Informationen in einem Array zurück.

```php
  Enigma2BY_GetTonspuren(integer $InstanzID);
```
Liest die verfügbaren Tonspuren der Sendung aus, speichert die Daten ggf. in Variablen und gibt die
Informationen in einem Array zurück.

```php
  Enigma2BY_SetTonspur(integer $InstanzID, integer $TonspurID);
```
Setzt die gewählte Tonspur für die aktuelle Sendung und gibt true/false zurück, wenn die Tonspur gesetzt oder
nicht gesetzt werden konnte. Die verfügbaren Tonspuren mit ID kann man über "Enigma2BY_GetTonspuren" auslesen.

```php
  Enigma2BY_EPGSuche(integer $InstanzID, string $Suchbegriff);
```
Durchsucht das EPG, anhand des/der gewählten Suchbegriff/e, gibt das Ergebnis als Array zurück und stellt die
Rückgabe in einer Variable (als HTMLBox) dar.

```php
  Enigma2BY_GetAC3DownmixInfo(integer $InstanzID);
```
Liest die aktuelle Einstellung für "AC3 Downmix" aus, speichert das Ergebnis ggf. in eine Variable und gibt die
Informationen in einem Array zurück.

```php
  Enigma2BY_GetSleeptimer(integer $InstanzID);
```
Liest die aktuelle Einstellungen des Sleeptimer aus, speichert das Ergebnis ggf. in Variablen und gibt die
Informationen in einem Array zurück.

```php
  Enigma2BY_SetSleeptimer(integer $InstanzID, integer $Minuten, string $Aktion, bool $Aktiv);
```
Setzt die Einstellungen für den Sleeptimer bzw. aktiviert/deaktiviert ihn. Die übernommenen Einstellungen werden
dann im Array zurück geliefert (zur Kontrolle) und ggf. in Variablen geschrieben.<br>
$Minuten (0-999)<br>
$Aktion (standby/shutdown)<br>
$Aktiv (true/false)<br>


## 5. Changelog
Version 1.0:
  - Erster Release
  
Version 1.1:
  - NEU # GetSignalInfos, GetTonspuren, SetTonspur, Netzwerkinformationen, Bildinformationen
  - NEU # Neustart und GUI-Neustart jetzt direkt in der Instanz möglich
  - NEU # Eingabe eines Port für das WebInterface des Receivers
  - FIX # HDD Werte wurden in MB angezeigt, statt in GB
  - FIX # Bei XTrend/VU+ wurde die Kapazität der HDD teilweise in TB zurückgegeben, wird jetzt in GB umgerechnet
  - CHANGE # Die Result-Arrays haben jetzt bessere Bezeichnungen, damit man sieht welche Daten was beinhalten
  
Version 1.2:
  - NEU # GetAC3DownmixInfo (AC3 Downmix aktiv=true / inaktiv=false)
  - NEU # GetSleeptimerInfos (Informationen [Aktiviert,Minuten,Aktion,Text] auslesen und ggf. in Variablen schreiben)
  - NEU # SetSleeptimer (Einstellungen des Sleeptimer setzen, sowie Sleeptimer aktivieren/deaktivieren)
  - NEU # EPGSuche (EPG mit einem Suchbegriff [z.B. Name einer Sendung] durchsuchen)
  - FIX # SendMsg-Frage (Semaphore wurde nicht immer verlassen)
  
Version 1.3:
  - NEU # AddTimerByEventID (Aufnahme-Timer hinzufügen)
  - NEU # DelTimer (Timer entfernen/löschen)
  - CHANGE # Durch das Abfragen der Aufnahmeliste (durch Intervall-Timer) wurde die HDD immer aus dem Standby geholt - deshalb
    muss die Aufnahmeliste ab jetzt manuell durch euch aktualisiert werden (z.B. immer nur Nachts oder wann ihr wollt).
	
Version 1.4:
  - ACHTUNG # Ich musste bei einigen Variablen die Idents ändern, deshalb bitte entweder alle Variablen vom Modul löschen und neu erstellen lassen, oder die alten Variablen einzeln löschen
  - NEU # SetPowerState (Werte "4" (Wakeup from Standby) und "5" (Standby) hinzugefügt)
  - NEU # GetEPGInfos erweitert (Zusätzliche ArrayReturns + Variablen für Sendungs-Start, Sendungs-Ende, Fortschritt, SRef, PRef, ...)
  - NEU # Checkbox um die Aufnahmen-Liste aus vom Receiver auszulesen (extra aktivierbar, weil die Festplatte dabei immer aus dem Standby geholt wird)