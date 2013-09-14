Kohana Auth module
===================

This is a Kohana module for authentication and user management.
This module is developed and maintained by Pap Tamas (me), and has nothing to do with the default Kohana Auth module.

##API

The list of the main classes and their API:

### User_Manager

- signup_user
- update_user
- get_user_data
- get_user_data_by
- garbage_collector
- instance


### Password_Manager

- recover_password
- check_recovery_link
- reset_password
- garbage_collector
- instance


### Identity

- authenticate
- authenticate_with_cookie
- authenticate_with_id
- set_state
- get_state
- get_states
- id
- factory


### User

- login
- logout
- logged_in
- username
- full_name
- timezone
- datetime
- id
- authenticated_with
- set_state
- get_state
- delete_state
- get_states
- clear_states
- states_to_load
- instance
- _after_login
- _create_cookie
- _delete_cookie

