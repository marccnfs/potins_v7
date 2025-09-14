<?php

namespace App\Infrastructure\ChabotIA;

interface IndexerInterface
{
    /**
     * Indexe un contenu dans le système de recherche.
     *
     * @param array $data {id: string, title: string, summary : string, content: string, category: string[], info: string, label:string, htlm_titre:string, published_at: int, url:string}
     */
    public function index(array $data): void;

    /**
     * Ajoute un contenu dans le système de recherche.
     *
     * @param array $data {id: string, title: string, summary : string, content: string, category: string[], info: string, label:string, htlm_titre:string, published_at: int, url:string}
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
