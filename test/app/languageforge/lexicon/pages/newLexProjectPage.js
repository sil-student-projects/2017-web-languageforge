'use strict';

module.exports = new NewLexProjectPage();

function NewLexProjectPage() {
  var mockUpload = require('../../../bellows/pages/mockUploadElement.js');
  var modal      = require('./lexModals.js');

  this.get = function get() {
    browser.get(browser.baseUrl + '/app/lexicon/new-project');
  };

  // form controls
  this.noticeList = element.all(by.repeater('notice in notices()'));
  this.firstNoticeCloseButton = this.noticeList.first().element(by.partialButtonText('×'));
  this.newLexProjectForm = element(by.tagName('form'));
  this.progressIndicatorStep3Label = element(by.binding('progressIndicatorStep3Label'));
  this.backButton = element(by.id('backButton'));
  this.nextButton = element(by.id('nextButton'));
  this.expectFormIsValid = function expectFormIsValid() {
    expect(this.nextButton.getAttribute('class')).toMatch(/btn-success(?:\s|$)/);
  };

  this.expectFormIsNotValid = function expectFormIsNotValid() {
    expect(this.nextButton.getAttribute('class')).not.toMatch(/btn-success(?:\s|$)/);
  };

  this.formStatus = element(by.id('form-status'));
  this.formStatus.expectHasNoError = function () {
    expect(this.formStatus.getAttribute('class')).not.toContain('alert');
  }.bind(this);

  this.formStatus.expectContainsError = function (partialMsg) {
    if (!partialMsg) partialMsg = '';
    expect(this.formStatus.getAttribute('class')).toContain('alert-danger');
    expect(this.formStatus.getText()).toContain(partialMsg);
  }.bind(this);

  // step 0: chooser
  this.chooserPage = {};
  this.chooserPage.sendReceiveButton = element(by.id('sendReceiveButton'));
  this.chooserPage.createButton = element(by.id('createButton'));

  // step 1: send receive credentials
  this.srCredentialsPage = {};
  this.srCredentialsPage.loginInput = element(by.id('srUsername'));
  this.srCredentialsPage.loginOk = element(by.id('usernameOk'));
  this.srCredentialsPage.passwordInput = element(by.id('srPassword'));
  this.srCredentialsPage.credentialsInvalid = element(by.id('credentialsInvalid'));
  this.srCredentialsPage.passwordOk = element(by.id('passwordOk'));
  this.srCredentialsPage.projectSelect = function () {
    return element(by.id('srProjectSelect'));
  };

  this.srCredentialsPage.projectNoAccess = element(by.id('projectNoAccess'));
  this.srCredentialsPage.projectOk = element(by.id('projectOk'));

  // step 1: project name
  this.namePage = {};
  this.namePage.projectNameInput = element(by.model('newProject.projectName'));
  this.namePage.projectCodeInput = element(by.model('newProject.projectCode'));
  this.namePage.projectCodeUneditableInput = element(by.binding('newProject.projectCode'));
  this.namePage.projectCodeLoading = element(by.id('projectCodeLoading'));
  this.namePage.projectCodeExists = element(by.id('projectCodeExists'));
  this.namePage.projectCodeAlphanumeric = element(by.id('projectCodeAlphanumeric'));
  this.namePage.projectCodeOk = element(by.id('projectCodeOk'));
  this.namePage.editProjectCodeCheckbox = element(by.model('newProject.editProjectCode'));

  // step 2: initial data
  this.initialDataPage = {};
  this.initialDataPage.browseButton = element(by.id('browseButton'));
  this.initialDataPage.mockUpload = mockUpload;

  // step 3: verify data
  this.verifyDataPage = {};
  this.verifyDataPage.title = element(by.tagName('h3'));
  this.verifyDataPage.nonCriticalErrorsButton = element(by.id('nonCriticalErrorsButton'));
  this.verifyDataPage.entriesImported = element(by.binding('newProject.entriesImported'));
  this.verifyDataPage.importErrors = element(by.binding('newProject.importErrors'));

  // step 3 alternate: primary language
  this.primaryLanguagePage = {};
  this.primaryLanguagePage.selectButton = element(by.id('selectLanguageButton'));

  // step 3 alternate: send receive clone
  this.srClonePage = {};
  this.srClonePage.cloning = element(by.id('cloning'));

  // see http://stackoverflow.com/questions/25553057/making-protractor-wait-until-a-ui-boostrap-modal-box-has-disappeared-with-cucum
  this.primaryLanguagePage.selectButtonClick = function () {
    this.primaryLanguagePage.selectButton.click();
    browser.executeScript('$(\'.modal\').removeClass(\'fade\');');
  }.bind(this);

  // select language modal
  this.modal = modal;
}
