from flask import Flask, request, jsonify
import subprocess

app = Flask(__name__)

@app.route('/run', methods=['POST'])
def run_code():
    data = request.get_json()
    code = data.get('code', '')

    try:
        result = subprocess.run(
            ['python3', '-c', code],
            capture_output=True, text=True, timeout=30
        )
        return jsonify({'output': result.stdout, 'error': result.stderr})
    except subprocess.TimeoutExpired:
        return jsonify({'error': 'Execution timed out'})

if __name__ == '__main__':
    app.run(host='0.0.0.0', port=5000)
