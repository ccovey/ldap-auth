<?php

use Ccovey\LdapAuth;
use Mockery as m;

/**
 * User Provider Test.
 */
class LdapAuthUserProviderTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->ad = m::mock(\adLDAP\adLDAP::class);
        $this->ad->shouldReceive('close')
            ->zeroOrMoreTimes()
            ->andReturn(null);

        $this->ident = 'strebel';

        $this->ad->shouldReceive('user')->atLeast(1)
                 ->andReturn($this->ad);

        $this->ad->shouldReceive('getRecursiveGroups')->atLeast(1)
                 ->andReturn(false);

        $this->config = [
            'fields'   => [],
            'userlist' => false,
            'group'    => [],
        ];
    }

    public function tearDown()
    {
        m::close();
    }

    public function testRetrieveByIDWithoutModelReturnsLdapUser()
    {
        $this->ad->shouldReceive('infoCollection')
            ->once()->with($this->ident, ['*'])->andReturn(false);

        $user = new LdapAuth\LdapAuthUserProvider($this->ad, $this->config);

        $returned = $user->retrieveByID($this->ident);

        $this->assertNull($returned);
    }

    public function testModelResolved()
    {
        $user = new LdapAuth\LdapAuthUserProvider($this->ad, $this->config, User::class);

        $this->assertInstanceOf(User::class, $user->createModel());
    }

    public function testRetrieveByIDWithModelAndNoUserReturnsNull()
    {
        $this->ad->shouldReceive('infoCollection')
            ->once()->with($this->ident, ['*'])->andReturn(false);
        $user = $this->getProvider($this->ad, User::class);

        $mock = m::mock('stdClass');
        $mock->shouldReceive('newQuery')->once()->andReturn($mock);

        $mock->shouldReceive('find')->once()->with($this->ident)->andReturn(null);

        $user->expects($this->once())->method('createModel')->will($this->returnValue($mock));

        $retrieved = $user->retrieveByID($this->ident);

        $this->assertNull($retrieved);
    }

    public function testRetrieveByIDWithModelAndLdapInfo()
    {
        $this->ad->shouldReceive('infoCollection')
            ->once()->with($this->ident, ['*'])->andReturn($this->getLdapInfo());

        $user = $this->getProvider($this->ad, User::class);

        $mock = m::mock('stdClass');
        $mock->shouldReceive('newQuery')->once()->andReturn($mock);

        $modelMock = m::mock('stdClass');
        $modelMock->username = 'strebel';
        $modelMock->shouldReceive('getAttributes')->once()->andReturn(['foo' => 'bar']);
        $modelMock->shouldReceive('fill')->once()->andReturn(['foo' => 'bar', $this->ident]);

        $mock->shouldReceive('find')->once()->with($this->ident)->andReturn($modelMock);

        $user->expects($this->once())->method('createModel')->will($this->returnValue($mock));

        $retrieved = $user->retrieveByID($this->ident);

        $this->assertContains('bar', $retrieved);

        $this->assertContains('strebel', $retrieved);
    }

    public function testValidateCredentials()
    {
        $credentials = ['username' => 'strebel', 'password' => 'password'];
        $this->ad->shouldReceive('authenticate')->once()->andReturn(true);
        $user = $this->getProvider($this->ad, User::class);
        $model = m::mock(LdapUser::class, Illuminate\Contracts\Auth\Authenticatable::class);
        $validate = $user->validateCredentials($model, $credentials);

        $this->assertTrue($validate);
    }

    protected function getProvider($conn, $model = null)
    {
        return $this->getMock(Ccovey\LdapAuth\LdapAuthUserProvider::class,
            ['createModel'], [$conn, $this->config, $model]);
    }

    protected function getLdapInfo()
    {
        $info = new stdClass();

        $info->samaccountname = 'strebel';

        $info->displayName = 'Manuel Strebel';

        $info->distinguishedname = 'DC=LDAP,OU=AUTH,OU=FIRST GROUP';

        $info->memberof = [];

        return $info;
    }
}

class User
{
}
