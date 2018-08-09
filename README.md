# TodoList - Tasks management

This project is part of the "php/symfony" training.

TodoList is a tasks management app where each user can connect to his account and set/mark as done/delete his tasks.

Install :

- Clone the repository
- run $ php composer install

Create database :

- $ php bin/console doctrine:database:create
- $ php bin/console doctrine:schema:update --force

Load the sample data :

- $ php bin/console doctrine:fixtures:load


Functional tests :

Functional tests covers 97.73% of the app (web\code-coverage)

Test the app: - vendor/bin/phpunit

Update code coverage : - vendor/bin/phpunit --coverage-html web/code-coverage

> Warning : to run correctly the code coverage , you need Xdebug 

The authentication process and the user's storage are explained in the [Authentication](https://github.com/Isond2/TodoList/blob/master/doc/Authentication.md) file

If you want to contribute ! : [Contribute to the project](https://github.com/Isond2/TodoList/blob/master/doc/Contribute%20to%20the%20project.md)

A more complete documentation of the app is also avaliable in pdf : doc/Documentation.pdf
