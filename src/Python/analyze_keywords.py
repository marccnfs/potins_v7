import os
import json
import spacy
import joblib
from analyse_keyword import analyze_and_enrich_with_synonyms
import unidecode
import numpy as np
from sklearn.feature_extraction.text import TfidfVectorizer
from spacy.lang.fr.stop_words import STOP_WORDS
from nltk.corpus import wordnet

# Précharger les ressources globales
nlp = spacy.load("fr_core_news_md")  # Utilisez 'fr_core_news_sm' si les performances sont critiques

# Stop words optimisés
def initialize_stop_words():
    stop_words = list(STOP_WORDS)
    additional_stop_words = ['neuf', 'qu', 'quelqu']
    stop_words.extend(additional_stop_words)
    stop_words = list(set(stop_words))
    normalized_stop_words = [unidecode.unidecode(word.lower()) for word in stop_words]
    return normalized_stop_words

STOP_WORDS_OPTIMIZED = initialize_stop_words()

# Mots-clés prioritaires
PRIORITY_KEYWORDS = {"quand", "où", "comment", "prochain", "prochains", "date", "dates", "pourquoi", "qui"}

# Charger les données une fois
def load_data():
    CURRENT_DIR = os.path.dirname(os.path.abspath(__file__))
    intents_path = os.path.join(CURRENT_DIR, "intents_and_questions.json")
    concepts_path = os.path.join(CURRENT_DIR, "keyword_concepts_data.json")
    model = os.path.join(CURRENT_DIR, "trained_model.pkl")

    with open(intents_path, "r", encoding="utf-8") as f1, open(concepts_path, "r", encoding="utf-8") as f2:
        corpus = [entry["text"] for entry in json.load(f1)]
        keyword_concepts = json.load(f2)

    return corpus, keyword_concepts, model

CORPUS, KEYWORD_CONCEPTS,MODEL_PATH = load_data()

# Charger le modèle et SpaCy
try:
    model = joblib.load(MODEL_PATH)
except FileNotFoundError:
    print(json.dumps({"error": f"Fichier non trouvé : {MODEL_PATH}"}))
    exit(1)

# Initialiser le vectoriseur TF-IDF une seule fois
TFIDF_VECTORIZER = TfidfVectorizer(stop_words=STOP_WORDS_OPTIMIZED)
TFIDF_VECTORIZER.fit(CORPUS)    

def configure_entity_ruler(nlp):
    """
    Configure un EntityRuler pour reconnaître les mots composés comme des entités.
    """
    # Supprime un éventuel EntityRuler existant pour éviter les conflits
    if "custom_ruler" in nlp.pipe_names:
        nlp.remove_pipe("custom_ruler")

    # Ajoute un nouveau EntityRuler
    ruler = nlp.add_pipe("entity_ruler", before="ner", name="custom_ruler")
    patterns = [
        {"label": "KEYWORD", "pattern": "Potins Numériques"},
        {"label": "KEYWORD", "pattern": "intelligence artificielle"},
        {"label": "KEYWORD", "pattern": "prévention des maladies"},
        {"label": "KEYWORD", "pattern": "ateliers en ligne"}
    ]
    ruler.add_patterns(patterns)
    return nlp

nlp = configure_entity_ruler(nlp)


# Ajouter une étape pour détecter les mots composés
def detect_composed_keywords(doc):
    return [ent.text.lower() for ent in doc.ents if ent.label_ == "KEYWORD"]

def refine_final_keywords(final_keywords, question):
    stopwords = {"le", "la", "les", "en", "de", "des", "à", "l'", "du", "?", "-", "'"}
    return [kw for kw in final_keywords if kw not in stopwords]

def filter_keywords(refined_keywords, expected_keywords):
    """
    Filtre les mots parasites des résultats.
    """
    stopwords = {"qui", "quoi", "pourquoi", "comment", "dates", "où", "prochain", "prochains"}
    return [kw for kw in refined_keywords if kw not in stopwords or kw in expected_keywords]

def prioritize_composed_keywords(filtered_keywords):
    """
    Supprime les fragments de mots si un mot-clé composé les inclut déjà.
    """
    composed_keywords = {kw for kw in filtered_keywords if " " in kw}  # Mots composés
    final_keywords = [
        kw for kw in filtered_keywords
        if not any(kw in composed and kw != composed for composed in composed_keywords)
    ]
    return final_keywords

def is_keyword_match(expected, extracted):
    """
    Vérifie si un mot-clé attendu correspond à un mot-clé extrait, avec une tolérance pour les variations mineures.
    """
    return expected in extracted or extracted in expected

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

def analyze_question(question):
    """
    Combine l'approche supervisée et TF-IDF pour améliorer la détection des mots-clés.
    """
   
    doc = nlp(question)
    supervised_keywords = []  # Mots-clés détectés par le modèle supervisé
    temp = []  # Liste temporaire pour grouper les mots consécutifs

    # Étape 1 : Prédiction supervisée
    for token in doc:
        probas = model.predict_proba([token.vector])
        prediction = 1 if probas[0, 1] > 0.5 else 0  # Ajuste le seuil si nécessaire
        if prediction == 1:
            temp.append(token.text.lower())
        elif temp:
            supervised_keywords.append(" ".join(temp))
            temp = []

    if temp:
        supervised_keywords.append(" ".join(temp))

    enriched_result = analyze_and_enrich_with_synonyms(question)
    tfidf_keywords = [item["keyword"] for item in enriched_result["keywords"]]
    
    composed_keywords = detect_composed_keywords(doc)

    final_keywords = list(set(composed_keywords + supervised_keywords + tfidf_keywords))
    refined_final_keywords = refine_final_keywords(final_keywords, question)

    # Étape 4 : Filtrage des mots parasites
    #filtered_keywords = filter_keywords(refined_final_keywords, expected_keywords)

    # Étape 5 : Priorisation des mots composés
    filtered_keywords = prioritize_composed_keywords(refined_final_keywords)

    return  filtered_keywords,



if __name__ == "__main__":
    # Récupère la question passée en argument
    import sys
    if len(sys.argv) < 2:
        print(json.dumps({"error": "Aucune question fournie"}))
        sys.exit(1)

    question = sys.argv[1]
    keywords = analyze_question(question)

    # Retourne les résultats en JSON
    print(json.dumps({"keywords": keywords}))
