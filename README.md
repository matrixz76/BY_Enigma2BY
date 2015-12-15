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
- Info (Nachricht mit Info-Symbol)
- Message (Nachricht mit Message-Symbol)
- Attention (Nachricht mit Attention-Symbol)
- Ja/Nein Frage (Am TV kann mit Ja/Nein geantwortet werden und die Antwort wird in eine Variable geschrieben und von der Funktion zurückgegeben)

    > Alle Nachrichten haben einen einstellbaren Timeout und werden automatisch nach den gewählten
    > X Sekunden wieder ausgeblendet. Wird als Timeout eine "0" angegeben, dann ist dieser inaktiv
    > und die Nachricht muss aktiv am Receiver weg gedrückt werden.

    > Ein Zeilenumbruch in der Nachricht kann mit dem Zeichen **§** erzeugt werden. Dieses Zeichen
    > wird vom Modul erkannt und automatisch in einen Zeilenumbruch umgewandelt.

**Informationen über das Receiver-System auslesen (einstellbarer Intervall):**  
- Enigma-Version
- Image-Version
- WebIf-Version
- Festplatte > Modell    *wenn HDD verbaut*
- Festplatte > Kapazität (in MB)    *wenn HDD verbaut*
- Festplatte > Freie Kapazität (in MB)    *wenn HDD verbaut*

**Informationen über den aktuellen/nächsten Sender/Sendung (einstellbarer Intervall):**
- Aktueller Sendername, Sendungsname, Sendungsbeschreibung kurz, Sendungsbeschreibung lang, Sendungsdauer,
  Sendungsrestdauer, EventID
- Nächster Sendungsname, Sendungsbeschreibung kurz, Sendungsbeschreibung lang, Sendungsstart, Sendungsdauer, EventID

**Timerliste auslesen (einstellbarer Intervall)**
- Liest die Timerliste aus dem Receiver aus, gibt diese als Array zurück und speichert die Daten in eine Variable (HTMLBox)

**Aufnahmenliste auslesen (einstellbarer Intervall)**
- Liest die Aufnahmenliste aus dem Receiver aus, gibt diese als Array zurück und speichert die Daten in eine Variable (HTMLBox)

**Eingestellte Lautstärke vom Receiver auslesen und setzen**
- Liest die aktuelle Receiver-Lautstärke aus (Volume 0-100 und Mute aktiv/inaktiv)
- Steuert die Lautstärke des Receiver (bestimmter Wert, leiser, lauter, Toggle Mute)

    > !Achtung! Lautstärke kann nur geändert werden, wenn AC3-Ton im Receiver nicht als Default gewählt wurde!

**Power-Zustand des Receiver auslesen und steuern**
- Liest den Power-Status des Receiver aus (eingeschaltet, ausgeschaltet, Standby)
- Steuert den Power-Status des Receiver (Toggle Standby, Deep Standby, Reboot, Restart GUI)

**Senderliste auslesen**
- Liest alle Sender der Senderliste mit ServiceReferenznummer aus und gibt es in einem Array zurück

**Sender umschalten**
- Schaltet auf den Receiver auf den angegeben Sender um


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
Receiver gesendet werden. Das muss bei der Wahl der Timeout-Länge beachtet werden!

> $Type:

    > 1 = Schickt eine Nachricht mit einem "Info-Symbol" an den Receiver.

    > 2 = Schickt eine Nachricht ohne Symbol an den Receiver.

    > 3 = Schickt eine Nachricht mit einem "Achtung-Symbol" an den Receiver.

    > 0 = Schickt eine Nachricht/Frage mit Ja/Nein als Antwortmöglichkeit an den Receiver.

       > Die Antwort steht dann in der Integer-Variable "Frage-Antwort" und in der Rückmeldung der Funktion.

       > Antwort-Bedeutungen: 0 = Nein // 1 = Ja // 2 = Keine Antwort innerhalb Timeout-Zeit

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
Steuert den Power-Zustand des Receiver (0 = Toggle Standby // 1 = Deep Standby // 2 = Reboot // 3 = Restart GUI).
Liefert true/false zurück, ob der Power-State gesendet wurde oder nicht.

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


## 5. Changelog
Version 1.0:
  - Erster Release
