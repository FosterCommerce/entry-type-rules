/**
 * Entry Type Rules plugin for Craft CMS
 *
 * Entry Type Rules JS
 *
 * @author    Foster Commerce
 * @copyright Copyright (c) 2022 Foster Commerce
 * @link      https://fostercommerce.com
 * @package   EntryTypeRules
 * @since     1.0.0
 */

(function($) {

	Craft.EntryTypeRules = Garnish.Base.extend({

		// Default values
		namespace: null,
		sectionId: null,
		$typeSelector: null,

		// Initialization method
		init: function(namespace) {
			const self = this;
			namespace = namespace || null;

			// Get the data we need to run the lockEntryTypes method
			self.namespace = namespace;

			// If we have a namespace it means we are in a HUD or Craft Slide-out, if not we are in a regular entry edit page
			if (self.namespace) {
				self.sectionId = $('#' + self.namespace + '-entryTypeRulesSectionId').data('value');
				self.$typeSelector = $('#' + self.namespace + '-entryType');
				self.$typeButton = $('#' + self.namespace + '-entryType-button');
				self.$typeInput = $('#' + self.namespace + '-entryType-input');
			} else {
				self.sectionId = $('#entryTypeRulesSectionId').data('value');
				self.$typeSelector = $('#entryType');
				self.$typeButton = $('#' + 'entryType-button');
				self.$typeInput = $('#' + 'entryType-input');
			}

			// Lock the entry types
			self.lockEntryTypes();
		},

		// Method that calls the plugin controller, and locks down the entry types selector
		lockEntryTypes: function() {
			const self = this;

			if (self.sectionId && self.$typeSelector.length) {
			
				$.ajax({
					type: "GET",
					url: "/actions/entry-type-rules/default?sectionId=" + self.sectionId,
					async: true,
					dataType: "json"
				}).done(function (response) {
					if (response.lockedEntryTypes) {
						//if the currently selected entry type change it to the first non-disabled
						response.lockedEntryTypes.forEach( (lockedEntryType) => {
							if(self.$typeInput.val() == lockedEntryType) {
								self.$typeButton.find('.label:first').addClass('disabled');
								// set the button and the hidden input to the first non-disabled entry type
								// but we don't know what they are until the button is clicked...hmmm
							}
						})

						// Disable the locked entry types in the 'select'
						// doing this with vanilla JS because I can't get the jQuery selector to work even if I escape the . in the menu's ID.
						self.$typeSelector.on('click', function(ev){
							const listBoxId = ev.currentTarget.querySelector('button').getAttribute('aria-controls')
							const listBox = document.getElementById(listBoxId)
							const buttons = listBox.querySelectorAll('.menu-item');
							buttons.forEach( (button) => {
								response.lockedEntryTypes.forEach( (lockedEntryType) => {
									if(lockedEntryType == button.dataset.value){
										button.disabled = true
										button.classList.remove('sel')
										button.classList.add('disabled')
									}
								})
							})
						})
						

						// Find out if we are on the new entry (URL param "fresh" is present)
						const urlParams = new URLSearchParams(window.location.search);

						// If this is a new entry, we need to set the entry type selector to the first available option
						if (!self.namespace && urlParams.has('fresh')) {
							const $available = self.$typeSelector.children('option:enabled').first();
							const $selected = self.$typeSelector.children('option:selected').first();

							if ($selected.prop('disabled') ) {
								$available.prop('selected', true);
								self.$typeSelector.trigger('change');
							}
						}

					}

				});

			}
		}

	});
})(jQuery);
