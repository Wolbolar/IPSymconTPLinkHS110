# IPSymconTPLinkHS110
[![Version](https://img.shields.io/badge/Symcon-PHPModul-red.svg)](https://www.symcon.de/service/dokumentation/entwicklerbereich/sdk-tools/sdk-php/)
[![Version](https://img.shields.io/badge/Symcon%20Version-%3E%205.1-green.svg)](https://www.symcon.de/service/dokumentation/installation/)
![Code](https://img.shields.io/badge/Code-PHP-blue.svg)
[![StyleCI](https://github.styleci.io/repos/114809698/shield?branch=master)](https://github.styleci.io/repos/114809698)

Das Modul kann aus IP-Symcon Befehle an ein TP Link HS 100 bzw. TP Link HS 110 verschicken und den Status der Steckdose in IP-Symcon in Variablen darstellen.

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

Die Webconsole von IP-Symcon mit _http://{IP-Symcon IP}:3777/console/_ öffnen. 


Anschließend oben rechts auf das Symbol für den Modulstore (IP-Symcon > 5.1) klicken

![Store](img/store_icon.png?raw=true "open store")

Im Suchfeld nun

```
TPLink
```  

eingeben

![Store](img/module_store_search.png?raw=true "module search")

und schließend das Modul auswählen und auf _Installieren_

![Store](img/install.png?raw=true "install")

drücken.


#### Alternatives Installieren über Modules Instanz

Den Objektbaum _Öffnen_.

![Objektbaum](img/objektbaum.png?raw=true "Objektbaum")	

Die Instanz _'Modules'_ unterhalb von Kerninstanzen im Objektbaum von IP-Symcon (>=Ver. 5.x) mit einem Doppelklick öffnen und das  _Plus_ Zeichen drücken.

![Modules](img/Modules.png?raw=true "Modules")	

![Plus](img/plus.png?raw=true "Plus")	

![ModulURL](img/add_module.png?raw=true "Add Module")
 
Im Feld die folgende URL eintragen und mit _OK_ bestätigen:

```
https://github.com/Wolbolar/IPSymconTPLinkHS110
```  
	
Anschließend erscheint ein Eintrag für das Modul in der Liste der Instanz _Modules_    

Es wird im Standard der Zweig (Branch) _master_ geladen, dieser enthält aktuelle Änderungen und Anpassungen.
Nur der Zweig _master_ wird aktuell gehalten.

![Master](img/master.png?raw=true "master") 

Sollte eine ältere Version von IP-Symcon die kleiner ist als Version 5.1 (min 4.2) eingesetzt werden, ist auf das Zahnrad rechts in der Liste zu klicken.
Es öffnet sich ein weiteres Fenster,

![SelectBranch](img/select_branch.png?raw=true "select branch") 

hier kann man auf einen anderen Zweig wechseln, für ältere Versionen kleiner als 5.1 (min 4.2) ist hier
_Old-Version_ auszuwählen. 


### b. Einrichtung in IP-Symcon
	
#### 1. Anlegen des TP Link HS

In IP-Symcon nun _Instanz hinzufügen_ (_Rechtsklick -> Objekt hinzufügen -> Instanz_) auswählen unter der Kategorie, unter der man die TPLink Steckdosen Instanz hinzufügen will,
und _TP LINK HS 110_ auswählen.

![AddInstance](img/TPLink_instance.png?raw=true "Add Instance")

Es wird ein TP Link HS Gerät angelegt. Nun die Instanz mit Doppelklick öffnen.

Im Konfigurationsformular wird nun die IP Adresse des Geräts eingetragen und desweiteren der passende Gerätetyp ausgewählt.

![Config](img/config_tplink.png?raw=true "Config")

Es wird ein Aktualisierungsintervall eingestellt und dann mit _Übernehmen_ bestätigt.

Im Anschluss kann auf _System Infomationen abrufen_ gedrückt werden, es werden dann erweiterte Systeminfomation des Geräts angerufen die noch erneuten Öffnen der Instanz im Formular sichtbar sind.


##### TP Link HS

Es werden automatisch passend zum Geräte Typ die Variablen mit angelegt.

Objektbaum:

![ModulesCommands](img/ips_objecttree.png?raw=true "Commands")


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

![ModulesCommands](img/wfview.png?raw=true "Commands")

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