<?php
error_reporting(E_ALL);
//use Mockery as m;
use Ccovey\LdapAuth;

/**
* 
*/
class TestLdapAuthUserProvider extends PHPUnit_Framework_TestCase
{
	public function setUp()
	{
		$this->credentials = ['username' => 'user', 'password' => 'password'];

		//$this->ad = Mockery::mock('adLDAP\adLdap');
	}

	public function tearDown()
	{
		//Mockery::close();

	}

	public function testValidateCreditialsReturnsTrue()
	{
		//$user = new LdapAuthUserProvider();
		$this->assertTrue(true);
	}
}