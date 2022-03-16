<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\AutomatedValues\DataAccess;

use MediaWiki\Revision\RevisionLookup;

class PageContentFetcher {

	private \TitleParser $titleParser;
	private RevisionLookup $revisionLookup;

	public function __construct( \TitleParser $titleParser, RevisionLookup $revisionLookup ) {
		$this->titleParser = $titleParser;
		$this->revisionLookup = $revisionLookup;
	}

	public function getPageContent( string $pageTitle ): ?\Content {
		try {
			$title = $this->titleParser->parseTitle( $pageTitle );
		}
		catch ( \MalformedTitleException $e ) {
			return null;
		}

		$revision = $this->revisionLookup->getRevisionByTitle( $title );

		if ( $revision === null ) {
			return null;
		}

		// $revision->getRevisionRecord()->getContent( 'main' );
		return $revision->getContent( 'main' );
	}

}
