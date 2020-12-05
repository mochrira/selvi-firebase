# Selvi Firebase

Fastest way to integrate selvi-framework with firebase

## Setup

Execute following lines on your cli

```
$ composer require mochrira/selvi-framework
$ composer require mochrira/selvi-firebase
```

## Configuration

Setup this package by adding following lines before `Selvi\Framework::run()` on your `index.php`

```
Firebase::setup([
    'dbConfig' => [
        'host' => 'localhost',
        'username' => '<your database user>',
        'password' => '<your database password>',
        'database' => '<your database name>'
    ],
    'serviceAccountFile' => '<service account file path>'
]);
```

## Setup Database

Setup your database by running following command on your project directory

```
$ php index.php migrate main up
```

Then, check your database, and you will see the default database structure for firebase project

## Accept Authorization header

To accept authorization header, add following lines to the end of your `.htaccess`

```
RewriteCond %{HTTP:Authorization} ^(.*)
RewriteRule .* - [e=HTTP_AUTHORIZATION:%1]
```
Then add following to the top of your `index.php`

```
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Authorization, authorization");
```