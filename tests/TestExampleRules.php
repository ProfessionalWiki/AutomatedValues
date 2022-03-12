<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\AutomatedValues\Tests;

use Opis\JsonSchema\Validator;
use PHPUnit\Framework\TestCase;

class TestExampleRules extends TestCase {

	public function testExampleRulesMatchSchema2(): void {
		$exampleRules = json_decode( file_get_contents( __DIR__ . '/../rules.example.json' ) );
		$schema = json_decode( file_get_contents( __DIR__ . '/../src/rule.schema.json' ) );

		$validator = new Validator();

		$validationResult = $validator->validate( $exampleRules, $schema );

		$this->assertNull( $validationResult->error() );
		$this->assertTrue( $validationResult->isValid() );
	}

}
