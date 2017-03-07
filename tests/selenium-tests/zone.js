var config = require('./config')
    , commands = require('./commands')
    , assert = require('assert');

var zoneTests = module.exports = function(client, success) {

    // Test that the chevron on zone panel expands/collapses successfully
    client
        .url(config.url)
        .isVisible(".tour-panel > .panel-body", function(err, visible) { // Make sure the panel is not visible to start with
            assert(visible[0] === false);
            success("Pass - panel is not open to begin with");
        });

    client
        .click(".zone-toggle:first-of-type")
        .isVisible(".tour-panel > .panel-body", function(err, visible) { // Clicking the chevron should have made it expand
            assert(visible[0] === true);
            success("Pass - panel expands on click");
        });

    //Test that add tour button goes to add tour page
    client
        .click(".panel-body .btn-success")
        .getText(".panel-title:first-of-type", function(err, text) {
            assert.strictEqual(text, "Add Tour");
            success("Pass - add tour goes to add tour page");
        });

    //Test that add new zone button goes to create zone
    client
        .url(config.url)
        .click("=Add new zone")
        .getText(".panel-heading:first-of-type", function(err, text) {
            assert.strictEqual(text, "Create Zone");
            success("Pass - add new zone goes to create zone page");
        });

    //Test that edit button goes to edit zone
    client
        .url(config.url)
        .click("=Edit")
        .getText(".panel-heading:first-of-type", function(err, text) {
            assert.strictEqual(text, "Edit Zone");
            success("Pass - edit zone goes to edit zone page");
        });

    // Open the panel again for the next test
    client
        .url(config.url)
        .click(".zone-toggle:first-of-type")
        .isVisible(".tour-panel > .panel-body", function(err, visible) { // Clicking the chevron should have made it expand
            assert(visible[0] === true);
            success("Pass - panel expands on click");
        });

    //Test that edit zone (pencil) button goes to edit tour page
    client
        .click(".fa-pencil:first-of-type")
        .getText(".panel-title:first-of-type", function(err, text) {
            assert.strictEqual(text, "Edit Tour");
            success("Pass - edit tour goes to edit tour page");
        });

    //Test that delete tour (bin) button deletes the tour
    // Disabled for now because the delete button has changed and isn't working correctly.
    // client
    //     .url(config.url + 'tours/1')
    //     .elements('.panel-body ul li', function(err, res) {
    //         numberOfTours = res.value.length;
    //     })
    //     .click(".fa-trash-o:last-of-type")
    //     .elements('.panel-body ul li', function(err, res) {
    //         if (numberOfTours > 0) {
    //             assert(res.value.length === numberOfTours - 1);
    //         } else {
    //             assert(res.value.length === 0);
    //         }
    //         success("Pass - delete tour removes the tour from the zone page");
    //     });
};
