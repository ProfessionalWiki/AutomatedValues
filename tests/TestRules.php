<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\AutomatedValues\Tests;

class TestRules {

	public static function invalidJsonProvider(): iterable {
		yield 'Rule is not an object' => [
			'{"rules": ["foo"] }'
		];

		yield 'Criteria need to be an array' => [
			'{"rules": [{"when": ""}] }'
		];

		yield 'Criterion statement key need to have valid property ID' => [
			'{"rules": [{"when": [{"statement": "hax", "equalTo": "foo"}]}] }'
		];

		yield 'Criterion equalTo needs to be string' => [
			'{"rules": [{"when": [{"statement": "P1", "equalTo": 42}]}] }'
		];

		yield 'Build specification keys need to be valid property IDs or ID.ID' => [
			'
{
	"rules": [
		{
			"buildLabel": {
				"en": {
					"P2.P3.P4": "$ ",
					"P2": " $",
					"P2.P4": " $"
				}
			}
		}
	]
}
			'
		];
	}

	public static function validJsonProvider(): iterable {
		yield 'Valid criterion' => [
			'{"rules": [{"when": [{"statement": "P1", "equalTo": "foo"}]}]}'
		];

		yield 'Valid example' => [
			file_get_contents( __DIR__ . '/../example.json' )
		];
	}

}
