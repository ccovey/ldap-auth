ldap-auth
=========

Notes
=======
I am going to be working on this this weekend. To get an idea of what this will use take a look at the develop branch. Essentially you will tell the config what items you need from the `infoCollection` method from `adLDAP` to be made available for `Auth::user()`. I will tag this as 1.0 when released. Thanks for the interest. 

ldap-auth

Laravel 4 Active Directory LDAP Authentication driver. 

After you run `composer install`, be sure to go into the adLDAP.php and set the configuration settings.

Current implementation returns user display name, user login name, groups the user is a part of, as well
as all users in the `Departments` User group. This will be changed shortly for more customizable user list returns.

To install simply add 
`ccovey/ldap-auth` to the `require` section of your Laravel 4 composer.json file.

Then go to config/app.php and change `Illuminate\Auth\AuthServiceProvider` to `LDAPAuth\LDAPAuthServiceProvider`

Use of Auth is the same throughout. 
