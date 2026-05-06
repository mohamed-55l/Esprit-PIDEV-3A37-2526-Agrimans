import sys
import json

try:
    from textblob import TextBlob
    from deep_translator import GoogleTranslator
except ImportError:
    print(json.dumps({"error": "Veuillez installer textblob et deep-translator : pip install textblob deep-translator"}))
    sys.exit(1)

def analyze_sentiment(text):
    try:
        if not text or len(text.strip()) == 0:
            return {"sentiment": "Neutre", "score": 0.0}
            
        # 1. Traduire le texte (Arabe Tunisien / Français -> Anglais) car TextBlob est meilleur en Anglais
        translated_text = GoogleTranslator(source='auto', target='en').translate(text)
        
        # 2. Utiliser le modèle ML de TextBlob
        blob = TextBlob(translated_text)
        polarity = blob.sentiment.polarity
        
        # 3. Classifier le sentiment
        if polarity >= 0.1:
            sentiment = 'Positif'
        elif polarity <= -0.1:
            sentiment = 'Négatif'
        else:
            sentiment = 'Neutre'
            
        return {
            "sentiment": sentiment, 
            "score": round(polarity, 2), 
            "translated_text": translated_text
        }
    except Exception as e:
        return {"error": str(e), "sentiment": "Neutre"}

if __name__ == "__main__":
    if len(sys.argv) > 1:
        # Recuperer le texte envoye depuis PHP/Symfony
        input_text = sys.argv[1]
        result = analyze_sentiment(input_text)
        print(json.dumps(result))
    else:
        print(json.dumps({"error": "Aucun texte fourni."}))
