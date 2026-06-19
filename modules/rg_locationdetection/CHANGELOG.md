# CHANGELOG

## 2.6.0 -- (2019, Aug 06)
[-] Error fixed that occurred when leaving the exclude option of user agents and IP addresses empty
[-] Error fixed in popup with long HTML content
[*] Major improvements in carrier detection
[*] Improvements in the detection of the country
[*] Minor general improvements

## 2.4.2 -- (2018, Oct 23)
[-] Fixed occasional error of "Bad Request"
[*] The exclusion by IP address was improved

## 2.4.0 -- (2018, Jul 09)
[+] Support for PrestaShop 1.7
[+] Support for IPv6
[+] Automatic updates of the IP database
[+] Security on SQL injections
[-] Other minor bug fixes

## 2.0.2 -- (2016, Apr 25)
[+] New hook added to synchronize carriers id when they have been updated
[*] Top-level domain (TLD) increased in the URL validator for redirections
[*] IP database updated
[-] Minor bug fixed in module configuration

## 2.0.1 -- (2015, Nov 24)
[*] IP database updated
[-] Minor bug fixed on the multi-store
[-] Error fixed in the license for some shops, when it was requesting all the time
[-] Error fixed in the redirection popup on mobile devices

## 2.0.0 -- (2015, Oct 01)
[+] Option to add user agents in the exclude list
[+] Option to select CloudFlare compatibility (for stores running with CloudFlare)
[+] Option to detect language based on the country
[+] Option to set a default carrier for each country
[+] Option to disable/enable all redirections
[+] Option to disable/enable all infobars
[+] Option to select the IP database you want
[+] Redirection - Option to set how many redirections you want
[+] Redirection - Option to use a popup instead of the immediate redirection
[+] Infobar - Option to use a fixed position
[+] Infobar - Option to use custom CSS
[+] Country - Option to set 3 languages
[+] Country - Option to set the default carrier
[*] IP detection (GeoIP) now is through ip2location
[*] Detect currency, you can now select it in the global settings
[*] Expiration of cookie has been changed to "hours"
[*] Improved speed, increased use of cookies instead of database
[-] Error adding domains with 4 digits in gTLD, ex: .INFO

## 1.0.26 -- (2015, May 13)
[+] Compatibility with CloudFlare
[+] Option to redirect to the full path of the URL
[*] Algorithm to detect all the versions of the browser (from major to minor important), detection is smarter
[*] GeoIP database updated
[-] Error on redirect for PS 1.4
[-] Error when the geolocation of PrestaShop is enabled
[-] Error in days of Cookie lifetime
[-] Error changing the currency when this has been deleted

## 1.0 -- (2013, Nov 13)
[+] Initial release
