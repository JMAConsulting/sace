<h3>{ts domain='purgelogs'}Guide{/ts}</h3>
<div class="crm-section">
  <div id="help" class="description">
    <h4>Basic information</h4><br/>
    <strong>Folder</strong> : Folder where the log files are located<br/>
    <strong>Filename</strong> : Log File Name (use regular expression)<br/>
    <strong>Frequency</strong> : Number of periods "older than" for files to be deleted.<br/>
    <strong>Period</strong> : Unit of period (Month / Week / Day).<br/>
    <strong>Active</strong> : Activate or deactivate the process<br/>
    <strong>Last Activity</strong> : Report the last activity executed by the process. This field should not be complete<br/>
    <br/>
    'Delete All' removes all the entries.<br/><br/>
    {$form.theme.label}: {$form.theme.html} <br />
  </div>


</div>

<div class="crm-section">
  <div class="crm-section">
    <div id="jsoneditor" style="width: 100%; min-height: 200px;"></div>
    <div class="clear"></div>
  </div>
  <div align="right">
    <font size="-2" color="gray">
    This brilliant <a href="https://github.com/json-editor/json-editor">JSON editor</a> is an enhancement to <a href="https://github.com/jdorn/json-editor">the original one</a> that was being developed by Jeremy Dorn.
    </font>
  </div>
</div>
<div>
  &nbsp;
</div>

{include file="CRM/common/formButtons.tpl" location="bottom"}

<script>

  {literal}

      CRM.$(document).ready(function(){
        // Initialize the editor with a JSON schema
        var editor = null;
        // create the editor
        var container = document.getElementById('jsoneditor');
        var configuration = CRM.$("input[name=configuration]").val();

        // Setup the options
        var options = {
          ajax: true,
          disable_collapse: false,
          disable_properties: true,
          disable_edit_json: true,
          prompt_before_delete: true,
          theme: '{/literal}{$theme}{literal}',
          iconlib: 'jqueryui',
          schema: {
            type: "array",
            title: "Processes",
            uniqueItems: true,
            items: {
              type: "object",
              options: {
                collapsed: true
              },
              title: "Entry",
              headerTemplate: "  - Remove \"{{self.Folder}} / {{self.Filename}}\" older than {{self.Frequency}} {{self.Period}}  ",
              required: [
                "Folder", "Filename", "Frequency"
              ],
              properties: {
                Folder: {
                  type: "string",
                  title: "Folder",
                  enum: [
                    "ConfigAndLog",
                    "custom",
                    "upload"
                  ],
                  default: "ConfigAndLog",
                  options: {
                    inputAttributes: {
                      class: "form-control input-folder"
                    }
                  }
                },
                Filename: {
                  type: "string",
                  title: "Filename (regex)",
                  minLength: 4,
                  options: {
                    inputAttributes: {
                      class: "form-control input-filename"
                    }
                  }
                },
                Frequency: {
                  type: "number",
                  title: "Frequency",
                  default: "3",
                  options: {
                    inputAttributes: {
                      class: "form-control input-frequency"
                    }
                  }
                },
                Period: {
                  type: "string",
                  title: "Period",
                  enum: [
                    "Months",
                    "Weeks",
                    "Days"
                  ],
                  default: "Months",
                  options: {
                    inputAttributes: {
                      class: "form-control input-period"
                    }
                  }
                },
                Active: {
                  type: "boolean",
                  format: "checkbox"
                },
                LastActivity: {
                  type: "string",
                  title: "Last activity",
                  readOnly: true,
                  options: {
                    inputAttributes: {
                      class: "form-control input-last_activity"
                    }
                  }
                }
              }
            }
          },
          // Seed the form with a starting value
          startval: JSON.parse(configuration)
        };

        initJsoneditor();

        function initJsoneditor() {
          // destroy old JSONEditor instance if exists
          if (editor) {
            editor.destroy()
          }
          editor = new JSONEditor(container, options, configuration);
          // Capture changes
          editor.on('change', function(){
            // Populate the configuration
            CRM.$("input[name=configuration]").val(JSON.stringify(editor.getValue()));
          });
        }

        CRM.$("#theme").on('change', function(){
          options.theme = this.value || 'jqueryui';
          initJsoneditor();
        });
      });
  </script>
{/literal}
