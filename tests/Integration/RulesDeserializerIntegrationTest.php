<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\AutomatedValues\Tests\Integration;

use PHPUnit\Framework\TestCase;
use ProfessionalWiki\AutomatedValues\DataAccess\RulesDeserializer;
use ProfessionalWiki\AutomatedValues\DataAccess\RulesJsonValidator;
use ProfessionalWiki\AutomatedValues\Domain\Rules;
use ProfessionalWiki\AutomatedValues\Tests\TestRules;

/**
 * @covers \ProfessionalWiki\AutomatedValues\DataAccess\RulesDeserializer
 */
class RulesDeserializerIntegrationTest extends TestCase {

	private const DEFAULT_LANGUAGE_CODES = [ 'nl', 'fr', 'de' ];

	private function newRulesDeserializer(): RulesDeserializer {
		return new RulesDeserializer(
			RulesJsonValidator::newInstance(),
			self::DEFAULT_LANGUAGE_CODES
		);
	}

	/**
	 * @dataProvider \ProfessionalWiki\AutomatedValues\Tests\TestRules::invalidJsonProvider
	 */
	public function testInvalidJsonResultsInEmptyRules( string $invalidRules ): void {
		$this->assertEquals(
			new Rules(),
			$this->newRulesDeserializer()->deserialize( $invalidRules )
		);
	}

	/**
	 * @dataProvider \ProfessionalWiki\AutomatedValues\Tests\TestRules::validJsonProvider
	 */
	public function testValidRulesDeserialize( string $validRules ): void {
		$this->assertInstanceOf(
			Rules::class,
			$this->newRulesDeserializer()->deserialize( $validRules )
		);
	}

}
