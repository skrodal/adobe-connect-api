# Adobe Connect API proxy

**NOTE: Created to suit higher education in Norway; makes use of Dataporten (UNINETT) for client/user (O)Authentication.** 

This API facilitates extraction of data from Adobe Connect Web Services (API) for statistical purposes. 

By implementing using Dataporten (OAuth), this API adds an extra layer of control (access, scopes) as well as simplifies reuse.   

## Installation

- Clone the repository
- Register the API in Dataporten and request the following scopes:
    - `groups`, `userid` and `userid-feide`
- Create a Client Scope named `admin`
- Populate the config-files in /etc (and move away from public html area) 

## Dependencies

- UNINETT Dataporten
- Alto Router

— by Simon Skrødal, 2016
 