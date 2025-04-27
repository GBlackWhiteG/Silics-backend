FROM python:3.12-slim
WORKDIR /var/www

RUN apt-get update && apt-get install -y \
    python3-pip && \
    apt-get clean

COPY code-share/python/requirements.txt ./

RUN pip install -r requirements.txt

COPY code-share/python/ ./

CMD ["python", "/var/www/code/python/index.py"]
