Header
=========
Component for generating beuty and corect html header with included AssetsCollector.

Is fork of http://github.com/ondrejmirtes/nette-headercontrol.

License: MIT

Requirements
------------
- PHP 5.3
- Nette 2.0.5 - https://github.com/nette/nette

Details
-------
http://forum.nette.org/cs/12704-dynamicke-vkladanie-iba-pouzitych-css-a-js-suborov

Add macros
----------
Usable in view or components.

    {css "grid.css"} {* Find in drirectory <directory with template>/screen.css *}
    {css "screen.css"} {* Find in drirectory $csspath/screen.css *}
    {css "/var/www/sk/web/www/style/css/screen.css"} {* Find in drirectory "/var/www/sk/web/www/style/css/screen.css" *}
    {js "live-form-validation.js"}
    {js "jquery.min.js", "jquery.nette.js", "jquery.ajaxform.js"} {* definition with dependency *}

    {cssContent}
    * { color:red}
    {/cssContent}

    {jsContent}
    $( 'textarea.editor' ).ckeditor();
    {/jsContent}

Packages
-----------
For often usable files exist option for simple definition packages of files.

### Example

Define in `config.neon`:

    common:
    	assetsCollector:
    		packages:
    			jQuery:
    				js:
    					- %wwwDir%/style/js/jquery-1.8.2.min.js
    			netteForms:
    				js:
    					- %wwwDir%/libs/Nette-extras/Niftyx-NiftyGrid-5163290/resources/js/netteForms.js
    			jQueryUI:
    				extends:
    					- jQuery
    				css:
    					- %wwwDir%/libs/jquery-ui-1.9.1.custom/css/smoothness/jquery-ui-1.9.1.custom.min.css
    				js:
    					- %wwwDir%/libs/jquery-ui-1.9.1.custom/js/jquery-ui-1.9.1.custom.min.js
    			NiftyGrid:
    				extends:
    					- jQueryUI
    					- netteForms
    				css:
    					- %wwwDir%/libs/Nette-extras/Niftyx-NiftyGrid-5163290/resources/css/grid.css
    				js:
    					- %wwwDir%/libs/Nette-extras/Niftyx-NiftyGrid-5163290/resources/js/grid.js

Use in latte templates:

    {pfpack "NiftyGrid"}

Compilators
-----------
On a CSS and JS files can be aplied compilators.

### Example

In `config.neon`:

    common:
		assetsCollector:
			addCssCompilator:
				- @superExtraCompilator
		enabledCompilers:
			- cssSimpleMinificator
			- imageToDataStream
			- imageReplacer
			- superExtraCompilator
		services:
			superExtraCompilator:
				class: SuperExtraCompilator

Installation
-----------
1) Download

2) Unpack

3) Register

    \RM\AssetsCollector\AssetsCollectorExtension::register($configurator);

4) Create component Header https://github.com/romanmatyus/Header

5) Change file @layout.latte such as this:

    {capture $html}
    <body>
    ...
    </body>
    </html>
    {/capture}
    {control header}
    {!$html}

6) Use it:)

