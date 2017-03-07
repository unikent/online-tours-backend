var config = require('./config')
  , commands = require('./commands')
  , assert = require('assert');

var loginTests = module.exports = function(client, success) {
  // Test that the system won't let you log in with no credentials
  client
      .url(config.url + "/auth/login")
      .submitForm(".form-horizontal")
      .getText(".panel-body .alert", function(err,text) {
        assert(text.indexOf("Whoops") !== -1);
        assert(text.indexOf("username") !== -1);
        assert(text.indexOf("password") !== -1);
        success("Pass - system won't let you log in with no credentials");
      });

  // Test that the system won't let you log in with only email filled in
  client
      .logIntoSystem(config.url + "/auth/login", config.email, '')
      .getText(".panel-body .alert", function(err,text) {
        assert(text.indexOf("Whoops") !== -1);
        assert(text.indexOf("password") !== -1);
        success("Pass - system won't let you log in with only email field filled in");
      });

  // Test that the system won't let you log in with only password filled in
  client
      .logIntoSystem(config.url + "/auth/login", '', config.password)
      .getText(".panel-body .alert", function(err,text) {
        assert(text.indexOf("Whoops") !== -1);
        assert(text.indexOf("username") !== -1);
        success("Pass - system won't let you log in with only password field filled in");
      });

  // Test that the system won't let you log in with incorrect credentials
  client
      .logIntoSystem(config.url + "/auth/login", 'is-webdev@kent.uk', 'test')
      .getText(".panel-body .alert", function(err,text) {
        assert(text.indexOf("Whoops") !== -1);
        assert(text.indexOf("credentials") !== -1);
        assert(text.indexOf("our records") !== -1);
        success("Pass - system won't let you log in with invalid credentials");
      });

  // Test that you can log into the system successfully
  client
      .logIntoSystem(config.url + "/auth/login", config.email, config.password)
      .getText('=Home', function(err, text) {
        assert(text.indexOf("Home") !== -1);
        success("Pass - system will let you log in with valid credentials");
      });
};
