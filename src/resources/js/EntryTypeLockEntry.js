(function($){
	Craft.EntryTypeLockEntry = Garnish.Base.extend({
		// DOM elements
		$sectionId: null,
		$sectionType: null,

		// Initialization method
		init: function() {
			var self = this;

			self.$sectionId = $('[data-etl-entry-section-id]').data('value');
			self.$sectionType = $('[data-etl-entry-section-type]').data('value');

			self.lockEntryTypes(self.$sectionId, self.$sectionType);
		},

		urlParam: function (name) {
			var self = this;

			var results = new RegExp('[\?&]' + name + '=([^&#]*)').exec(window.location.href);
			if (results == null) {
				return 0;
			}
			return results[1] || 0;
		},

		lockEntryTypes: function(sectionId, sectionType) {
			var self = this;

			if (sectionId && sectionType !== 'single') {
				$.ajax({
					type: "GET",
					url: "/actions/entry-type-lock/default?sectionId=" + sectionId,
					async: true,
					dataType: "json"
				}).done(function (response) {

					console.log(response);

					$('#entryType option').attr('disabled', false);

					if (response.lockedEntryTypes) {

						// Disable the entry types in the select
						response.lockedEntryTypes.forEach( function (entryType) {
							$('#entryType option[value=' + entryType + ']').attr('disabled', true);
						});

						// Special logic for new entries only
						if(self.urlParam('fresh') == 1) {
							var firstEnabledOption = $('#entryType').children('option:enabled').eq(0);
							var selectedOption = $('#entryType').children('option:selected').eq(0);

							// If the current selected option isn't enabled, then force
							// switch to the first enabled entry type
							if(selectedOption.prop('disabled') ) {
								firstEnabledOption.prop('selected',true);
								$('#entryType').trigger('change');
							}
						}
					}

				});
			}
		}
	});
})(jQuery);