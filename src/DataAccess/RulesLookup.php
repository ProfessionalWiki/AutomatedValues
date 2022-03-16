<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\AutomatedValues\DataAccess;

use ProfessionalWiki\AutomatedValues\Domain\Rules;

class RulesLookup {

	private PageContentFetcher $contentFetcher;
	private RulesDeserializer $deserializer;
	private string $pageName;

	public function __construct( PageContentFetcher $contentFetcher, RulesDeserializer $deserializer, string $pageName ) {
		$this->contentFetcher = $contentFetcher;
		$this->deserializer = $deserializer;
		$this->pageName = $pageName;
	}

	public function getRules(): Rules {
		$content = $this->contentFetcher->getPageContent( 'MediaWiki:' . $this->pageName );

		if ( $content instanceof \JsonContent ) {
			return $this->rulesFromJsonContent( $content );
		}

		return new Rules();
	}

	private function rulesFromJsonContent( \JsonContent $content ): Rules {
		return $this->deserializer->deserialize( $content->getText() );
	}

}
