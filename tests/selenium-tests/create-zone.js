var config = require('./config')
    , commands = require('./commands')
    , assert = require('assert');

var createZoneTests = module.exports = function(client, success) {

    // Test that the selected POI is highlighted
    client
        .url(config.url + "/zone/create")
        .click(".jstree-anchor:first-of-type")
        .isEnabled(".jstree-clicked", function(err, isEnabled) {
            assert(isEnabled === true);
            success("Pass - selected POI becomes highlighted")
        });
};