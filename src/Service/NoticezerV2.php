<?php


namespace App\Service;


use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class NoticezerV2
{


    /**
     * @return array|mixed
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function lastnoticeclub(): mixed
    {
        $client = HttpClient::create();

        $response = $client->request('GET', 'https://affichange.com/api/ressources/affi/post/search/allfind/?page=1&publied=true&order%5BcreateAt%5D=desc&keymodule=50159020391993ad3ccbf82bd20d149d67a25c10');
        if ($response->getStatusCode()==200){
            if($response->getContent()){

                $content = $response->toArray();
                $postations=$content["hydra:member"];

                foreach ($postations as $key =>$postation) {  //todo pour la verfi de l'existence du fichier
                    if ($postation['htmlcontent']['apifileblob']) {
                        $htmlcontent = $client->request('GET', ($postation['htmlcontent']['apifileblob']));
                        $postations[$key]['content'] = $htmlcontent->getContent();

                    }else{
                        $postations[$key]['content'] =false;
                    }
                }
                return  $postations;
            }else{
                return [];
            }
        }else{
            return [];
        }
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     */
    public function majnoticeclub()
    {
        $client = HttpClient::create();
        try {
            $response = $client->request('GET', 'http://localhost/affichange2020/public/api/postations?deleted=false&order[createAt]=desc&moduletype=4&page=1');
            if ($response){
                $content = $response->toArray();
                $postations=$content["hydra:member"][0]['module']['postations'];

                foreach ($postations as $key =>$postation){
                    if($postation['htmlcontent']['apifileblob']){
                        $page = file_get_contents($postation['htmlcontent']['apifileblob']);
                        if ($page) {
                            $postations[$key]['content']=$page;
                        }
                    }
                }
                return  $postations;

            }else{
                return [];
            }
        } catch (TransportExceptionInterface|ClientExceptionInterface|DecodingExceptionInterface|ServerExceptionInterface $e) {
            throw $e;
        } catch (RedirectionExceptionInterface $e) {
            return false;
        }
    }

    public function twoLastnoticeclub(){
        $client = HttpClient::create();
        try {
            $response = $client->request('GET', 'https://affichange.com/api/ressources/affi/post/search/allfind/?page=1&publied=false&order%5BcreateAt%5D=desc&keymodule=50159020391993ad3ccbf82bd20d149d67a25c10');

            if($response){
                $content = $response->toArray();

                $postations=$content["hydra:member"];
                foreach ($postations as $key =>$postation) {  //todo pour la verfi de l'existence du fichier
                    if ($postation['htmlcontent']['apifileblob']) {
                        $htmlcontent = $client->request('GET', ($postation['htmlcontent']['apifileblob']));
                        $postations[$key]['content'] = $htmlcontent->getContent();
                    }else{
                        $postations[$key]['content'] =false;
                    }
                }
                return  array_slice($postations, 0, 2);
            }else{
                return [];
            }
        } catch (TransportExceptionInterface|ClientExceptionInterface|DecodingExceptionInterface|ServerExceptionInterface $e) {
            throw $e;
        } catch (RedirectionExceptionInterface $e) {
            return false;
        }
    }

    public function getNotice($id){
        $client = HttpClient::create();
        try {
            $response = $client->request('GET', 'https://affichange.com/api/ressources/affi/post/search/find/'.$id);
            if ($response){
                $post = $response->toArray();
                if ($post['htmlcontent']['apifileblob']) {
                    $htmlcontent = $client->request('GET', ($post['htmlcontent']['apifileblob']));
                    $post['content'] = $htmlcontent->getContent();
                }else{
                    $post['content'] =false;
                }
                return  $post;
            }else{
                return [];
            }
        } catch (TransportExceptionInterface|ClientExceptionInterface|DecodingExceptionInterface|ServerExceptionInterface $e) {
            throw $e;
        } catch (RedirectionExceptionInterface $e) {
            return false;
        }
    }

    public function lastpromo()
    {
        $client = HttpClient::create();
        try {

            $response = $client->request('GET', 'https://affichange.com/api/ressources/affi/offre/allfind/?page=1&deleted=false&order%5BcreateAt%5D=desc&keymodule=50159020391993ad3ccbf82bd20d149d67a25c10&page=1');

            if ($response){

                $content = $response->toArray();

                $listoffres=$content["hydra:member"];
                foreach ($listoffres as $key => $offre) {
                    $product=$offre['product'];


                    if ($product['htmlcontent']['apifileblob']) {
                        $htmlcontent = $client->request('GET', ($product['htmlcontent']['apifileblob']));
                        $listoffres[$key]['product']['content'] = $htmlcontent->getContent();

                    }else{
                        $listoffres[$key]['product']['content'] =false;
                    }
                }
                return  $listoffres;
            }else{
                return [];
            }
        } catch (TransportExceptionInterface|ClientExceptionInterface|DecodingExceptionInterface|ServerExceptionInterface $e) {
            throw $e;
        } catch (RedirectionExceptionInterface $e) {
            return false;
        }
    }

    /**
     * @return array|null
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function worksNotice(): ?array
    {
        $client = HttpClient::create();
        $response = $client->request('GET', 'https://affichange.com/api/ressources/affi/work/search/allfind/?page=1&publied=true&order%5BcreateAt%5D=desc&keymodule=50159020391993ad3ccbf82bd20d149d67a25c10');

        if ($response->getStatusCode()==200){
            if($response->getContent()){

                $content = $response->toArray();
                $postations=$content["hydra:member"];
                foreach ($postations as $key =>$postation) {  //todo pour la verfi de l'existence du fichier
                    if ($postation['htmlcontent']['apifileblob']) {
                        $htmlcontent = $client->request('GET', ($postation['htmlcontent']['apifileblob']));
                        $postations[$key]['content'] = $htmlcontent->getContent();
                    }else{
                        $postations[$key]['content'] =false;
                    }
                }
                return  $postations;
            }else{
                return null;
            }
        }else{
            return null;
        }
    }


    /**
     * @return array|null
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function featuredNotice(): ?array
    {
        $client = HttpClient::create();

        $response = $client->request('GET', 'https://affichange.com/api/ressources/affi/post/search/allfind/?page=1&publied=true&order%5BcreateAt%5D=desc&keymodule=50159020391993ad3ccbf82bd20d149d67a25c10');


        if ($response->getStatusCode()==200){
            if($response->getContent()){

                $content = $response->toArray();
                $postations=$content["hydra:member"];
                foreach ($postations as $key =>$postation) {  //todo pour la verfi de l'existence du fichier
                    if ($postation['htmlcontent']['apifileblob']) {
                        $htmlcontent = $client->request('GET', ($postation['htmlcontent']['apifileblob']));
                        $postations[$key]['content'] = $htmlcontent->getContent();
                    }else{
                        $postations[$key]['content'] =false;
                    }
                }
                return  $postations;
            }else{
                return null;
            }
        }else{
            return null;
        }
    }

    /**
     * @return array|mixed
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function lastRecipe()
    {
        $client = HttpClient::create();
        $response = $client->request('GET', 'https://affichange.com/api/ressources/affi/formule/allfind/?page=1&publied=true&order%5BcreateAt%5D=asc&keymodule=50159020391993ad3ccbf82bd20d149d67a25c10');

        if ($response->getStatusCode()==200){
            $content = $response->toArray();

            // $hydra=$content["hydra:member"];
            //dump(end($hydra));
            return $content["hydra:member"];

        }else{
            return [];
        }
    }

    /**
     * @return array|mixed
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function lastpromo2(): mixed
    {
        $client = HttpClient::create();

        $response = $client->request('GET', 'https://affichange.com/api/ressources/affi/offre/allfind/?deleted=false&order[createAt]=asc&keymodule=50159020391993ad3ccbf82bd20d149d67a25c10&idparutions.days[after]=%222020-06-05%22&page=1');

        if ($response->getStatusCode()=="200"){
            $content = $response->toArray();
            $listoffres=$content["hydra:member"];
            foreach ($listoffres as $key => $offre) {
                $product=$offre['product'];

                if ($product['htmlcontent']['apifileblob']) {
                    //$link=str_replace("https://affichange.com", "http://localhost/affichange2020/public", $product['htmlcontent']['fileblob']);
                    $htmlcontent = $client->request('GET', ($product['htmlcontent']['apifileblob']));
                    $listoffres[$key]['product']['content'] = $htmlcontent->getContent();

                }else{
                    $listoffres[$key]['product']['content'] =false;
                }

            }
            return  $listoffres;
        }else{
            return [];
        }
    }

    /**
     * @return array|mixed
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function listWorkShop()
    {
        $client = HttpClient::create();

        $response = $client->request('GET', 'https://affichange.com/api/ressources/affi/offre/allfind/?deleted=false&order[createAt]=asc&keymodule=50159020391993ad3ccbf82bd20d149d67a25c10&idparutions.days[after]=%222020-06-05%22&page=1');

        if ($response->getStatusCode()=="200"){
            $content = $response->toArray();
            $listoffres=$content["hydra:member"];
            foreach ($listoffres as $key => $offre) {
                $product=$offre['product'];

                if ($product['htmlcontent']['apifileblob']) {
                    //$link=str_replace("https://affichange.com", "http://localhost/affichange2020/public", $product['htmlcontent']['fileblob']);
                    $htmlcontent = $client->request('GET', ($product['htmlcontent']['apifileblob']));
                    $listoffres[$key]['product']['content'] = $htmlcontent->getContent();

                }else{
                    $listoffres[$key]['product']['content'] =false;
                }

            }
            return  $listoffres;
        }else{
            return [];
        }
    }

    /**
     * @return array|mixed
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function oneWorkShop()
    {
        $client = HttpClient::create();

        $response = $client->request('GET', 'https://affichange.com/api/ressources/affi/offre/allfind/?deleted=false&order[createAt]=asc&keymodule=50159020391993ad3ccbf82bd20d149d67a25c10&idparutions.days[after]=%222020-06-05%22&page=1');

        if ($response->getStatusCode()=="200"){
            $content = $response->toArray();
            $listoffres=$content["hydra:member"];
            foreach ($listoffres as $key => $offre) {
                $product=$offre['product'];

                if ($product['htmlcontent']['apifileblob']) {
                    //$link=str_replace("https://affichange.com", "http://localhost/affichange2020/public", $product['htmlcontent']['fileblob']);
                    $htmlcontent = $client->request('GET', ($product['htmlcontent']['apifileblob']));
                    $listoffres[$key]['product']['content'] = $htmlcontent->getContent();

                }else{
                    $listoffres[$key]['product']['content'] =false;
                }

            }
            return  $listoffres;
        }else{
            return [];
        }
    }
}