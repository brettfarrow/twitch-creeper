# Twitch Creeper 

## Requirements
- PHP (tested on v5.5.9)
- MySQL (tested on v5.5.40)
- Composer (tested on v1.0-dev)

## Setup
1. Install / run Composer (composer install)
	- [Installation instructions](https://getcomposer.org/doc/00-intro.md)
	- Run `composer install` in the project directory
2. Set up your database
	- Create a table using the structure in `table-setup.sql`
3. Set up environment variables
	1. Copy `.env.example` to `.env`
	2. Update the `.env` data to match your own. You'll need:
		- MySQL username
		- MySQL password
		- MySQL table name
		- Twitch.tv API Client ID ([How to get your Twitch client ID](https://github.com/justintv/Twitch-API/blob/master/authentication.md))
4. Edit and run the example script via your shell of choice.
	- `php5 example.php`
	- Feel free to use cron for automation, as long as you don't run into Twitch's [API limits](https://github.com/justintv/twitch-api#rate-limits).