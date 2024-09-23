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
			} else {
				self.sectionId = $('#entryTypeRulesSectionId').data('value');
				self.$typeSelector = $('#entryType');
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
						// if the currently selected entry type change it to the first non-disabled
						// const entryTypeInput = document.querySelector('#entryType-input')
						// response.lockedEntryTypes.forEach( (lockedEntryType) => {
						// 	if(entryTypeInput.value == lockedEntryType) {
								
						// 	}
						// })

						// Disable the locked entry types in the select
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
							// response.lockedEntryTypes.forEach( function (entryType) {
							// 	// self.$typeSelector.find('option').filter('[value=' + entryType + ']').prop('disabled', true);
							// 	b = listBox.find('.menu-item');
							// 	console.log('button', b)
							// });
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
