# API Reference

All Spine endpoints are prefixed with `/api/v1` and require `auth:sanctum` unless marked public.

## Base URL

```
https://your-app.test/api/v1
```

## Authentication

Include token in header:
```
Authorization: Bearer {token}
```

Or use session cookie for web apps.

---

## Auth Endpoints

| Method | Path | Description |
|--------|------|-------------|
| POST | `/auth/login` | Login (consumer-implemented) |
| POST | `/auth/register` | Register (consumer-implemented) |
| GET | `/auth/me` | Current user info |
| PUT | `/auth/me` | Update profile |
| POST | `/auth/logout` | Revoke token |

---

## Settings Endpoints

| Method | Path | Description |
|--------|------|-------------|
| GET | `/settings/schema` | Get settings tabs & fields |
| GET | `/settings/{key}` | Get single setting |
| PUT | `/settings/{key}` | Update single setting |
| DELETE | `/settings/{key}` | Delete setting |
| POST | `/settings/bulk` | Bulk update settings |

---

## Profile Endpoints

| Method | Path | Description |
|--------|------|-------------|
| GET | `/profile/schema` | Get profile tabs & fields |

---

## Activity Log Endpoints

| Method | Path | Description |
|--------|------|-------------|
| GET | `/activity-logs` | List activity logs |
| POST | `/activity-logs` | Create activity log |
| GET | `/activity-logs/{id}` | Get activity log |
| DELETE | `/activity-logs/{id}` | Delete activity log |

---

## Meta Endpoints

| Method | Path | Description |
|--------|------|-------------|
| GET | `/meta/{type}/{id}` | Get all meta for entity |
| POST | `/meta/{type}/{id}` | Create meta |
| GET | `/meta/{type}/{id}/{key}` | Get specific meta |
| PUT | `/meta/{type}/{id}/{key}` | Update meta |
| DELETE | `/meta/{type}/{id}/{key}` | Delete meta |

---

## File Endpoints

| Method | Path | Description |
|--------|------|-------------|
| POST | `/files` | Upload file |
| GET | `/files/{id}` | Get file info |
| GET | `/files/{id}/download` | Download file |
| GET | `/files/{id}/preview` | Preview file |
| DELETE | `/files/{id}` | Delete file |

---

## Mail Endpoints

| Method | Path | Description |
|--------|------|-------------|
| POST | `/mail/send` | Send email |
| POST | `/mail/notify` | Send notification email |
| POST | `/mail/retry` | Retry failed email |
| POST | `/mail/cleanup` | Cleanup old emails |
| GET | `/mail/queue` | Get mail queue |

---

## PDF Endpoints

| Method | Path | Description |
|--------|------|-------------|
| POST | `/pdf/generate` | Generate PDF from data |
| POST | `/pdf/from-html` | Generate PDF from HTML |
| POST | `/pdf/bulk-export` | Bulk export PDFs |

---

## SMS Endpoints

| Method | Path | Description |
|--------|------|-------------|
| POST | `/sms/send` | Send SMS |
| GET | `/sms/drivers` | List SMS drivers |

---

## QR Code Endpoints

| Method | Path | Description |
|--------|------|-------------|
| POST | `/qr-code/generate` | Generate QR code |

---

## Excel Endpoints

| Method | Path | Description |
|--------|------|-------------|
| POST | `/excel/export` | Export to Excel |
| POST | `/excel/import` | Import from Excel |

---

## Tag Endpoints

| Method | Path | Description |
|--------|------|-------------|
| GET | `/tags` | List tags |
| POST | `/tags` | Create tag |
| DELETE | `/tags/{id}` | Delete tag |

---

## Module Endpoints

| Method | Path | Description |
|--------|------|-------------|
| GET | `/modules` | List modules |
| POST | `/modules/install` | Install module from ZIP |
| POST | `/modules/{name}/enable` | Enable module |
| POST | `/modules/{name}/disable` | Disable module |
| DELETE | `/modules/{name}` | Uninstall module |

---

## Menu Endpoints

| Method | Path | Description |
|--------|------|-------------|
| GET | `/menus/sidebar` | Get sidebar menu |
| GET | `/menus/quick-actions` | Get quick actions |
| GET | `/meta/settings-tabs` | Get settings tabs |

---

## Translation Endpoints

| Method | Path | Description |
|--------|------|-------------|
| GET | `/translations/{locale}` | Get translations |

---

## System Endpoints

| Method | Path | Description |
|--------|------|-------------|
| GET | `/health` | Health check |
| GET | `/system/languages` | List languages |

---

## Notification Endpoints

| Method | Path | Description |
|--------|------|-------------|
| GET | `/notifications` | List notifications |
| POST | `/notifications/read` | Mark as read |
| DELETE | `/notifications/{id}` | Delete notification |

---

## GDPR Endpoints

| Method | Path | Description |
|--------|------|-------------|
| GET | `/gdpr/export` | Export user data |
| POST | `/gdpr/anonymize` | Anonymize user data |
| POST | `/gdpr/delete` | Delete user data |

---

## Broadcast Endpoints

| Method | Path | Description |
|--------|------|-------------|
| GET | `/broadcast/config` | Get broadcast config |
| POST | `/broadcast/test` | Test broadcast |

---

## Payment Endpoints

| Method | Path | Description |
|--------|------|-------------|
| GET | `/payment/gateways` | List payment gateways |
| POST | `/payment/intent` | Create payment intent |

---

## Relation Endpoints

| Method | Path | Description |
|--------|------|-------------|
| GET | `/relations/types` | List relation types |
| GET | `/relations/{type}/{id}` | Get relations |

---

## Error Responses

| Status | Meaning |
|--------|---------|
| 401 | Unauthenticated |
| 403 | Forbidden (permission denied) |
| 404 | Not found |
| 422 | Validation error |
| 500 | Internal server error |

```json
{
    "message": "Error message",
    "errors": {
        "field": ["Error detail"]
    }
}
```

---

## Related

- [Installation](./installation.md)
- [Authentication](./authentication.md)
- [Hooks & Events](./hooks.md)
