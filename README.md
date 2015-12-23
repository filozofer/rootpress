# RootPress alpha 0.2.0

Rootpress is a Wordpress Skeleton to start building wordpress custom site using an MVC-like pattern. 

We are in alpha version, so please, do not use this on production site without fully understand what this involve.

## Why Rootpress ?

When you build new full custom wordpress site you generally start with an empty new custom theme which hereby from a parent theme.
The architecture inside this new custom theme is up to you, which is fine, but as always, it can lead you to produce not easyly evolutive and maintenable code.
To avoid this behaviour, most framework have there own architecture which consist to follow rules like the MVC pattern.

Rootpress help you to start with a good architecture for your wordpress project by proposing you a skeleton and adding some wordpress filters and hooks which allow you to use:
- Models
- Repositories
- Controllers
- Views

## Installation

- Create your new custom theme specific to the website you working on.
- Clone or download the RootPress project inside your plugins folder.
- Enable the plugin RootPress.
- Copy the content of the folder plugins/rootpress/skeleton inside your theme folder.
- Change all the value of "ChangeThisToYourThemeName" by your own theme name.
- Change the values inside style.css file.
- Read the documentation (you will need it!)

## Documentation 

Soon...

## What except from the future ?

- Skeleton for views using Timber
- Proper Documentation
- Make this plugin available on Wordpress plugins store (and so composer)
- Allow to change easyly the location of the basics directories with the rootpress-config.json file
- Create a service for using RootPress with WP-CLI allowing developers to generate basic files when starting a project (models, repositories, controllers)
- Extension to service using WP-CLI: Generate default/base content (pages, posts, ...)
- Integrate front Form usage inside Wordpress
- More Services class
- More Utils class
- Visual Composer Widgets Backoffice render
- Router system with configuration file


*All the roots you need to start a new wordpress custom project*