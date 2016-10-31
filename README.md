# Adobe Connect Web Services Proxy (API)

*Note: This API is developed by UNINETT AS and is tailor-made to suit higher education in Norway. 
It caters for an in-house developed client pertaining to a specific use-case and relies heavily on UNINETT Dataporten (for OAuth). 
Dependencies also require access to a self-hosted instance of Adobe Connect.*  

This API facilitates extraction of data (READ-ONLY) from (a self-hosted) Adobe Connect Web Services (API) for information/statistical purposes. 

## Installation

- Clone the repository
- Register the API in Dataporten and request the following scopes:
    - `groups`, `userid` and `userid-feide`
- Create a Client Scope named `admin`
- Populate the config-files in /etc (and move away from public html area) 

## Dependencies

- UNINETT Dataporten
- Alto Router
- JWT (JSON Web Token) implementation as described [here](https://coderwall.com/p/8wrxfw/goodbye-php-sessions-hello-json-web-tokens)

## Client 

Code/docs for the client, `ConnectAdmin`, for which this API was developed may be [found here](https://github.com/skrodal/adobe-connect-admin) 
Developed by Simon Skrødal, October 2016 
 