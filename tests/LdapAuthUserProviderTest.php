<?php

use Ccovey\LdapAuth;

use Mockery as m;

/**
* User Provider Test
*/
class LdapAuthUserProviderTest extends PHPUnit_Framework_TestCase
{
	public function setUp()
	{
		$this->ad = m::mock('adLDAP\adLDAP');
		$this->ad->shouldReceive('close')
			->zeroOrMoreTimes()
			->andReturn(null);

		$this->ident = 'ccovey';

		$this->ad->shouldReceive('user')->atLeast(1)
			->andReturn($this->ad);

		$this->config = array(
			'fields' => array(),
			'userlist' => false,
			'group' => array()
		);
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
		$user = new LdapAuth\LdapAuthUserProvider($this->ad, $this->config, 'User');

		$this->assertInstanceOf('User', $user->createModel());
	}

	public function testRetrieveByIDWithModelAndNoUserReturnsNull()
	{
		$this->ad->shouldReceive('infoCollection')
			->once()->with($this->ident, ['*'])->andReturn(false);
		$user = $this->getProvider($this->ad, 'User');

		$retrieved = $user->retrieveByID($this->ident);

		$this->assertNull($retrieved);
	}

	public function testRetrieveByIDWithModelAndLdapInfo()
	{
		$this->ad->shouldReceive('infoCollection')
			->once()->with($this->ident, ['*'])->andReturn($this->getLdapInfo());

		$user = $this->getProvider($this->ad, 'User');

		$mock = m::mock('stdClass');
		$mock->shouldReceive('newQuery')->once()->andReturn($mock);
		
		$modelMock = m::mock('stdClass');
		$modelMock->shouldReceive('getAttributes')->once()->andReturn(array('foo' => 'bar'));
		$modelMock->shouldReceive('fill')->once()->andReturn(['foo' => 'bar', $this->ident]);

		$mock->shouldReceive('find')->once()->with($this->ident)->andReturn($modelMock);

		$user->expects($this->once())->method('createModel')->will($this->returnValue($mock));

		$retrieved = $user->retrieveByID($this->ident);

		$this->assertContains('bar', $retrieved);

		$this->assertContains('ccovey', $retrieved);
	}

	public function testValidateCredentials()
	{
		$credentials = array('username' => 'ccovey', 'password' => 'password');
		$this->ad->shouldReceive('authenticate')->once()->andReturn(true);
		$user = new Ccovey\LdapAuth\LdapAuthUserProvider($this->ad,$this->config);
		$model = m::mock('Ccovey\LdapAuth\LdapUser');
		$validate = $user->validateCredentials($model, $credentials);

		$this->assertTrue($validate);
	}

	protected function getProvider($conn, $model = null)
	{
		return $this->getMock('Ccovey\LdapAuth\LdapAuthUserProvider', 
			array('createModel'), array($conn, $this->config, $model));
	}

	protected function getLdapInfo()
	{
		$info = new stdClass;

		$info->samaccountname = 'ccovey';

		$info->displayName = 'Cody Covey';

		$info->distinguishedname = 'DC=LDAP,OU=AUTH,OU=FIRST GROUP';

		$info->memberof = array();

		return $info;
	}
}

class User{}