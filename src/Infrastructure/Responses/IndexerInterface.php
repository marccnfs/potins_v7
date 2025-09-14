<?php

namespace App\Infrastructure\Responses;

interface IndexerInterface
{
    /**
     * Indexe un contenu dans le système de recherche.
     *
     * @param array $data {id: string, keys_find: string, keyword: string, concepts: string, concepts: string[], definition : string,  description : string, date : bool, link : string, score : int, info : string}
     */
    public function index(array $data): void;

    /**
     * Ajoute un contenu dans le système de recherche.
     *
     * @param array $data {id: string, keys_find: string, keyword: string, concepts: string, concepts: string[], definition : string,  description : string, date : bool, link : string, score : int, info : string}
     */
    public function indexOne(array $data): void;

    /**
     * Supprime un contenu de l'index.
     */
    public function remove(string $id): void;

    /**
     * Vide l'index de toutes données.
     */
    public function clean(): void;
}
