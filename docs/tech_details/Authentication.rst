Authentication
==============

AppAPI introduces a distinct method of authentication for external apps.
This authentication relies on a shared secret between Nextcloud and the external app

Authentication flow
^^^^^^^^^^^^^^^^^^^

1. ExApp sends a request to Nextcloud
2. Nextcloud passes request to AppAPI
3. AppAPI validates request (see `authentication flow in details`_)
4. Request is accepted/rejected

.. mermaid::

	sequenceDiagram
    	participant ExApp
    	box Nextcloud
			participant Nextcloud
			participant AppAPI
		end
    	ExApp->>+Nextcloud: Request to API
    	Nextcloud->>+AppAPI: Validate request
    	AppAPI-->>-Nextcloud: Request accepted/rejected
    	Nextcloud-->>-ExApp: Response (200/401)


Authentication headers
^^^^^^^^^^^^^^^^^^^^^^

Each ExApp request to secured API with AppAPIAuth must contain the following headers:

1. ``AA-VERSION`` - minimal version of the AppAPI
2. ``EX-APP-ID``- ID of the ExApp
3. ``EX-APP-VERSION`` - version of the ExApp
4. ``AUTHORIZATION-APP-API`` - base64 encoded ``userid:secret``

Authentication flow in details
******************************

.. mermaid::
	:zoom:

	sequenceDiagram
		autonumber
		participant ExApp
		box Nextcloud
			participant Nextcloud
			participant AppAPI
		end
		ExApp->>+Nextcloud: Request to API
		Nextcloud->>Nextcloud: Check if AUTHORIZATION-APP-API header exists
		Nextcloud-->>ExApp: Reject if AUTHORIZATION-APP-API header not exists
		Nextcloud->>Nextcloud: Check if AppAPI app is enabled
		Nextcloud-->>ExApp: Reject if AppAPI is not exists or disabled
		Nextcloud->>+AppAPI: Validate request
		AppAPI-->>AppAPI: Check if ExApp exists and enabled
		AppAPI-->>Nextcloud: Reject if ExApp not exists or disabled
		AppAPI-->>AppAPI: Check if ExApp version changed
		AppAPI-->>Nextcloud: Disable ExApp and notify admins if version changed
		AppAPI-->>AppAPI: Validate shared secret from AUTHORIZATION-APP-API
		AppAPI-->>Nextcloud: Reject if secret does not match
		AppAPI-->>AppAPI: Check API scope
		AppAPI-->>Nextcloud: Reject if API scope not match
		AppAPI-->>AppAPI: Check if user interacted with ExApp
		AppAPI-->>Nextcloud: Reject if user has not interacted with ExApp (attempt to bypass user)
		AppAPI-->>AppAPI: Check if user is not empty and active
		AppAPI-->>Nextcloud: Set active user
		AppAPI->>-Nextcloud: Request accepted/rejected
		Nextcloud->>-ExApp: Response (200/401)


AppAPIAuth
^^^^^^^^^^

AppAPI provides ``AppAPIAuth`` attribute with middleware to validate requests from ExApps.
In your API controllers you can use it as an PHP attribute.
