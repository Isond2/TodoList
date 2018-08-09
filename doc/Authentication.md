# Authentication

The security configuration is configured in the [security.yml](https://github.com/Isond2/TodoList/blob/master/app/config/security.yml) file .

Users are stocked into the database :

```YML
providers:
    doctrine:
        entity:
            class: AppBundle:User
            property: username
```

The password encoder used is *bcrypt* :
```YML
security:
    encoders:
        AppBundle\Entity\User: bcrypt
```

Role hierarchy :
There is 2 roles , User and Admin . The Admin have Admin + User's rights :
```YML
role_hierarchy:
    ROLE_ADMIN: ROLE_USER
```

Login form ( called in [loginAction](https://github.com/Isond2/TodoList/blob/master/src/AppBundle/Controller/SecurityController.php#L14) ) from the SecurityController  : 
```YML
main:
    logout_on_user_change: true
    anonymous: ~
    pattern: ^/
    form_login:
        login_path: login
        always_use_default_target_path:  true
        default_target_path:  /
    logout: ~
  ```
  
Permissions :

  All the pages are only accessible if you are logged as User or Admin , exept the login page and the main page .
  Only Admins can manage users (create/edit/acces to list) .
  ```php
  class UserController extends Controller
  {
  /**
  * @Security("has_role('ROLE_ADMIN')")
  */
  public function createAction ...
  ```
  
  Users can only manage their own tasks .
  
