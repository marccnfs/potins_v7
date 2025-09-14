<?php


namespace Command;

use App\Event\PotinCreatedEvent;
use App\Infrastructure\Search\Typesense\TypesenseIndexer;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class IndexPotinsSubscriber implements EventSubscriberInterface
{


    // todo mis ici en attendant de remettre a jour dans le dossier eventlistener pour indexer les potins


    private TypesenseIndexer $indexer;
    private NormalizerInterface $normalizer;

    public function __construct(
        TypesenseIndexer $indexer,
        NormalizerInterface $normalizer,
    )
    {
        $this->indexer = $indexer;
        $this->normalizer = $normalizer;
    }

    public static function getSubscribedEvents(): array
    {
        // return the subscribed events, their methods and priorities
        return [
                PotinCreatedEvent::CREATE => 'indexNewPotin',
                PotinCreatedEvent::MAJ => 'majNewPotin',
            ];
    }

    /**
     * @throws ExceptionInterface
     */
    public function indexNewPotin(PotinCreatedEvent $event): void
    {
        $data=$this->normalizer->normalize($event->getPotin(), 'search');
        $this->indexer->indexOne((array) $data);
    }

    /**
     * @throws ExceptionInterface
     */
    public function majNewPotin(PotinCreatedEvent $event): void
    {
        $data=$this->normalizer->normalize($event->getPotin(), 'search');
        $this->indexer->indexOne((array) $data);
    }

}