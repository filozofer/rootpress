# RootPress beta 0.4.0

Rootpress is a Wordpress Skeleton helper to start building wordpress custom site using an MVC-like pattern.

We are in beta version without proper documentation for the moment.
If you want to use this on production site, please fully understand what this involve.

## Why Rootpress ?

When you build new full custom wordpress site you generally start with an empty new custom theme which hereby from a parent theme.
The architecture inside this new custom theme is up to you, which is fine, but as always, it can lead you to produce not easyly evolutive and maintenable code.
To avoid this behaviour, most framework have there own architecture which consist to follow rules like the MVC pattern.

Rootpress help you to start with a good architecture for your wordpress project by generating you a proper architecture for mvc pattern-like and adding some wordpress filters and hooks which allow you to use:
- Models
- Repositories
- Controllers
- Views
- Hooks

## Installation

You can install Rootpress as any Wordpress plugin.
More documentation will come when will be out of beta for this part...

## Documentation 

In progress...

## Rootpress CLI

Rootpress allow you to generate your new theme using wp-cli command.
For more information about WP-CLI and installation go here: https://wp-cli.org/

Here's the full procedure to create a new theme very quickly:
```
// Allow you to generate a new theme with all the needed folders and basic files
wp rootpress generate theme
// Enable your new theme
wp theme enable <name>
// Generate the files needed to start using sass
wp rootpress generate sass
// Generate the basic structure to using Timber plugin (twig inside wordpress)
wp rootpress generate twig-timber
// Generate a new model for custom type or taxonomy
wp rootpress generate model
// Generate a new controller
wp rootpress generate controller
// Generate a new repository
wp rootpress generate repository
// Generate a new hook
wp rootpress generate hook

// Get help !
wp rootpress generate --help
```

## What except from the future ?

- Proper Documentation
- Make this plugin available on Wordpress plugins store (and so via composer using wppackagist)
- Better parent repositories & parent controllers
- More Hooks class
- More Utils class
- Router system with configuration file ?


*All the roots you need to start a new wordpress custom project*