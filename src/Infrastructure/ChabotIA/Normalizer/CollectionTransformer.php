<?php


namespace App\Infrastructure\ChabotIA\Normalizer;


class CollectionTransformer
{
    public static function toarray( $collection): array
    {
        $tabtag=[];
        foreach ($collection as $tag){
            $tabtag[]=$tag;
        }
        return $tabtag;
    }
}