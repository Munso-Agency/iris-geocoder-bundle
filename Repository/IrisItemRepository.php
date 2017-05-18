<?php
/**
 * Created by PhpStorm.
 * User: dev1
 * Date: 15/05/2017
 * Time: 15:14
 */

namespace Munso\IRISGeocoderBundle\Repository;

use \Doctrine\ORM\EntityRepository;

class IrisItemRepository extends EntityRepository
{
    /**
     * @param $latitude
     * @param $longitude
     * @param $srid
     * @return mixed
     */
    public function findByGeolocation($latitude, $longitude, $srid = 4326)
    {
        $qb = $this->createQueryBuilder('c');

        $qb
            ->select('c.id, c.name, c.code, c.insee')
            ->where('TRUE = ST_Contains(c.geom, ST_SetSRID(ST_MakePoint(:lng, :lat), :srid))')
            ->setParameter(':lat', $latitude)
            ->setParameter(':lng', $longitude)
            ->setParameter(':srid', $srid)
            ->setMaxResults(1)
        ;

        return $qb->getQuery()->getOneOrNullResult();
    }
}