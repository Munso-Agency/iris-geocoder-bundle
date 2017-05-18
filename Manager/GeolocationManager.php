<?php
/**
 * Created by PhpStorm.
 * User: dev1
 * Date: 15/05/2017
 * Time: 16:16
 */

namespace Munso\IRISGeocoderBundle\Manager;

use Doctrine\ORM\EntityManagerInterface;

class GeolocationManager
{
    CONST REVERSE_API = 'http://api-adresse.data.gouv.fr/';

    protected $em;

    protected $entityName;

    /**
     * GeolocationManager constructor.
     * @param EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em , $entityName)
    {
        $this->em = $em;
        $this->entityName = $entityName;
    }

    /**
     * @param $address
     * @param int $limit
     * @return array|bool
     */
    public function reverseGeocoding($address, $limit = 1)
    {
        $browser = new \Buzz\Browser();
        $url = self::REVERSE_API.'search?'.http_build_query(
                array(
                    'q' => $address,
                    'limit' => $limit,
                )
            );
        $response = $browser->get($url);
        if ($response->getStatusCode() === 200) {
            $content = json_decode($response->getContent(), true);

            if (isset($content['features'][0])) {
                $item = $content['features'][0];
                return array(
                    'lng' => $item['geometry']['coordinates'][0],
                    'lat' => $item['geometry']['coordinates'][1],
                    'address' => $item['properties']['label'],
                );
            }
        }

        return false;
    }

    /**
     * @param $address
     * @return IrisItem
     */
    public function getIRISByAddress($address)
    {
        $coords = $this->reverseGeocoding($address);

        if (false !== $coords) {
            return $this->getRepository()->findByGeolocation($coords['lat'], $coords['lng']);
        }
        return false;
    }


    /**
     * @return Doctrine\ORM\EntityRepository
     */
    public function getRepository()
    {
        return $this->em->getRepository($this->entityName);
    }
}