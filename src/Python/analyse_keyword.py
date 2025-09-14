import os
import json
import unidecode
import numpy as np
import spacy
from sklearn.feature_extraction.text import TfidfVectorizer
from spacy.lang.fr.stop_words import STOP_WORDS
from nltk.corpus import wordnet


# Précharger les ressources globales
nlp = spacy.load("fr_core_news_md")  # Utilisez 'fr_core_news_sm' si les performances sont critiques

# Stop words optimisés
def initialize_stop_words():
    stop_words = list(STOP_WORDS)
    additional_stop_words = ['neuf', 'qu', 'quelqu',"le", "la", "les", "en", "de", "des", "à", "l'", "du", "un", "une",
    "et", "ou", "mais", "ce", "cet", "cette", "ces", "ne", "pas", "que", "qui"]
    stop_words.extend(additional_stop_words)
    stop_words = list(set(stop_words))
    normalized_stop_words = [unidecode.unidecode(word.lower()) for word in stop_words]
    return normalized_stop_words

STOP_WORDS_OPTIMIZED = initialize_stop_words()

# Mots-clés prioritaires
PRIORITY_KEYWORDS = {"quand", "où", "comment", "prochain", "prochains", "date", "dates", "pourquoi", "qui"}

# Charger les données une fois
def load_data():
    script_dir = os.path.dirname(os.path.abspath(__file__))
    intents_path = os.path.join(script_dir, "intents_and_questions.json")
    concepts_path = os.path.join(script_dir, "keyword_concepts_data.json")

    with open(intents_path, "r", encoding="utf-8") as f1, open(concepts_path, "r", encoding="utf-8") as f2:
        corpus = [entry["text"] for entry in json.load(f1)]
        keyword_concepts = json.load(f2)

    return corpus, keyword_concepts

CORPUS, KEYWORD_CONCEPTS = load_data()

# Initialiser le vectoriseur TF-IDF une seule fois
TFIDF_VECTORIZER = TfidfVectorizer(stop_words=STOP_WORDS_OPTIMIZED)
TFIDF_VECTORIZER.fit(CORPUS)

# Enrichir les mots-clés avec métadonnées
def enrich_keywords_with_metadata(keywords):
    enriched_keywords = []
    concepts_map = {entry["keyword"].lower(): entry for entry in KEYWORD_CONCEPTS}

    for keyword in keywords:
        metadata = concepts_map.get(keyword.lower(), {"definition": "Aucune correspondance trouvée", "concepts": []})
        enriched_keywords.append({
            "keyword": keyword,
            "definition": metadata.get("definition", ""),
            "concepts": metadata.get("concepts", []),
        })

    return enriched_keywords

# Récupérer les synonymes
def get_synonyms(keyword):
    synonyms = set()

    # WordNet
    for syn in wordnet.synsets(keyword, lang="fra"):
        for lemma in syn.lemmas(lang="fra"):
            synonyms.add(lemma.name().lower())

    # Concepts associés
    for entry in KEYWORD_CONCEPTS:
        if keyword.lower() == entry["keyword"].lower() or keyword in entry["concepts"]:
            synonyms.update([concept.lower() for concept in entry.get("concepts", [])])

    synonyms.discard(keyword.lower())
    return list(synonyms)

# Pipeline principal
def analyze_and_enrich_with_synonyms(question):
    doc = nlp(question)
    keywords_spacy = [token.text.lower() for token in doc if token.pos_ in {"NOUN", "VERB", "ADJ", "ADV", "PROPN"}]
    
    # Ajouter les mots-clés prioritaires
    keywords_priority = [token.text.lower() for token in doc if token.text.lower() in PRIORITY_KEYWORDS]
    keywords_spacy.extend(keywords_priority)

    # Calculer les scores TF-IDF
    response_vector = TFIDF_VECTORIZER.transform([question])
    scores = response_vector.toarray().flatten()
    feature_names = TFIDF_VECTORIZER.get_feature_names_out()
    keywords_with_scores = [(feature_names[i], scores[i]) for i in range(len(scores)) if scores[i] > 0]

    # Trier par score décroissant
    sorted_keywords = sorted(keywords_with_scores, key=lambda x: x[1], reverse=True)

    # Enrichissement
    enriched_keywords = []
    full_vector = doc.vector
    for keyword, tfidf_score in sorted_keywords:
        synonyms = get_synonyms(keyword)
        similarity = nlp(keyword).vector @ full_vector / (np.linalg.norm(nlp(keyword).vector) * np.linalg.norm(full_vector))
        final_score = 0.6 * tfidf_score + 0.3 * similarity + (0.1 if keyword in PRIORITY_KEYWORDS else 0)
        enriched_keywords.append({
            "keyword": keyword,
            "synonyms": synonyms,
            "tfidf_score": tfidf_score,
            "final_score": final_score,
        })

    # Ajouter les mots prioritaires s'ils sont absents
    for priority_word in PRIORITY_KEYWORDS:
        if priority_word not in [kw["keyword"] for kw in enriched_keywords]:
            enriched_keywords.append({
                "keyword": priority_word,
                "synonyms": get_synonyms(priority_word),
                "tfidf_score": 0,
                "final_score": 0.2,
            })    

    return {"keywords": enriched_keywords}

if __name__ == "__main__":
    import sys
    question = " ".join(sys.argv[1:])
    try:
        output = analyze_and_enrich_with_synonyms(question)
        print(json.dumps(output, ensure_ascii=False, indent=2))
    except Exception as e:
        print(json.dumps({"error": str(e)}, ensure_ascii=False))
