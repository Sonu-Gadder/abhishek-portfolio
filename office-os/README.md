# Office OS (Enterprise Structure)

## Frontend API URL update

Use:

```js
const API_URL = 'api/v1.php';

fetch(`${API_URL}?action=intelligent_fetch`, {
  credentials: 'include'
});
```

## Password hashing reminder

```php
$password = password_hash('admin123', PASSWORD_DEFAULT);
```

Do not store plaintext passwords.
