<?php
namespace WSR\Myttaddressmap\Domain\Repository;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Extbase\Persistence\Generic\Query;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;

use TYPO3\CMS\Core\Database\Connection;


/***
 *
 * This file is part of the "Myttaddressmap" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 *  (c) 2018 - 2020 Joachim Ruhs <postmaster@joachim-ruhs.de>, Web Services Ruhs
 *
 ***/

/**
 * The repository for Addresses
 */
class AddressRepository extends \TYPO3\CMS\Extbase\Persistence\Repository
{

	/**
	 * search for records which need to be updated lat lon when coordinates are 0.0 and
	 * mapgeocode = 1
	 * @param string $storagePid
	 * 
	 * @return array
	 */
	public function updateLatLon($storagePid) {

		$queryBuilder = GeneralUtility::makeInstance(\TYPO3\CMS\Core\Database\ConnectionPool::class)
			->getQueryBuilderForTable('tx_myttaddressmap_domain_model_address');

		$queryBuilder->select('*')->from('tt_address', 'a');

		$arrayOfPids = \TYPO3\CMS\Core\Utility\GeneralUtility::intExplode(',', $storagePid, TRUE);

		$queryBuilder->where(
			$queryBuilder->expr()->in(
				'a.pid',
				$queryBuilder->createNamedParameter(
					$arrayOfPids,
					Connection::PARAM_INT_ARRAY
				)
			)
		);		

		$queryBuilder->andWhere(
				$queryBuilder->expr()->and(
					$queryBuilder->expr()->eq('mapgeocode', $queryBuilder->createNamedParameter(1, Connection::PARAM_INT))
				),
				$queryBuilder->expr()->or(
					$queryBuilder->expr()->and(
						$queryBuilder->expr()->eq('latitude', $queryBuilder->createNamedParameter('0.0', Connection::PARAM_STR)),
						$queryBuilder->expr()->eq('longitude', $queryBuilder->createNamedParameter('0.0', Connection::PARAM_STR))
					),
					$queryBuilder->expr()->and(
						$queryBuilder->expr()->eq('latitude', $queryBuilder->createNamedParameter('', Connection::PARAM_STR)),
						$queryBuilder->expr()->eq('longitude', $queryBuilder->createNamedParameter('', Connection::PARAM_STR))
					),
					$queryBuilder->expr()->and(
						$queryBuilder->expr()->isNull('latitude', $queryBuilder->createNamedParameter(NULL, Connection::PARAM_NULL)),
						$queryBuilder->expr()->isNull('longitude', $queryBuilder->createNamedParameter(NULL, Connection::PARAM_NULL))
					)
				)
				
		);
		$result = $queryBuilder->executeQuery()->fetchAllAssociative();
		return $result;
	}



	/**
	 * Find locations within radius
	 *
	 * @param stdClass  $latLon
	 * @param int  $radius
	 * @param array $categoryList
	 * @param string $storagePid
	 * @param int  $limit
	 * @param int  $page
	 * 
	 * @return QueryResultInterface|array the locations
	 */
	public function findLocationsInRadius($latLon, $radius, $categoryList, $storagePid, $language, $limit, $page) {
		$radius = intval($radius);
		$lat = $latLon->lat;
		$lon =  $latLon->lon;

        $context = GeneralUtility::makeInstance(\TYPO3\CMS\Core\Context\Context::class);
        $sys_language_uid = $context->getPropertyFromAspect('language', 'id'); 
        
		$queryBuilder = GeneralUtility::makeInstance(\TYPO3\CMS\Core\Database\ConnectionPool::class)
			->getQueryBuilderForTable('tx_myttaddressmap_domain_model_address');

		$queryBuilder->from('tt_address', 'a');

		$arrayOfPids = \TYPO3\CMS\Core\Utility\GeneralUtility::intExplode(',', $storagePid, TRUE);
		$storagePidList = implode(',', $arrayOfPids);

		if ($language  && $sys_language_uid) {
		$queryBuilder->selectLiteral(
			'distinct a.*', '(acos(sin(' . floatval($lat * M_PI / 180) . ') * sin(latitude * ' . floatval(M_PI / 180) . ') + cos(' . floatval($lat * M_PI / 180) . ') *
			cos(latitude * ' . floatval(M_PI / 180) . ') * cos((' . floatval($lon) . ' - longitude) * ' . floatval(M_PI / 180) . '))) * 6370 as `distance`,

			(SELECT GROUP_CONCAT(e.title ORDER BY e.title SEPARATOR \', \') from tt_address d, sys_category 
						e , sys_category_record_mm m
						where  m.uid_foreign = d.uid
						and e.sys_language_uid = ' . intval($sys_language_uid) . '
						and e.l10n_parent = m.uid_local
						and d.uid = a.uid
						and e.pid in (' . $storagePidList  . ')
					) as categories			

			');
		} else {
		$queryBuilder->selectLiteral(
			'distinct a.*', '(acos(sin(' . floatval($lat * M_PI / 180) . ') * sin(latitude * ' . floatval(M_PI / 180) . ') + cos(' . floatval($lat * M_PI / 180) . ') *
			cos(latitude * ' . floatval(M_PI / 180) . ') * cos((' . floatval($lon) . ' - longitude) * ' . floatval(M_PI / 180) . '))) * 6370 as `distance`,

			(SELECT GROUP_CONCAT(e.title ORDER BY e.title SEPARATOR \', \') from tt_address d, sys_category 
						e , sys_category_record_mm m
						where m.uid_local = e.uid
						and m.uid_foreign = d.uid
						and e.sys_language_uid = 0
						and d.uid = a.uid
						and e.pid in (' . $storagePidList  . ')
					) as categories
			');
			
		}			

		$queryBuilder->where(
			$queryBuilder->expr()->in(
				'a.pid',
				$queryBuilder->createNamedParameter(
				$arrayOfPids,
				Connection::PARAM_INT_ARRAY
				)
			)
		)
		
		->andWhere(
			$queryBuilder->expr()->eq('a.sys_language_uid',	$queryBuilder->createNamedParameter($language, Connection::PARAM_INT))
		)
		
		->orderBy('distance');

        $queryBuilder->having('`distance` <= ' . $queryBuilder->createNamedParameter($radius, Connection::PARAM_INT));
		$queryBuilder = $this->addCategoryQueryPart($categoryList, $queryBuilder);
		$queryBuilder->setMaxResults(intval($limit))->setFirstResult(intval($page * $limit));

		$result =  $queryBuilder->executeQuery()->fetchAllAssociative();
		return $result;
	}
    


	/**
	 * Find locations of a country
	 *
	 * @param stdClass  $latLon
	 * @param string  $country
	 * @param array $categoryList
	 * @param string $storagePid
	 * @param int  $limit
	 * @param int  $page
	 * 
	 * @return QueryResultInterface|array the locations
	 */
	public function findLocationsOfCountry($latLon, $country, $categoryList, $storagePid, $language, $limit, $page, $orderBy = 'distance') {
		$lat = $latLon->lat;
		$lon =  $latLon->lon;

        $context = GeneralUtility::makeInstance(\TYPO3\CMS\Core\Context\Context::class);
        $sys_language_uid = $context->getPropertyFromAspect('language', 'id'); 
        
		$queryBuilder = GeneralUtility::makeInstance(\TYPO3\CMS\Core\Database\ConnectionPool::class)
			->getQueryBuilderForTable('tx_myttaddressmap_domain_model_address');

		$queryBuilder->from('tt_address', 'a');

		$arrayOfPids = \TYPO3\CMS\Core\Utility\GeneralUtility::intExplode(',', $storagePid, TRUE);
		$storagePidList = implode(',', $arrayOfPids);

/*		$queryBuilder->selectLiteral(
			'distinct a.*', '(acos(sin(' . floatval($lat * M_PI / 180) . ') * sin(latitude * ' . floatval(M_PI / 180) . ') + cos(' . floatval($lat * M_PI / 180) . ') *
			cos(latitude * ' . floatval(M_PI / 180) . ') * cos((' . floatval($lon) . ' - longitude) * ' . floatval(M_PI / 180) . '))) * 6370 as `distance`,

			(SELECT GROUP_CONCAT(e.title ORDER BY e.title SEPARATOR \', \') from tt_address d, sys_category 
						e, sys_category_record_mm f
						where f.uid_local = e.uid
						AND f.uid_foreign= d.uid
						and d.uid = a.uid
						and e.pid in (' . $storagePidList . ')
					) as categories			
			'
		);
*/
		if ($language  && $sys_language_uid) {
		$queryBuilder->selectLiteral(
			'distinct a.*', '(acos(sin(' . floatval($lat * M_PI / 180) . ') * sin(latitude * ' . floatval(M_PI / 180) . ') + cos(' . floatval($lat * M_PI / 180) . ') *
			cos(latitude * ' . floatval(M_PI / 180) . ') * cos((' . floatval($lon) . ' - longitude) * ' . floatval(M_PI / 180) . '))) * 6370 as `distance`,

			(SELECT GROUP_CONCAT(e.title ORDER BY e.title SEPARATOR \', \') from tt_address d, sys_category 
						e , sys_category_record_mm m
						where  m.uid_foreign = d.uid
						and e.sys_language_uid = ' . intval($sys_language_uid) . '
						and e.l10n_parent = m.uid_local
						and d.uid = a.uid
						and e.pid in (' . $storagePidList  . ')
					) as categories			
			');
		} else {
		$queryBuilder->selectLiteral(
			'distinct a.*', '(acos(sin(' . floatval($lat * M_PI / 180) . ') * sin(latitude * ' . floatval(M_PI / 180) . ') + cos(' . floatval($lat * M_PI / 180) . ') *
			cos(latitude * ' . floatval(M_PI / 180) . ') * cos((' . floatval($lon) . ' - longitude) * ' . floatval(M_PI / 180) . '))) * 6370 as `distance`,

			(SELECT GROUP_CONCAT(e.title ORDER BY e.title SEPARATOR \', \') from tt_address d, sys_category 
						e , sys_category_record_mm m
						where m.uid_local = e.uid
						and m.uid_foreign = d.uid
						and e.sys_language_uid = 0
						and d.uid = a.uid
						and e.pid in (' . $storagePidList  . ')
					) as categories			
			');
			
		}			
		
		
		$queryBuilder->where(
			$queryBuilder->expr()->in(
				'a.pid',
				$queryBuilder->createNamedParameter(
					$arrayOfPids,
					\Doctrine\DBAL\Connection::PARAM_INT_ARRAY
				)
			)
		)		
		->orderBy('distance');

		$queryBuilder->andWhere(
			$queryBuilder->expr()->and(
				$queryBuilder->expr()->eq('a.country', $queryBuilder->createNamedParameter($country, Connection::PARAM_STR)),
				$queryBuilder->expr()->eq('a.sys_language_uid', $queryBuilder->createNamedParameter($language,  Connection::PARAM_INT))
			)
		);
		
		
		$queryBuilder = $this->addCategoryQueryPart($categoryList, $queryBuilder);
		$queryBuilder->setMaxResults(intval($limit))->setFirstResult(intval($page * $limit));
		
		$result =  $queryBuilder->executeQuery()->fetchAllAssociative();

		return $result;

	}


	/*
	 * 	adopted from EXT storefinder
	 * 	
	 * @param string $categories
	 * @param QueryBuilder $queryBuilder
	 *
	 * @return QueryBuilder
	 */
    protected function addCategoryQueryPart($categoryList, QueryBuilder $queryBuilder): QueryBuilder
    {
		$arrayOfCategories = \TYPO3\CMS\Core\Utility\GeneralUtility::intExplode(',', $categoryList, TRUE);

        if (!empty($arrayOfCategories)) {
			$expression = $queryBuilder->expr();
			$queryBuilder->innerJoin(
				'a',
				'sys_category_record_mm',
				'c',
                $expression->and(
                    $expression->eq('a.uid', 'c.uid_foreign'),
                    $expression->eq(
						'c.tablenames',
						$queryBuilder->createNamedParameter('tt_address')
                    ),
					$expression->eq(
						'c.fieldname',
						$queryBuilder->createNamedParameter('categories')
					)
                )
            );
			$queryBuilder->andWhere(
				$expression->in(
					'c.uid_local',
					$queryBuilder->createNamedParameter($arrayOfCategories, Connection::PARAM_INT_ARRAY)
				)
			);
		}
		return $queryBuilder;
	}

    
}
