# 📧 Guide de Dépannage - Emails Gmail Non Reçus

## ✅ Problèmes Résolus

- ✓ Configuration MAILER_DSN corrigée
- ✓ Service email paramétré correctement  
- ✓ Template HTML OTP créée
- ✓ Email se soumet sans erreur

## ❌ Problèmes à Vérifier

### 1. **Gmail App Password** (Solution Principale)
Gmail reject les connexions avec le mot de passe normal si 2FA est activé.

👉 **Solution:**
1. Allez à: https://myaccount.google.com/
2. Cliquez sur "Sécurité" (left menu)
3. Activez "Authentification 2-Facteurs" (si pas fait)
4. Cherchez "Mots de passe d'application"
5. Créez un mot de passe d'application pour "Mail" + "Windows"
6. Remplacez le mot de passe dans `.env`:

```env
MAILER_DSN=smtp://zidisamir993@gmail.com:YOUR_APP_PASSWORD@smtp.gmail.com:587?encryption=tls&auth_mode=login
```

### 2. **Gmail Security Settings**
Si 2FA n'est pas activé, Gmail peut bloquer.

👉 **Solution:**
1. Allez à: https://myaccount.google.com/lesssecureapps
2. Basculez "Accès des applications moins sécurisées" à ON

### 3. **Check Spam/Promotions**
L'email peut arriver en spam.

👉 **Solution:**
- Vérifiez vos dossiers "Spam" et "Promotions"
- Allez sur https://mail.google.com/mail/u/0/#spam

### 4. **Firewall/Port Issues**
Le port 587 peut être bloqué par votre réseau.

👉 **Solution:**
Essayez le port 465 (SSL instead of TLS):

```env
MAILER_DSN=smtp://zidisamir993@gmail.com:xjrwwwntzgaemior@smtp.gmail.com:465?encryption=ssl&auth_mode=login
```

## 🧪 Test Commands

### Test Email Envoi:
```bash
php bin/console app:test-email votreemail@gmail.com
```

### Voir les Logs:
```bash
tail -f var/log/dev.log
```

## 📝 Vérification du Fichier .env

Assurez-vous que votre `.env` à la bonne ligne MAILER_DSN:

```env
###> symfony/mailer ###
MAILER_DSN=smtp://zidisamir993@gmail.com:xjrwwwntzgaemior@smtp.gmail.com:587?encryption=tls&auth_mode=login
###< symfony/mailer ###
```

⚠️ IL NE DOIT Y AVOIR QU'UNE SEULE MAILER_DSN LINE!

## 🔗 Resources
- https://support.google.com/accounts/answer/185833
- https://support.google.com/accounts/answer/6010255
- https://symfony.com/doc/current/mailer.html
