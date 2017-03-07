var config = require('./config')
  , commands = require('./commands')
  , assert = require('assert')
  , client = require('webdriverio').remote(config.options);


// Add custom command actions to the client
client.addCommand('logIntoSystem', commands.logIntoSystem.bind(client));

// Define success helper
var success = function(testName) {
    if (!testName) {
        testName = 'Test'
    }

    console.log("\x1b[32m", testName + " ✓"); // Print test name in green
    console.log("\x1b[0m", "\n"); // Reset color
};

// Boot the browser
client
    .init()
    .setViewportSize({
        width: 1280,
        height: 800
    });

// Test that the system is up.
client
    .url(config.url)
    .title(function(err, title) {
        assert(title.value === 'Kent Tours');
        success("System is up");
    });


// Run all tests in login.js
require("./login")(client, success);
// Run tests for home (zone) page
require("./zone")(client, success);
//Run tests for create zone page
require("./create-zone")(client, success);

// Close the browser when done
client.end();
