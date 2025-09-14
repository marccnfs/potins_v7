<?php


namespace App\Infrastructure\Search\Normalizer;

use App\Encoder\PathEncoder;
use App\Entity\Boards\Board;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class BoardPathNormalizer implements NormalizerInterface
{
    public function normalize($data, string $format = null, array $context = []): array
    {
        if ($data instanceof Board) {
            return [
                'path' => 'show_board',
                'params' => ['board' => $data->getSlug()],
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

    public function supportsNormalization( $data, string $format = null, array $context = []): bool
    {
        return ($data instanceof Board)
            && PathEncoder::FORMAT === $format;
    }

    // NEW: indique au Serializer ce que ce normalizer gère
    public function getSupportedTypes(?string $format): array
    {
        // true = cacheable (pas besoin d'appeler supportsNormalization)
        return [Board::class => true, '*' => false];
    }
}
