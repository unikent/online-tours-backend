# Online Tours - Backend App

This codebase is provided primarily as a reference and will require its authentication system to be swapped out to the native laravel implementation in order to use outside of the University of Kent. Significant other portions of this code are likely also tied to kent web-services & have not been altered to be generic in this release.

The code is licensed under the MIT License. Kent branding / logos remain (c) of the University of Kent.

## Summary

The online tours back end provides the API's and editing interfaces necessary to create content and tours within the [Online Tours front end app](https://github.com/unikent/online-tours-frontend).

It will accept GET requests from the mobile JS application to get tour routes & pin details.

It will also accept POST / PUT etc. requests from the back end to allow users to update tours, pins and other information.

### Setup
1. Clone this repository. Change into the directory.
2. Create a MySQL database
3. Copy .env.sample to .env in the root of the repo - you will need to enter your database credentials in this file
4. Install [Composer](http://getcomposer.org/) and run `composer install` to grab the dependencies - **Please note, the kent auth dependency cannot be installed & users by none kent users.**
5. Run the migrations `php artisan migrate:install --env=local` to setup the migrations table
6. Run `php artisan migrate --env=local` to run all the migrations to setup your database
7. Run `php artisan db:seed --env=local` to run the seeds and get going with some sample data
8. Point a webserver at the `public/` folder.
9. Point a browser to the URL of the Online Tours


### Testing
#### Selenium Testing
This app uses [WebdriverIO](http://webdriver.io) to run selenium tests. To use:

1. cd into `tests/selenium-tests`
2. You can either run the tests using phantomjs or a browser you have installed locally (firefox, chrome etc).
  1. Using phantomjs:
    * Included in the package.json file: `npm install` to grab dependencies
    * Modify config.js:
       * Set browserName: 'phantomjs'
       * Set port: 4444
    * Run phantomjs: `phantomjs --webdriver=4444`  
  2. Using a local browser:
    * The first time you run you will need to download selenium standalone server:
`curl -O http://selenium-release.storage.googleapis.com/2.45/selenium-server-standalone-2.45.0.jar`
    * Set the browser you want to use in config.js, e.g. browserName: 'firefox'
    * Start the selenium standalone server: `java -jar selenium-server-standalone-2.45.0.jar`
(keep this running in a seperate terminal)
3. Download WebdriverIO: `npm install webdriverio`
4. Run tests `node test.js` where test.js is the name of the file containing your tests
*If running phantomjs, ensure to end the session after the tests have completed as otherwise it will try to pick up on the page where the last test ended ([this is a known bug in phantomjs](https://github.com/detro/ghostdriver/issues/170))*

#### Load Testing 
This app uses [Siege](https://www.joedog.org/siege-home/) to load test the API endpoints. To use:

1. Install by running `sudo apt-get install siege`
   * Alternatively via Homebrew: `brew install siege`
2. Generate a siege file with `php artisan generate:endpoints`
3. Run `siege -c10 -d5 -t60S -i -f storage/app/siege.txt` where:
  * -c is the number of concurrent users
  * -d is the delay between hitting a URL in seconds
  * -t is the time to run the load test for (S=seconds, M=minutes)
  * -i randomises the URL the test grabs from the text file, simulating real traffic
