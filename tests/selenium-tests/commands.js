var commands = module.exports = {
    logIntoSystem : function(url, username, pwd, callback) {
        this.url(url)
            .setValue('input[name=username]', username)
            .setValue('input[name=password]', pwd)
            .submitForm('.form-horizontal')
            .call(callback);
    }
};
