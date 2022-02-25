/**
 * Entry Type Rules plugin for Craft CMS
 *
 * Entry Type Rules Settings JS
 *
 * @author    Foster Commerce
 * @copyright Copyright (c) 2022 Foster Commerce
 * @link      https://fostercommerce.com
 * @package   EntryTypeRules
 * @since     1.0.0
 */

(function($) {

	Craft.EntryTypeRulesSettings = Garnish.Base.extend({

		// Default values
		$limitFields: null,

		// Initialization method
		init: function() {
			const self = this;

			// Get all the entry limit fields
			self.$limitFields = $('.entryTypeRulesLimit input[name$="_limit"]');

			// Run through all the limit fields to check if any warnings need to be displayed
			self.$limitFields.each(function() {
				self.highlightLimit(this);
			});

			// Add the highlight method to the inputs as an event handler for each
			self.$limitFields.on('input', function() {
				self.highlightLimit(this);
			});
		},

		// Method that highlights and displays a warning for a limit field based on its value and
		// the current entry count for that entry type
		highlightLimit: function(field) {
			const $input = $(field);
			const inputValue = $input[0].value;
			const $parent = $input.closest('.entryTypeRulesLimit');
			const $count = $parent.find('.entryTypeRulesLimit__count');
			const $warning = $parent.find('.entryTypeRulesLimit__warning');
			const $label = $parent.find('.entryTypeRulesLimit__input .label');
			const currentCount = $parent.data('count');
			if ((inputValue !== '' && inputValue !== '0') && currentCount > parseInt(inputValue)) {
				$label[0].classList.add('warning');
				$input[0].classList.add('warning');
				$count[0].classList.add('warning');
				$warning[0].classList.remove('hidden');
			} else {
				$label[0].classList.remove('warning');
				$input[0].classList.remove('warning');
				$count[0].classList.remove('warning');
				$warning[0].classList.add('hidden');
			}
		}

	});
})(jQuery);
