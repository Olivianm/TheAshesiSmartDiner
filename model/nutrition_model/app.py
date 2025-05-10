import os
from flask import Flask, request, jsonify, render_template
from flask_cors import CORS
import joblib
import numpy as np
from tensorflow.keras.models import load_model

# Disable oneDNN optimization warnings
os.environ['TF_ENABLE_ONEDNN_OPTS'] = '0'

# Initialize Flask app with correct template folder
app = Flask(__name__, 
            template_folder=os.path.join(os.path.dirname(os.path.abspath(__file__)), 'templates'))

CORS(app)

# Debug verification
print(f"Current directory: {os.path.dirname(os.path.abspath(__file__))}")
print(f"Template directory: {app.template_folder}")
print(f"Template contents: {os.listdir(app.template_folder) if os.path.exists(app.template_folder) else 'Directory not found'}")

# Load the model and tools
model_path = os.path.join(os.path.dirname(os.path.abspath(__file__)), 'nutrition_model.h5')
label_encoder_path = os.path.join(os.path.dirname(os.path.abspath(__file__)), 'label_encoder.pkl')
scaler_path = os.path.join(os.path.dirname(os.path.abspath(__file__)), 'scaler.pkl')

try:
    model = load_model(model_path, compile=False)  # Compile False avoids the mse bug
    label_encoder = joblib.load(label_encoder_path)
    scaler = joblib.load(scaler_path)
    
    # Load normalized label classes for validation
    label_classes_normalized = [label.lower() for label in label_encoder.classes_]
    print("Model and preprocessing tools loaded successfully")
except Exception as e:
    print(f"Error loading model files: {str(e)}")
    model = None
    label_encoder = None
    scaler = None

@app.route('/')
def home():
    try:
        return render_template('index.html')
    except Exception as e:
        return f"""
        <h1>Template Error</h1>
        <p>{str(e)}</p>
        <p>Looking in: {app.template_folder}</p>
        <p>Contents: {os.listdir(app.template_folder) if os.path.exists(app.template_folder) else 'Directory not found'}</p>
        """

@app.route('/predict', methods=['POST'])
def predict():
    if not all([model, label_encoder, scaler]):
        return jsonify({'error': 'Model or pre-processing tools not loaded properly'})

    data = request.get_json()
    food_name = data.get('food', '').strip().lower()

    if not food_name:
        return jsonify({'error': 'No food name provided'})

    if food_name not in label_classes_normalized:
        # Suggest similar matches
        similar = [food for food in label_classes_normalized if food_name in food][:3]
        return jsonify({
            'error': f"'{food_name}' not found in database",
            'similar': similar
        })

    try:
        # Debugging: Print inputs
        print(f"Food Name: {food_name}")

        # Encode food name and check shape
        food_encoded = label_encoder.transform([food_name])
        print(f"Encoded Food: {food_encoded}")

        food_features = np.array(food_encoded).reshape(1, -1)
        print(f"Food Features Shape: {food_features.shape}")

        # Get prediction and apply scaler
        prediction_scaled = model.predict(food_features)
        print(f"Prediction Scaled: {prediction_scaled}")

        prediction = scaler.inverse_transform(prediction_scaled)
        print(f"Prediction After Scaling: {prediction}")

        # Process the nutrients
        nutrients = {
            col: round(float(val), 4)
            for col, val in zip([  # Nutrient names
                'Water (g)', 'Protein (g)', 'Fat (g)', 'Total carbohydrate (g)',
                'Cholesterol (mg)', 'Phytosterols (mg)', 'SFA (g)', 'MUFA (g)', 'PUFA (g)'
            ], prediction[0])
        }

        return jsonify({
            'food': food_name,
            'nutrients': nutrients
        })

    except Exception as e:
        # Log and return the error message
        print(f"Error during prediction: {str(e)}")
        return jsonify({'error': str(e)})

if __name__ == '__main__':
    app.run(debug=True, host="0.0.0.0")
