# Configuration JSON

Access this with `CRM_Mailchimpsync::getConfig()`. It is an array
structure as follows:

```json
[
  "lists": [
    <mailchimp_list_id>: {
      "apiKey": <mailchimp_or_mock_api_key>,
      "subscriptionGroup": <civicrm_group_id>,
    },
  ],
  "accounts": {
    "<account_api_key>": {
      "audiences": {
        <mailchimp_list_id>: {
          ?
        }

      },
      "batchWebhookSecret": <string>
      }
  }
	"api_keys": {
		<mailchimp_or_mock_api_key>: { "accountName": <string> }
	}
]

```

