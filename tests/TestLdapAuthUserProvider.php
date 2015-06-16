<?php

use Ccovey\LdapAuth;

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

        $this->credentials = ['username' => 'user', 'password' => 'password'];

        $this->user = $this->getMockBuilder(Illuminate\Contracts\Auth\Authenticatable::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function tearDown()
    {
        Mockery::close();
    }

    public function testValidateCreditialsReturnsTrue()
    {
        $user = new LdapAuth\LdapAuthUserProvider($this->ad, 'User');
        $this->assertTrue($user->validateCredentials($this->user, $this->credentials));
    }
}
