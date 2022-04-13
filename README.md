# exaple-email-client

PHP webmail client that uses [EmailEngine](https://emailengine.app) to list and view emails.

### Features

- List registered email accounts
- Browse folders and emails
- View emails
- Download attachments

---

**Screenshot 1: Account listing**

![](https://cldup.com/8XmKK1OWMd.png)

**Screenshot 2: Message listing**

![](https://cldup.com/KS5K_u19Gc.png)

**Screenshot 3: Message view**

![](https://cldup.com/-lM3nzgRml.png)

---

### 1\. Setup

Requires the following environment variables:

- **EE_API_TOKEN** is the API token for EmailEngine requests, eg "f4dbbb8cfd9241fa510..."
- **EE_BASE_URL** is the EmailEngines origin, eg "https://emailengine.srv.dev/" or "http://127.0.0.1:3000/"

### 2\. Use Composer to install the dependencies

```
$ cd htdocs
$ composer install
```

### 3\. Run

Make sure that the _htdocs_ folder is the document root for a PHP virtual host and then navigate to that web page.

### License

**MIT**
