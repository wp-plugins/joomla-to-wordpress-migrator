=== Joomla To Wordpress Migrator ===
Contributors: christian_gnoth
Author Uri: http://it-gnoth.de
Plugin Uri: http://it-gnoth.de/wordpress/wordpress-plugins
Tags: joomla, wordpress, migrator, converter
Requires at least: 2.7
Tested up to: 30 RC3
Stable tag: 1.2.0

A plugin to migrate content from Joomla to Wordpress.

== Description ==

Tested with **Joomla 1.5** and **Wordpress 3.0 RC3**

The Wordpress Installation should be empty !!! No posts, pages or categories !!!

Go to the Plugin Admin Page and fill in the MySQL Connection Parameters !!!

You can choose under the WP Admin section on the Plugin Option Page if you want migrate all categories at once or select specific categories. 

Start the Migration with the button on the Plugin Panel.

After sucessfull migration you can press the "Change Urls" button to change the links in the content of the posts.  

= Support =

Please take a look at **[Support page](http://it-gnoth.de/projekte/wordpress/wp-support/)** 


== Installation ==

1.  extract plugin zip file and load up to your wp-content/plugin directory
2.  Activate Plugin in the Admin => Plugins Menu

== Frequently Asked Questions ==

= A question that someone might have =

An answer to that question.

= What about foo bar? =

Answer to foo bar dilemma.

== Screenshots ==

1. screenshot-1.png  Plugin Admin Panel
2. This is the second screen shot


== Translations ==
* German (de_DE)
* English (default)
* other must be translated

== Changelog ==

= 1.0.1 =
- output changed 

= 1.1.0 = 
- feature added: possibility to choose categories

= 1.1.1 =
- syntax error fixed in admin.php

= 1.1.2 =
- mysql connect without MYSQL_CLINET_COMPRESS parameter in joomla2wp-mig.php

= 1.1.3 =
- error fixed in joomla2wp-functions.php

= 1.1.4 =
- error fixed in joomla2wp-mig.php

= 1.2.0 =
- script stop working problem solved - set php ini values mysql.connect_timeout - now big amount of data no problem

`<?php code(); // goes in backticks ?>`

