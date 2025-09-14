<?php


namespace App\Service\Localisation;


use App\Entity\Member\Activmember;
use App\Entity\Sector\Adresses;
use App\Entity\Sector\Gps;
use App\Entity\Sector\Sectors;
use App\Entity\Boards\Board;
use App\Repository\GpsRepository;
use App\Repository\SectorsRepository;
use App\Service\Registration\Sessioninit;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class LocalisationServices
{

    private GpsRepository $gpsRepository;
    private EntityManagerInterface $em;
    private Sessioninit $sessioninit;
    private $reposector;

    public function __construct(SectorsRepository $sectorsRepository, GpsRepository $gpsRepository, EntityManagerInterface $em, Sessioninit $sessioninit){

        $this->gpsRepository = $gpsRepository;
        $this->em = $em;
        $this->sessioninit = $sessioninit;
        $this->reposector=$sectorsRepository;
    }

    public function newAdressDispatch($result, $dispatch, $order)
    {
        /** @var Adresses $adress */

        if (!$sector =$dispatch->getSector()) {
            $sector = new Sectors();
            $dispatch->setSector($sector);
        }
        if ($order) { // nouvelle adresse
            $adress = new Adresses();
            $sector->addAdresse($adress);
        } else {
            $adresses = $sector->getAdresse(); // ??? todo a revoir ???
            foreach ($adresses as $oneadress) {
                if ($oneadress->getIdMap() == $order) {
                    $adress = $oneadress;
                    break;
                }
            }
        }
        if($adress!=null) {
            $action=$this->adressor($adress, $result);
            return $action['adress'];
        }
        return false;
    }


    public function initAdressMediaBoard($data, $board)
    {
            $adress = new Adresses();
            $board->getTemplate()->getSector->addAdresse($adress);
            $action=$this->adressor($adress, $data);
            return $action['adress'];

    }

    /**
     * @param $result []
     * @param $website Board
     * @param $order
     * @return bool|Adresses
     */
    public function newAdress($result, Board $board, $order): bool|Adresses
    {

        $sector=$this->reposector->findBy(['codesite'=>$board->getCodesite()]);

        if($sector){
            $sector=new Sectors();
            $sector->setCodesite($board->getCodesite());
        }

        if($order==1){ // nouvelle adresse
            $adress=new Adresses();
            $sector->addAdresse($adress);
        }else {
            $adresses = $sector->getAdresse();
            foreach ($adresses as $oneadress) {
                if ($oneadress->getIdMap() == $order) {
                    $adress = $oneadress;
                    break;
                }
            }
        }

        if($adress!=null) {
            $action=$this->adressor($adress, $result);
            $board->addLocality($action['gps']);
            return $action['adress'];
        }
        return false;
    }

    /**
     * @param $adress
     * @param $result []
     * @return array
     */
    public function adressor($adress, $result): array
    {
        $adress->setLabel($result['properties']['label']);
        $adress->setIdMap($result['properties']['id']);
        $tabcontext = explode(',', $result['properties']['context']);
        $adress->setNomCommune($result['properties']['city']);
        $adress->setNumdepart($tabcontext[0]);
        $adress->setDepartement($tabcontext[1]);
        $adress->setRegion($tabcontext[2]);
        switch ($result['properties']['type']){
            case "housenumber":
                $adress->setTypeadress("housenumber");
                $adress->setNumero($result['properties']['housenumber'] ?? "");
                $adress->setNomVoie($result['properties']['street'] ?? "");
                break;

            case "street":
                $adress->setTypeadress('street');
                $adress->setNomVoie($result['properties']['name'] ?? "");
                break;

            default :
                $adress->setTypeadress("none");
                $adress->setNumero("");
                $adress->setNomVoie("");
        }

        $adress->setCodePostal($result['properties']['postcode']);
        $adress->setInsee($result['properties']['citycode']);
        $adress->setNomCommune($result['properties']['city']);
        $adress->setLabel($result['properties']['label']);
        $adress->setIdMap($result['properties']['id']);
        $context=explode(",",$result['properties']['context']);
        $adress->setDepartement($context[1]);
        $adress->setRegion($context[2]);
        $adress->setLat($result['geometry']['coordinates'][1]);
        $adress->setLon($result['geometry']['coordinates'][0]);


        $gps=$this->gpsRepository->findOneBy(["insee"=>$adress->getInsee()]);

        if($gps==null){
            $gps=new Gps();
            $gps->setLatloc($adress->getLat());
            $gps->setLonloc($adress->getLon());
            $gps->setNameloc($adress->getNomCommune().",".$adress->getInsee());
            $gps->setCity($adress->getNomCommune());
            $gps->setCode($adress->getCodePostal());
            $gps->setInsee($adress->getInsee());
            $gps->setPerimeter(10);
            $this->em->persist(($gps));

        }
        $adress->setGps($gps);
        return ['adress'=>$adress,'gps'=>$gps];
    }

    /**
     * @param $oneadress Adresses
     * @return array|false
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function apicodep(Adresses $oneadress): bool|array
    {
        $searchadress = $oneadress->getCodePostal();
        $client = HttpClient::create();
        $response = $client->request('GET', 'https://api-adresse.data.gouv.fr/search/?q=' . $searchadress);
        $response = $client->request('GET','https://geo.api.gouv.fr/communes?codePostal='.$searchadress.'&fields=nom,code,codesPostaux,codeDepartement,codeRegion,population&format=json&geometry=centre');
        if ($response) {
            return $response->toArray();
        } else {
            return false;
        }
    }

    /**
     * @param $slugcity
     * @return Gps|null
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    protected function findlocateByName($slugcity): ?Gps     // version de base commune dispatch et website
    {
        $cityshearch = $slugcity;
        $cityshearch = preg_replace("#st #i", ' SAINT ', $cityshearch);
        $cityshearch = preg_replace("#ste #i", ' SAINTE ', $cityshearch);
        $cityshearch = preg_replace("#- #i", ' ', $cityshearch);

        $client = HttpClient::create();
        $response = $client->request('GET','https://geo.api.gouv.fr/communes?nom='.$cityshearch.'&fields=nom,code,codesPostaux,centre,codeDepartement,departement&format=json&geometry=centre');

        if($response) {
            $content = $response->toArray();
            if (array_keys($content, true)) {
                if (!$gps = $this->gpsRepository->findOneBy(['insee' => $content[0]['code']])) $gps = New Gps();
                if ($coordonnates = $content[0]['centre']['coordinates']) {
                    $gps->setLatloc($coordonnates[1]);
                    $gps->setLonloc($coordonnates[0]);
                }
                $gps->setCity($content[0]['nom']);
                $gps->setNameloc($content[0]['nom']);
                $gps->setCode($content[0]['codesPostaux'][0]);
                $gps->setInsee($content[0]['code']);
                $gps->setNamecodep($cityshearch.','. $content[0]['codesPostaux'][0]);
                $gps->setPerimeter(0);
                $this->em->persist($gps);
                $this->em->flush();
                return $gps;
            } else{
                return null;
            }
        }else{
            return null;
        }
    }


    /**
     * @param $code
     * @param $city
     * @return Gps|null
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    protected function findlocateHttp($code, $city): ?Gps     // version de base commune dispatch et website
    {
        $repogps=$this->em->getRepository(Gps::class);
        $cityshearch = $city;
        $codeshearch= $code;
        $cityshearch = preg_replace("#st #i", ' SAINT ', $cityshearch);
        $cityshearch = preg_replace("#ste #i", ' SAINTE ', $cityshearch);

        $client = HttpClient::create();
        $response = $client->request('GET','https://geo.api.gouv.fr/communes?nom='.$cityshearch.'&fields=nom,code,codesPostaux,centre,codeDepartement,departement&format=json&geometry=centre');

        if($response) {
            $content = $response->toArray();
            if (array_keys($content, true)) {
                $gps = $repogps->findOneBy(['insee' => $content[0]['code']]);
                if (!$gps) {
                    $gps = New Gps();
                    if ($coordonnates = $content[0]['centre']['coordinates']) {
                        $gps->setLatloc($coordonnates[1]);
                        $gps->setLonloc($coordonnates[0]);
                    }
                    $gps->setCity($content[0]['nom']);
                    $gps->setNameloc($content[0]['nom']);
                    $gps->setCode($content[0]['codesPostaux'][0]);
                    $gps->setInsee($content[0]['code']);
                    $gps->setNamecodep($cityshearch.','. $code);
                    $gps->setPerimeter(0);
                    $this->em->persist($gps);
                    $this->em->flush();
                }
            return $gps;
            } else{
                return null;
            }
        }else{
            throw new Exception("y a une couille dans le paté au niveau de la requete vers conversations");
        }
    }


    public function initlocate($space, $code,$city,$q){
        $repogps=$this->em->getRepository(Gps::class);
        $cityshearch = $city;
        $codeshearch= $code;

        $cityshearch = preg_replace("#st #i", ' SAINT ', $cityshearch);
        $cityshearch = preg_replace("#ste #i", ' SAINTE ', $cityshearch);
        $client = HttpClient::create();
        $response = $client->request('GET','https://geo.api.gouv.fr/communes?codePostal='.$codeshearch.'&nom='.$cityshearch.'&fields=centre&format=json&geometry=centre');
        if ($response) {
            $content = $response->toArray();
            $gps = $repogps->findOneBy(['insee' => $content[0]['code']]);
            if(!$gps){
                $gps = New Gps();
                if($coordonnates=$content[0]['centre']['coordinates']) {
                    $gps->setLatloc($coordonnates[1]);
                    $gps->setLonloc($coordonnates[0]);
                }
                $gps->setCity($content[0]['nom']);
                $gps->setNameloc($content[0]['nom']);
                $gps->setCode($content[0]['codesPostaux'][0]);
                $gps->setInsee($content[0]['code']);
                $gps->setNamecodep($cityshearch.','. $code);
                $gps->setPerimeter(10);
                $this->em->persist($gps);
            }
            if(!$space)return $locate=['lat'=>floatval($gps->getLatloc()),'long'=>floatval($gps->getLonloc()),'city'=>$gps->getNameloc()];
            $space->setLocality($gps);
            return true;
        }else{
            throw new Exception("y a une couille dans le paté au niveau de la requete vers conversations");
        }
    }

    /**
     * @param $dispatch
     * @param $code
     * @param $city
     * @return Gps|object|null
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     * @throws Exception
     */
    public function changeLocate($dispatch, $code,$city){
        $repogps=$this->em->getRepository(Gps::class);
        $cityshearch = $city;
        $codeshearch= $code;
        $cityshearch = preg_replace("#st #i", ' SAINT ', $cityshearch);
        $cityshearch = preg_replace("#ste #i", ' SAINTE ', $cityshearch);


        $client = HttpClient::create();
        $response = $client->request('GET','https://geo.api.gouv.fr/communes?codePostal='.$codeshearch.'&nom='.$cityshearch.'&fields=nom,code,codesPostaux,centre,codeDepartement,departement&format=json&geometry=centre');
        if($response) {

            $content = $response->toArray();
            if (array_keys($content, true)) {

                $gps = $repogps->findOneBy(['insee' => $content[0]['code']]);
                if (!$gps) {
                    $gps = New Gps();
                    if ($coordonnates = $content[0]['centre']['coordinates']) {
                        $gps->setLatloc($coordonnates[1]);
                        $gps->setLonloc($coordonnates[0]);
                    }
                    $gps->setCity($content[0]['nom']);
                    $gps->setNameloc($content[0]['nom']);
                    $gps->setCode($content[0]['codesPostaux'][0]);
                    $gps->setInsee($content[0]['code']);
                    $gps->setNamecodep($cityshearch.','. $code);
                    $gps->setPerimeter(0);
                    $this->em->persist($gps);
                    $this->em->flush();
                }

                if ($dispatch != null) {
                    $dispatch->setLocality($gps);
                    $this->em->persist($dispatch);
                    $this->em->flush();
                }
            return $gps;
            } else {throw new Exception("y a une couille dans le paté au niveau de la requete vers conversations");}
        }else{throw new Exception("y a une couille dans le paté au niveau de la requete vers conversations");}
    }

    /**
     * @param $content
     * @param $lat
     * @param $lon
     * @return Gps
     * @throws Exception
     */
    public function testLocate($content,$lat, $lon): Gps
    {
        if (array_keys($content, true)) {
            $gps = $this->gpsRepository->findOneBy(['insee' => $content[0]['code']]);
            if (!$gps) {
                $gps = New Gps();
               // if ($coordonnates = $content[0]['centre']['coordinates']) {
                    $gps->setLatloc($lat);
                    $gps->setLonloc($lon);
               // }
                $gps->setCity($content[0]['nom']);
                $gps->setNameloc($content[0]['nom']);
                $gps->setCode($content[0]['codesPostaux'][0]);
                $gps->setInsee($content[0]['code']);
                $gps->setNamecodep($content[0]['population']);
                $gps->setPerimeter(0);
                $this->em->persist($gps);
                $this->em->flush();
            }
            return $gps;
        }else{
            throw new Exception("y a une couille dans le paté au niveau de la requete vers conversations");
        }
    }

    /**
     * @param $lat
     * @param $lon
     * @return array|bool
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function apilocatecityuser($lat, $lon)
    {
        $client = HttpClient::create();
        $response = $client->request('GET',
            'https://geo.api.gouv.fr/communes?lat='.$lat.'&lon='.$lon.'&fields=nom,code,codesPostaux,codeDepartement,codeRegion,population&format=json&geometry=centre');
        if ($response) {
            return $response->toArray();
        } else {
            return false;
        }
    }

    /**
     * @param $lon
     * @param $lat
     * @return Gps|bool
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     * @throws Exception
     */
    public function defineCity($lat, $lon): Gps|bool
    {
        $locate = $this->apilocatecityuser($lat, $lon);
        if($locate){
            $gps=$this->testLocate($locate,$lat, $lon);
            $this->sessioninit->ipOrGpsPublicLoc($gps);
            return $gps;
        } else{
            return false;
        }
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     * @throws Exception
     */
    public function findGps($lat, $lon, $city): Gps|bool
    {
        $locate = $this->apilocatecityuser($lat, $lon);
        if($locate){
            return $this->testLocate($locate,$lat, $lon);
        } else{
            return false;
        }
    }

    /**
     * @param $lon
     * @param $lat
     * @return Gps|bool
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     * @throws Exception
     */
    public function redefineCityCustomer($lat, $lon)
    {
        $locate = $this->apilocatecityuser($lat, $lon);
        if($locate){
            $gps=$this->testLocate($locate,$lat, $lon);
            $this->sessioninit->ipOrGpsPublicLoc($gps);
            return $gps;
        } else{
            return false;
        }
    }

    public function initLocateNewDispatch($city,$form): Gps
    {
        if($city){
            return $this->findLocate($city);
        }else{
            $code=$form['codep']->getData();
            $city=$form['city']->getData();
            return $this->findlocateHttp($code, $city);
        }
    }

    public function initLocateNewwebsite($form, $dispatch): bool
    {
        $code=$form['codeok']->getData();
        $city=$form['city']->getData();
        $q=$form['codep']->getData();
        return $this->initlocate($dispatch, $code, $city, $q);
    }

    /**
     * @param $city
     * @return Gps|null
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function findLocate($city): ?Gps
    {
        if(!$locate=$this->gpsRepository->findOneBy(['slugcity'=>$city])){
            $locate = $this->findlocateByName($city);
        }
        return $locate??null;
    }

    public function findLocateByCenter($center): array
    {
        if(!$locate= $this->gpsRepository->findByCenter($center)){
            $locate= $this->gpsRepository->findInBetwen($center['lon'], $center['lat']);
        }
        return $locate;
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     */
    public function defineGpsOfNewWebsite($code, $city): ?Gps
    {
        return $this->findlocateHttp($code, $city);
    }

}