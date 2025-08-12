<?php
use CRM_Mailchimpsync_ExtensionUtil as E;

class CRM_Mailchimpsync_Page_BatchWebhook extends CRM_Core_Page {

  public function run() {
    Civi::log()->info("Batch webhook received", ['=start' => 'mailchimpsyncBatchWebhook', 'data' => file_get_contents('php://input'), 'method' => $_SERVER['REQUEST_METHOD']]);
    if ($_POST) {
      if (CRM_Mailchimpsync::batchWebhookKeyIsValid($_GET['secret'])) {
        Civi::log()->debug("Valid secret, will attempt to process.");
        try {
          $this->processWebhook($_POST);
          Civi::log()->debug("No errors on processing");
        }
        catch (CRM_Mailchimpsync_BatchWebhookNotRelevantException $e) {
          // Softer more expected error. As far as Mailchimp is concerned this
          // is fine, we serve a 200 OK response.
          Civi::log()->notice($e->getMessage(), ['_POST' => $_POST]);
        }
        catch (Exception $e) {
          // All other errors.
          Civi::log()->error("Exception processing Mailchimp batch webhook: '{message}' in line {line} of {file}.",
            [
              'message' => $e->getMessage(),
              'line' => $e->getLine(),
              'file' => $e->getFile(),
              'exception' => $e,
              '_POST' => $_POST,
            ]);

          // Let Mailchimp know that didn't work.
          http_response_code(500);
        }
      }
      else {
        Civi::log()->notice("Invalid secret", ['_GET' => $_GET]);
        // Forbidden.
        http_response_code(401);
      }
    }
    else {
      // I think Mailchimp uses a GET request to validate the endpoint URL.
      Civi::log()->debug("Responding OK to a GET request");
      echo "OK";
    }
    Civi::log()->debug("", ['=pop' => 1]);
    CRM_Utils_System::civiExit();
  }

  /**
   * "Mailchimp will POST all completed batch requests to the webhook"
   * @see https://mailchimp.com/developer/marketing/guides/run-async-requests-batch-endpoint/
   *
   * If the admin user has sat clicking Refresh status, then we may have processed a batch by the time
   * Mailchimp gets around to telling us about it with a webhook.
   *
   * @throw CRM_Mailchimpsync_BatchWebhookNotRelevantException if it doesn't look relevant to us.
   * @throw InvalidArgumentException if something looks wrong.
   */
  public function processWebhook($data) {
    if (!is_array($data)) {
      throw new InvalidArgumentException("Data missing or not array");
    }
    if ((($data['type'] ?? '') === "batch_operation_completed")) {
      if (preg_match('/^[0-9a-zA-Z]+$/', $data['data']['id'] ?? '')) {
        // OK, looks possibly legit.
        $batch = new CRM_Mailchimpsync_BAO_MailchimpsyncBatch();
        $batch->mailchimp_batch_id = $data['data']['id'];
        if (!$batch->find(1)) {
          throw new CRM_Mailchimpsync_BatchWebhookNotRelevantException("Batch ID not one we are tracking");
        }
        try {
          $batch->processCompletedBatch();
        }
        catch (InvalidArgumentException $e) {
          if (preg_match('/^Batch .* (currently being|already) processed/', $e->getMessage(), $matches)) {
            // Downgrade to a not-relevant; it's fine. This can happen if the user keeps clicking
            // Refresh Status, then later Mailchimp sends a batch webhook.
            throw new CRM_Mailchimpsync_BatchWebhookNotRelevantException(
              "Ignoring webhook suggesting we process a batch that is $matches[1] processed.");
          }
          // Any others re-throw.
          throw $e;
        }
      }
      else {
        throw new InvalidArgumentException("Batch ID not in expected format.");
      }
    }
    else {
      throw new CRM_Mailchimpsync_BatchWebhookNotRelevantException("Ignoring webhook as it is not batch_operation_completed");
    }
  }

}
