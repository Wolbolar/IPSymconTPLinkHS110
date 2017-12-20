# IPSymconTPLinkHS110
===

Modul für IP-Symcon ab Version 4.2 ermöglicht die Kommunikation mit einer TP Link HS 100 oder TP Link HS 110.

## Dokumentation

**Inhaltsverzeichnis**

1. [Funktionsumfang](#1-funktionsumfang)  
2. [Voraussetzungen](#2-voraussetzungen)  
3. [Installation](#3-installation)  
4. [Funktionsreferenz](#4-funktionsreferenz)
5. [Konfiguration](#5-konfiguration)  
6. [Anhang](#6-anhang) 

## 1. Funktionsumfang

Das Modul kann aus IP-Symcon Befehle an eine TP Link HS 100 bzw. TP Link HS 110 verschicken und den Status der Steckdose in IP-Symcon in Variablen darstellen.

## 2. Voraussetzungen

 - IPS 4.2
 - TP Link HS 100 oder TP Link HS 110

## 3. Installation

### a. Laden des Moduls

Die IP-Symcon (min Ver. 4.2) Konsole öffnen. Im Objektbaum unter Kerninstanzen die Instanz __*Modules*__ durch einen doppelten Mausklick öffnen.

![Modules](docs/Modules.png?raw=true "Modules")

In der _Modules_ Instanz rechts oben auf den Button __*Hinzufügen*__ drücken.

![ModulesAdd](docs/Hinzufuegen.png?raw=true "Hinzufügen")
 
In dem sich öffnenden Fenster folgende URL hinzufügen:

	
    `https://github.com/Wolbolar/IPSymconTPLinkHS110`  
    
und mit _OK_ bestätigen.    
    
Anschließend erscheint ein Eintrag für das Modul in der Liste der Instanz _Modules_ 

### b. Einrichtung in IPS

#### 1. Anlegen des TP Link HS

In IP-Symcon in eine Kategorie wechseln unter der die Instanz angelegt werden soll. Hier eine neue Instanz mit _Rechter Mausklick->Objekt hinzufügen->Instanz hinzufügen_ oder _CTRL+1_ erzeugen und als Gerät __*TP LINK HS 110*__ wählen.

![AddInstance](docs/Instance.png?raw=true "Add Instance")

Es wird ein TP Link HS Gerät angelegt. Nun die Instanz mit Doppelklick öffnen.

Im Konfiguartionsformular wird nun die IP Adresse des Geräts eingetragen und desweiteren der passende Gerätetyp ausgewählt.

![Config](docs/Config.png?raw=true "Config")

Es wird ein Aktualisierungsintervall eingestellt und dann mit _Übernehmen_ bestätigt.

Im Anschluss kann auf _System Infomationen abrufen_ gedrückt werden, es werden dann erweiterte Systeminfomation des Geräts angerufen die noch erneuten Öffnen der Instanz im Formular sichtbar sind.


##### TP Link HS

Es werden automatisch passend zum Geräte Typ die Variablen mit angelegt.

Objektbaum:

![ModulesCommands](docs/objecttree.png?raw=true "Commands")


Ausgelesen werden aus dem TP LINK HS 100

* Power

Ausgelesen werden aus dem TP LINK HS 110

* Status
* Power
* Aktueller Wert
* Leistung
* Spannung


#### 2. Absenden von Gerätebefehlen

Es kann ganz normal im Webfront auf die Taste gedrückt werden dann wird der Befehl an den TP Link HS verschickt.

Webfront:

![ModulesCommands](docs/wfview.png?raw=true "Commands")



## 4. Funktionsreferenz

### TP Link Device

_**Power On**_

```php
TPLHS_PowerOn($InstanceID);
```   

Parameter _$InstanceID_ __*ObjektID*__ der TP Link HS Geräte Instanz für die ein Befehl verschickt werden soll

_**Power Off**_

```php
TPLHS_PowerOff($InstanceID);
```   

Parameter _$InstanceID_ __*ObjektID*__ der TP Link HS Geräte Instanz für die ein Befehl verschickt werden soll

_**Get System Info**_

```php
TPLHS_GetSystemInfo($InstanceID);
```   

Parameter _$InstanceID_ __*ObjektID*__ der TP Link HS Geräte Instanz für die ein Befehl verschickt werden soll
 
_**Get Realtime Current**_

```php
TPLHS_GetRealtimeCurrent($InstanceID);
```   

Parameter _$InstanceID_ __*ObjektID*__ der TP Link HS Geräte Instanz für die ein Befehl verschickt werden soll
   

## 5. Konfiguration:

### TP Link HS: 

| Eigenschaft       | Typ     | Standardwert | Funktion                                                  |
| :---------------: | :-----: | :----------: | :-------------------------------------------------------: |
| host              | string  | 		     | IP Adresse des TP Link HS 110 bzw 100                                  |



## 6. Anhang

###  a. GUIDs und Datenaustausch:

#### TP LINK HS Device:

GUID: `{F270009C-576A-E818-816F-67CF636E285A}` 

### b. Quellen

[TP110-Addon von *nisbo*](https://github.com/Nisbo/TP110-Addon "TP110-Addon von nisbo")
