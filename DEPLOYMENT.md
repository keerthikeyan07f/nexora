# 🌐 NEXORA AI - Cloud Deployment Guide (Public URL)

This guide provides simple, step-by-step instructions to deploy **NEXORA AI Stock Intelligence** to the cloud and obtain a free live HTTPS URL (e.g., `https://nexora-ai-stock.onrender.com` or `https://nexora.up.railway.app`).

---

## ⚡ Option 1: Render.com (Recommended - 100% Free)

Render provides free SSL-enabled web hosting using the project's included `Dockerfile` and `render.yaml`.

### Step 1: Push Code to GitHub
1. Create a repository on [GitHub](https://github.com/new) named `nexora-stock-prediction`.
2. Push your project code to GitHub:
   ```bash
   git init
   git add .
   git commit -m "Deploy NEXORA AI Stock Intelligence"
   git branch -M main
   git remote add origin https://github.com/YOUR_USERNAME/nexora-stock-prediction.git
   git push -u origin main
   ```

### Step 2: Deploy on Render
1. Log in to [Render.com](https://dashboard.render.com/).
2. Click **New +** -> **Web Service**.
3. Connect your GitHub repository `nexora-stock-prediction`.
4. Select **Docker** runtime (Render will automatically detect the `Dockerfile`).
5. Choose the **Free** instance type.
6. Click **Create Web Service**.

🎉 Render will build your container and give you a public live URL:
`https://nexora-ai-stock.onrender.com`

---

## 🚀 Option 2: Railway.app (Instant Deployment with MySQL)

Railway supports multi-service deployments with integrated MySQL databases.

1. Log in to [Railway.app](https://railway.app/).
2. Click **New Project** -> **Deploy from GitHub repo**.
3. Select your `nexora-stock-prediction` repository.
4. Add a **MySQL** database plugin to your project.
5. Railway will automatically inject the `MYSQL_URL` environment variable into your container.
6. Under Settings -> **Generate Domain**, click to get your public live URL:
   `https://nexora-production.up.railway.app`

---

## 🐳 Option 3: Local Docker Execution

To test the production container locally before deploying to the cloud:

```bash
# Build and run using Docker Compose (includes MySQL database)
docker-compose up --build
```
Access locally at:
- Web Interface: `http://localhost:8080`
- Python ML API: `http://localhost:5000`

---

## 📊 Environment Variables Reference

| Variable | Description | Default |
| :--- | :--- | :--- |
| `FLASK_API_URL` | Microservice URL for AI predictions | `http://127.0.0.1:5000` |
| `DB_HOST` | MySQL hostname | `127.0.0.1` |
| `DB_PORT` | MySQL port | `3306` |
| `DB_NAME` | MySQL database name | `nexora_db` |
| `DB_USER` | Database user | `root` |
| `DB_PASS` | Database password | `""` |
| `DATABASE_URL` | Connection string (auto-parsed) | `mysql://user:pass@host:port/dbname` |
