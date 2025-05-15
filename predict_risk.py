import pandas as pd
import joblib
import json
import sys

# Load model, scaler, and encoder
model = joblib.load("C:/laragon/www/jam-sms/model/rf_model.joblib")
scaler = joblib.load("C:/laragon/www/jam-sms/model/scaler.joblib")  # ✅ Load the scaler
label_encoder = joblib.load("C:/laragon/www/jam-sms/model/label_encoder.joblib")

# Read JSON input from Laravel
input_json = sys.stdin.read()
input_data = json.loads(input_json)

df = pd.DataFrame(input_data)

# Ensure the correct order of features
ordered_cols = ['studytime', 'failures', 'schoolsup', 'famsup', 'paid',
                'activities', 'absences', 'G1', 'G2', 'G1_G2_diff', 'G_avg']

df = df[ordered_cols]

# Scale the input data ✅
df_scaled = scaler.transform(df)

# Predict class codes
grade_codes = model.predict(df_scaled)

# Convert to letter grades
grades = label_encoder.inverse_transform(grade_codes)

# Define risk mapping
def risk_level(grade):
    if grade in ['F', 'D']:
        return 'High'
    elif grade == 'C':
        return 'Moderate'
    else:
        return 'Low'

risks = [risk_level(grade) for grade in grades]

# Output to Laravel
print(json.dumps(risks))
