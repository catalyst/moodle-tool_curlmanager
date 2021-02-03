# moodle-tool_curlmanager

* [What is this?](#what-is-this)
* [How does it work?](#how-does-it-work)
* [Branches](#branches)
* [Installation](#installation)
* [References](#references)


What is this?
-------------
Curl Manger - Alternative security helper

This plugin logs all the outbound http requests made by moodle curl lib (new curl()) and produce a report (Curl Manager Report) based on the log.

In addition, this alternative security helper will allow only curl requests on list of hosts/ports specified in the config.

How does it work?
-----------------

Configure in config.php:

```
$CFG->alternative_security_helper = "\tool_curlmanager\tool_curlmanager_curl_security_helper";
```

This will allow moodle curl to use an alternative security helper tool_curlmanager_curl_security_helper specified in this plugin
instead of core plugin helper curl_security_helper.

tool_curlmanager_curl_security_helper extends core plugin helper curl_security_helper and override url_is_blocked method in 
core plugin helper curl_security_helper to allow only specified list hosts/prots to make curl requests.

To configure a list of allow hosts and ports:

```
$CFG->curlsecurityallowedhosts
$CFG->curlsecurityallowedport
```

Branches
--------

| Moodle verion     | Branch      | PHP       |
| ----------------- | ----------- | --------  |
| Moodle 2.7 to 3.7 | master      | 5.5 - 7.2 |

Installation
------------
Checkout or download the plugin source code into folder `admin\tool_curlmanager` of your Moodle installation.

```sh
git clone git@github.com:catalyst/moodle-tool_curlmanager.git admin\tool_curlmanager
```
or
```sh
wget https://github.com/catalyst/moodle-tool_curlmanager/archive/master.zip
mkdir -p admin\tool_curlmanager
unzip master.zip -d admin\tool_curlmanager
```
Then go to your Moodle admin interface and complete installation and configuration.

References
----------

See also:

Tracker: Allow an alternate curl security helper
```
https://https://tracker.moodle.org/browse/MDL-70649
```

This plugin was developed by Catalyst IT Australia:
```
https://www.catalyst-au.net/
```

<img alt="Catalyst IT" src="https://cdn.rawgit.com/CatalystIT-AU/moodle-auth_saml2/master/pix/catalyst-logo.svg" width="400">
