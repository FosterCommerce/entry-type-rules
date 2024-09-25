Craft.EntryTypeRules = Garnish.Base.extend({
	namespace: null,
	sectionId: null,
	selectedType: null,
	typeButton: null,
	createButton: null,
	elementId: false,

	init: function(namespace) {
		const self = this;

		// Get the data we need to run the lockEntryTypes method
		self.namespace = namespace || null;

		// If we have a namespace it means we are in a HUD or Craft Slide-out, if not we are in a regular entry edit page
		if (self.namespace) {
			let sectionIdElement = document.getElementById(self.namespace + '-entryTypeRulesSectionId');
			self.sectionId = sectionIdElement?.value;
			self.selectedType = document.getElementById(self.namespace + '-entryType-input');
			self.typeButton = jQuery('#' + self.namespace + '-entryType-button').data('menubtn');
			self.elementId = document.getElementsByName(self.namespace + '[elementId]')[0]?.value;
			self.createButton = sectionIdElement?.closest('form').querySelector('button[type="submit"]');
		} else {
			let sectionIdElement = document.getElementById('entryTypeRulesSectionId');
			self.sectionId = sectionIdElement?.value;
			self.selectedType = document.getElementById('entryType-input');
			self.typeButton = jQuery('#entryType-button').data('menubtn');
			self.createButton = sectionIdElement?.closest('form').querySelector('button[type="submit"]')?.parentElement;
			self.elementId = document.getElementsByName('elementId')[0]?.value;
		}

		// sectionId will be null if we are not editing an entry
		if (self.sectionId === null || self.selectedType === null) {
			return;
		}

		// Lock the entry types
		self.lockEntryTypes();
	},

	// Method that calls the plugin controller, and locks down the entry types selector
	lockEntryTypes: function() {
		const self = this;

		let sectionId = parseInt(self.sectionId, 10);
		let elementId = parseInt(self.elementId, 10);
		fetch(`/actions/entry-type-rules/default?entryId=${elementId}&sectionId=${sectionId}`)
			.then(response => response.json())
			.then(response => {
				if (!response.lockedEntryTypes) {
					return;
				}

				const lockedTypes = response.lockedEntryTypes;
				const options = self.typeButton.menu.$options.toArray();

				let firstEnabledOption = null;
				// disable any locked items in the menu
				options.forEach((option) => {
					const value = parseInt(option.dataset.value, 10);
					if (lockedTypes.includes(value)) {
						option.disabled = true;
						option.classList.add('disabled');
					} else if (firstEnabledOption === null) {
						// We want to track which is the first unlocked item in case the current selection is a locked item.
						firstEnabledOption = option;
					}
				});

				// Find out if we are on the new entry (URL param "fresh" is present)
				const urlParams = new URLSearchParams(window.location.search);

				// We only want to prevent folks from changing the type if the entry is new
				if (! response.entryExists) {
					// If there is an available option
					if (firstEnabledOption !== null) {
						// And if the currently selected type is locked
						const initialSelectedType = parseInt(self.selectedType.value, 10);
						if (lockedTypes.includes(initialSelectedType)) {
							// Select the first option
							self.typeButton.onOptionSelect(firstEnabledOption);
						}
					} else {
						self.typeButton.disable();
						self.createButton.disabled = true;
						self.createButton.classList.add('disabled');
					}
				}
			});
	}
});
