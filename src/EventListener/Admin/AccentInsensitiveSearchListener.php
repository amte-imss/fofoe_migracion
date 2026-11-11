<?php

namespace App\EventListener\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Event\AfterEntitySearchEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

/**
 * Makes every EasyAdmin text search accent-insensitive on PostgreSQL by wrapping
 * the generated "LOWER(CONCAT(alias.property, '')) LIKE :param" conditions in UNACCENT().
 */
#[AsEventListener(event: AfterEntitySearchEvent::class)]
final class AccentInsensitiveSearchListener
{
    private const string TEXT_CONDITION_PATTERN = '/LOWER\(CONCAT\(([\w.]+), \'\'\)\) LIKE (:\w+)/';

    private const string ACCENT_INSENSITIVE_CONDITION = 'UNACCENT(LOWER(CONCAT($1, \'\'))) LIKE UNACCENT($2)';

    public function __invoke(AfterEntitySearchEvent $event): void
    {
        $queryBuilder = $event->getQueryBuilder();
        $where = $queryBuilder->getDQLPart('where');

        if (null === $where) {
            return;
        }

        $rewrittenWhere = preg_replace(self::TEXT_CONDITION_PATTERN, self::ACCENT_INSENSITIVE_CONDITION, (string) $where);

        if (null !== $rewrittenWhere) {
            $queryBuilder->where($rewrittenWhere);
        }
    }
}
