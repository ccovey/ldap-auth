Active Directory LDAP Authentication
=========

Laravel 4 Active Directory LDAP Authentication driver. 

Installation
============

To install this in your application add the following to your `composer.json` file

```json
require {
	"ccovey/ldap-auth": "dev-master",
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

Usage
======

$guarded is now defaulted to all so to use a model you must change to `$guarded = array()`. If you store Roles or similar sensitive information make sure that you add that to the guarded array.

To define your domain and other AD specific information you must set it in /vendor/adLDAP/adLDAP/lib/adLDAP/adLDAP.php

Use of `Auth` is the same as with the default service provider.

By Default this will have the `username (samaccountname)`, `displayname`, `primary group`, as well as all groups user is a part of

To edit what is returned you can specify in `config/auth.php` under the `fields` key.

For more information on what fields from AD are available to you visit http://goo.gl/6jL4V

You may also get a complete user list for a specific OU by defining the `table` key.

Model Usage
===========

You can still use a model with this implementation as well if you want. ldap-auth will take your fields from ldap and attach them to the model allowing you to access things such as roles / permissions from the model if the account is valid in Active Directory.
