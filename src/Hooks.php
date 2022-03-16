<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\AutomatedValues;

use Content;
use IContextSource;
use Status;
use Title;
use User;
use Wikibase\DataModel\Statement\StatementListProvider;
use Wikibase\DataModel\Term\FingerprintProvider;
use Wikibase\Repo\Content\EntityContent;

class Hooks {

	public static function onEditFilterMergedContent( IContextSource $context, Content $content, Status $status, string $summary, User $user, bool $minorEdit ): void {
		if ( $content instanceof EntityContent ) {
			$entity = $content->getEntity();

			if ( $entity instanceof FingerprintProvider && $entity instanceof StatementListProvider ) {
				$entity->getFingerprint()->setLabel( 'en', (string)count( $entity->getStatements() ) );
			}
		}
	}

	public static function onContentHandlerDefaultModelFor( Title $title, ?string &$model ): void {
		if ( $title->getNamespace() === NS_MEDIAWIKI && $title->getText() === 'AutomatedValues' ) {
			$model = 'json'; // CONTENT_MODEL_JSON (string to make Psalm happy)
		}
	}

}
