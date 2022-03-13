<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\AutomatedValues\Tests\Integration;

use PHPUnit\Framework\TestCase;
use ProfessionalWiki\AutomatedValues\Presentation\RuleValidator;
use ProfessionalWiki\AutomatedValues\Tests\TestRules;

/**
 * @covers \ProfessionalWiki\AutomatedValues\Presentation\RuleValidator
 */
class RuleValidatorTest extends TestCase {

	/**
	 * @dataProvider \ProfessionalWiki\AutomatedValues\Tests\TestRules::invalidJsonProvider
	 */
	public function testInvalidJson( string $json ): void {
		$this->assertFalse( RuleValidator::newInstance()->validate( $json ) );
	}

	public function invalidJsonProvider(): iterable {
		yield from TestRules::invalidJsonProvider();
	}

	/**
	 * @dataProvider \ProfessionalWiki\AutomatedValues\Tests\TestRules::validJsonProvider()
	 */
	public function testValidJson( string $json ): void {
		$this->assertTrue( RuleValidator::newInstance()->validate( $json ) );
	}

}
