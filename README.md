## sign_in_with_apple flutter package backend implementation in php

This Laravel project receives the redirect request from Apple after the user has successfully login to Apple and approve the use of Apple ID to authenticate the mobile app.
It is a very simple implementation and the purpose is to just verify the data being sent to the backend and how to verify that these data are legit. Lastly, the response will redirect the user back to the mobile app.
This project does not create a new user's account or store any information. Furthermore, it did not handle situation subsequent login (where user's email and name are not presented) as well as refreshing of tokens. These have to be implemented in the full implementation.

The mobile app implementation is in https://github.com/leekaimun/sign_in_apple_flutter
