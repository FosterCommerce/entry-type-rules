(function($) {

  Craft.EntryTypeLock = Garnish.Base.extend({

    // Default values
    namespace: null,
    sectionId: null,
    $typeSelector: null,

    // Initialization method
    init: function(namespace) {
      var self = this;
      namespace = namespace || null;

      // Get the data we need to run the lockEntryTypes method
      self.namespace = namespace;

      // If we have a namespace it means we are in a HUD or Craft Slideout, if not we are in a regular entry edit page
      if (self.namespace) {
        self.sectionId = $('#' + self.namespace + '-entryTypeLockSectionId').data('value');
        self.$typeSelector = $('#' + self.namespace + '-entryType');
      } else {
        self.sectionId = $('#entryTypeLockSectionId').data('value');
        self.$typeSelector = $('#entryType');
      }

      // Lock the entry types
      self.lockEntryTypes();
    },

    // Method for checking URL params
    urlParam: function (name) {
      var results = new RegExp('[\?&]' + name + '=([^&#]*)').exec(window.location.href);
      if (results == null) {
        return 0;
      }
      return results[1] || 0;
    },

    // Method that calls the plugin controller, and locks down the entry types selector
    lockEntryTypes: function() {
      var self = this;

      if (self.sectionId && self.$typeSelector.length) {

        $.ajax({
          type: "GET",
          url: "/actions/entry-type-lock/default?sectionId=" + self.sectionId,
          async: true,
          dataType: "json"
        }).done(function (response) {

          if (response.lockedEntryTypes) {

            // Disable the entry types in the select
            response.lockedEntryTypes.forEach( function (entryType) {
              self.$typeSelector.find('option').filter('[value=' + entryType + ']').prop('disabled', true);
            });

            // If we are in a regular entry page, we need to add some logic to reset the selected type to the first available one
            if (!self.namespace && self.urlParam('fresh') == 1) {
              var firstEnabledOption = self.$typeSelector.children('option:enabled').eq(0);
              var selectedOption = self.$typeSelector.children('option:selected').eq(0);

              if (selectedOption.prop('disabled') ) {
                firstEnabledOption.prop('selected', true);
                self.$typeSelector.trigger('change');
              }
            }

          }

        });

      }
    }

  });
})(jQuery);
