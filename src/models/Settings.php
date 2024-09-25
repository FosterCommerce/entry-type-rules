<?php

namespace fostercommerce\entrytyperules\models;

use craft\base\Model;

use craft\validators\ArrayValidator;

/**
 * @phpstan-type LimitRule int
 * @phpstan-type UserGroupRule array<int, string>
 * @phpstan-type EntryType array<string, LimitRule|UserGroupRule>
 * @phpstan-type EntryTypes array<string, EntryType>
 * @phpstan-type Sections array<string, EntryTypes>
 */
class Settings extends Model
{
	/**
	 * @var Sections The section and entry type map of rules to limit entry types
	 *
	 * [
		'pages' => [
			'blog' => [
				'limit' => 1,
				'userGroups' => [
					'siteAdmins'
				]
			]
		]
	]
	 */
	public array $sections = [];

	/**
	 * @return array<int, mixed>
	 */
	public function rules(): array
	{
		return [
			[['sections'], ArrayValidator::class],
		];
	}
}
