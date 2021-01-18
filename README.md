# Toolset CLI

WP-CLI commands for Toolset plugins.

## Requirements

- PHP 7.0 or higher
- [Composer](https://getcomposer.org/) 1.x
- Latest versions of [Toolset plugins](https://toolset.com)

## Installation

1. Clone this repository in the plugin directory (`wp-content/plugins/`) of your WordPress site.
   ```bash
   cd wp-content/plugins
   git clone https://github.com/OnTheGoSystems/toolset-cli.git toolset-cli
   ```
2. Install composer dependencies.
   ```bash
   cd toolset-cli
   composer install
   ```
2. Activate the `Toolset CLI` plugin your WordPress site.
   ```bash
   wp plugin activate toolset-cli
   ```
   
## Documentation

At the moment, three top-level commands are available. Feel free to explore
them and all their available subcommands here:

- [wp toolset](docs/toolset.md)
- [wp types](docs/types.md)
- [wp views](docs/views.md)
- [wp layouts](docs/layouts.md)

Of course, when installed, the same information will be available via the `wp help` command.

## Support and collaboration

Please understand that this is _not_ an official part of the Toolset plugins,
but rather a tool used internally within our company, which we decided to share
with our more advanced clients and Toolset users.

Therefore, the usual guarantees do not apply here, and support will be provided 
exclusively through this GitHub repository. 

That being said, we are dedicated to keeping this tool up-to-date and grow
its feature set.

You are very welcome to:

- Report problems, including issues with documentation.
- Send pull requests (we suggest reaching out to us first, if you want to
   contribute with a larger piece of code).
  
## Credits

Many thanks to [@baizmandesign](https://github.com/baizmandesign) for his contributions to this project.

---

Made with :heart: for [Toolset](http://toolset.com) and [OnTheGoSystems](http://onthegosystems.com).
