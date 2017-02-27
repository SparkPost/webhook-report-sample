# SparkPost Sample: Using Webhooks To Report on Recipient OS Preferences

This project accepts SparkPost webhooks event batches and maintains a report of recipient OS preferences based on the `user_agent` field from `click` events.

## Usage

### Heroku

You can deploy it directly to Heroku: [![Deploy](https://www.herokucdn.com/deploy/button.svg)](https://heroku.com/deploy).

Register your service as a webhook endpoint on your SparkPost account. You can [do that here](https://app.sparkpost.com/account/webhooks).

The webhook endpoint is at `https://your-app-name.herokuapp.com/clicks.php`.

The report is at `https://your-app-name.herokuapp.com/`.

### Manual Deployment

Prerequisites:
 - PostgreSQL (tested with 9.5)
 - PHP 5.6

Steps:
 - Clone this repo
 - Create an empty database for this service
 - Edit public/clicks.php to add a PostgreSQL database URL. Format: `postgres://username:password@dbhostname:5432/dbname`
 - Configure your webserver to serve from `/public`.
 - Register your service as a webhook endpoint on your SparkPost account. You can [do that here](https://app.sparkpost.com/account/webhooks).

The webhook endpoint is at `https://your-app-name.herokuapp.com/clicks.php`.

The report is at `https://your-app-name.herokuapp.com/`.

