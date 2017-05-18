<?php

namespace Munso\IRISGeocoderBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Class ApiController
 * @package Munso\IRISGeocoderBundle\Controller
 */
class ApiController extends Controller
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