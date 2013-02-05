<?php
use Ccovey\LdapAuth;
use adLDAP\adLDAP;
use Illuminate\Auth;

/**
* 
*/
class TestLdapAuthUserProvider extends PHPUnit_Framework_TestCase
{
	public function setUp()
	{
		$this->credentials = ['username' => 'user', 'password' => 'password'];

		$this->ad = $this->getMockBuilder('adLDAP\adLDAP')
			->setMethods(['authenticate'])
			->disableOriginalConstructor()
			->getMock();

		$this->ad->expects($this->atLeastOnce())
			->method('authenticate')
			->will($this->returnValue(true));

		$this->credentials = array('username' => 'user', 'password' => 'password');

		$this->user = $this->getMockBuilder('Illuminate\Auth\UserInterface')
			->disableOriginalConstructor()
			->getMock();
	}

	public function tearDown()
	{
		//Mockery::close();

	}

	public function testValidateCreditialsReturnsTrue()
	{
		$user = new LdapAuth\LdapAuthUserProvider($this->ad);
		$this->assertTrue($user->validateCredentials($this->user, $this->credentials));
	}
}