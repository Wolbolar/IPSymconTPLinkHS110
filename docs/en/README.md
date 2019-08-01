# IPSymconTPLinkHS110
[![Version](https://img.shields.io/badge/Symcon-PHPModule-red.svg)](https://www.symcon.de/service/dokumentation/entwicklerbereich/sdk-tools/sdk-php/)
[![Version](https://img.shields.io/badge/Symcon%20Version-%3E%205.1-green.svg)](https://www.symcon.de/en/service/documentation/installation/)
![Code](https://img.shields.io/badge/Code-PHP-blue.svg)
[![StyleCI](https://github.styleci.io/repos/114809698/shield?branch=master)](https://github.styleci.io/repos/114809698)

The module can send commands from IP-Symcon to a TP Link HS 100 or TP Link HS 110 and display the status of the socket in IP-Symcon in variables.

## Documentation

**Table of Contents**

1. [Features](#1-features)
2. [Requirements](#2-requirements)
3. [Installation](#3-installation)
4. [Function reference](#4-functionreference)
5. [Configuration](#5-configuration)
6. [Annex](#6-annex)

## 1. Features

The module can send commands from IP-Symcon to a TP Link HS 100 or TP Link HS 110 and display the status of the socket in IP-Symcon in variables.

## 2. Requirements

 - IPS 4.2
 - TP Link HS 100 oder TP Link HS 110

## 3. Installation

### a. Loading the module

Open the IP Console's web console with _http://<IP-Symcon IP>:3777/console/_.

Then click on the module store icon in the upper right corner.

![Store](img/store_icon.png?raw=true "open store")

In the search field type

```
TPLink
```  


![Store](img/module_store_search_en.png?raw=true "module search")

Then select the module and click _Install_

![Store](img/install_en.png?raw=true "install")


#### Install alternative via Modules instance

_Open_ the object tree.

![Objektbaum](img/object_tree.png?raw=true "object tree")	

Open the instance _'Modules'_ below core instances in the object tree of IP-Symcon (>= Ver 5.x) with a double-click and press the _Plus_ button.

![Modules](img/modules.png?raw=true "modules")	

![Plus](img/plus.png?raw=true "Plus")	

![ModulURL](img/add_module.png?raw=true "Add Module")
 
Enter the following URL in the field and confirm with _OK_:


```	
https://github.com/Wolbolar/IPSymconTPLinkHS110
```
    
and confirm with _OK_.    
    
Then an entry for the module appears in the list of the instance _Modules_

By default, the branch _master_ is loaded, which contains current changes and adjustments.
Only the _master_ branch is kept current.

![Master](img/master.png?raw=true "master") 

If an older version of IP-Symcon smaller than version 5.1 (min 4.2) is used, click on the gear on the right side of the list.
It opens another window,

![SelectBranch](img/select_branch_en.png?raw=true "select branch") 

here you can switch to another branch, for older versions smaller than 5.1 (min 4.2) select _Old-Version_ .

### b.  Setup in IP-Symcon

#### 1. Creating the TP Link HS

In IP-Symcon _add Instance_ (_rightclick -> add object -> instance_) under the category under which you want to add the TPLink instance,
and select _TP LINK HS 110_.

![AddInstance](img/TPLink_instance_en.png?raw=true "Add Instance")

A TP Link HS device is created. Now open the instance with a double-click.

In the configuration form the IP address of the device is now entered and furthermore the suitable device type is selected.

![Config](img/config_tplink_en.png?raw=true "Config")

An update interval is set and then confirmed with _Apply Changes_.

Afterwards, you can press _Get System Info_, then advanced system information of the device will be received, which will be visible again in the form.

##### TP Link HS

The variables are automatically created according to the device type.

Objecttree:

![ModulesCommands](img/ips_objecttree.png?raw=true "Commands")


Values of TP LINK HS 100

* Power

Values of TP LINK HS 110

* State
* Power
* Elektricity
* Work
* Voltage


#### 2. Sending device commands

In the webfront a button can be pressed to send a command to the TP Link HS.

Webfront:

![ModulesCommands](img/wfview.png?raw=true "Commands")

## 4. Function reference

### TP Link Device

_**Power On**_

```php
TPLHS_PowerOn($InstanceID);
```   

Parameter _$InstanceID_ __*ObjektID*__ the TP Link HS device instance for which a command is to be sent

_**Power Off**_

```php
TPLHS_PowerOff($InstanceID);
```   

Parameter _$InstanceID_ __*ObjektID*__ the TP Link HS device instance for which a command is to be sent

_**Get System Info**_

```php
TPLHS_GetSystemInfo($InstanceID);
```   

Parameter _$InstanceID_ __*ObjektID*__ the TP Link HS device instance for which a command is to be sent
 
_**Get Realtime Current**_

```php
TPLHS_GetRealtimeCurrent($InstanceID);
```   

Parameter _$InstanceID_ __*ObjektID*__ the TP Link HS device instance for which a command is to be sent

## 5. Configuration:

### TP Link HS: 

| Property          | Type    | Value        | Description                                               |
| :---------------: | :-----: | :----------: | :-------------------------------------------------------: |
| host              | string  | 		     | IP Adress from the TP Link HS 110 or 100                  |

## 6. Annex

###  a. GUIDs and data exchange:

#### TP LINK HS Device:

GUID: `{F270009C-576A-E818-816F-67CF636E285A}` 

### b. Sources

[TP110-Addon von *nisbo*](https://github.com/Nisbo/TP110-Addon "TP110-Addon von nisbo")