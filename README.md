[![Build Status](https://img.shields.io/travis/strebl/ldap-auth.svg?style=flat-square)](https://travis-ci.org/strebl/ldap-auth)
[![Total Downloads](https://img.shields.io/packagist/dt/strebl/l5-ldap-auth.svg?style=flat-square)](https://packagist.org/packages/strebl/l5-ldap-auth)
[![Latest Stable Version](https://img.shields.io/packagist/v/strebl/l5-ldap-auth.svg?style=flat-square)](https://packagist.org/packages/strebl/l5-ldap-auth)
[![License](https://img.shields.io/packagist/l/strebl/l5-ldap-auth.svg?style=flat-square)](https://packagist.org/packages/strebl/l5-ldap-auth)

Active Directory LDAP Authentication
=========

Laravel 5 Active Directory LDAP Authentication driver. 

Fork
====

This is a fork of Cody Covey's ldap-auth package. Unfortunately he doesn't develeped the package recently and didn't update the package to Laravel 4.1+ or even Laravel 5. Therefore I decided to fork the package to provide a minimal Laravel 5 support.

The first release, 2.0, isn't well tested. Just be careful!

Contribution
------------
Just post an issue or create a pull request on this repository. I'll really appreciate it.

Installation
============

Versions
---------

This will follow releases similar to how Laravel itself manages releases. When Laravel moves to 5.2 this package will move to 2.2, with minor versions signifying bug fixes, etc.

| Laravel Version | Package Version | Package Status |
|-----------------|-----------------|----------------| 
| 5.1.x			  | ~2.1	 	    | maintaned 	 |
| 5.0.x 		  | ~2.1	 	    | maintaned 	 |
| 5.0.x 		  | ~2.0	 	    | abandon 	 	 |
| 4.x 			  | ~1.0	 	    | abandon		 |

Version 2.1 requires PHP 5.5+. If you are using Laravel 5.0 which supports PHP 5.4 you can still use ~2.0. However, this version won't get updates.

Laravel 5.1 / 5.0
---------

To install this package pull it in through Composer.

```bash
composer require strebl/l5-ldap-auth:~2.1
```

After Composer is done, you need to tell your application to use the LDAP service provider.

Open `config/app.php` and find

`Illuminate\Auth\AuthServiceProvider::class`

and replace it with

`Ccovey\LdapAuth\LdapAuthServiceProvider::class`

This tells Laravel to use the service provider from the vendor folder.

You also need to direct Auth to use the ldap driver instead of Eloquent or Database, edit `config/auth.php` and change driver to `ldap`:

```
    'driver' => 'ldap',
```

Laravel 4
---------
***The Laravel 4 version of this package is no longer maintained.***

To install this package pull it in through Composer.

```bash
composer require strebl/l5-ldap-auth:~1.0
```

After Composer is done, you need to tell your application to use the LDAP service provider.

Open `config/app.php` and find

`Illuminate\Auth\AuthServiceProvider`

and replace it with

`Ccovey\LdapAuth\LdapAuthServiceProvider`

This tells Laravel to use the service provider from the vendor folder.

You also need to direct Auth to use the ldap driver instead of Eloquent or Database, edit `app/config/auth.php` and change driver to `ldap`

Configuration
=============
To specify the username field to be used in `config/auth.php` (Laravel 4: `app/config/auth.php`) set a key / value pair `'username_field' => 'fieldname'` This will default to `username` if you don't provide one.

To set up your adLDAP for connections to your domain controller, create a file `config/adldap.php` (Laravel 4: `app/config/adldap.php`) This will provide all the configuration values for your connection. For all configuration options an array like the one below should be provided.

It is important to note that the only required options are `account_suffix`, `base_dn`, and `domain_controllers`. The others provide either security or more information. If you don't want to use the others simply delete them.

```php
// Example adldap.php file.
return [
	'account_suffix' => "@domain.local",

	'domain_controllers' => array("dc1.domain.local", "dc2.domain.local"), // An array of domains may be provided for load balancing.

	'base_dn' => 'DC=domain,DC=local',

	'admin_username' => 'user',

	'admin_password' => 'password',
	
	'real_primary_group' => true, // Returns the primary group (an educated guess).

	'use_ssl' => true, // If TLS is true this MUST be false.

	'use_tls' => false, // If SSL is true this MUST be false.

	'recursive_groups' => true,
];
```

Usage
======

$guarded is now defaulted to all so to use a model you must change to `$guarded = []`. If you store Roles or similar sensitive information make sure that you add that to the guarded array.

Use of `Auth` is the same as with the default service provider.

By Default this will have the `username (samaccountname)`, `displayname`, `primary group`, as well as all groups user is a part of

To edit what is returned you can specify in `config/auth.php` (Laravel 4: `app/config/auth.php`) under the `fields` key.

For more information on what fields from AD are available to you visit http://goo.gl/6jL4V

You may also get a complete user list for a specific OU by defining the `userList` key and setting it to `true`. You must also set the `group` key that defined which OU to look at. Do not that on a large AD this may slow down the application.

Model Usage
===========

You can still use a model with this implementation as well if you want. ldap-auth will take your fields from ldap and attach them to the model allowing you to access things such as roles / permissions from the model if the account is valid in Active Directory. It is also important to note that no authentication takes place off of the model. All authentication is done from Active Directory and if they are removed from AD but still in a users table they WILL NOT be able to log in.
