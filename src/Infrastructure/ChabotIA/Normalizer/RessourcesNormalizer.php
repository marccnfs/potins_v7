<?php


namespace App\Infrastructure\ChabotIA\Normalizer;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use App\Entity\Ressources\Ressources;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;


class RessourcesNormalizer implements NormalizerInterface
{

    private UrlGeneratorInterface $urlGenerator;

    public function __construct(UrlGeneratorInterface $urlGenerator )
    {
        $this->urlGenerator = $urlGenerator;
    }

    public function getSupportedTypes(?string $format): array
    {
        // false signifie que seules les instances exactes de App\Entity\Ressources sont supportées,
        // true inclurait également toutes les sous-classes.
        return [
            \App\Entity\Ressources\Ressources::class => false
        ];
    }

    public function supportsNormalization($data, string $format = null, array $context = []): bool
    {
        return $data instanceof Ressources && 'search' === $format;
    }

    public function normalize($object, string $format = null, array $context = []): array
    {
        if (!$object instanceof Ressources) {
            throw new \InvalidArgumentException('Unexpected type for normalization, expected Course, got '.get_class($object));
        }

        $url = $this->normalizepath($object);

        return [
            'id' => (string) $object->getId(),
            'title' =>(string) $object->getTitre()|"",
            'summary'=>MarkdownTransformer::toText((string) $object->getComposition())|"",
            'content'=>(string) $object->getDescriptif()|"",
            'category' => array_map(fn ($t) => $t->getName(), CollectionTransformer::toarray($object->getCategorie())),
            'info'=> (string) $object->getInfos()|"",
            'label' => (string) $object->getLabel()|"",
            'htlm_titre'=> (string) $object->getHtmltitre()|"",
            'published_at'=>(string) '',
            'url' => $this->urlGenerator->generate($url['path'], $url['params']),
        ];
    }

    public function normalizepath($object, string $format = null, array $context = []): array
    {
        if ($object instanceof Ressources) {
            return [
                'path' => 'show_ressource',
                'params' => ['id' => $object->getId()],
            ];
        }
        throw new \RuntimeException("Can't normalize path");
    }

}