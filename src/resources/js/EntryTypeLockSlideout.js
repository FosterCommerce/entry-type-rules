(function($) {

  Craft.EntryTypeLockSlideout = Garnish.Base.extend({

    // Default values
    namespace: null,
    sectionId: null,
    sectionType: null,

    init: function(viewNamespace) {
      var self = this;

      // Get the data we need to run the lockEntryTypes method
      self.namespace = viewNamespace;
      self.sectionId = $('#' + viewNamespace + '-entryTypeLockSectionId').data('value');
      self.sectionType = $('#' + viewNamespace + '-entryTypeLockSectionType').data('value');

      // Lock the entry types
      self.lockEntryTypes();
    },

    lockEntryTypes: function() {
      var self = this;

      if (self.sectionId && self.sectionType !== 'single') {

        $.ajax({
          type: "GET",
          url: "/actions/entry-type-lock/default?sectionId=" + self.sectionId,
          async: true,
          dataType: "json"
        }).done(function (response) {

          // Find the entry type selector dropdown
          var $typeSelector = $('#' + self.namespace + '-entryType');

          // If we found one, reset the disabled props on the options and then loop though the data we get back
          // for the locked down entry types and disable them.
          if ($typeSelector.length) {
            var $typeOptions = $typeSelector.find('option');
            $typeOptions.attr('disabled', false)
            response.lockedEntryTypes.forEach( function (entryType) {
              $typeOptions.filter('[value=' + entryType + ']').attr('disabled', true);
            });
          }

        });

      }
    }

  });
})(jQuery);
