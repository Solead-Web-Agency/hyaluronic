Module is installed in a regular way - simply upload your archive and click install

CHANGELOG:
===========================
v 3.0.3 (November 23, 2021)
===========================
- [+] Translate articles/categories/sliders/galleries/autors of Blog module (by ETS-soft)
- [+] Translate articles/categories of Advance Blog module
- [+] Translate labels for product customization fields
- [+] Translate File Attachments
- [*] Improved compatibility with DeepL: more languages, free subscription plan, minor fixes
- [*] Misc fixes and optimizations

Files modified
-----
- /autotranslator.php
- /classes/DeeplTranslate.php
- /classes/GoogleTranslate.php
- /classes/IBMTranslate.php
- /classes/MicrosoftTranslate.php
- /classes/TranslationAPI.php
- /classes/YandexTranslate.php
- /views/css/back.css
- /views/templates/admin/api-settings.tpl
- /views/templates/admin/how-to.tpl

Files added
-----
- /upgrade/install-3.0.3.php

===========================
v 3.0.2 (July 29, 2020)
===========================
- [+] New translation provider: DeepL Translator
- [+] Added Polish translation for configuration interface
- [*] Misc fixes and optimizations

Files modified
-----
- /autotranslator.php
- /classes/YandexTranslate.php
- /readme_en.pdf
- /views/css/back.css
- /views/js/back.js
- /views/templates/admin/api-settings.tpl
- /views/templates/admin/how-to.tpl

Files added
-----
- /translations/pl.php

===========================
v 3.0.1 (May 22, 2020)
===========================
- [*] Translate large texts over 5000 characters
- [*] Automatically decode possible apostrophes and other html_entities in translation results
- [*] Minor fixes and optimizations

Files modified
-----
- /autotranslator.php
- /classes/MicrosoftTranslate.php
- /classes/TranslationAPI.php
- /classes/TranslationProvider.php
- /classes/YandexTranslate.php
- /views/css/back.css
- /views/js/back.js
- /views/templates/admin/list.tpl

===========================
v 3.0.0 (February 7, 2020)
===========================
- [+] Select translation provider: Yandex, Google, IBM, Microsoft
- [+] Optionally translate only fields that are empty or have same value as original
- [+] Optionally select fields that should be translated
- [+] Possibility to filter products by multiple categories/manufacturers
- [+] Quick search for category/manufacturer names in filters
- [+] Visual translation statistics for each item in resource list
- [-] Temporarily removed functionality for translating themes and modules
- [*] Misc fixes and optimizations

Files modified
-----
All files

===========================
v 2.7.2 (January 8, 2017)
===========================
- [+] Translate multilingual data of module "Home text editor"
- [*] Improved error handling while exchanging informations with remote server
- [*] Recognize additional iso codes: vn, nn, qc

Files modified
-----
- /autotranslator.php

===========================
v 2.7.1 (August 14, 2017)
===========================
-[*] Extended compatibility with PS 1.7.2

Files modified
-----
- /autotranslator.php

===========================
v 2.7.0 (July 7, 2017)
===========================
- [+] Translate articles/categories of module "Amazzing blog"
- [+] Translate meta fields for controller pages, available in "Preferences > SEO & URLs" menu
- [+] Display number of processed characters on configuration page
- [*] Automatically re-build search index for new translations
- [*] Minor fixes and optimizations

Files modified
-----
- /autotranslator.php
- /views/css/back.css
- /views/js/back.js
- /views/templates/admin/configure.tpl
- /views/templates/admin/key-form.tpl
- /views/templates/admin/list.tpl
- /readme_en.pdf

Files added
-----
- /upgrade/install-2.7.0.php

===========================
v 2.6.4 (May 1, 2017)
===========================
- [+] Translate articles/categories of module "ph_simpleblog"
- [*] Fixed recognition of translatable strings in overriden module templates

Files modified
-----
- /autotranslator.php
- /views/js/back.js

===========================
v 2.6.3 (March 30, 2017)
===========================
- [*] Fixed error "Unsupported media type"

Files modified
-----
- /autotranslator.php

===========================
v 2.6.2 (March 23, 2017)
===========================
- [*] Added support for English with GB iso code
- [*] Minor fix for retro-compatibility with PS 1.6.0.6

Files modified
-----
- /autotranslator.php

===========================
v 2.6.1 (March 11, 2017)
===========================
- [*] Don't interrupt bulk translation if error appears, and show this error to user

Files modified
-----
- /autotranslator.php
- /views/css/back.css
- /views/js/back.js


===========================
v 2.6.0 (February 10, 2017)
===========================
- [+] Added documentation
- [*] Extended compatibility for PS versions 1.6.0.4 - 1.6.0.9 and and 1.7+
- [*] New user-friendly listings with ajax sorting and pagination
- [*] Added IDs to category filter, for easier selection
- [*] Added filtering by manufacturer

Files modified
-----
- /autotranslator.php
- /views/css/back.css
- /views/js/back.js
- /views/templates/admin/key-form.tpl
- /translations/es.php
- /translations/fr.php
- /translations/ru.php

Files added
-----
- /readme_en.pdf
- /views/templates/admin/configure.tpl
- /views/templates/admin/list.tpl
- /views/templates/admin/pagination.tpl
- /translations/uk.php
- /translations/ro.php

Files removed
-----
- /views/templates/admin/content-types-form.tpl

===========================
v 2.5.5 (October 3, 2016)
===========================
- [+] Autosave overwriting option
- [*] Truncate object fields before saving, if required
- [*] Minor bug fixes

Files modified
-----
- /autotranslator.php
- /views/js/back.js

===========================
v 2.5.4 (March 28, 2016)
===========================
- [*] Keep selected category when pagination or ordering are changed
- [*] Extended compatibility with different server configurations (ssl_verify_peer false)

Files modified
-----
- /autotranslator.php
- /views/js/back.js

===========================
v 2.5.3 (January 24, 2016)
===========================
- [+] Filter products by categories
- [*] Misc bug fixes

Files modified
-----
- /autotranslator.php
- /views/js/back.js
- /views/templates/admin/content-types-form.tpl

===========================
v 2.5.2 (September 8, 2015)
===========================
- [+] Translations for manufacturers
- [+] Translations for suppliers
- [+] Translations for product tags
- [+] Translations for product image legends
- [+] Option to keep or overwrite existing translations
- [-] Removed requirement for having English in the list of installed langauges
- [*] Optimized queries for generating lists. Thousands of products can be displayed without timeout errors
- [*] PSR-2

Files modified
-----
- /autotranslator.php
- /views/js/back.js
- /views/css/back.css
- /logo.png

Files added
-----
- /views/templates/index.php
- /views/templates/admin/index.php
- /views/templates/admin/key-form.tpl
- /views/templates/admin/content-types-form.tpl

===========================
v 2.5.1 (July 25, 2015)
===========================
- [+] Possibility to add module translations to theme directory
- [*] Notification if a new file was created

Files modified
-----
- /autotranslator.php
- /views/js/back.js

===========================
v 2.5.0 (May 12, 2015)
===========================
Added
-----
- Possibility to translate attributes, attribute groups, features, feature values
- Stop button for bulk translations

Fixed
-----
- Pagination, filtering, sorting

Files modified
-----
- /autotranslator.php
- /views/css/back.css
- /views/js/back.js
- logo.gif
- logo.png

===========================
v 2.0.0 (March 16, 2015)
===========================
Added
-----
- Possibility to translate modules, themes, categories, cms pages, cms categories
- Extended resource translations: including meta tags + link_rewrite generation basing on resource name
- Possibility to translate resources to all languages in one click

Changed
-----
- Optimized bulk translation
- Slightly modified settings interface
- Other code fixes and optimizations

Directories moved
-----
- /css/ -> /views/css/
- /js/ -> /views/js/

Files modified
-----
- /autotranslator.php
- /css/back.css
- /js/back.js

Files added
-----
- /Readme.md

Directories removed
-----
- /controllers/

===========================
v 1.0.0 (September 01, 2014)
===========================
Initial relesase
