'use strict';

var loginPage     = require('../../../bellows/pages/loginPage.js');

describe('Rapid Words', function(){

    it('title should exist', function(){
        loginPage.loginAsUser();
        browser.get(browser.baseUrl + '/app/rapid-words');
        expect(element(by.id('rapid-words-title')).isPresent()).toBe(true)
    });

});