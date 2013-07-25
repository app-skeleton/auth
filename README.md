Kohana Auth module
===================

This is a Kohana module for authentication and user management.
This module is developed and maintained by Pap Tamas (me), and has nothing to do with the default Kohana Auth module.

##API

The list of the main classes and their API:

### User_Manager

- signup($values)
- update($user_id, $values)
- get_data($find_by_value, $fields = array('*'), $find_by_field = 'users.user_id')
- garbage_collector()
- instance()


### Password_Manager

- recover($email)
- get_recovery_email($secure_key)
- reset_($secure_key, $password, $password_confirm)
- delete_recovery_links($email)
- garbage_collector()
- instance()  


### Identity

- factory($username, $password)
- authenticate()
- authenticate_with_cookie()
- authenticate_with_id($user_id)
- set_state($key, $value)
- get_state($key, $default = NULL)
- get_states()



### User

- login(Identity $identity, $create_cookie = FALSE, $cookie_lifetime = NULL)
- logout()
- logged_in()
- name()
- id()
- authenticated_with($auth_with = NULL)
- set_state($key, $value)
- get_state($key, $default = NULL)
- delete_state($key)
- get_states()
- clear_states()
- states_to_load($states = NULL)
- instance()
- _after_login()
- _create_cookie($cookie_lifetime = NULL)
- _delete_cookie()

