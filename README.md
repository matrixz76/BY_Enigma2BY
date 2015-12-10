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
Dieses Modul stellt Funktionen zur Verfügung, damit Nachrichten an einen Receiver (mit Enigma2)
gesendet und über diese dann am TV/Monitor/... angezeigt werden können. Für jeden Receiver muss
eine eigene Modul-Instanz angelegt werden.

Folgende Nachrichten-Varianten stehen zur Verfügung:
- Ja/Nein Frage (Am TV kann mit Ja/Nein geantwortet werden und die Antwort wird in eine Variable geschrieben)
- Info (Nachricht mit Info-Symbol)
- Message (Nachricht mit Message-Symbol)
- Attention (Nachricht mit Attention-Symbol)
Alle Nachrichten haben einen einstellbaren Timeout und werden automatisch nach den gewählten
X Sekunden wieder ausgeblendet. Wird als Timeout eine "0" angegeben, dann muss die Nachricht
aktiv am Receiver weg gedrückt werden.

Ein Zeilenumbruch in der Nachricht kann mit dem Zeichen § erzeugt werden. Dieses Zeichen
wird vom Modul erkannt und automatisch in einen Zeilenumbruch umgewandelt.


## 2. Systemanforderungen
- IP-Symcon ab Version 4.x


## 3. Installation
Über die Kern-Instanz "Module Control" folgende URL hinzufügen:

`git://github.com/BayaroX/BY_Enigma2BY.git`

Die neue Instanz findet ihr in der IPS-Console, in dem Ordner in dem ihr die Instanz erstellt habt.


## 4. Befehlsreferenz
```php
  Enigma2BY_Info(integer $InstanzID, string $Text, integer $TimeoutSekunden);
```
Schickt eine Nachricht mit einem "Info-Symbol" an den Receiver.

```php
  Enigma2BY_Message(integer $InstanzID, string $Text, integer $TimeoutSekunden);
```
Schickt eine Nachricht ohne Symbol an den Receiver.

```php
  Enigma2BY_Attention(integer $InstanzID, string $Text, integer $TimeoutSekunden);
```
Schickt eine Nachricht mit einem "Achtung-Symbol" an den Receiver.

```php
  Enigma2BY_Frage(integer $InstanzID, string $Text, integer $TimeoutSekunden);
```
Schickt eine Nachricht mit Ja/Nein als Antwortmöglichkeit an den Receiver.
Die Antwort steht dann in der Integer-Variable "Frage-Antwort" (0 = Nein, 1 = Ja, 2 = Keine Antwort).

```php
  Enigma2BY_SendKey(integer $InstanzID, string $Key, string $LongShort);
```
Löst einen virtuellen Tastendruck, der gewählten Taste, am Receiver aus. Eine Liste
der verfügbaren Tasten ist in der Modul-Instanz zu finden (DropDown-Auswahl).
Bei $LongShort muss entweder "long" oder "short" angegeben werden (langer/kurzer Tastendruck).


## 5. Changelog
Version 1.0:
  - Erster Release