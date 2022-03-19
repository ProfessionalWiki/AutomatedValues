<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\AutomatedValues\DataAccess;

use Opis\JsonSchema\Validator;

class RulesJsonValidator {

	public static function newInstance(): self {
		$json = file_get_contents( __DIR__ . '/../../schema.json' );

		if ( !is_string( $json ) ) {
			throw new \RuntimeException( 'Could not obtain JSON Schema' );
		}

		$schema = json_decode( $json );

		if ( !is_object( $schema ) ) {
			throw new \RuntimeException( 'Failed to deserialize JSON Schema' );
		}

		return new self( $schema );
	}

	private object $jsonSchema;

	private function __construct( object $jsonSchema ) {
		$this->jsonSchema = $jsonSchema;
	}

	public function validate( string $rule ): bool {
		$validator = new Validator();

		$validationResult = $validator->validate( json_decode( $rule ), $this->jsonSchema );

		return $validationResult->error() === null;
	}

}
