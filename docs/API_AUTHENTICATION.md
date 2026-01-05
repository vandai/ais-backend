# AIS Member Authentication API Documentation

## Overview

This API provides authentication services for the AIS Member application using Laravel Sanctum with SPA (cookie-based) authentication. The API is designed to work with frontend applications (e.g., Next.js) running on `localhost:3000`.

**Base URL:** `http://localhost:8000`

---

## Authentication Flow

### SPA Authentication (Cookie-Based)

1. **Get CSRF Token** - Call `/sanctum/csrf-cookie` to receive XSRF-TOKEN cookie
2. **Authenticate** - Use `/api/login` or `/api/register` with the XSRF token
3. **Access Protected Routes** - Include credentials and XSRF token in subsequent requests
4. **Logout** - Call `/api/logout` to end the session

### Required Headers

All API requests should include:

```
Accept: application/json
Content-Type: application/json
X-XSRF-TOKEN: <token-from-cookie>
```

### Credentials

All requests must include credentials to maintain session:

```javascript
credentials: 'include'  // fetch API
withCredentials: true   // axios
```

---

## Endpoints

### 1. Get CSRF Cookie

Initialize CSRF protection before making authentication requests.

**Endpoint:** `GET /sanctum/csrf-cookie`

**Authentication Required:** No

**Request:**
```http
GET /sanctum/csrf-cookie HTTP/1.1
Host: localhost:8000
Accept: application/json
```

**Response:**
- Status: `204 No Content`
- Sets `XSRF-TOKEN` cookie

**Example (JavaScript):**
```javascript
await fetch('http://localhost:8000/sanctum/csrf-cookie', {
  credentials: 'include',
});
```

---

### 2. Register

Create a new user account.

**Endpoint:** `POST /api/register`

**Authentication Required:** No

**Request Body:**

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `name` | string | Yes | User's full name (max 255 chars) |
| `email` | string | Yes | Valid email address (unique) |
| `password` | string | Yes | Password (min 8 chars) |
| `password_confirmation` | string | Yes | Must match password |

**Request:**
```json
{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "securepassword123",
  "password_confirmation": "securepassword123"
}
```

**Success Response:**
- Status: `201 Created`

```json
{
  "message": "Registration successful",
  "user": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "role": ["guest"],
    "email_verified_at": null,
    "created_at": "2026-01-05T10:00:00.000000Z",
    "updated_at": "2026-01-05T10:00:00.000000Z"
  }
}
```

**Error Response:**
- Status: `422 Unprocessable Entity`

```json
{
  "message": "The email has already been taken.",
  "errors": {
    "email": ["The email has already been taken."]
  }
}
```

---

### 3. Login

Authenticate an existing user.

**Endpoint:** `POST /api/login`

**Authentication Required:** No

**Request Body:**

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `email` | string | Yes | User's email address |
| `password` | string | Yes | User's password |
| `remember` | boolean | No | Keep user logged in (default: false) |

**Request:**
```json
{
  "email": "john@example.com",
  "password": "securepassword123",
  "remember": true
}
```

**Success Response:**
- Status: `200 OK`

```json
{
  "message": "Login successful",
  "user": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "role": ["guest"],
    "email_verified_at": "2026-01-05T10:00:00.000000Z",
    "created_at": "2026-01-05T10:00:00.000000Z",
    "updated_at": "2026-01-05T10:00:00.000000Z"
  }
}
```

**Error Responses:**

Invalid credentials (Status: `422`):
```json
{
  "message": "These credentials do not match our records.",
  "errors": {
    "email": ["These credentials do not match our records."]
  }
}
```

Invalid role (Status: `422`):
```json
{
  "message": "Your account does not have the required role to access this application.",
  "errors": {
    "email": ["Your account does not have the required role to access this application."]
  }
}
```

---

### 4. Logout

End the authenticated session.

**Endpoint:** `POST /api/logout`

**Authentication Required:** Yes

**Request:**
```http
POST /api/logout HTTP/1.1
Host: localhost:8000
Accept: application/json
X-XSRF-TOKEN: <token>
```

**Success Response:**
- Status: `200 OK`

```json
{
  "message": "Logout successful"
}
```

**Error Response:**
- Status: `401 Unauthorized`

```json
{
  "message": "Unauthenticated."
}
```

---

### 5. Get Current User

Retrieve the authenticated user's information.

**Endpoint:** `GET /api/user`

**Authentication Required:** Yes

**Request:**
```http
GET /api/user HTTP/1.1
Host: localhost:8000
Accept: application/json
X-XSRF-TOKEN: <token>
```

**Success Response:**
- Status: `200 OK`

```json
{
  "user": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "role": ["member"],
    "email_verified_at": "2026-01-05T10:00:00.000000Z",
    "created_at": "2026-01-05T10:00:00.000000Z",
    "updated_at": "2026-01-05T10:00:00.000000Z"
  }
}
```

**Error Response:**
- Status: `401 Unauthorized`

```json
{
  "message": "Unauthenticated."
}
```

---

### 6. Forgot Password

Request a password reset link.

**Endpoint:** `POST /api/forgot-password`

**Authentication Required:** No

**Request Body:**

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `email` | string | Yes | User's email address |

**Request:**
```json
{
  "email": "john@example.com"
}
```

**Success Response:**
- Status: `200 OK`

```json
{
  "message": "We have emailed your password reset link."
}
```

**Error Response:**
- Status: `422 Unprocessable Entity`

```json
{
  "message": "We can't find a user with that email address.",
  "errors": {
    "email": ["We can't find a user with that email address."]
  }
}
```

---

### 7. Reset Password

Reset the user's password using the token from email.

**Endpoint:** `POST /api/reset-password`

**Authentication Required:** No

**Request Body:**

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `token` | string | Yes | Reset token from email |
| `email` | string | Yes | User's email address |
| `password` | string | Yes | New password (min 8 chars) |
| `password_confirmation` | string | Yes | Must match password |

**Request:**
```json
{
  "token": "abc123def456...",
  "email": "john@example.com",
  "password": "newsecurepassword123",
  "password_confirmation": "newsecurepassword123"
}
```

**Success Response:**
- Status: `200 OK`

```json
{
  "message": "Your password has been reset."
}
```

**Error Response:**
- Status: `422 Unprocessable Entity`

```json
{
  "message": "This password reset token is invalid.",
  "errors": {
    "email": ["This password reset token is invalid."]
  }
}
```

---

### 8. Send Email Verification

Resend the email verification notification.

**Endpoint:** `POST /api/email/verification-notification`

**Authentication Required:** Yes

**Rate Limit:** 6 requests per minute

**Request:**
```http
POST /api/email/verification-notification HTTP/1.1
Host: localhost:8000
Accept: application/json
X-XSRF-TOKEN: <token>
```

**Success Response:**
- Status: `200 OK`

```json
{
  "message": "Verification link sent"
}
```

**Already Verified Response:**
- Status: `200 OK`

```json
{
  "message": "Email already verified"
}
```

---

### 9. Verify Email

Verify the user's email address.

**Endpoint:** `GET /api/verify-email/{id}/{hash}`

**Authentication Required:** Yes

**Rate Limit:** 6 requests per minute

**URL Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `id` | integer | User ID |
| `hash` | string | Verification hash |

**Query Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `expires` | integer | Expiration timestamp |
| `signature` | string | URL signature |

**Success Response:**
- Status: `200 OK`

```json
{
  "message": "Email verified successfully"
}
```

**Already Verified Response:**
- Status: `200 OK`

```json
{
  "message": "Email already verified"
}
```

**Error Response:**
- Status: `400 Bad Request`

```json
{
  "message": "Invalid verification link"
}
```

---

### 10. Get Profile by User ID

Retrieve user profile with member data by user ID.

**Endpoint:** `GET /api/profile/user/{user_id}`

**Authentication Required:** Yes

**URL Parameters:**

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `user_id` | integer | Yes | User ID (positive integer) |

**Request:**
```http
GET /api/profile/user/1 HTTP/1.1
Host: localhost:8000
Accept: application/json
X-XSRF-TOKEN: <token>
```

**Success Response:**
- Status: `200 OK`

```json
{
  "data": {
    "user_id": 1,
    "member_number": 10001,
    "full_name": "John Doe",
    "email": "john@example.com",
    "phone": "+1234567890",
    "birthdate": "1990-05-15",
    "address": "123 Main Street",
    "city": "New York",
    "province": "NY",
    "country": "USA",
    "profile_picture_url": "http://localhost:8000/storage/profile-pictures/a1b2c3d4-e5f6-7890-abcd-ef1234567890.jpg",
    "status": "active",
    "created_at": "2026-01-05T10:00:00.000000Z",
    "updated_at": "2026-01-05T10:00:00.000000Z"
  }
}
```

**Note:** `profile_picture_url` will be `null` if no profile picture has been uploaded.

**Error Responses:**

Invalid user ID (Status: `422`):
```json
{
  "message": "Invalid user ID. Must be a positive integer.",
  "errors": {
    "user_id": ["The user ID must be a positive integer."]
  }
}
```

User not found (Status: `404`):
```json
{
  "message": "User not found.",
  "errors": {
    "user_id": ["No user found with the provided ID."]
  }
}
```

Member profile not found (Status: `404`):
```json
{
  "message": "Member profile not found for this user.",
  "errors": {
    "user_id": ["No member profile associated with this user."]
  }
}
```

---

### 11. Get Member by Member Number

Retrieve member data by member number.

**Endpoint:** `GET /api/profile/member/{member_number}`

**Authentication Required:** Yes

**URL Parameters:**

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `member_number` | integer | Yes | Member number (positive integer) |

**Request:**
```http
GET /api/profile/member/10001 HTTP/1.1
Host: localhost:8000
Accept: application/json
X-XSRF-TOKEN: <token>
```

**Success Response:**
- Status: `200 OK`

```json
{
  "data": {
    "user_id": 1,
    "member_number": 10001,
    "full_name": "John Doe",
    "status": "active",
    "updated_at": "2026-01-05T10:00:00.000000Z"
  }
}
```

**Error Responses:**

Invalid member number (Status: `422`):
```json
{
  "message": "Invalid member number. Must be a positive integer.",
  "errors": {
    "member_number": ["The member number must be a positive integer."]
  }
}
```

Member not found (Status: `404`):
```json
{
  "message": "Member not found.",
  "errors": {
    "member_number": ["No member found with the provided member number."]
  }
}
```

---

### 12. Update Password

Update the authenticated user's password.

**Endpoint:** `PUT /api/password`

**Authentication Required:** Yes

**Request Body:**

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `current_password` | string | Yes | User's current password |
| `password` | string | Yes | New password (min 8 chars, must be different from current) |
| `password_confirmation` | string | Yes | Must match new password |

**Request:**
```http
PUT /api/password HTTP/1.1
Host: localhost:8000
Accept: application/json
Content-Type: application/json
X-XSRF-TOKEN: <token>
```

```json
{
  "current_password": "oldpassword123",
  "password": "newsecurepassword456",
  "password_confirmation": "newsecurepassword456"
}
```

**Success Response:**
- Status: `200 OK`

```json
{
  "message": "Password updated successfully"
}
```

**Error Responses:**

Invalid current password (Status: `422`):
```json
{
  "message": "The provided password does not match your current password.",
  "errors": {
    "current_password": ["The provided password does not match your current password."]
  }
}
```

New password same as current (Status: `422`):
```json
{
  "message": "The new password must be different from your current password.",
  "errors": {
    "password": ["The new password must be different from your current password."]
  }
}
```

Password confirmation mismatch (Status: `422`):
```json
{
  "message": "The password field confirmation does not match.",
  "errors": {
    "password": ["The password field confirmation does not match."]
  }
}
```

Password too weak (Status: `422`):
```json
{
  "message": "The password field must be at least 8 characters.",
  "errors": {
    "password": ["The password field must be at least 8 characters."]
  }
}
```

---

### 13. Update Profile

Update the authenticated user's profile data and/or profile picture.

**Endpoint:** `POST /api/profile`

**Authentication Required:** Yes

**Content-Type:** `multipart/form-data` (required for file upload)

**Request Body:**

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `full_name` | string | No | User's full name (max 255 chars) |
| `phone` | string | No | Phone number (max 20 chars) |
| `birthdate` | date | No | Birthdate in YYYY-MM-DD format (must be before today) |
| `address` | string | No | Address (max 500 chars) |
| `city` | string | No | City (max 100 chars) |
| `province` | string | No | Province/State (max 100 chars) |
| `country` | string | No | Country (max 100 chars) |
| `profile_picture` | file | No | Profile picture (jpeg, jpg, png, gif, webp; max 5 MB) |

**Request Example (multipart/form-data):**
```http
POST /api/profile HTTP/1.1
Host: localhost:8000
Accept: application/json
Content-Type: multipart/form-data
X-XSRF-TOKEN: <token>

------boundary
Content-Disposition: form-data; name="full_name"

John Doe
------boundary
Content-Disposition: form-data; name="phone"

+1234567890
------boundary
Content-Disposition: form-data; name="profile_picture"; filename="photo.jpg"
Content-Type: image/jpeg

<binary data>
------boundary--
```

**Success Response:**
- Status: `200 OK`

```json
{
  "message": "Profile updated successfully",
  "data": {
    "user_id": 1,
    "member_number": 10001,
    "full_name": "John Doe",
    "email": "john@example.com",
    "phone": "+1234567890",
    "birthdate": "1990-05-15",
    "address": "123 Main Street",
    "city": "New York",
    "province": "NY",
    "country": "USA",
    "profile_picture_url": "http://localhost:8000/storage/profile-pictures/a1b2c3d4-e5f6-7890-abcd-ef1234567890.jpg",
    "status": "active",
    "updated_at": "2026-01-05T10:00:00.000000Z"
  }
}
```

**Error Responses:**

No member profile (Status: `404`):
```json
{
  "message": "Member profile not found for this user.",
  "errors": {
    "user": ["No member profile associated with this user."]
  }
}
```

Invalid image file type (Status: `422`):
```json
{
  "message": "The image must be a file of type: jpeg, jpg, png, gif, webp.",
  "errors": {
    "profile_picture": ["The image must be a file of type: jpeg, jpg, png, gif, webp."]
  }
}
```

Image too large (Status: `422`):
```json
{
  "message": "The image must not be larger than 5 MB.",
  "errors": {
    "profile_picture": ["The image must not be larger than 5 MB."]
  }
}
```

Invalid birthdate (Status: `422`):
```json
{
  "message": "The birthdate must be a date before today.",
  "errors": {
    "birthdate": ["The birthdate must be a date before today."]
  }
}
```

---

### 14. Delete Profile Picture

Delete the authenticated user's profile picture.

**Endpoint:** `DELETE /api/profile/picture`

**Authentication Required:** Yes

**Request:**
```http
DELETE /api/profile/picture HTTP/1.1
Host: localhost:8000
Accept: application/json
X-XSRF-TOKEN: <token>
```

**Success Response:**
- Status: `200 OK`

```json
{
  "message": "Profile picture deleted successfully"
}
```

**Error Responses:**

No member profile (Status: `404`):
```json
{
  "message": "Member profile not found for this user.",
  "errors": {
    "user": ["No member profile associated with this user."]
  }
}
```

No profile picture to delete (Status: `404`):
```json
{
  "message": "No profile picture to delete."
}
```

---

## News Endpoints

### 15. List News

Get all published news with pagination, sorted by latest created.

**Endpoint:** `GET /api/news`

**Authentication Required:** No

**Query Parameters:**

| Parameter | Type | Required | Default | Description |
|-----------|------|----------|---------|-------------|
| `per_page` | integer | No | 10 | Number of items per page (1-100) |
| `page` | integer | No | 1 | Page number |

**Request:**
```http
GET /api/news?per_page=10&page=1 HTTP/1.1
Host: localhost:8000
Accept: application/json
```

**Success Response:**
- Status: `200 OK`

```json
{
  "data": [
    {
      "id": 1,
      "title": "Latest News Title",
      "slug": "latest-news-title",
      "image": "abc123.jpg",
      "image_url": "http://localhost:8000/storage/posts/abc123.jpg",
      "excerpt": "This is a summary of the news article...",
      "contents": "<p>Full content of the news...</p>",
      "status": "published",
      "author_id": 1,
      "created_at": "2026-01-05T10:00:00.000000Z",
      "updated_at": "2026-01-05T10:00:00.000000Z",
      "author": {
        "id": 1,
        "name": "John Doe"
      },
      "categories": [
        {
          "id": 1,
          "name": "General",
          "slug": "general"
        }
      ]
    }
  ],
  "meta": {
    "current_page": 1,
    "last_page": 5,
    "per_page": 10,
    "total": 50
  },
  "links": {
    "first": "http://localhost:8000/api/news?page=1",
    "last": "http://localhost:8000/api/news?page=5",
    "prev": null,
    "next": "http://localhost:8000/api/news?page=2"
  }
}
```

---

### 16. News Detail

Get news detail by ID.

**Endpoint:** `GET /api/news/{news_id}`

**Authentication Required:** No

**URL Parameters:**

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `news_id` | integer | Yes | News ID (positive integer) |

**Request:**
```http
GET /api/news/1 HTTP/1.1
Host: localhost:8000
Accept: application/json
```

**Success Response:**
- Status: `200 OK`

```json
{
  "data": {
    "id": 1,
    "title": "Latest News Title",
    "slug": "latest-news-title",
    "image": "abc123.jpg",
    "image_url": "http://localhost:8000/storage/posts/abc123.jpg",
    "excerpt": "This is a summary of the news article...",
    "contents": "<p>Full content of the news with <strong>HTML</strong> and multimedia...</p>",
    "status": "published",
    "author_id": 1,
    "created_at": "2026-01-05T10:00:00.000000Z",
    "updated_at": "2026-01-05T10:00:00.000000Z",
    "author": {
      "id": 1,
      "name": "John Doe"
    },
    "categories": [
      {
        "id": 1,
        "name": "General",
        "slug": "general",
        "description": "General news and announcements"
      }
    ]
  }
}
```

**Error Responses:**

Invalid news ID (Status: `422`):
```json
{
  "message": "Invalid news ID. Must be a positive integer.",
  "errors": {
    "news_id": ["The news ID must be a positive integer."]
  }
}
```

News not found (Status: `404`):
```json
{
  "message": "News not found.",
  "errors": {
    "news_id": ["No news found with the provided ID."]
  }
}
```

---

### 17. Search News

Search news by keywords, date range, and category.

**Endpoint:** `GET /api/news/search`

**Authentication Required:** No

**Query Parameters:**

| Parameter | Type | Required | Default | Description |
|-----------|------|----------|---------|-------------|
| `keyword` | string | No | - | Search in title and excerpt |
| `start_date` | date | No | - | Filter by created date (YYYY-MM-DD) |
| `end_date` | date | No | - | Filter by created date (YYYY-MM-DD) |
| `category_id` | integer | No | - | Filter by category ID |
| `per_page` | integer | No | 10 | Number of items per page (1-100) |
| `page` | integer | No | 1 | Page number |

**Request:**
```http
GET /api/news/search?keyword=arsenal&start_date=2026-01-01&end_date=2026-01-31&category_id=1&per_page=10 HTTP/1.1
Host: localhost:8000
Accept: application/json
```

**Success Response:**
- Status: `200 OK`

```json
{
  "data": [
    {
      "id": 1,
      "title": "Arsenal Indonesia Event",
      "slug": "arsenal-indonesia-event",
      "image": "abc123.jpg",
      "image_url": "http://localhost:8000/storage/posts/abc123.jpg",
      "excerpt": "Arsenal Indonesia is hosting a special event...",
      "contents": "<p>Full content...</p>",
      "status": "published",
      "author_id": 1,
      "created_at": "2026-01-05T10:00:00.000000Z",
      "updated_at": "2026-01-05T10:00:00.000000Z",
      "author": {
        "id": 1,
        "name": "John Doe"
      },
      "categories": [
        {
          "id": 1,
          "name": "Events",
          "slug": "events"
        }
      ]
    }
  ],
  "meta": {
    "current_page": 1,
    "last_page": 1,
    "per_page": 10,
    "total": 1
  },
  "links": {
    "first": "http://localhost:8000/api/news/search?page=1",
    "last": "http://localhost:8000/api/news/search?page=1",
    "prev": null,
    "next": null
  },
  "filters": {
    "keyword": "arsenal",
    "start_date": "2026-01-01",
    "end_date": "2026-01-31",
    "category_id": 1
  }
}
```

**Error Responses:**

Invalid date range (Status: `422`):
```json
{
  "message": "The end date must be after or equal to the start date.",
  "errors": {
    "end_date": ["The end date must be after or equal to the start date."]
  }
}
```

Invalid category (Status: `422`):
```json
{
  "message": "The selected category id is invalid.",
  "errors": {
    "category_id": ["The selected category id is invalid."]
  }
}
```

---

### 18. List Categories

Get all post categories.

**Endpoint:** `GET /api/categories`

**Authentication Required:** No

**Request:**
```http
GET /api/categories HTTP/1.1
Host: localhost:8000
Accept: application/json
```

**Success Response:**
- Status: `200 OK`

```json
{
  "data": [
    {
      "id": 1,
      "name": "Announcements",
      "slug": "announcements",
      "description": "Official announcements from AIS",
      "created_at": "2026-01-05T10:00:00.000000Z",
      "updated_at": "2026-01-05T10:00:00.000000Z"
    },
    {
      "id": 2,
      "name": "Events",
      "slug": "events",
      "description": "Upcoming and past events",
      "created_at": "2026-01-05T10:00:00.000000Z",
      "updated_at": "2026-01-05T10:00:00.000000Z"
    },
    {
      "id": 3,
      "name": "Match Reports",
      "slug": "match-reports",
      "description": "Arsenal match reports and analysis",
      "created_at": "2026-01-05T10:00:00.000000Z",
      "updated_at": "2026-01-05T10:00:00.000000Z"
    }
  ]
}
```

---

## User Roles

The system supports two user roles:

| Role | Description |
|------|-------------|
| `guest` | Default role for newly registered users |
| `member` | Full member access |

Roles are stored as a JSON array and can be checked in your frontend:

```javascript
const user = await getUser();
const isMember = user.role.includes('member');
const isGuest = user.role.includes('guest');
```

---

## Error Handling

### Common Error Responses

**401 Unauthorized**
```json
{
  "message": "Unauthenticated."
}
```

**403 Forbidden**
```json
{
  "message": "This action is unauthorized."
}
```

**404 Not Found**
```json
{
  "message": "Not Found."
}
```

**419 CSRF Token Mismatch**
```json
{
  "message": "CSRF token mismatch."
}
```

**422 Validation Error**
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "field_name": ["Error message for this field."]
  }
}
```

**429 Too Many Requests**
```json
{
  "message": "Too Many Attempts."
}
```

---

## Next.js Integration

### API Client Setup

Create a reusable API client for your Next.js application:

```javascript
// lib/api.js

const API_URL = process.env.NEXT_PUBLIC_API_URL || 'http://localhost:8000';

/**
 * Get XSRF token from cookies
 */
function getXsrfToken() {
  if (typeof document === 'undefined') return '';
  const match = document.cookie.match(/XSRF-TOKEN=([^;]+)/);
  return match ? decodeURIComponent(match[1]) : '';
}

/**
 * Initialize CSRF protection
 */
export async function initCsrf() {
  await fetch(`${API_URL}/sanctum/csrf-cookie`, {
    credentials: 'include',
  });
}

/**
 * Make authenticated API request
 */
async function apiRequest(endpoint, options = {}) {
  const response = await fetch(`${API_URL}${endpoint}`, {
    ...options,
    credentials: 'include',
    headers: {
      'Accept': 'application/json',
      'Content-Type': 'application/json',
      'X-XSRF-TOKEN': getXsrfToken(),
      ...options.headers,
    },
  });

  const data = await response.json().catch(() => ({}));

  if (!response.ok) {
    const error = new Error(data.message || 'API Error');
    error.status = response.status;
    error.errors = data.errors || {};
    throw error;
  }

  return data;
}

/**
 * Register a new user
 */
export async function register(name, email, password, passwordConfirmation) {
  await initCsrf();
  return apiRequest('/api/register', {
    method: 'POST',
    body: JSON.stringify({
      name,
      email,
      password,
      password_confirmation: passwordConfirmation,
    }),
  });
}

/**
 * Login user
 */
export async function login(email, password, remember = false) {
  await initCsrf();
  return apiRequest('/api/login', {
    method: 'POST',
    body: JSON.stringify({ email, password, remember }),
  });
}

/**
 * Logout user
 */
export async function logout() {
  return apiRequest('/api/logout', {
    method: 'POST',
  });
}

/**
 * Get current user
 */
export async function getUser() {
  return apiRequest('/api/user');
}

/**
 * Request password reset
 */
export async function forgotPassword(email) {
  await initCsrf();
  return apiRequest('/api/forgot-password', {
    method: 'POST',
    body: JSON.stringify({ email }),
  });
}

/**
 * Reset password
 */
export async function resetPassword(token, email, password, passwordConfirmation) {
  await initCsrf();
  return apiRequest('/api/reset-password', {
    method: 'POST',
    body: JSON.stringify({
      token,
      email,
      password,
      password_confirmation: passwordConfirmation,
    }),
  });
}

/**
 * Resend email verification
 */
export async function resendVerification() {
  return apiRequest('/api/email/verification-notification', {
    method: 'POST',
  });
}

/**
 * Get user profile by user ID
 */
export async function getProfileByUserId(userId) {
  return apiRequest(`/api/profile/user/${userId}`);
}

/**
 * Get member by member number
 */
export async function getMemberByNumber(memberNumber) {
  return apiRequest(`/api/profile/member/${memberNumber}`);
}

/**
 * Update user password
 */
export async function updatePassword(currentPassword, newPassword, passwordConfirmation) {
  return apiRequest('/api/password', {
    method: 'PUT',
    body: JSON.stringify({
      current_password: currentPassword,
      password: newPassword,
      password_confirmation: passwordConfirmation,
    }),
  });
}

/**
 * Update user profile (with optional profile picture)
 */
export async function updateProfile(data) {
  const formData = new FormData();

  if (data.full_name) formData.append('full_name', data.full_name);
  if (data.phone !== undefined) formData.append('phone', data.phone || '');
  if (data.birthdate !== undefined) formData.append('birthdate', data.birthdate || '');
  if (data.address !== undefined) formData.append('address', data.address || '');
  if (data.city !== undefined) formData.append('city', data.city || '');
  if (data.province !== undefined) formData.append('province', data.province || '');
  if (data.country !== undefined) formData.append('country', data.country || '');
  if (data.profile_picture) formData.append('profile_picture', data.profile_picture);

  const response = await fetch(`${API_URL}/api/profile`, {
    method: 'POST',
    credentials: 'include',
    headers: {
      'Accept': 'application/json',
      'X-XSRF-TOKEN': getXsrfToken(),
    },
    body: formData,
  });

  const result = await response.json().catch(() => ({}));

  if (!response.ok) {
    const error = new Error(result.message || 'API Error');
    error.status = response.status;
    error.errors = result.errors || {};
    throw error;
  }

  return result;
}

/**
 * Delete profile picture
 */
export async function deleteProfilePicture() {
  return apiRequest('/api/profile/picture', {
    method: 'DELETE',
  });
}

/**
 * Get news list
 */
export async function getNews(page = 1, perPage = 10) {
  return apiRequest(`/api/news?page=${page}&per_page=${perPage}`);
}

/**
 * Get news detail by ID
 */
export async function getNewsById(newsId) {
  return apiRequest(`/api/news/${newsId}`);
}

/**
 * Search news
 */
export async function searchNews(params = {}) {
  const queryParams = new URLSearchParams();
  if (params.keyword) queryParams.append('keyword', params.keyword);
  if (params.start_date) queryParams.append('start_date', params.start_date);
  if (params.end_date) queryParams.append('end_date', params.end_date);
  if (params.category_id) queryParams.append('category_id', params.category_id);
  if (params.page) queryParams.append('page', params.page);
  if (params.per_page) queryParams.append('per_page', params.per_page);

  return apiRequest(`/api/news/search?${queryParams.toString()}`);
}

/**
 * Get all categories
 */
export async function getCategories() {
  return apiRequest('/api/categories');
}
```

### React Hook Example

```javascript
// hooks/useAuth.js

import { useState, useEffect, createContext, useContext } from 'react';
import * as api from '../lib/api';

const AuthContext = createContext(null);

export function AuthProvider({ children }) {
  const [user, setUser] = useState(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    checkAuth();
  }, []);

  async function checkAuth() {
    try {
      const data = await api.getUser();
      setUser(data.user);
    } catch (error) {
      setUser(null);
    } finally {
      setLoading(false);
    }
  }

  async function login(email, password, remember) {
    const data = await api.login(email, password, remember);
    setUser(data.user);
    return data;
  }

  async function register(name, email, password, passwordConfirmation) {
    const data = await api.register(name, email, password, passwordConfirmation);
    setUser(data.user);
    return data;
  }

  async function logout() {
    await api.logout();
    setUser(null);
  }

  return (
    <AuthContext.Provider value={{
      user,
      loading,
      login,
      register,
      logout,
      checkAuth,
    }}>
      {children}
    </AuthContext.Provider>
  );
}

export function useAuth() {
  const context = useContext(AuthContext);
  if (!context) {
    throw new Error('useAuth must be used within an AuthProvider');
  }
  return context;
}
```

### Login Component Example

```jsx
// components/LoginForm.jsx

import { useState } from 'react';
import { useAuth } from '../hooks/useAuth';
import { useRouter } from 'next/router';

export default function LoginForm() {
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [remember, setRemember] = useState(false);
  const [errors, setErrors] = useState({});
  const [loading, setLoading] = useState(false);

  const { login } = useAuth();
  const router = useRouter();

  async function handleSubmit(e) {
    e.preventDefault();
    setLoading(true);
    setErrors({});

    try {
      await login(email, password, remember);
      router.push('/dashboard');
    } catch (error) {
      setErrors(error.errors || { general: [error.message] });
    } finally {
      setLoading(false);
    }
  }

  return (
    <form onSubmit={handleSubmit}>
      {errors.general && (
        <div className="error">{errors.general[0]}</div>
      )}

      <div>
        <label htmlFor="email">Email</label>
        <input
          id="email"
          type="email"
          value={email}
          onChange={(e) => setEmail(e.target.value)}
          required
        />
        {errors.email && <span className="error">{errors.email[0]}</span>}
      </div>

      <div>
        <label htmlFor="password">Password</label>
        <input
          id="password"
          type="password"
          value={password}
          onChange={(e) => setPassword(e.target.value)}
          required
        />
        {errors.password && <span className="error">{errors.password[0]}</span>}
      </div>

      <div>
        <label>
          <input
            type="checkbox"
            checked={remember}
            onChange={(e) => setRemember(e.target.checked)}
          />
          Remember me
        </label>
      </div>

      <button type="submit" disabled={loading}>
        {loading ? 'Logging in...' : 'Login'}
      </button>
    </form>
  );
}
```

---

## Environment Configuration

### Laravel (.env)

```env
# Application
APP_URL=http://localhost:8000

# Sanctum
SANCTUM_STATEFUL_DOMAINS=localhost:3000,127.0.0.1:3000
FRONTEND_URL=http://localhost:3000

# Session
SESSION_DRIVER=database
SESSION_DOMAIN=localhost
```

### Next.js (.env.local)

```env
NEXT_PUBLIC_API_URL=http://localhost:8000
```

---

## Production Configuration

When deploying to production, update the following:

### Laravel (.env)

```env
APP_URL=https://api.yourdomain.com

SANCTUM_STATEFUL_DOMAINS=yourdomain.com,www.yourdomain.com
FRONTEND_URL=https://yourdomain.com

SESSION_DOMAIN=.yourdomain.com
SESSION_SECURE_COOKIE=true
```

### CORS (config/cors.php)

```php
'allowed_origins' => [env('FRONTEND_URL', 'https://yourdomain.com')],
```

---

## Testing the API

### Using cURL

```bash
# 1. Get CSRF cookie
curl -c cookies.txt -b cookies.txt \
  http://localhost:8000/sanctum/csrf-cookie

# 2. Extract XSRF token
XSRF_TOKEN=$(grep XSRF-TOKEN cookies.txt | awk '{print $7}')

# 3. Register
curl -c cookies.txt -b cookies.txt \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -H "X-XSRF-TOKEN: $XSRF_TOKEN" \
  -d '{"name":"Test User","email":"test@example.com","password":"password123","password_confirmation":"password123"}' \
  http://localhost:8000/api/register

# 4. Login
curl -c cookies.txt -b cookies.txt \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -H "X-XSRF-TOKEN: $XSRF_TOKEN" \
  -d '{"email":"test@example.com","password":"password123"}' \
  http://localhost:8000/api/login

# 5. Get user
curl -c cookies.txt -b cookies.txt \
  -H "Accept: application/json" \
  -H "X-XSRF-TOKEN: $XSRF_TOKEN" \
  http://localhost:8000/api/user

# 6. Logout
curl -c cookies.txt -b cookies.txt \
  -X POST \
  -H "Accept: application/json" \
  -H "X-XSRF-TOKEN: $XSRF_TOKEN" \
  http://localhost:8000/api/logout

# 7. Get profile by user ID
curl -c cookies.txt -b cookies.txt \
  -H "Accept: application/json" \
  -H "X-XSRF-TOKEN: $XSRF_TOKEN" \
  http://localhost:8000/api/profile/user/1

# 8. Get member by member number
curl -c cookies.txt -b cookies.txt \
  -H "Accept: application/json" \
  -H "X-XSRF-TOKEN: $XSRF_TOKEN" \
  http://localhost:8000/api/profile/member/10001

# 9. Update password
curl -c cookies.txt -b cookies.txt \
  -X PUT \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -H "X-XSRF-TOKEN: $XSRF_TOKEN" \
  -d '{"current_password":"oldpassword123","password":"newpassword456","password_confirmation":"newpassword456"}' \
  http://localhost:8000/api/password

# 10. Update profile (with profile picture)
curl -c cookies.txt -b cookies.txt \
  -X POST \
  -H "Accept: application/json" \
  -H "X-XSRF-TOKEN: $XSRF_TOKEN" \
  -F "full_name=John Doe" \
  -F "phone=+1234567890" \
  -F "city=New York" \
  -F "profile_picture=@/path/to/photo.jpg" \
  http://localhost:8000/api/profile

# 11. Delete profile picture
curl -c cookies.txt -b cookies.txt \
  -X DELETE \
  -H "Accept: application/json" \
  -H "X-XSRF-TOKEN: $XSRF_TOKEN" \
  http://localhost:8000/api/profile/picture

# 12. Get news list (public, no auth required)
curl -H "Accept: application/json" \
  "http://localhost:8000/api/news?per_page=10&page=1"

# 13. Get news detail (public)
curl -H "Accept: application/json" \
  http://localhost:8000/api/news/1

# 14. Search news (public)
curl -H "Accept: application/json" \
  "http://localhost:8000/api/news/search?keyword=arsenal&start_date=2026-01-01&end_date=2026-01-31&category_id=1"

# 15. Get all categories (public)
curl -H "Accept: application/json" \
  http://localhost:8000/api/categories
```

### Using Postman

1. Create a new environment with variable `base_url` = `http://localhost:8000`
2. Send `GET {{base_url}}/sanctum/csrf-cookie`
3. Postman automatically stores cookies
4. Add header `X-XSRF-TOKEN` with value from `XSRF-TOKEN` cookie (URL decoded)
5. Make authenticated requests

---

## Troubleshooting

### CSRF Token Mismatch

**Problem:** Getting "CSRF token mismatch" error

**Solutions:**
1. Ensure you call `/sanctum/csrf-cookie` before authentication
2. Include `credentials: 'include'` in all requests
3. URL decode the XSRF-TOKEN cookie value before using in header
4. Check that `SESSION_DOMAIN` matches your domain

### Unauthenticated After Login

**Problem:** Login succeeds but subsequent requests return 401

**Solutions:**
1. Verify `credentials: 'include'` is set on all requests
2. Check that cookies are being sent (browser DevTools > Network > Cookies)
3. Ensure `SANCTUM_STATEFUL_DOMAINS` includes your frontend domain
4. Verify `SESSION_DOMAIN` configuration

### CORS Errors

**Problem:** Getting CORS errors in browser console

**Solutions:**
1. Check `config/cors.php` has correct `allowed_origins`
2. Ensure `supports_credentials` is `true`
3. Verify `FRONTEND_URL` environment variable
4. Clear config cache: `php artisan config:clear`

---

## Security Considerations

1. **HTTPS in Production:** Always use HTTPS in production
2. **Secure Cookies:** Set `SESSION_SECURE_COOKIE=true` in production
3. **Rate Limiting:** Login and verification endpoints are rate-limited
4. **Password Requirements:** Minimum 8 characters enforced
5. **CSRF Protection:** All state-changing requests require XSRF token
6. **Session Regeneration:** Session ID regenerates on login/logout
