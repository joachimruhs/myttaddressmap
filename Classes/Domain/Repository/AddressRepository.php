<?php
namespace WSR\Myttaddressmap\Domain\Repository;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Extbase\Persistence\Generic\Query;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;

/***************************************************************
 *
 *  Copyright notice
 *
 *  (c) 2018-2020 Joachim Ruhs <postmaster@joachim-ruhs.de>, Web Services Ruhs
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

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
					\Doctrine\DBAL\Connection::PARAM_INT_ARRAY
				)
			)
		);		

		$queryBuilder->andWhere(
				$queryBuilder->expr()->andX(
					$queryBuilder->expr()->eq('mapgeocode', $queryBuilder->createNamedParameter(1, \PDO::PARAM_INT))
				),
				$queryBuilder->expr()->orX(
					$queryBuilder->expr()->andX(
						$queryBuilder->expr()->eq('latitude', $queryBuilder->createNamedParameter('0.0', \PDO::PARAM_STR)),
						$queryBuilder->expr()->eq('longitude', $queryBuilder->createNamedParameter('0.0', \PDO::PARAM_STR))
					),
					$queryBuilder->expr()->andX(
						$queryBuilder->expr()->eq('latitude', $queryBuilder->createNamedParameter('', \PDO::PARAM_STR)),
						$queryBuilder->expr()->eq('longitude', $queryBuilder->createNamedParameter('', \PDO::PARAM_STR))
					),
					$queryBuilder->expr()->andX(
						$queryBuilder->expr()->eq('latitude', $queryBuilder->createNamedParameter(NULL, \PDO::PARAM_NULL)),
						$queryBuilder->expr()->eq('longitude', $queryBuilder->createNamedParameter(NULL, \PDO::PARAM_NULL))
					)
				)
				
		);
		$result = $queryBuilder->execute()->fetchAll();
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
	public function findLocationsInRadius($latLon, $radius, $categoryList, $storagePid, $limit, $page) {
		$radius = intval($radius);
		$lat = $latLon->lat;
		$lon =  $latLon->lon;
//		$query = $this->createQuery();


		$queryBuilder = GeneralUtility::makeInstance(\TYPO3\CMS\Core\Database\ConnectionPool::class)
			->getQueryBuilderForTable('tx_myttaddressmap_domain_model_address');

		$queryBuilder->from('tt_address', 'a');

		$arrayOfPids = \TYPO3\CMS\Core\Utility\GeneralUtility::intExplode(',', $storagePid, TRUE);
		$storagePidList = implode(',', $arrayOfPids);
		
		$queryBuilder->selectLiteral(
			'distinct a.*', '(acos(sin(' . floatval($lat * M_PI / 180) . ') * sin(latitude * ' . floatval(M_PI / 180) . ') + cos(' . floatval($lat * M_PI / 180) . ') *
			cos(latitude * ' . floatval(M_PI / 180) . ') * cos((' . floatval($lon) . ' - longitude) * ' . floatval(M_PI / 180) . '))) * 6370 as `distance`,

			(SELECT GROUP_CONCAT(e.title ORDER BY e.title SEPARATOR \', \') from tt_address d, sys_category 
						e, sys_category_record_mm f
						where f.uid_local = e.uid
						AND f.uid_foreign= d.uid
						and d.uid = a.uid
						and e.pid in (' . $storagePidList  . ')
					) as categories			
			'
		)

		->where(
			$queryBuilder->expr()->in(
				'a.pid',
				$queryBuilder->createNamedParameter(
				$arrayOfPids,
				\Doctrine\DBAL\Connection::PARAM_INT_ARRAY
				)
			)
		)		
		
		->orderBy('distance');

        $queryBuilder->having('`distance` <= ' . $queryBuilder->createNamedParameter($radius, \PDO::PARAM_INT));
		$queryBuilder = $this->addCategoryQueryPart($categoryList, $queryBuilder);
		$queryBuilder->setMaxResults(intval($limit))->setFirstResult(intval($page * $limit));

		$result =  $queryBuilder->execute()->fetchAll();
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
	public function findLocationsOfCountry($latLon, $country, $categoryList, $storagePid, $limit, $page, $orderBy = 'distance') {
		$lat = $latLon->lat;
		$lon =  $latLon->lon;

		$queryBuilder = GeneralUtility::makeInstance(\TYPO3\CMS\Core\Database\ConnectionPool::class)
			->getQueryBuilderForTable('tx_myttaddressmap_domain_model_address');

		$queryBuilder->from('tt_address', 'a');

		$arrayOfPids = \TYPO3\CMS\Core\Utility\GeneralUtility::intExplode(',', $storagePid, TRUE);
		$storagePidList = implode(',', $arrayOfPids);

		$queryBuilder->selectLiteral(
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
		)

		->where(
			$queryBuilder->expr()->in(
				'a.pid',
				$queryBuilder->createNamedParameter(
					$arrayOfPids,
					\Doctrine\DBAL\Connection::PARAM_INT_ARRAY
				)
			)
		)		
		->orderBy('distance');

		$queryBuilder->andWhere($queryBuilder->expr()->eq('a.country', $queryBuilder->createNamedParameter($country, \PDO::PARAM_STR)));
		$queryBuilder = $this->addCategoryQueryPart($categoryList, $queryBuilder);
		$queryBuilder->setMaxResults(intval($limit))->setFirstResult(intval($page * $limit));
		
		$result =  $queryBuilder->execute()->fetchAll();

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
                $expression->andX(
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
					$queryBuilder->createNamedParameter($arrayOfCategories, \Doctrine\DBAL\Connection::PARAM_INT_ARRAY)
				)
			);
		}
		return $queryBuilder;
	}


	/**
	 * findByUidNew
	 * 
	 * @return array
	 */
	public function findByUidNew($uid) {

		$queryBuilder = GeneralUtility::makeInstance(\TYPO3\CMS\Core\Database\ConnectionPool::class)
			->getQueryBuilderForTable('tt_address');

		$queryBuilder->select('*')->from('tt_address', 'a');


		$queryBuilder->where(
			$queryBuilder->expr()->eq(
				'a.uid',
				$queryBuilder->createNamedParameter($uid, \PDO::PARAM_INT
				)
			)
		);		

		$result = $queryBuilder->execute()->fetchAll();
		return $result[0];
	}


    
}
