# SSL / HTTPS Setup Guide

This directory is where SSL certificate files would be placed for local HTTPS development.

## Current Production Setup

The production application on Render already has **automatic SSL/HTTPS** enabled. Render provides free TLS certificates for all services. No additional configuration needed.

**Live URL**: `https://waste-management-tu84.onrender.com/`

---

## Option 1: Render SSL (Current — Already Active ✅)

Render automatically:
- Provides free Let's Encrypt SSL certificates
- Handles HTTPS termination at their load balancer
- Redirects HTTP → HTTPS automatically
- Renews certificates before expiry

**No action needed** — this is already working on your deployed application.

---

## Option 2: Let's Encrypt with Certbot (Self-Hosted)

If you deploy on your own server (VPS, cloud VM):

```bash
# 1. Install Certbot
sudo apt install certbot python3-certbot-nginx

# 2. Obtain certificate
sudo certbot --nginx -d your-domain.com

# 3. Auto-renewal (Certbot adds this automatically)
sudo certbot renew --dry-run
```

### Docker with Let's Encrypt

```bash
# Add certbot service to docker-compose.yml:
certbot:
  image: certbot/certbot
  volumes:
    - ./docker/nginx/ssl:/etc/letsencrypt
    - ./public:/var/www/html/public
  command: certonly --webroot -w /var/www/html/public -d your-domain.com --agree-tos --email your@email.com
```

Then update `docker/nginx-docker.conf` to:
1. Uncomment the SSL server block
2. Update the certificate paths
3. Enable the HTTP → HTTPS redirect

---

## Option 3: Cloudflare SSL (Free)

1. Sign up at [cloudflare.com](https://www.cloudflare.com/)
2. Add your domain to Cloudflare
3. Update your domain's nameservers to Cloudflare
4. In Cloudflare Dashboard → SSL/TLS:
   - Set mode to **Full (Strict)**
   - Enable **Always Use HTTPS**
   - Enable **Automatic HTTPS Rewrites**

Benefits:
- Free SSL certificate
- DDoS protection
- CDN for static assets
- Performance optimization

---

## Option 4: Self-Signed Certificate (Development Only)

For local Docker development:

```bash
# Generate self-signed certificate (valid for 365 days)
openssl req -x509 -nodes -days 365 -newkey rsa:2048 \
  -keyout docker/nginx/ssl/key.pem \
  -out docker/nginx/ssl/cert.pem \
  -subj "/CN=localhost"
```

Then update `docker/nginx-docker.conf` to use these certificate files.

> ⚠️ **Warning**: Self-signed certificates will show browser security warnings. Use only for development.

---

## Certificate File Placement

Place your certificate files in this directory:

```
docker/nginx/ssl/
├── cert.pem          # SSL certificate
├── key.pem           # Private key
└── README.md         # This file
```

> 🔒 **Security**: Never commit real SSL certificates to Git. Add `*.pem` to `.gitignore`.
