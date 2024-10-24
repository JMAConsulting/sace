CDNTaxReceipts
==============

Canadian Tax Receipts extension for CiviCRM

To set up the extension:
------------

1. Make sure your CiviCRM Extensions directory is set (Administer > System Settings > Directories).
2. Make sure your CiviCRM Extensions Resource URL is set (Administer > System Settings > Resource URLs).
3. Unpack the code
    - cd extensions directory
    - git clone https://lab.civicrm.org/extensions/cdntaxreceipts.git org.civicrm.cdntaxreceipts
4. Enable the extension at Administer > System Settings > Manage Extensions
5. Configure CDN Tax Receipts at Administer > CiviContribute > CDN Tax Receipts. (Take note of the dimensions for each of the image parameters. Correct sizing is important. You might need to try a few times to get it right.)
6. Review permissions: The extension has added a new permission called "CiviCRM CDN Tax Receipts: Issue Tax Receipts". "Edit all Contacts" is also required.

Note: if you are installing this on Drupal 8 or Drupal 9 -> remember clear the Drupal cache or you may not be able to get to the CiviCRM CDNTaxReceipts settings screen.

Now you should be able to use the module.

**Note: Compatibility issue with open_basedir**

This extension uses the TCPDF library from CiviCRM. If your server has open_basedir set initializing the library
causes a warning. To avoid this please add the following to your civicrm.settings.php anywhere after $civicrm_root
is defined:

    /**
     * Early define for tcpdf constants to avoid warnings with open_basedir.
     */
    if (!defined('K_PATH_MAIN')) {
      define('K_PATH_MAIN', $civicrm_root . '/packages/tcpdf/');
    }

    if (!defined('K_PATH_IMAGES')) {
      define('K_PATH_IMAGES', K_PATH_MAIN . 'images');
    }


Operations
------------
**Individual or Single Tax Receipts**

These are receipts issued as one receipt to one contribution.
- To issue an individual receipt, pull up the contact record, go to 'contributions' tab, view the contribution, and click the "Tax Receipt" button. Follow on-screen instructions from there.
Single receipts can be issued in bulk for multiple contributions. This process issues one receipt per contribution.
- To issue bulk-issue receipts, go to Contributions > Find Contributions, run a search, select one or more search results, and select "Issue Tax Receipts" in the actions drop-down. Follow on-screen instructions from there.

**Annual Tax Receipts**

These are receipts that collect all outstanding contributions for the year into one receipt. If some contributions have already been sent a receipt they will not be included in the total.
Since there are multiple contributions on one receipt there are some differences in the template. In-kind fields are not shown, contribution type and source are also not shown since the collected contributions over the year could be of multiple types and from multiple sources.

- To issue Annual Tax Receipts, go to Search > Find Contacts (or Search > Advanced Search), run a search for contacts, select one or more contacts, and select "Issue Annual Tax Receipts" in the actions drop-down. Follow on-screen instructions from there.

**Reports**

The extension also enables two CiviReport templates, found under `Reports - Contribution Reports - New Contribution Report`, which can be used to see a list of receipts issued and receipts outstanding.

- Tax Receipts - Receipts Issued
- Tax Receipts - Receipts Not Issued

**Testing Tax Receipts**

- To test your template settings and view a receipt without e-mailing the contact or making a database record, follow the directions for bulk-issueing of receipts: go to Contributions > Find Contributions, run a search, select a search result, and select "Issue Tax Receipts" in the actions drop-down. On the next screen, make sure to select 'Run in preview mode?', and follow on-screen instructions for other options, a pdf will be generated.

**Tracking Email openings**

- Earlier versions of this Extension required a permission -> "CiviCRM CDN Tax Receipts: Open Tracking". That's no longer required - but make sure that $openTracking parameter is in the message template!

hook_cdntaxreceipts_eligible()
------------

You may be in a situation where certain Contributions are eligible for tax receipts and others are not (e.g. donations are receiptable, but only for individuals, and event fees are not receiptable). If this is the case, there is a PHP hook hook_cdntaxreceipts_eligible($contribution) that can be used for complex eligibility criteria. Hook implementations should return one of TRUE or FALSE, wrapped in an array.

```php
    // Example hook implementation:
    //  Contributions have a custom yes/no field called "receiptable". Issue tax receipt
    //  on any contribution where receiptable = Yes.
    function mymodule_cdntaxreceipts_eligible( $contribution ) {

      // load custom field
      $query = "
      SELECT receiptable_119
      FROM civicrm_value_tax_receipt_23
      WHERE entity_id = %1";

      $params = array(1 => array($contribution->id, 'Integer'));
      $field = CRM_Core_DAO::singleValueQuery($query, $params);

      if ( $field == 1 ) {
        return array(TRUE);
      }
      else {
        return array(FALSE);
      }

    }
```

By default, a contribution is eligible for tax receipting if it is completed, and if its Financial Type is deductible.

hook_cdntaxreceipts_eligibleAmount()
------------

If you need to customize the amount that is tax-deductible on a receipt, use this hook.

```php
    // Example hook implementation:
    //  Return a maximum tax deduction of $1000.00
    function mymodule_cdntaxreceipts_eligibleAmount( $contribution ) {
      if ($contribution->total_amount - $contribution->non_deductible_amount > 1000) {
        return array(1000.00);
      }
      else {
        return [$contribution->total_amount - $contribution->non_deductible_amount];
      }
    }
```

hook_cdntaxreceipts_alter_receipt()
-----------

If you need to customize the variables that are passed to the receipt e.g. display name

```php
// example combining the name of a spouse in the receipt
function mymodule_cdntaxreceipts_alter_receipt(&$receipt) {
  if (!empty($_POST['is_spouse'])) {
    $relationships = civicrm_api3('Relationship', 'get', [
      'sequential' => 1,
      'contact_id_a' => $receipt['contact_id'],
      'relationship_type_id' => "Spouse of",
      'is_active' => 1,
      'options' => ['limit' => 1],
    ])['values'];

    if (empty($relationships)) {
      $relationships = civicrm_api3('Relationship', 'get', [
        'sequential' => 1,
        'contact_id_b' => $receipt['contact_id'],
        'relationship_type_id' => "Spouse of",
        'is_active' => 1,
        'options' => ['limit' => 1],
      ])['values'];
      $relContact = $relationships[0]['contact_id_a'] ?? NULL;
    }
    else {
      $relContact = $relationships[0]['contact_id_b'];
    }

    $spouseRecords[] = CRM_Contact_BAO_Contact::displayName($receipt['contact_id']);
    if (!empty($relContact)) {
      $spouseRecords[] = CRM_Contact_BAO_Contact::displayName($relContact);
      $receipt['display_name'] = E::ts('%1 and %2', [1 => $spouseRecords[0], 2 => $spouseRecords[1]]);
    }
  }
```

Disclaimer
------------

This extension has been developed in consultation with a number of non-profits and with the help of a senior accountant. The maintainers have made every reasonable effort to ensure compliance with CRA guidelines and best practices. However, it is the reponsibility of each organization using this extension to do their own due diligence in ensuring compliance with CRA guidelines and with their organizational policies.
