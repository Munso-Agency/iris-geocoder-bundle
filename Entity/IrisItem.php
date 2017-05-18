<?php
/**
 * Created by PhpStorm.
 * User: dev1
 * Date: 12/05/2017
 * Time: 12:49
 */

namespace Munso\IRISGeocoderBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class IrisItem
 * @package Munso\IRISGeocoderBundle\Entity
 * @ORM\Table(
 *     name="public.munso_iris",
 *     indexes={
 *          @ORM\Index(name="idx_geom", columns="geom", flags={"spatial"})
 *     }
 * )
 * @ORM\Entity(repositoryClass="Munso\IRISGeocoderBundle\Repository\IrisItemRepository")
 */
class IrisItem
{
    /**
     * @ORM\Column(name="gid", type="integer", options={"default"="nextval('munso_iris_gid_seq'::regclass)"})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\SequenceGenerator(sequenceName="munso_iris_gid_seq")
     */
    protected $id;

    /**
     * @var
     * @ORM\Column(name="depcom", type="string", length=5, nullable=true)
     */
    protected $insee;

    /**
     * @var
     * @ORM\Column(name="nom_com", type="string", length=255)
     */
    protected $cityName;

    /**
     * @var
     * @ORM\Column(name="iris", type="string", length=5)
     */
    protected $iris;

    /**
     * @var
     * @ORM\Column(name="dcomiris", type="string", length=9)
     */
    protected $code;

    /**
     * @var
     * @ORM\Column(name="nom_iris", type="string", length=255)
     */
    protected $name;

    /**
     * @var
     * @ORM\Column(name="typ_iris", type="string", length=1, nullable=true)
     */
    protected $type;


    /**
     * @var
     * @ORM\Column(name="origine", type="string", length=1, nullable=true)
     */
    protected $origin;



    /**
     * ESCG code for 'Lambert French 93' projection
     * @var
     * @ORM\Column(name="geom", type="geometry", nullable=true, options={"geometry_type"="MULTIPOLYGON", "srid"="4326"})
     */
    protected $geom;


    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set departement
     *
     * @param string $departement
     *
     * @return IrisItem
     */
    public function setDepartement($departement)
    {
        $this->departement = $departement;

        return $this;
    }

    /**
     * Get departement
     *
     * @return string
     */
    public function getDepartement()
    {
        return $this->departement;
    }

    /**
     * Set cityName
     *
     * @param string $cityName
     *
     * @return IrisItem
     */
    public function setCityName($cityName)
    {
        $this->cityName = $cityName;

        return $this;
    }

    /**
     * Get cityName
     *
     * @return string
     */
    public function getCityName()
    {
        return $this->cityName;
    }

    /**
     * Set code
     *
     * @param string $code
     *
     * @return IrisItem
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * Get code
     *
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return IrisItem
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set type
     *
     * @param string $type
     *
     * @return IrisItem
     */
    public function setType($type = null)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set geom
     *
     * @param geometry $geom
     *
     * @return IrisItem
     */
    public function setGeom($geom = null)
    {
        $this->geom = $geom;

        return $this;
    }

    /**
     * Get geom
     *
     * @return geometry
     */
    public function getGeom()
    {
        return $this->geom;
    }

    /**
     * Get insee
     *
     * @return mixed
     */
    public function getInsee()
    {
        return $this->insee;
    }


    /**
     * Set Insee
     *
     * @param null $insee
     * @return $this
     */
    public function setInsee($insee = null)
    {
        $this->insee = $insee;

        return $this;
    }

    /**
     * Get iris
     *
     * @return mixed
     */
    public function getIris()
    {
        return $this->iris;
    }


    /**
     * Set iris
     *
     * @param null $iris
     * @return $this
     */
    public function setIris($iris = null)
    {
        $this->iris = $iris;

        return $this;
    }

    /**
     * Get origin
     *
     * @return mixed
     */
    public function getOrigin()
    {
        return $this->origin;
    }


    /**
     * Set origin
     *
     * @param null $origin
     * @return $this
     */
    public function setOrigin($origin = null)
    {
        $this->origin = $origin;

        return $this;
    }
}
