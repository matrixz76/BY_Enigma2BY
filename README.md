# !!! TEST !!! NICHT VERWENDEN !!! TEST !!!
---

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

###### Übersicht der Features (die Funktionen findet ihr in der Befehlsreferenz):
**Nachrichten an Receiver senden:**
- Ja/Nein Frage (Am TV kann mit Ja/Nein geantwortet werden und die Antwort wird in eine Variable geschrieben)
- Info (Nachricht mit Info-Symbol)
- Message (Nachricht mit Message-Symbol)
- Attention (Nachricht mit Attention-Symbol)
    *Alle Nachrichten haben einen einstellbaren Timeout und werden automatisch nach den gewählten*
    *X Sekunden wieder ausgeblendet. Wird als Timeout eine "0" angegeben, dann ist dieser inaktiv*
    *und die Nachricht muss aktiv am Receiver weg gedrückt werden.*

    *Ein Zeilenumbruch in der Nachricht kann mit dem Zeichen § erzeugt werden. Dieses Zeichen*
    *wird vom Modul erkannt und automatisch in einen Zeilenumbruch umgewandelt.*

**Informationen über das Receiver-System auslesen (einstellbarer Intervall):**  
- Image-Version
- Festplatte > Modell    *wenn HDD verbaut*
- Festplatte > Kapazität (in MB)    *wenn HDD verbaut*
- Festplatte > Freie Kapazität (in MB)    *wenn HDD verbaut*

**Informationen über den aktuellen/nächsten Sender/Sendung (einstellbarer Intervall):**
- Aktueller Sendername, Sendungsname, Sendungsbeschreibung kurz, Sendungsbeschreibung lang, Sendungsdauer,
  Sendungsrestdauer, EventID
- Nächster Sendungsname, Sendungsbeschreibung kurz, Sendungsbeschreibung lang, Sendungsstart, Sendungsdauer, EventID

**Timerliste auslesen**
- Liest die Timerliste aus dem Receiver aus, gibt diese als Array zurück und speichert die Daten in eine Variable (HTMLBox)


## 2. Systemanforderungen
- IP-Symcon ab Version 4.x


## 3. Installation
Über die Kern-Instanz "Module Control" folgende URL hinzufügen:

`git://github.com/BayaroX/BY_Enigma2BY.git`

Die neue Instanz findet ihr in der IPS-Console, in dem Ordner in dem ihr die Instanz erstellt habt.


## 4. Befehlsreferenz
```php
  Enigma2BY_SendMsg(integer $InstanzID, integer $Type, string $Text, integer $TimeoutSekunden);
```
Schickt eine Nachricht an den Receiver und dieser zeigt diese dann auf TV/Beamer an.
Liefert ein Array mit Informationen zurück, ob die Nachricht erfolgreich gesendet wurde oder nicht.
$Type:
   1 = Schickt eine Nachricht mit einem "Info-Symbol" an den Receiver.
   2 = Schickt eine Nachricht ohne Symbol an den Receiver.
   3 = Schickt eine Nachricht mit einem "Achtung-Symbol" an den Receiver.
   0 = Schickt eine Nachricht/Frage mit Ja/Nein als Antwortmöglichkeit an den Receiver.
       Die Antwort steht dann in der Integer-Variable "Frage-Antwort".
       Antwort-Bedeutungen in Variable: 0 = Nein // 1 = Ja // 2 = Keine Antwort innerhalb Timeout-Zeit

```php
  Enigma2BY_SendKey(integer $InstanzID, string $Key, string $LongShort);
```
Löst einen virtuellen Tastendruck, der gewählten Taste, am Receiver aus. Eine Liste
der verfügbaren Tasten ist in der Modul-Instanz zu finden (DropDown-Auswahl).
Bei $LongShort muss entweder "long" oder "short" angegeben werden (langer/kurzer Tastendruck).
Liefert ein Array mit Informationen zurück, ob die Taste erfolgreich gesendet wurde oder nicht.

```php
  Enigma2BY_GetSystemInfos(integer $InstanzID);
```
Liest Systeminfos vom Receiver aus (siehe Funktionsumfang), speichert diese in Variablen und
gibt die Daten als Array zurück.

```php
  Enigma2BY_GetEPGInfos(integer $InstanzID);
```
Liest EPG-Infos vom Receiver aus (siehe Funktionsumfang), speichert diese in Variablen und
gibt die Daten als Array zurück.

```php
  Enigma2BY_GetPowerState(integer $InstanzID);
```
Liefert den Power-Zustand des Receiver zurück (0 = ausgeschaltet // 1 = eingeschaltet // 2 = Standby)

```php
  Enigma2BY_SetPowerState(integer $InstanzID, integer $PowerState);
```
Steuert den Power-Zustand des Receiver (0 = Toggle Standby // 1 = Deep Standby // 2 = Reboot // 3 = Restart GUI).

```php
  Enigma2BY_GetVolume(integer $InstanzID);
```
Liefert ein Array mit Volume-Informationen zurück (Volume 0-100, Mute true/false)

```php
  Enigma2BY_SetVolume(integer $InstanzID, integer $Volume);
  Enigma2BY_SetVolume(integer $InstanzID, string "+");
  Enigma2BY_SetVolume(integer $InstanzID, string "-");
  Enigma2BY_SetVolume(integer $InstanzID, string "MUTE");
```
Setzt die Lautstärke des Receiver auf den angegebenen Wert (0-100).
Die anderen Parameter steuern lauter/leiser/Mute Toggle

```php
  Enigma2BY_GetTimerliste(integer $InstanzID);
```
Liefert ein Array mit allen Timern + Details zurück und schreibt die Daten in eine Variable (als HTML-Tabelle)


## 5. Changelog
Version 1.0:
  - Erster Release