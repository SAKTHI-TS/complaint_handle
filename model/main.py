from fastapi import FastAPI, Form, UploadFile, File
from fastapi.middleware.cors import CORSMiddleware
from fastapi.staticfiles import StaticFiles
import mysql.connector
from transformers import AutoTokenizer, AutoModelForSequenceClassification
import pandas as pd
import torch
import bcrypt
import os
from datetime import datetime

# --------------------------- FastAPI Setup ---------------------------

app = FastAPI()

# Mount uploads folder to serve files
app.mount("/uploads", StaticFiles(directory="uploads"), name="uploads")

# CORS Middleware
app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],  # Allow all origins
    allow_credentials=True,
    allow_methods=["GET", "POST", "PUT", "DELETE"],
    allow_headers=["*"]
)

# --------------------------- Model Setup ----------------------------

MODEL_DIRECTORY = os.path.join(os.path.dirname(__file__), "trained_model")
tokenizer = None
model = None

def load_model():
    global tokenizer, model
    if tokenizer is None or model is None:
        tokenizer = AutoTokenizer.from_pretrained(MODEL_DIRECTORY)
        model = AutoModelForSequenceClassification.from_pretrained(MODEL_DIRECTORY)

# --------------------------- DB Connection ----------------------------

def connect_to_db():
    return mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="complaint_system",
        port=3306
    )

# --------------------------- Department Mapping ----------------------------

table_map = {
    0: "Water",
    1: "Education",
    2: "Electricity"
}

department_tables = {
    "Water": "complaints",
    "Education": "complaints",
    "Electricity": "complaints"
}

# --------------------------- Basic Routes ----------------------------

@app.get("/")
def index():
    return {"message": "API up and running. Model loaded."}

# --------------------------- Rule-Based Classifier ----------------------------

def simple_rule_classifier(text):
    text = text.lower()
    # Water Department
    if any(term in text for term in [
        "water", "pipeline", "tap", "drainage", "sewage", "leak", 
        "water bill", "water connection", "water meter"
    ]):
        return "Water"
        
    # Education Department
    if any(term in text for term in [
        "school", "college", "university", "teacher", "student", 
        "education", "classroom", "admission", "scholarship"
    ]):
        return "Education"
        
    # Electricity Department
    if any(term in text for term in [
        "electricity", "power", "voltage", "current", "electric",
        "transformer", "wire", "meter", "bulb", "light"
    ]):
        return "Electricity"
    
    return "Water"  # Default department if no match found

# --------------------------- Auth: Register ----------------------------

@app.post("/register")
def register_account(
    full_name: str = Form(...),
    new_user: str = Form(...),
    new_pass: str = Form(...)
):
    db = connect_to_db()
    cur = db.cursor()
    cur.execute("SELECT * FROM users WHERE username = %s", (new_user,))
    if cur.fetchone():
        return {"error": "Username is already taken"}

    hashed_password = bcrypt.hashpw(new_pass.encode(), bcrypt.gensalt()).decode()
    cur.execute("INSERT INTO users (name, username, password) VALUES (%s, %s, %s)", (full_name, new_user, hashed_password))
    db.commit()
    cur.close()
    db.close()
    return {"message": "User registered successfully"}

# --------------------------- Auth: Login ----------------------------

@app.post("/login")
def login_user(user_id: str = Form(...), passcode: str = Form(...)):
    try:
        db = connect_to_db()
        cur = db.cursor(dictionary=True)

        # Try admin login first
        cur.execute("""
            SELECT a.*, d.name as department_name 
            FROM admins a
            JOIN departments d ON a.department_id = d.id
            WHERE a.username = %s
        """, (user_id,))
        admin = cur.fetchone()

        if admin and bcrypt.checkpw(passcode.encode(), admin["password"].encode()):
            return {
                "message": "Admin login successful",
                "role": "admin",
                "dashboard": "admin/dashboard.php",
                "department": admin["department_name"],
                "user_id": admin["id"]
            }

        # Try user login
        cur.execute("SELECT * FROM users WHERE email = %s", (user_id,))
        user = cur.fetchone()
        
        if user and bcrypt.checkpw(passcode.encode(), user["password"].encode()):
            return {
                "message": "User login successful",
                "role": "user",
                "dashboard": "user/dashboard.php",
                "user_id": user["id"]
            }

        return {"error": "Invalid login credentials"}

    except Exception as e:
        print(f"Login error: {str(e)}")
        return {"error": "Login failed"}
    finally:
        cur.close()
        db.close()

# --------------------------- Classify Petition ----------------------------

@app.post("/classify")
def predict_category(petition_text: str = Form(...)):
    guessed = simple_rule_classifier(petition_text)
    if guessed:
        return {"category": guessed}

    load_model()
    inputs = tokenizer(petition_text, return_tensors="pt", truncation=True, padding=True)
    with torch.no_grad():
        logits = model(**inputs).logits
    result = torch.argmax(logits, dim=1).item()
    return {"category": table_map.get(result, "Unknown")}

# --------------------------- Submit Petition ----------------------------

@app.post("/submit_to_department")
def save_petition(
    name: str = Form(...),
    phone: str = Form(...),
    address: str = Form(...),
    petition_type: str = Form(...),
    petition_subject: str = Form(...),
    petition_description: str = Form(...),
    category: str = Form(...),
    petition_file: UploadFile = File(None)
):
    try:
        table = department_tables.get(category)
        if not table:
            return {"error": "Invalid category provided"}

        file_name = None
        if petition_file:
            upload_path = "uploads"
            os.makedirs(upload_path, exist_ok=True)
            file_name = datetime.now().strftime("%Y%m%d%H%M%S_") + petition_file.filename
            with open(os.path.join(upload_path, file_name), "wb") as f:
                f.write(petition_file.file.read())

        conn = connect_to_db()
        cursor = conn.cursor()
        insert_query = f"""
            INSERT INTO {table}
            (name, phone, address, petition_type, petition_subject, petition_description, petition_file, status)
            VALUES (%s, %s, %s, %s, %s, %s, %s, %s)
        """
        values = (name, phone, address, petition_type, petition_subject, petition_description, file_name, "Pending")
        cursor.execute(insert_query, values)
        conn.commit()
        cursor.close()
        conn.close()

        return {"message": "Petition recorded successfully", "department": category}

    except mysql.connector.Error as db_err:
        return {"error": f"Database error: {db_err}"}
    except OSError as os_err:
        return {"error": f"File system error: {os_err}"}
    except Exception as ex:
        return {"error": f"Unexpected error: {ex}"}

# --------------------------- Admin View Petitions ----------------------------

@app.get("/admin/petitions")
def list_petitions(department: str):
    table = department_tables.get(department)
    if not table:
        return {"error": "Invalid department requested"}

    db = connect_to_db()
    cur = db.cursor(dictionary=True)
    cur.execute(f"SELECT * FROM {table}")
    result = cur.fetchall()
    cur.close()
    db.close()

    return result

def classify_complaint(text):
    # First try rule-based classification
    rule_result = simple_rule_classifier(text)
    if rule_result:
        return rule_result
    
    try:
        # Use trained model
        load_model()
        inputs = tokenizer(text, return_tensors="pt", truncation=True, padding=True)
        with torch.no_grad():
            logits = model(**inputs).logits
            probs = torch.softmax(logits, dim=1)
            prediction = torch.argmax(logits, dim=1).item()
            confidence = probs[0][prediction].item()
            
            if confidence > 0.6:  # Confidence threshold
                return table_map.get(prediction)
    except Exception as e:
        print(f"Model prediction error: {e}")
    
    # Fallback to default
    return "Water"

@app.post("/submit_complaint")
async def submit_complaint(description: str = Form(...), user_id: int = Form(...)):
    try:
        # Get department using trained model
        department = classify_complaint(description)
        print(f"Classified department: {department}")

        conn = connect_to_db()
        cursor = conn.cursor(dictionary=True)

        # Get department ID
        cursor.execute("SELECT id FROM departments WHERE name = %s", (department,))
        dept_result = cursor.fetchone()
        if not dept_result:
            return {"error": f"Department not found: {department}"}

        # Get first category for department
        cursor.execute("""
            SELECT id FROM complaint_categories 
            WHERE department_id = %s 
            ORDER BY id ASC LIMIT 1
        """, (dept_result['id'],))
        category = cursor.fetchone()

        # Insert complaint
        title = description[:50] + "..." if len(description) > 50 else description
        cursor.execute("""
            INSERT INTO complaints 
            (user_id, category_id, title, description, status)
            VALUES (%s, %s, %s, %s, 'pending')
        """, (user_id, category['id'], title, description))
        
        conn.commit()
        complaint_id = cursor.lastrowid
        
        cursor.close()
        conn.close()

        return {
            "success": True,
            "message": f"Complaint submitted successfully to {department} Department",
            "department": department,
            "complaint_id": complaint_id
        }

    except Exception as ex:
        print(f"Error: {str(ex)}")
        return {"error": f"Error submitting complaint: {str(ex)}"}