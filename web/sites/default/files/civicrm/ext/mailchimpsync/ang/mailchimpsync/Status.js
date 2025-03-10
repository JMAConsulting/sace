(function(angular, $, _) {

  angular.module('mailchimpsync').config(function($routeProvider) {
      $routeProvider.when('/mailchimpsync', {
        controller: 'MailchimpsyncStatus',
        templateUrl: '~/mailchimpsync/Status.html',

        // If you need to look up data when opening the page, list it out
        // under "resolve".
        resolve: {
          mcsConfig: function(crmApi) {
            return crmApi('Setting', 'getvalue', { name: 'mailchimpsync_config' }).then(r => {
              // Basic validation
              var result = JSON.parse(r.result);
              if (!((typeof(result) === 'object') && (!Array.isArray(result)))) {
                // Config is invalid.
                console.warn("Invalid config, resetting. Received: ", r);
                alert("Invalid configuration. Please visit Administer » System Settings » Configure Mailchimp Sync");
                return;
              }
              console.info("loaded config:", result);
              return result;
            });
          },
          mailingGroups: function(crmApi4) {
            return crmApi4('Group', 'get',  {
              select: ['id', 'title'],
              where: [
                ["group_type:name", "CONTAINS", "Mailing List"],
                ["is_hidden", "=", false]
              ],
              orderBy: {"title":"ASC"}
            }, 'id');
          },
          mcsStatus: function(crmApi) {
            return crmApi('Mailchimpsync', 'getstatus',  {}).then(r => r.values || {});
          }
        }
      });
    }
  );

  angular.module('mailchimpsync').controller('MailchimpsyncStatus', function($scope, crmApi, crmStatus, crmUiHelp, mcsConfig, mailingGroups, mcsStatus) {
    // The ts() and hs() functions help load strings for this module.
    var ts = $scope.ts = CRM.ts('mailchimpsync');
    var hs = $scope.hs = crmUiHelp({file: 'CRM/mailchimpsync/Status'}); // See: templates/CRM/mailchimpsync/Config.hlp

    // We have myContact available in JS. We also want to reference it in HTML.
    $scope.mcsConfig = mcsConfig;
    $scope.mcsStatus = mcsStatus;
    $scope.crmUrl = CRM.url;
    $scope.view = 'overview';
    $scope.selectedList = null;
    $scope.selectedListId = null;
    $scope.editData = null;
    $scope.mailingGroups = mailingGroups;
    $scope.cacheRows = null;
    $scope.cacheRowCount = null;
    $scope.isLoading = true;
    $scope.cacheBrowserIsLoading = false;
    $scope.cacheParams = {
      sync_status: '',
      mailchimp_status: '',
      mailchimp_list_id: '',
      civicrm_status: '',
      mailchimp_email: '',
      civicrm_contact_id: '',
    };
    $scope.cacheOptions = { limit: 10, offset: 0 };

    // Now page is loaded, do the slower fetch that gets more info.
    var interval;
    $scope.getDetailedUpdate = function() {
      $scope.isLoading = true;
      clearInterval(interval);
      interval = setInterval( $scope.getDetailedUpdate, 60000);
      return crmApi('Mailchimpsync', 'getstatus', {batches: 1})
      .then(r => {
        mcsStatus = r.values || {};
        $scope.mcsStatus= mcsStatus;
        $scope.isLoading = false;
      });
    };
    $scope.getDetailedUpdate();

    $scope.setCacheTodo = function(cacheRow) {
      return crmApi('MailchimpsyncCache', 'create', { id: cacheRow.id, sync_status: 'todo' })
      .then(r => {
        if (r.is_error == 0) {
          cacheRow.sync_status = 'todo';
        }
        else {
          alert("Error: " . JSON.stringify(r));
        }
      });
    };
    $scope.showOverview = function showOverview() {
      $scope.view = 'overview';
      $scope.selectedListId = null;
      $scope.selectedList = null;
    }
    $scope.showDetails = function showDetails(listId) {
      $scope.view = 'detail';
      $scope.selectedListId = listId;
      $scope.selectedList = mcsConfig.lists[listId];
    }
    $scope.cacheView = function cacheView(params) {
      $scope.cacheRows = null;
      $scope.view = 'cache';
      $scope.cacheParams = Object.assign({
        sync_status: '',
        mailchimp_status: '',
        civicrm_status: '',
        mailchimp_email: '',
        mailchimp_list_id: '',
        civicrm_contact_id: '',
      }, params);
      $scope.cacheRowCount = null;
      $scope.cacheOptions = { limit: 10, offset: 0 };
      $scope.loadCacheData();
    }
    $scope.handleAbortSync = function handleAbortSync() {
      const group_id = mcsConfig.lists[this.selectedListId].subscriptionGroup;
      if (!confirm("This will cancel all outstanding updates and try to cancel batches being processed for this Audience. You may also want to disable the scheduled job. Sure?")){
        return;
      }
      return crmStatus('Aborting Sync...', 'Done',
        crmApi('Mailchimpsync', 'abortsync', {group_id: group_id})
        .then(r => {
          return getDetailedUpdate.bind(this)()
        })
      );
    };
    $scope.loadCacheData = function loadCacheData(skipRecount) {
      const p = {};

      for (const key in $scope.cacheParams) {
        if ($scope.cacheParams[key]) {
          p[key] = $scope.cacheParams[key];
        }
      }
      if (p.mailchimp_email) {
        // This is a like query.
        p.mailchimp_email = {LIKE: '%' + p.mailchimp_email + '%'};
      }
      if (! p['mailchimp_list_id']) {
        alert("Select Mailchimp Audience");
        return;
      }

      $scope.cacheBrowserIsLoading = true;
      if (!skipRecount) {
        crmApi('MailchimpsyncCache', 'getcount', p)
        .then(r => {
          $scope.cacheRowCount = r.result;
          Object.assign(p, {options: $scope.cacheOptions, troubleshoot: 1});
          return (r.result > 0) ? crmApi('MailchimpsyncCache', 'get', p) : {};
        })
        .then(r => {
          $scope.cacheRows = r.values || [];
          $scope.cacheBrowserIsLoading = false;
        });
      }
      else {
        Object.assign(p, {options: $scope.cacheOptions, troubleshoot: 1});
        crmApi('MailchimpsyncCache', 'get', p)
        .then(r => {
          $scope.cacheRows = r.values || [];
          $scope.cacheBrowserIsLoading = false;
        });
      }
    };
    $scope.mapMailchimpStatusToColour = function mapMailchimpStatusToColour(status) {
      const map = {
        subscribed: 'good',
        pending: 'good',
        unsubscribed: 'meh',
        transactional: 'meh',
        cleaned: 'bad',
        archived: 'meh',
      };
      return (status in map) ? map[status] : 'grey';
    };
    $scope.mapSyncStatusToColour = function mapMailchimpStatusToColour(status) {
      const map = {
        ok: 'good',
        live: 'meh',
        fail: 'bad',
      };
      return (status in map) ? map[status] : 'grey';
    };
    $scope.mapCiviCRMStatusToColour = function mapMailchimpStatusToColour(status) {
      const map = {
        Added: 'good',
        Removed: 'meh',
      };
      return (status in map) ? map[status] : 'grey';
    };
  });

})(angular, CRM.$, CRM._);

