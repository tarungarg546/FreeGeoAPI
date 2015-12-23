<?php

namespace YupItsZac\FreeGeoBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use YupItsZac\FreeGeoBundle\Entity\Session;
use YupItsZac\FreeGeoBundle\Entity\Config;
use YupItsZac\FreeGeoBundle\Entity\Strings;
use \DateTime;
use YupItsZac\FreeGeoBundle\Helpers\ResponseHelper;
use YupItsZac\FreeGeoBundle\Helpers\DataHelper;

class ApiController extends Controller {

    private $dataHelper;

    public function __construct() {

        $this->dataHelper = new DataHelper();
    }

    public function authAction(Request $request) {

    	if($request->request->has('public') === false || $request->request->has('secret') === false) {

            return ResponseHelper::prepareResponse(Strings::API_STATUS_FATAL, Strings::API_REASON_FORBIDDEN, Strings::API_MSG_MISSING_CREDENTIALS);
        }

        $public = $request->request->get('public');
        $secret = $request->request->get('secret');

        $app = $this->getDoctrine()->getRepository('YupItsZacFreeGeoBundle:Apps')->findOneBy(array('publickey' => $public));

        if($app === null) {

            return ResponseHelper::prepareResponse(Strings::API_STATUS_FATAL, Strings::API_REASON_FORBIDDEN, Strings::API_MSG_MISSING_CREDENTIALS);
        }

        $appId = $app->getId();
        $appTitle = $app->getApptitle();
        $appStatus = $app->getStatus();
        
        if($appStatus != 'Active') {

            return ResponseHelper::prepareResponse(Strings::API_STATUS_FATAL, Strings::API_REASON_FORBIDDEN, Strings::API_MSG_REVOKED_CREDENTIALS);
        }

        $sessionKey = md5(time().$public.time().$secret.time().$appId);

        $em = $this->getDoctrine()->getEntityManager();

        $session = new Session();
        $session->setSession($sessionKey);
        $session->setPublic($public);
        $session->setSecret($secret);
        $session->setAppid($appId);
        $now = new DateTime('now');
        $session->setTimestamp($now);
        $em->persist($session);
        $em->flush();

        $payload = array('session' => $sessionKey);

        return ResponseHelper::prepareResponse(Strings::API_STATUS_SUCCESS, Strings::API_REASON_SUCCESS, Strings::API_MSG_SUCCESS, $payload);


    }

    public function findNearAirportAction(Request $request) {

        $session = $request->request->get('session');
        $lat = $request->request->get('lat');
        $lon = $request->request->get('lon');
        $limit = $request->request->get('limit');
        $max = $request->request->get('max');

        if(!$this->dataHelper->verifyAppSession($session)) {
            return ResponseHelper::prepareResponse(Strings::API_STATUS_FATAL, Strings::API_REASON_INVALID_SESSION, Strings::API_MSG_INVALID_SESSION);
        }

        $dataAirports = $this->dataHelper->findNearAirport($lat, $lon, $limit, $max);

        return ResponseHelper::prepareResponse(Strings::API_STATUS_SUCCESS, Strings::API_REASON_SUCCESS, Strings::API_MSG_SUCCESS, $dataAirports);

    }

    public function findNearCityAction(Request $request) {

        $session = $request->request->get('session');
        $lat = $request->request->get('lat');
        $lon = $request->request->get('lon');
        $limit = $request->request->get('limit');
        $max = $request->request->get('max');

        if(!$this->dataHelper->verifyAppSession($session)) {
            return ResponseHelper::prepareResponse(Strings::API_STATUS_FATAL, Strings::API_REASON_INVALID_SESSION, Strings::API_MSG_INVALID_SESSION);
        }

        $dataCities = $this->dataHelper->findNearCity($lat, $lon, $limit, $max);

        return ResponseHelper::prepareResponse(Strings::API_STATUS_SUCCESS, Strings::API_REASON_SUCCESS, Strings::API_MSG_SUCCESS, $dataCities);

    }

    public function findNearLakeAction(Request $request) {

        $session = $request->request->get('session');
        $lat = $request->request->get('lat');
        $lon = $request->request->get('lon');
        $limit = $request->request->get('limit');
        $max = $request->request->get('max');

        if(!$this->dataHelper->verifyAppSession($session)) {
            return ResponseHelper::prepareResponse(Strings::API_STATUS_FATAL, Strings::API_REASON_INVALID_SESSION, Strings::API_MSG_INVALID_SESSION);
        }

        $dataLakes = $this->dataHelper->findNearLake($lat, $lon, $limit, $max);

        return ResponseHelper::prepareResponse(Strings::API_STATUS_SUCCESS, Strings::API_REASON_SUCCESS, Strings::API_MSG_SUCCESS, $dataLakes);

    }

    public function findNearPortAction(Request $request) {

        $session = $request->request->get('session');
        $lat = $request->request->get('lat');
        $lon = $request->request->get('lon');
        $limit = $request->request->get('limit');
        $max = $request->request->get('max');

        if(!$this->dataHelper->verifyAppSession($session)) {
            return ResponseHelper::prepareResponse(Strings::API_STATUS_FATAL, Strings::API_REASON_INVALID_SESSION, Strings::API_MSG_INVALID_SESSION);
        }

        $dataPorts = $this->dataHelper->findNearPort($lat, $lon, $limit, $max);

        return ResponseHelper::prepareResponse(Strings::API_STATUS_SUCCESS, Strings::API_REASON_SUCCESS, Strings::API_MSG_SUCCESS, $dataPorts);

    }

    public function timeZoneAction(Request $request) {

        $session = $request->request->get('session');
        $lat = $request->request->get('lat');
        $lon = $request->request->get('lon');

        if(!$this->dataHelper->verifyAppSession($session)) {
            return ResponseHelper::prepareResponse(Strings::API_STATUS_FATAL, Strings::API_REASON_INVALID_SESSION, Strings::API_MSG_INVALID_SESSION);
        }

        $em = $this->getDoctrine()->getEntityManager();

        $con = $em->getConnection();

        $dataTimeZone = $this->dataHelper->timeZone($lat, $lon);

        return ResponseHelper::prepareResponse(Strings::API_STATUS_SUCCESS, Strings::API_REASON_SUCCESS, Strings::API_MSG_SUCCESS, $dataTimeZone);
    }

    public function detectCountryAction(Request $request) {

        $session = $request->request->get('session');
        $lat = $request->request->get('lat');
        $lon = $request->request->get('lon');

        if(!$this->dataHelper->verifyAppSession($session)) {
            return ResponseHelper::prepareResponse(Strings::API_STATUS_FATAL, Strings::API_REASON_INVALID_SESSION, Strings::API_MSG_INVALID_SESSION);
        }

        $dataCountry = $this->dataHelper->detectCountry($lat, $lon);

        return ResponseHelper::prepareResponse(Strings::API_STATUS_SUCCESS, Strings::API_REASON_SUCCESS, Strings::API_MSG_SUCCESS, $dataCountry);

    }

    public function calculateDistanceAction(Request $request) {

        $session = $request->request->get('session');
        $lata = $request->request->get('lata');
        $lona = $request->request->get('lona');

        $latb = $request->request->get('latb');
        $lonb = $request->request->get('lonb');

        $metric = $request->request->get('metric');

        $round = $request->request->get('round');

        if(!$this->dataHelper->verifyAppSession($session)) {
            return ResponseHelper::prepareResponse(Strings::API_STATUS_FATAL, Strings::API_REASON_INVALID_SESSION, Strings::API_MSG_INVALID_SESSION);
        }

        $theta = $lona - $lonb;
        $dist = sin(deg2rad($lata)) * sin(deg2rad($latb)) +  cos(deg2rad($lata)) * cos(deg2rad($latb)) * cos(deg2rad($theta));
        $dist = acos($dist);
        $dist = rad2deg($dist);
        $miles = $dist * 60 * 1.1515;
        $unit = strtolower($metric);

        if($unit == 'k') {
            $distance = $miles * 1.609344;
            $name = 'kilometers';
        } else if($unit == 'n') {
            $distance = $miles * 0.8684;
            $name = 'nautical miles';
        } else {
            $distance = $miles;
            $name = 'miles';
        }

        if(!empty($round)) {
            $final = round($distance, $round);
        } else {
            $final = $distance;
        }

        $payload = array('distance' => $final, 'metric' => strtoupper($metric), 'fullMetric' => $name);

        return ResponseHelper::prepareResponse(Strings::API_STATUS_SUCCESS, Strings::API_REASON_SUCCESS, Strings::API_MSG_SUCCESS, $payload);
    }

    public function resetKeysAction(Request $request) {

        $session = $request->request->get('session');

        if(!$this->dataHelper->verifyAppSession($session)) {
            return ResponseHelper::prepareResponse(Strings::API_STATUS_FATAL, Strings::API_REASON_INVALID_SESSION, Strings::API_MSG_INVALID_SESSION);
        }

        $appSession = $this->dataHelper->fetchAppSession($session);

        if(!$appSession) {
            return ResponseHelper::prepareResponse(Strings::API_STATUS_FATAL, Strings::API_REASON_INVALID_SESSION, Strings::API_MSG_ERROR_LOCATING_SESSION);
        }

        $publicKey = md5(time().$appSession->getPublic().time());
        $privateKey = md5(time().time().$appSession->getSecret().time());
        $appId = $appSession->getAppid();

        if($this->dataHelper->resetKeys($publicKey, $privateKey, $appId) === false) {
            return ResponseHelper::prepareResponse(Strings::API_STATUS_FATAL, Strings::API_REASON_DB_ERROR, Strings::API_MSG_KEY_RESET_FAILED_DB);
        }

        $app = $this->dataHelper->fetchAppById($appId);

        $contactEmail = $app->getEmail();
        $appTitle = $app->getApptitle();
        $contactFirstName = $app->getFirstname();

        $message = $this->dataHelper->prepareMessage($contactEmail, $contactFirstName, $appTitle, $publicKey, $privateKey);

        $this->sendEmailWithMailgun($message);

        return ResponseHelper::prepareResponse(Strings::API_STATUS_SUCCESS, Strings::API_REASON_SUCCESS, Strings::API_MSG_KEY_RESET_SUCCESS);

    }

    private function sendEmailWithMailgun($message) {

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, Config::MAILGUN_API_URL);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_USERPWD, 'api:{'.Config::MAILGUN_API_KEY.'}');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS,$message);

        $result = curl_exec($ch);

        curl_close($ch);
    }
}
