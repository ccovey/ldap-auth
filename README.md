Active Directory LDAP Authentication
=========

Laravel 4 Active Directory LDAP Authentication driver. 

Installation
============

To install this in your application add the following to your `composer.json` file

```json
require {
	"ccovey/ldap-auth": "1.0.x",
}
```

Then run 

`composer install` or `composer update` as appropriate

Once you have finished downloading the package from Packagist.org you need to tell your Application to use the LDAP service provider.

Open `config/app.php` and find

`Illuminate\Auth\AuthServiceProvider`

and replace it with

`Ccovey\LdapAuth\LdapAuthServiceProvider`

This tells Laravel 4 to use the service provider from the vendor folder.

You also need to direct Auth to use the ldap driver instead of Eloquent or Database, edit `config/auth.php` and change driver to `ldap`

Configuration
=============
To specify the username field to be used in `app/config/auth.php` set a key / value pair `'username_field' => 'fieldname'` This will default to `username` if you don't provide one.

To set up your adLDAP for connections to your domain controller, create a file app/config/adldap.php This will provide all the configuration values for your connection. For all configuration options an array like the one below should be provided.

It is important to note that the only required options are `account_suffix`, `base_dn`, and `domain_controllers`The others provide either security or more information. If you don't want to use the others simply delete them.

```php
return array(
	'account_suffix' => "@domain.local",

	'domain_controllers' => array("dc1.domain.local", "dc2.domain.local"), // An array of domains may be provided for load balancing.

	'base_dn' => 'DC=domain,DC=local',

	'admin_username' => 'user',

	'admin_password' => 'password',
	'real_primary_group' => true, // Returns the primary group (an educated guess).

	'use_ssl' => true, // If TLS is true this MUST be false.

	'use_tls' => false, // If SSL is true this MUST be false.

	'recursive_groups' => true,
);
```

Usage
======

$guarded is now defaulted to all so to use a model you must change to `$guarded = array()`. If you store Roles or similar sensitive information make sure that you add that to the guarded array.

Use of `Auth` is the same as with the default service provider.

By Default this will have the `username (samaccountname)`, `displayname`, `primary group`, as well as all groups user is a part of

To edit what is returned you can specify in `config/auth.php` under the `fields` key.

For more information on what fields from AD are available to you visit http://goo.gl/6jL4V

You may also get a complete user list for a specific OU by defining the `userList` key and setting it to `true`. You must also set the `group` key that defined which OU to look at. Do not that on a large AD this may slow down the application.

Model Usage
===========

You can still use a model with this implementation as well if you want. ldap-auth will take your fields from ldap and attach them to the model allowing you to access things such as roles / permissions from the model if the account is valid in Active Directory. It is also important to note that no authentication takes place off of the model. All authentication is done from Active Directory and if they are removed from AD but still in a users table they WILL NOT be able to log in.

You can also synchronize your LDAP info to your original Model Database. Just set `'save_model' => true,` in your `config/auth.php`
