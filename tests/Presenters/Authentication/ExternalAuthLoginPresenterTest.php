<?php

declare(strict_types=1);

require_once(ROOT_DIR . 'Pages/Authentication/ExternalAuthLoginPage.php');
require_once(ROOT_DIR . 'Presenters/Authentication/ExternalAuthLoginPresenter.php');
require_once(ROOT_DIR . 'lib/Common/Validators/namespace.php');

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;

class TestableExternalAuthLoginPresenter extends ExternalAuthLoginPresenter
{
    public function callProcessUserData(
        string $username,
        string $email,
        string $firstName,
        string $lastName,
        ?string $phone = null,
        ?string $organization = null,
        ?string $title = null
    ): void {
        $this->processUserData($username, $email, $firstName, $lastName, $phone, $organization, $title);
    }

    public function callBuildRedirectUri(string $configuredPath): string
    {
        return $this->buildRedirectUri($configuredPath);
    }
}

class ExternalAuthLoginPresenterTest extends TestBase
{
    private ExternalAuthLoginPage $page;
    private FakeWebAuthentication $auth;
    private FakeRegistration $registration;
    private TestableExternalAuthLoginPresenter $presenter;

    public function setUp(): void
    {
        parent::setUp();

        $this->page = $this->createMock(ExternalAuthLoginPage::class);
        $this->auth = new FakeWebAuthentication();
        $this->registration = new FakeRegistration();
        $this->presenter = new TestableExternalAuthLoginPresenter(
            $this->page,
            $this->auth,
            $this->registration,
            new Client(['handler' => HandlerStack::create(new MockHandler([]))]),
        );
    }

    public function testExistingUserIsLoggedInAndRedirected(): void
    {
        $this->registration->_UserExists = true;
        $this->page->method('Redirect');

        $this->presenter->callProcessUserData('user@example.com', 'user@example.com', 'John', 'Doe');

        $this->assertTrue($this->auth->_LoginCalled);
        $this->assertSame('user@example.com', $this->auth->_LastLogin);
    }

    public function testNewUserIsSynchronizedAndLoggedInWhenRegistrationIsAllowed(): void
    {
        $this->registration->_UserExists = false;
        $this->fakeConfig->SetKey(ConfigKeys::REGISTRATION_ALLOW_SELF, true);
        $this->page->method('Redirect');

        $this->presenter->callProcessUserData('user@example.com', 'user@example.com', 'John', 'Doe');

        $this->assertTrue($this->registration->_SynchronizeCalled);
        $this->assertSame('user@example.com', $this->registration->_LastSynchronizedUser->Email());
        $this->assertTrue($this->auth->_LoginCalled);
    }

    public function testNewUserSeesErrorWhenRegistrationIsDisabled(): void
    {
        $this->registration->_UserExists = false;
        $this->fakeConfig->SetKey(ConfigKeys::REGISTRATION_ALLOW_SELF, false);
        $this->page->expects($this->once())->method('ShowError');

        $this->presenter->callProcessUserData('user@example.com', 'user@example.com', 'John', 'Doe');

        $this->assertFalse($this->auth->_LoginCalled);
    }

    public function testInvalidEmailDomainShowsError(): void
    {
        $this->fakeConfig->SetKey(ConfigKeys::AUTHENTICATION_REQUIRED_EMAIL_DOMAINS, 'example.com');
        $this->page->expects($this->once())->method('ShowError');

        $this->presenter->callProcessUserData('user@other.com', 'user@other.com', 'John', 'Doe');

        $this->assertFalse($this->auth->_LoginCalled);
    }

    public function testMatchingEmailDomainPassesThroughToLogin(): void
    {
        $this->registration->_UserExists = true;
        $this->fakeConfig->SetKey(ConfigKeys::AUTHENTICATION_REQUIRED_EMAIL_DOMAINS, 'example.com');
        $this->page->method('Redirect');

        $this->presenter->callProcessUserData('user@example.com', 'user@example.com', 'John', 'Doe');

        $this->assertTrue($this->auth->_LoginCalled);
    }

    public function testBuildRedirectUriStripsWebPrefixToAvoidDuplication(): void
    {
        $this->fakeConfig->_ScriptUrl = 'http://localhost/approot/Web';

        $result = $this->presenter->callBuildRedirectUri('/Web/external-auth.php');

        $this->assertSame('http://localhost/approot/Web/external-auth.php', $result);
    }

    public function testBuildRedirectUriLeavesPathIntactWhenScriptUrlHasNoWebSuffix(): void
    {
        $this->fakeConfig->_ScriptUrl = 'http://localhost/approot';

        $result = $this->presenter->callBuildRedirectUri('/Web/external-auth.php');

        $this->assertSame('http://localhost/approot/Web/external-auth.php', $result);
    }

    public function testBuildRedirectUriStripsTrailingSlashFromScriptUrl(): void
    {
        $this->fakeConfig->_ScriptUrl = 'http://localhost/approot/Web/';

        $result = $this->presenter->callBuildRedirectUri('/callback.php');

        $this->assertSame('http://localhost/approot/Web/callback.php', $result);
    }

    public function testBuildRedirectUriAddsLeadingSlashToPath(): void
    {
        $this->fakeConfig->_ScriptUrl = 'http://localhost';

        $result = $this->presenter->callBuildRedirectUri('callback.php');

        $this->assertSame('http://localhost/callback.php', $result);
    }
}
