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
- get_user_id_by
- garbage_collector
- instance


### Password_Manager

- recover
- get_recovery_email
- reset
- delete_recovery_links
- garbage_collector
- instance


### Identity

- authenticate
- authenticate_with_cookie
- authenticate_with_id
- set_state
- get_state
- get_states
- factory


### User

- login
- logout
- logged_in
- name
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

