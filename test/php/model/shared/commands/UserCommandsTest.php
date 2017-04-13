<?php

use Api\Library\Shared\Communicate\DeliveryInterface;
use Api\Library\Shared\Website;
use Api\Model\Shared\Command\UserCommands;
use Api\Model\Shared\PasswordModel;
use Api\Model\Shared\ProjectModel;
use Api\Model\Shared\Rights\SystemRoles;
use Api\Model\Shared\UserModel;

class MockUserCommandsDelivery implements DeliveryInterface
{
    public $from;
    public $to;
    public $subject;
    public $content;
    public $htmlContent;
    public $smsModel;

    public function sendEmail($from, $to, $subject, $content, $htmlContent = '')
    {
        $this->from = $from;
        $this->to = $to;
        $this->subject = $subject;
        $this->content = $content;
        $this->htmlContent = $htmlContent;
    }

    public function sendSms($smsModel)
    {
        $this->smsModel = $smsModel;
    }
}

class UserCommandsTest extends PHPUnit_Framework_TestCase
{
    /** @var MongoTestEnvironment Local store of mock test environment */
    private static $environ;

    /** @var mixed[] Data storage between tests */
    private static $save;

    const CROSS_SITE_DOMAIN = 'languageforge.org';

    public static function setUpBeforeClass()
    {
        self::$environ = new MongoTestEnvironment();
        self::$environ->clean();
        self::$save = [];
    }

    public function testDeleteUsers_NoThrow()
    {
        self::$environ->clean();

        $userId = self::$environ->createUser('somename', 'Some Name', 'somename@example.com');

        UserCommands::deleteUsers(array($userId));
    }

    public function testUpdateUserProfile_SetLangCode_LangCodeSet()
    {
        self::$environ->clean();

        // setup parameters
        $userId = self::$environ->createUser('username', 'name', 'name@example.com');
        $params = array(
            'id' => '',
            'interfaceLanguageCode' => 'th'
        );

        $newUserId = UserCommands::updateUserProfile($params, $userId);

        // user profile updated
        $user = new UserModel($newUserId);
        $this->assertEquals('th', $user->interfaceLanguageCode);
        $this->assertEquals($newUserId, $userId);
    }

    /**
     * @expectedException Exception
     */
    public function testCheckIdentity_userDoesNotExist_Exception()
    {
        $identityCheck = UserCommands::checkIdentity('', '', null);
    }

    public function testCheckUniqueIdentity_userExistsNoEmail_UsernameExistsEmailEmpty()
    {
        self::$environ->clean();

        $userId = self::$environ->createUser('jsmith', 'joe smith','');
        $joeUser = new UserModel($userId);

        $identityCheck = UserCommands::checkUniqueIdentity($joeUser, 'jsmith', '', self::$environ->website);

        $this->assertTrue($identityCheck->usernameExists);
        $this->assertTrue($identityCheck->usernameExistsOnThisSite);
        $this->assertTrue($identityCheck->usernameMatchesAccount);
        $this->assertTrue($identityCheck->allowSignupFromOtherSites);
        $this->assertFalse($identityCheck->emailExists);
        $this->assertTrue($identityCheck->emailIsEmpty);
        $this->assertTrue($identityCheck->emailMatchesAccount);
    }

    public function testCheckUniqueIdentity_userExistsWithEmail_UsernameExistsEmailMatches()
    {
        self::$environ->clean();

        $userId = self::$environ->createUser('jsmith', 'joe smith','joe@smith.com');
        $joeUser = new UserModel($userId);

        $identityCheck = UserCommands::checkUniqueIdentity($joeUser, 'jsmith', 'joe@smith.com', null);

        $this->assertTrue($identityCheck->usernameExists);
        $this->assertFalse($identityCheck->usernameExistsOnThisSite);
        $this->assertTrue($identityCheck->usernameMatchesAccount);
        $this->assertTrue($identityCheck->emailExists);
        $this->assertFalse($identityCheck->emailIsEmpty);
        $this->assertTrue($identityCheck->emailMatchesAccount);
    }

    public function testCheckUniqueIdentity_userExistsWithEmail_UsernameExistsEmailDoesNotMatch()
    {
        self::$environ->clean();

        $user1Id = self::$environ->createUser('zedUser', 'zed user','zed@example.com');
        $zedUser = new UserModel($user1Id);
        $originalWebsite = clone self::$environ->website;
        self::$environ->website->domain = 'default.local';
        self::$environ->createUser('jsmith', 'joe smith','joe@smith.com');

        $identityCheck = UserCommands::checkUniqueIdentity($zedUser, 'jsmith', 'zed@example.com', $originalWebsite);

        $this->assertTrue($identityCheck->usernameExists);
        $this->assertFalse($identityCheck->usernameExistsOnThisSite);
        $this->assertFalse($identityCheck->usernameMatchesAccount);
        $this->assertTrue($identityCheck->emailExists);
        $this->assertFalse($identityCheck->emailIsEmpty);
        $this->assertTrue($identityCheck->emailMatchesAccount);

        // cleanup so following tests are OK
        self::$environ->website->domain = $originalWebsite->domain;
    }

    public function testCheckUniqueIdentity_userExistsWithEmail_UsernameExistsEmailDoesNotMatchEmpty()
    {
        self::$environ->clean();

        $userId = self::$environ->createUser('jsmith', 'joe smith','joe@smith.com');
        $joeUser = new UserModel($userId);

        $identityCheck = UserCommands::checkUniqueIdentity($joeUser, 'jsmith', '', self::$environ->website);

        $this->assertTrue($identityCheck->usernameExists);
        $this->assertTrue($identityCheck->usernameExistsOnThisSite);
        $this->assertTrue($identityCheck->usernameMatchesAccount);
        $this->assertFalse($identityCheck->emailExists);
        $this->assertFalse($identityCheck->emailIsEmpty);
        $this->assertFalse($identityCheck->emailMatchesAccount);
    }

    public function testCheckUniqueIdentity_doesNotExist_UsernameDoesNotExist()
    {
        self::$environ->clean();

        $userId = self::$environ->createUser('jsmith', 'joe smith','joe@smith.com');
        $joeUser = new UserModel($userId);

        $identityCheck = UserCommands::checkUniqueIdentity($joeUser, 'zedUser', 'zed@example.com', self::$environ->website);
        $this->assertFalse($identityCheck->usernameExists);
        $this->assertFalse($identityCheck->usernameExistsOnThisSite);
        $this->assertFalse($identityCheck->usernameMatchesAccount);
        $this->assertFalse($identityCheck->emailExists);
        $this->assertTrue($identityCheck->emailIsEmpty);
        $this->assertFalse($identityCheck->emailMatchesAccount);
    }

    public function testCheckUniqueIdentity_emailExist_UsernameDoesNotExist()
    {
        self::$environ->clean();

        $userId = self::$environ->createUser('jsmith', 'joe smith','joe@smith.com');
        $joeUser = new UserModel($userId);

        $identityCheck = UserCommands::checkUniqueIdentity($joeUser, 'zedUser', 'joe@smith.com', self::$environ->website);

        $this->assertFalse($identityCheck->usernameExists);
        $this->assertFalse($identityCheck->usernameExistsOnThisSite);
        $this->assertFalse($identityCheck->usernameMatchesAccount);
        $this->assertTrue($identityCheck->emailExists);
        $this->assertTrue($identityCheck->emailIsEmpty);
        $this->assertTrue($identityCheck->emailMatchesAccount);
    }

    public function testCheckUniqueIdentity_userExist()
    {
        self::$environ->clean();

        $user1Id = self::$environ->createUser('jsmith', 'joe smith','joe@smith.com');
        $joeUser = new UserModel($user1Id);
        $user2Id = self::$environ->createUser('zedUser', 'zed user','zed@example.com');
        $zedUser = new UserModel($user2Id);

        $identityCheck = UserCommands::checkUniqueIdentity($zedUser, 'jsmith', 'joe@smith.com', self::$environ->website);

        $this->assertTrue($identityCheck->usernameExists);
        $this->assertTrue($identityCheck->usernameExistsOnThisSite);
        $this->assertFalse($identityCheck->usernameMatchesAccount);
        $this->assertTrue($identityCheck->emailExists);
        $this->assertFalse($identityCheck->emailIsEmpty);
        $this->assertFalse($identityCheck->emailMatchesAccount);
    }

    public function testCheckUniqueIdentity_caseInsensitiveEmail_userExist()
    {
        self::$environ->clean();

        $user1Id = self::$environ->createUser('jsmith', 'joe smith','joe@smith.com');
        $joeUser = new UserModel($user1Id);
        $user2Id = self::$environ->createUser('zedUser', 'zed user','zed@example.com');
        $zedUser = new UserModel($user2Id);

        $identityCheck = UserCommands::checkUniqueIdentity($zedUser, 'jsmith', 'ZED@example.com', self::$environ->website);

        $this->assertTrue($identityCheck->usernameExists);
        $this->assertTrue($identityCheck->usernameExistsOnThisSite);
        $this->assertFalse($identityCheck->usernameMatchesAccount);
        $this->assertTrue($identityCheck->emailExists);
        $this->assertFalse($identityCheck->emailIsEmpty);
        $this->assertFalse($identityCheck->emailMatchesAccount);
    }

    public function testCreateSimple_CreateUser_PasswordAndJoinProject()
    {
        self::$environ->clean();

        // setup parameters: username and project
        $userName = 'username';
        $project = self::$environ->createProject(SF_TESTPROJECT, SF_TESTPROJECTCODE);
        $projectId = $project->id->asString();

        $currentUserId = self::$environ->createUser('test1', 'test1', 'test@test.com');

        // create user
        $dto = UserCommands::createSimple($userName, $projectId, $currentUserId, self::$environ->website);

        // read from disk
        $user = new UserModel($dto['id']);
        $sameProject = new ProjectModel($projectId);

        // user created and password created, user joined to project
        $this->assertEquals('username', $user->username);
        $this->assertEquals(7, strlen($dto['password']));
        $projectUser = $sameProject->listUsers()->entries[0];
        $this->assertEquals('username', $projectUser['username']);
        $userProject = $user->listProjects(self::$environ->website->domain)->entries[0];
        $this->assertEquals(SF_TESTPROJECT, $userProject['projectName']);
    }

    /**
     * @expectedException Exception
     */
    public function testCreateSimple_UsernameExist_Exception()
    {
        self::$environ->clean();

        // setup parameters: name and project
        $name = 'User Name';
        $project = self::$environ->createProject(SF_TESTPROJECT, SF_TESTPROJECTCODE);
        $projectId = $project->id->asString();

        $currentUserId = self::$environ->createUser('test1', 'test1', 'test@test.com');

        // create user
        $dto = UserCommands::createSimple($name, $projectId, $currentUserId, self::$environ->website);

        // create user again
        $dto = UserCommands::createSimple($name, $projectId, $currentUserId, self::$environ->website);
    }

    public function testRegister_WithProjectCode_UserInProjectAndProjectHasUser()
    {
        // todo: implement this - register within a project context
    }

    public function testRegister_NoProjectCode_UserInNoProjects()
    {
        self::$environ->clean();

        $validCode = 'validCode';
        $params = array(
                'id' => '',
                'username' => 'someusername',
                'name' => 'Some Name',
                'email' => 'someone@example.com',
                'password' => 'somepassword',
                'captcha' => $validCode
        );
        $captcha_info = array('code' => $validCode);

        $this->assertFalse(UserModel::userExists($params['email']));

        $delivery = new MockUserCommandsDelivery();
        UserCommands::register($params, self::$environ->website, $captcha_info, $delivery);

        $user = new UserModel();
        $user->readByEmail($params['email']);
        $this->assertEquals($params['email'], $user->email);
        $this->assertEquals(0, $user->listProjects(self::$environ->website->domain)->count);
    }

    public function testRegister_InvalidCaptcha_CaptchaFail()
    {
        self::$environ->clean();

        $validCode = 'validCode';
        $invalidCode = 'invalidCode';
        $params = array(
            'id' => '',
            'username' => 'someusername',
            'name' => 'Some Name',
            'email' => 'someone@example.com',
            'password' => 'somepassword',
            'captcha' => $invalidCode
        );
        $captcha_info = array('code' => $validCode);

        $delivery = new MockUserCommandsDelivery();
        $result = UserCommands::register($params, self::$environ->website, $captcha_info, $delivery);

        $this->assertEquals($result, 'captchaFail');
    }

    public function testRegister_EmailInUsePasswordExists_EmailNotAvailable()
    {
        self::$environ->clean();

        // setup parameters: user 'test1'
        $userName = 'username';
        $project = self::$environ->createProject(SF_TESTPROJECT, SF_TESTPROJECTCODE);
        $projectId = $project->id->asString();
        $currentUserId = self::$environ->createUser('test1', 'test1', 'test@test.com');

        // create user 'username' with password, and assign an email address
        $dto = UserCommands::createSimple($userName, $projectId, $currentUserId, self::$environ->website);
        $user = new UserModel($dto['id']);
        $takenEmail = 'username@test.com';
        $user->email = $takenEmail;
        $user->write();

        $validCode = 'validCode';
        $params = array(
            'id' => '',
            'username' => 'someusername',
            'name' => 'Some Name',
            'email' => $takenEmail,
            'password' => 'somepassword',
            'captcha' => $validCode
        );
        $captcha_info = array('code' => $validCode);
        $delivery = new MockUserCommandsDelivery();

        // Attempt to register
        $result = UserCommands::register($params, self::$environ->website, $captcha_info, $delivery);

        $this->assertEquals($result, 'emailNotAvailable');
    }

    public function testRegister_EmailInUseNoPassword_Login()
    {
        self::$environ->clean();

        // setup parameters: user 'test1'
        $takenEmail = 'test@test.com';
        $currentUserId = self::$environ->createUser('test1', 'test1', $takenEmail);

        $validCode = 'validCode';
        $params = array(
            'id' => '',
            'username' => 'someusername',
            'name' => 'Some Name',
            'email' => $takenEmail,
            'password' => 'somepassword',
            'captcha' => $validCode
        );
        $captcha_info = array('code' => $validCode);
        $delivery = new MockUserCommandsDelivery();

        // Attempt to register
        $result = UserCommands::register($params, self::$environ->website, $captcha_info, $delivery);

        $this->assertEquals($result, 'login');
    }

    public function testRegister_NewUser_Login()
    {
        self::$environ->clean();

        $validCode = 'validCode';
        $params = array(
            'id' => '',
            'username' => 'anotherusername',
            'name' => 'Another Name',
            'email' => 'another@example.com',
            'password' => 'anotherpassword',
            'captcha' => $validCode
        );
        $captcha_info = array('code' => $validCode);
        $userId = self::$environ->createUser('someusername', 'Some Name', 'someone@example.com');

        $delivery = new MockUserCommandsDelivery();
        $result = UserCommands::register($params, self::$environ->website, $captcha_info, $delivery);

        $this->assertEquals($result, 'login');

    }

    public function testRegister_CrossSiteEnabled_UserHasSiteRole()
    {
        self::$environ->clean();
        $validCode = 'validCode';
        $params = array(
            'id' => '',
            'username' => 'jsmith',
            'name' => 'joe smith',
            'email' => 'joe@smith.com',
            'password' => 'somepassword',
            'captcha' => $validCode
        );
        $website = Website::get(self::CROSS_SITE_DOMAIN);
        $captcha_info = array('code' => $validCode);
        $delivery = new MockUserCommandsDelivery();
        // Register user to default website
        $result = UserCommands::register($params, self::$environ->website, $captcha_info, $delivery);
        $joeUser = new UserModel();
        $joeUser->readByEmail('joe@smith.com');
        $this->assertFalse($joeUser->hasRoleOnSite($website));

        // Register user to cross-site
        $result = UserCommands::register($params, $website, $captcha_info, $delivery);

        $joeUser->readByEmail('joe@smith.com');
        $this->assertEquals($result, 'login');
        $this->assertTrue($joeUser->hasRoleOnSite($website));
    }

    public function testSendInvite_Register_UserActive()
    {
        self::$environ->clean();

        $inviterUserId = self::$environ->createUser("inviteruser", "Inviter Name", "inviter@example.com");
        $project = self::$environ->createProject(SF_TESTPROJECT, SF_TESTPROJECTCODE);
        $project->projectCode = 'someProjectCode';
        $project->write();
        $delivery = new MockUserCommandsDelivery();

        $validCode = 'validCode';
        $captcha_info = array('code' => $validCode);
        $params = array(
            'id' => '',
            'username' => 'jsmith',
            'name' => 'joe smith',
            'email' => 'joe@smith.com',
            'password' => 'somepassword',
            'captcha' => $validCode
        );

        $toUserId = UserCommands::sendInvite($project->id->asString(), $inviterUserId, self::$environ->website, $params['email'], $delivery);
        $joeUser = new UserModel($toUserId);
        $this->assertEquals($joeUser->active, null);
        $this->assertNull($joeUser->active);

        $result = UserCommands::register($params, self::$environ->website, $captcha_info, $delivery);

        $joeUser = new UserModel($toUserId);
        $this->assertTrue($joeUser->active);
    }

    public function testSendInvite_SendInvite_PropertiesFromToBodyOk()
    {
        self::$environ->clean();

        $inviterUserId = self::$environ->createUser("inviteruser", "Inviter Name", "inviter@example.com");
        $toEmail = 'someone@example.com';
        $project = self::$environ->createProject(SF_TESTPROJECT, SF_TESTPROJECTCODE);
        $project->projectCode = 'someProjectCode';
        $project->write();
        $delivery = new MockUserCommandsDelivery();

        $toUserId = UserCommands::sendInvite($project->id->asString(), $inviterUserId, self::$environ->website, $toEmail, $delivery);

        // What's in the delivery?
        $toUser = new UserModel($toUserId);

        $senderEmail = 'no-reply@' . self::$environ->website->domain;
        $expectedFrom = array($senderEmail => self::$environ->website->name);
        $expectedTo = array($toUser->emailPending => $toUser->name);
        $this->assertEquals($expectedFrom, $delivery->from);
        $this->assertEquals($expectedTo, $delivery->to);
        $this->assertRegExp('/Inviter Name/', $delivery->content);
        $this->assertRegExp('/Test Project/', $delivery->content);
    }

    public function testChangePassword_SystemAdminChangeOtherUser_Succeeds()
    {
        self::$environ->clean();

        $adminModel = new UserModel();
        $adminModel->username = 'admin';
        $adminModel->role = SystemRoles::SYSTEM_ADMIN;
        $adminId = $adminModel->write();
        $userModel = new UserModel();
        $userModel->username = 'user';
        $userModel->role = SystemRoles::NONE;
        $userId = $userModel->write();

        $this->assertNotEquals($userId, $adminId);
        UserCommands::changePassword($userId, 'somepass', $adminId);
        $passwordModel = new PasswordModel($userId);
        $result = $passwordModel->verifyPassword('somepass');
        $this->assertTrue($result, 'Could not verify changed password');
    }
}
