import os
import json
import spacy
import joblib
import numpy as np
from sklearn.feature_extraction.text import TfidfVectorizer
from spacy.lang.fr.stop_words import STOP_WORDS
from nltk.corpus import wordnet
from collections import defaultdict
from prometheus_client import start_http_server, Summary
import unidecode
import time



# Crée un résumé pour mesurer les temps d'exécution
REQUEST_TIME = Summary('python_request_processing_seconds', 'Temps d\'analyse des mots-clés')

PRIORITY_KEYWORDS = {"quand", "où", "comment", "prochain", "prochains", "date", "dates", "pourquoi", "qui"}
STOPWORDS = {"le", "la", "les", "en", "de", "des", "à", "l'", "du", "?", "-", "'"}
CURRENT_DIR = os.path.dirname(os.path.abspath(__file__))

class ResourceManager:
    _instance = None

    def __new__(cls):
        if cls._instance is None:
            cls._instance = super(ResourceManager, cls).__new__(cls)
            cls._instance._initialize_resources()
        return cls._instance

    def _initialize_resources(self):
        self.nlp = spacy.load("fr_core_news_md")
        self.model = joblib.load(os.path.join(CURRENT_DIR, "trained_model.pkl"))
        with open(os.path.join(CURRENT_DIR, "keyword_concepts_data.json"), "r", encoding="utf-8") as f:
            self.concepts = json.load(f)

        # Entraîner le TF-IDF sur les mots-clés des concepts
        corpus = [entry["keyword"] for entry in self.concepts]
        self.tfidf_vectorizer = TfidfVectorizer(stop_words=list(STOP_WORDS))
        self.tfidf_vectorizer.fit(corpus)   

    def get_nlp(self):
        return self.nlp

    def get_model(self):
        return self.model

    def get_concepts(self):
        return self.concepts
    
    def get_tfidf_vectorizer(self):
        return self.tfidf_vectorizer

resources = ResourceManager()

# Recherche de synonymes avec WordNet
def find_synonyms(word):
    synonyms = set()
    for syn in wordnet.synsets(word):
        for lemma in syn.lemmas():
            synonyms.add(lemma.name().lower())
    return list(synonyms)

# Détection des mots composés
def detect_composed_keywords(doc,concepts):
    """
    Détecte explicitement les mots composés définis dans les concepts.
    """
    concept_keywords = {entry["keyword"].lower() for entry in concepts}
    composed_keywords = [
        ent.text.lower() for ent in doc.ents if ent.text.lower() in concept_keywords
    ]
    return composed_keywords

metadata_cache = {}
synonyms_cache = {}
# Enrichir les mots-clés avec métadonnées et synonymes
def enrich_keywords_with_metadata(keywords, concepts):
    enriched_keywords = []
    concepts_map = {entry["keyword"].lower(): entry for entry in concepts}

    for keyword in keywords:
        # Cache pour les métadonnées
        if keyword.lower() not in metadata_cache:
            metadata_cache[keyword.lower()] = concepts_map.get(keyword.lower(), {"definition": "Aucune correspondance trouvée", "concepts": []})

        metadata = metadata_cache[keyword.lower()]

        # Cache pour les synonymes
        if keyword.lower() not in synonyms_cache:
            synonyms_cache[keyword.lower()] = find_synonyms(keyword)

        synonyms = synonyms_cache[keyword.lower()]

        unique_concepts = list(set(metadata.get("concepts", [])))
        enriched_keywords.append({
            "keyword": keyword,
            "definition": metadata.get("definition", ""),
            "concepts": unique_concepts,
            "synonyms": synonyms,
        })

    return enriched_keywords

@REQUEST_TIME.time()
# Analyse de la question (pipeline unifié)
def analyze_question(question):
    start_time = time.time()

    resources = ResourceManager()
    load_time = time.time()
    nlp = resources.get_nlp()
    model = resources.get_model()
    concepts = resources.get_concepts()
    tfidf_vectorizer = resources.get_tfidf_vectorizer()
    """
    Analyse la question pour retourner les mots-clés simples, enrichis, et associés à des concepts.
    """
    doc = nlp(question)
    all_scores = defaultdict(float)  # Stocke les scores pour chaque mot-clé
    processing_time = time.time()

    # Détection des mots composés
    composed_keywords = detect_composed_keywords(doc,concepts)

    # Ajout : Prédiction groupée
    token_vectors = [token.vector for token in doc]
    probas = model.predict_proba(token_vectors)

    # Calcul des scores supervisés
    for i, token in enumerate(doc):
        score = probas[i, 1]
        if score > 0.5:
            all_scores[token.text.lower()] += score

    # TF-IDF avec scores
    response_vector = tfidf_vectorizer.transform([question])
    scores = response_vector.toarray().flatten()
    feature_names = tfidf_vectorizer.get_feature_names_out()
    for i, score in enumerate(scores):
        if score > 0:
            all_scores[feature_names[i]] += score

    # Assigner un score explicite aux mots composés
    for composed in composed_keywords:
        # Moyenne des scores des composants
        components = composed.split()
        composed_score = sum(all_scores.get(comp, 0) for comp in components) / len(components)
        all_scores[composed] = max(all_scores.get(composed, 0), composed_score)
        
    # Supprimer les composants individuels des mots composés
    filtered_scores = {
        kw: score for kw, score in all_scores.items()
        if not any(kw in composed and kw != composed for composed in composed_keywords)
    }

    # Combiner les mots simples et composés
    all_keywords = list(set(composed_keywords + list(filtered_scores.keys())))
    filtered_keywords = [kw for kw in all_keywords if kw not in STOPWORDS]

    # Enrichissement final
    enriched_keywords = enrich_keywords_with_metadata(filtered_keywords,concepts)

    # Tri des mots-clés par score
    sorted_keywords = sorted(
        [{"keyword": kw, "score": all_scores.get(kw, 0)} for kw in filtered_keywords],
        key=lambda x: x["score"], reverse=True
    )
    extraction_time = time.time()

    print(f"Temps total : {extraction_time - start_time:.2f}s")
    print(f"Temps de chargement : {load_time - start_time:.2f}s")
    print(f"Temps de traitement : {processing_time - load_time:.2f}s")
    print(f"Temps d'extraction : {extraction_time - processing_time:.2f}s")

    return {
        "keywords": sorted_keywords,  # Liste des mots-clés triés avec leurs scores
        "enriched_keywords": enriched_keywords  # Liste enrichie avec définition et concepts
    }
pass

if __name__ == "__main__":
    import sys
    if len(sys.argv) < 2:
        print(json.dumps({"error": "Aucune question fournie"}))
        sys.exit(1)

    question = sys.argv[1]
    result = analyze_question(question)

    # Retourne une chaîne JSON propre
    print(json.dumps(result, ensure_ascii=False, indent=2))
