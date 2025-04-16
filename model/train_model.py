import pandas as pd
from sklearn.feature_extraction.text import TfidfVectorizer
from sklearn.linear_model import LogisticRegression
from sklearn.model_selection import train_test_split
from sklearn.metrics import accuracy_score, classification_report
import joblib
import os

# Load the dataset
df = pd.read_csv(r"C:\xampp\htdocs\model\complaints_dataset_5000_(1).csv")

# Use correct column names
texts = df['text']
labels = df['department']

# Split into training and test sets (80/20)
X_train, X_test, y_train, y_test = train_test_split(texts, labels, test_size=0.2, random_state=42)

# Vectorize text data
vectorizer = TfidfVectorizer()
X_train_vectorized = vectorizer.fit_transform(X_train)
X_test_vectorized = vectorizer.transform(X_test)

# Train classifier
model = LogisticRegression(max_iter=1000)
model.fit(X_train_vectorized, y_train)

# Evaluate model
y_pred = model.predict(X_test_vectorized)
accuracy = accuracy_score(y_test, y_pred)

print(f"\n✅ Model Accuracy: {accuracy * 100:.2f}%")
print("\n📊 Classification Report:")
print(classification_report(y_test, y_pred))

# Save model artifacts
MODEL_OUTPUT_DIR = r"C:\xampp\htdocs\model"
os.makedirs(MODEL_OUTPUT_DIR, exist_ok=True)

joblib.dump(model, os.path.join(MODEL_OUTPUT_DIR, "classifier.pkl"))
joblib.dump(vectorizer, os.path.join(MODEL_OUTPUT_DIR, "vectorizer.pkl"))

print("\n🎉 Model training and saving completed.")
