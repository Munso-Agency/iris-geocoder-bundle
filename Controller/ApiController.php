<?php
/**
 * Created by PhpStorm.
 * User: dev1
 * Date: 18/05/2017
 * Time: 09:50
 */

namespace Munso\IRISGeocoderBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Class ApiController
 * @package Munso\IRISGeocoderBundle\Controller
 */
class ApiController extends Controlller
{

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function searchAction(Request $request)
    {
        $results = array();

        if ($request->query->has('q')) {
            $results = $this->get('munso.iris_geocoder')->getIRISByAddress($request->query->get('q', ''));
        }

        return new JsonResponse($results, 200);
    }
}