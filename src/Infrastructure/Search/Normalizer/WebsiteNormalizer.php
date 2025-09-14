<?php


namespace App\Infrastructure\Search\Normalizer;

use App\Repository\SectorsRepository;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use App\Entity\Boards\Board;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;


class WebsiteNormalizer implements NormalizerInterface
{

    private UrlGeneratorInterface $urlGenerator;
    private SectorsRepository $reposector;

    public function __construct(UrlGeneratorInterface $urlGenerator, SectorsRepository $reposector )
    {
        $this->urlGenerator = $urlGenerator;
        $this->reposector=$reposector;
    }

    public function supportsNormalization($data, string $format = null, array $context = []): bool
    {
        return $data instanceof Board && 'search' === $format;
    }

    public function normalize($data, string $format = null, array $context = []): array
    {
        if (!$data instanceof Board) {
            throw new \InvalidArgumentException('Unexpected type for normalization, expected Course, got '.get_class($data));
        }

        $url = $this->normalizepath($data);
        $sector=$this->reposector->findOneBy(['codesite'=>$data->getCodesite()]);
        if($sector){
            $adresse=$sector->getAdresse()[0];
        }else{
            $adresse=null;
        }

        return [
            'id' => (string) $data->getId(),
            'content' => MarkdownTransformer::toText((string) $data->getTemplate()->getDescription()),
            'url' => $this->urlGenerator->generate($url['path'], $url['params']),
            'title' => $data->getNameboard(),
            'category' => array_map(fn ($t) => $t->getName(), CollectionTransformer::toarray($data->getTemplate()->getTagueries())),
            'type' => 'website',
            'city'=> $data->getLocality()->getCity(),
            'adresses'=>$adresse?array_map(fn ($t) => $t->getNumero().', '.$t->getNomVoie().' '.$t->getCodePostal().' '.$t->getNomCommune(), CollectionTransformer::toarray($adresse)):"",
            'created_at' => $data->getCreateAt()->getTimestamp(),
            'gps'=> (string) $data->getLocality()->getId()??"",
            'pict'=>$this->normalizePict($data)
        ];
    }

    public function normalizepath($object, string $format = null, array $context = []): array
    {
        if ($object instanceof Board) {
            return [
                'path' => 'show_board',
                'params' => ['slugboard' => $object->getSlug()],
            ];
        }
        throw new \RuntimeException("Can't normalize path");
    }

    public function normalizePict($object, string $format = null, array $context = []): string
    {
        if ($object instanceof Board) {
            if($object->getTemplate()->getLogo()!= null){
                return  '/spaceweb/template/'.$object->getTemplate()->getLogo()->getNamefile();
            }else{
                return  '/img/pinsaffi.png';
            }
        }
        throw new \RuntimeException("Can't normalize path");
    }

    // NEW: indique au Serializer ce que ce normalizer gère
    public function getSupportedTypes(?string $format): array
    {
        // true = cacheable (pas besoin d'appeler supportsNormalization)
        return [Board::class => true, '*' => false];
    }
}
