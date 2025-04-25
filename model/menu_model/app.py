from flask import Flask, request, jsonify, render_template
from flask_cors import CORS  
import pickle
import numpy as np
import pandas as pd
import os

# Flask setup
app = Flask(__name__, template_folder='templates')
CORS(app)  

# Load models
try:
    with open(r'C:/xampp/htdocs/AshesiSmartDiner/model/menu_model/hallmark_model.pkl', 'rb') as f:
        hallmark_model, hallmark_scaler, hallmark_encoder, hallmark_label_encoders = pickle.load(f)

    with open(r'C:/xampp/htdocs/AshesiSmartDiner/model/menu_model/akorno_model.pkl', 'rb') as f:
        akorno_model, akorno_scaler, akorno_encoder, akorno_label_encoders = pickle.load(f)

    with open(r'C:/xampp/htdocs/AshesiSmartDiner/model/menu_model/munchies_model.pkl', 'rb') as f:
        munchies_model, munchies_scaler, munchies_encoder, munchies_label_encoders = pickle.load(f)

except FileNotFoundError:
    print("Error: Model pickle files not found. Check paths!")
    exit()

# Mappings
day_mapping = {'Monday': 0, 'Tuesday': 1, 'Wednesday': 2, 'Thursday': 3, 'Friday': 4, 'Saturday': 5, 'Sunday': 6}
cafeteria_mapping = {"Akorno Services Ltd - Main Cafe": 0, "Hallmark": 1, "Munchies Services Ltd": 2}
meal_period_mapping = {"Breakfast": 0, "Lunch": 1, "Dinner": 2}

@app.route('/')
def home():
    return render_template('index.html')

@app.route('/predict', methods=['POST']) 
def predict():
    try:
        if not request.form:
            return jsonify({'error': 'No form data received'}), 400

        cafreteria_input = request.form.get('cafreteria')  
        day_of_week_input = request.form.get('day_of_week')
        meal_period_input = request.form.get('meal_period')

        if not cafreteria_input or not day_of_week_input or not meal_period_input:
            return jsonify({'error': 'Missing required fields'}), 400

        cafreteria = cafeteria_mapping.get(cafreteria_input)
        day_of_week = day_mapping.get(day_of_week_input)
        meal_period = meal_period_mapping.get(meal_period_input)

        if None in (cafreteria, day_of_week, meal_period):
            return jsonify({'error': 'Invalid input values'}), 400

        model_dict = {
            "Hallmark": (hallmark_model, hallmark_scaler, hallmark_encoder, hallmark_label_encoders),
            "Akorno Services Ltd - Main Cafe": (akorno_model, akorno_scaler, akorno_encoder, akorno_label_encoders),
            "Munchies Services Ltd": (munchies_model, munchies_scaler, munchies_encoder, munchies_label_encoders)
        }

        model, scaler, encoder, label_encoders = model_dict.get(cafreteria_input, (None, None, None, None))

        if not model:
            return jsonify({'error': 'Invalid cafeteria name'}), 400

        input_data = pd.DataFrame({'cafetreria': [cafreteria], 'day_of_week': [day_of_week], 'meal_period': [meal_period]})
        scaled_features = scaler.transform(input_data)

        prediction_probabilities = model.predict_proba(scaled_features)
        top_indices = np.argsort(prediction_probabilities[0])[-3:][::-1]

        label_encoder = label_encoders.get('product', None)
        if label_encoder:
            top_predictions = label_encoder.inverse_transform(top_indices)
        else:
            return jsonify({'error': 'Label encoder not found'}), 500

        return jsonify({"predictions": top_predictions.tolist()})

    except Exception as e:
        return jsonify({"error": str(e)}), 500

if __name__ == "__main__":
    app.run(debug=True)
