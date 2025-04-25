from flask import Flask, request, jsonify
from flask_cors import CORS  # Enables cross-origin requests
import joblib  # For loading ML models
import numpy as np

app = Flask(__name__)
CORS(app)  # Allow requests from your PHP web app

# Load your ML model (Example: model.pkl)
model = joblib.load("model.pkl")  # Ensure model.pkl is in the same directory

@app.route('/predict', methods=['POST'])
def predict():
    try:
        # Get JSON data sent from frontend
        data = request.json
        features = np.array(data['features']).reshape(1, -1)  # Convert to NumPy array

        # Make prediction
        prediction = model.predict(features)

        # Send response
        return jsonify({"prediction": int(prediction[0])})
    
    except Exception as e:
        return jsonify({"error": str(e)}), 400

if __name__ == '__main__':
    app.run(debug=True, port=5000)  # Runs on localhost:5000
