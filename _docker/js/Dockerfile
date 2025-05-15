FROM node:20-slim
WORKDIR /var/www

COPY code-share/js/package.json ./

RUN npm install

COPY code-share/js/ ./

CMD ["node", "/var/www/code/js/index.js"]
