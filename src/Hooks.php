<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\AutomatedValues;

use Content;
use IContextSource;
use Status;
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

}
