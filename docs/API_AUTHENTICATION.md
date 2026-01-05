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

## Football API Endpoints

The Football API provides Arsenal FC match data, fixtures, results, and league standings. Data is fetched from api-football.com and cached locally, with automatic sync every 6 hours.

### 19. Get Competitions

Get all competitions/leagues that Arsenal is currently participating in. Use this endpoint to populate a competition filter dropdown in the frontend.

**Endpoint:** `GET /api/football/competitions`

**Authentication Required:** No

**Query Parameters:**

| Parameter | Type | Required | Default | Description |
|-----------|------|----------|---------|-------------|
| `season` | integer | No | current | Season year (e.g., 2025) |
| `include_match_count` | boolean | No | true | Include match count for each competition |

**Request:**
```http
GET /api/football/competitions HTTP/1.1
Host: localhost:8000
Accept: application/json
```

**Success Response:**
- Status: `200 OK`

```json
{
  "data": [
    {
      "id": 2,
      "name": "UEFA Champions League",
      "type": "Cup",
      "logo": "https://media.api-sports.io/football/leagues/2.png",
      "country": {
        "name": "World",
        "code": null,
        "flag": null
      },
      "season": {
        "year": 2025,
        "start": "2025-09-17",
        "end": "2026-05-30"
      },
      "match_count": 6
    },
    {
      "id": 39,
      "name": "Premier League",
      "type": "League",
      "logo": "https://media.api-sports.io/football/leagues/39.png",
      "country": {
        "name": "England",
        "code": "GB",
        "flag": "https://media.api-sports.io/flags/gb.svg"
      },
      "season": {
        "year": 2025,
        "start": "2025-08-16",
        "end": "2026-05-24"
      },
      "match_count": 20
    },
    {
      "id": 45,
      "name": "FA Cup",
      "type": "Cup",
      "logo": "https://media.api-sports.io/football/leagues/45.png",
      "country": {
        "name": "England",
        "code": "GB",
        "flag": "https://media.api-sports.io/flags/gb.svg"
      },
      "season": {
        "year": 2025,
        "start": "2025-11-01",
        "end": "2026-05-17"
      },
      "match_count": 3
    },
    {
      "id": 48,
      "name": "League Cup",
      "type": "Cup",
      "logo": "https://media.api-sports.io/football/leagues/48.png",
      "country": {
        "name": "England",
        "code": "GB",
        "flag": "https://media.api-sports.io/flags/gb.svg"
      },
      "season": {
        "year": 2025,
        "start": "2025-08-12",
        "end": "2026-03-01"
      },
      "match_count": 2
    }
  ],
  "meta": {
    "total": 4,
    "season": 2025
  }
}
```

**Competition Object Fields:**

| Field | Type | Description |
|-------|------|-------------|
| `id` | integer | League/competition ID (use for filtering results) |
| `name` | string | Competition name |
| `type` | string | `League` or `Cup` |
| `logo` | string | Competition logo URL |
| `country.name` | string | Country name |
| `country.code` | string\|null | Country code (e.g., "GB") |
| `country.flag` | string\|null | Country flag URL |
| `season.year` | integer | Season year |
| `season.start` | string | Season start date |
| `season.end` | string | Season end date |
| `match_count` | integer | Number of matches played in this competition |

**Frontend Usage - Competition Filter:**

```typescript
// TypeScript interface
interface Competition {
  id: number;
  name: string;
  type: string;
  logo: string;
  country: {
    name: string;
    code: string | null;
    flag: string | null;
  };
  season: {
    year: number;
    start: string;
    end: string;
  };
  match_count: number;
}

// React component example
const CompetitionFilter = () => {
  const [competitions, setCompetitions] = useState<Competition[]>([]);
  const [selected, setSelected] = useState<number | null>(null);

  useEffect(() => {
    fetch('http://localhost:8000/api/football/competitions')
      .then(res => res.json())
      .then(data => setCompetitions(data.data));
  }, []);

  const handleFilter = (leagueId: number | null) => {
    setSelected(leagueId);
    // Fetch results filtered by competition
    const url = leagueId
      ? `http://localhost:8000/api/football/results?league_id=${leagueId}`
      : 'http://localhost:8000/api/football/results';
    // ... fetch and update results
  };

  return (
    <div className="competition-filter">
      <button
        className={selected === null ? 'active' : ''}
        onClick={() => handleFilter(null)}
      >
        All Competitions
      </button>
      {competitions.map(comp => (
        <button
          key={comp.id}
          className={selected === comp.id ? 'active' : ''}
          onClick={() => handleFilter(comp.id)}
        >
          <img src={comp.logo} alt={comp.name} width="20" />
          {comp.name} ({comp.match_count})
        </button>
      ))}
    </div>
  );
};
```

---

### 20. Get Upcoming Fixtures

Get Arsenal's upcoming matches.

**Endpoint:** `GET /api/football/fixtures`

**Authentication Required:** No

**Query Parameters:**

| Parameter | Type | Required | Default | Description |
|-----------|------|----------|---------|-------------|
| `limit` | integer | No | 5 | Number of fixtures to return |
| `season` | integer | No | current | Season year (e.g., 2025) |

**Request:**
```http
GET /api/football/fixtures?limit=5 HTTP/1.1
Host: localhost:8000
Accept: application/json
```

**Success Response:**
- Status: `200 OK`

```json
{
  "data": [
    {
      "id": 1234567,
      "date": "2026-01-15T15:00:00+00:00",
      "timestamp": 1736953200,
      "venue": {
        "name": "Emirates Stadium",
        "city": "London"
      },
      "league": {
        "id": 39,
        "name": "Premier League",
        "round": "Regular Season - 21",
        "logo": "https://media.api-sports.io/football/leagues/39.png"
      },
      "home": {
        "id": 42,
        "name": "Arsenal",
        "logo": "https://media.api-sports.io/football/teams/42.png"
      },
      "away": {
        "id": 33,
        "name": "Manchester United",
        "logo": "https://media.api-sports.io/football/teams/33.png"
      },
      "arsenal": {
        "is_home": true,
        "opponent": "Manchester United",
        "opponent_logo": "https://media.api-sports.io/football/teams/33.png",
        "venue_type": "H"
      },
      "status": {
        "long": "Not Started",
        "short": "NS"
      }
    }
  ],
  "meta": {
    "total": 5,
    "season": 2025
  }
}
```

---

### 21. Get Next Fixture

Get Arsenal's next upcoming match.

**Endpoint:** `GET /api/football/fixtures/next`

**Authentication Required:** No

**Request:**
```http
GET /api/football/fixtures/next HTTP/1.1
Host: localhost:8000
Accept: application/json
```

**Success Response:**
- Status: `200 OK`

```json
{
  "data": {
    "id": 1234567,
    "date": "2026-01-15T15:00:00+00:00",
    "timestamp": 1736953200,
    "venue": {
      "name": "Emirates Stadium",
      "city": "London"
    },
    "league": {
      "id": 39,
      "name": "Premier League",
      "round": "Regular Season - 21",
      "logo": "https://media.api-sports.io/football/leagues/39.png"
    },
    "home": {
      "id": 42,
      "name": "Arsenal",
      "logo": "https://media.api-sports.io/football/teams/42.png"
    },
    "away": {
      "id": 33,
      "name": "Manchester United",
      "logo": "https://media.api-sports.io/football/teams/33.png"
    },
    "arsenal": {
      "is_home": true,
      "opponent": "Manchester United",
      "opponent_logo": "https://media.api-sports.io/football/teams/33.png",
      "venue_type": "H"
    },
    "status": {
      "long": "Not Started",
      "short": "NS"
    }
  }
}
```

**No Upcoming Fixtures Response:**
```json
{
  "data": null,
  "message": "No upcoming fixtures found"
}
```

---

### 22. Get Match Results

Get Arsenal's match results.

**Endpoint:** `GET /api/football/results`

**Authentication Required:** No

**Query Parameters:**

| Parameter | Type | Required | Default | Description |
|-----------|------|----------|---------|-------------|
| `limit` | integer | No | 10 | Number of results to return |
| `season` | integer | No | current | Season year (e.g., 2025) |
| `league_id` | integer | No | - | Filter by league ID (39 = Premier League) |

**Request:**
```http
GET /api/football/results?limit=5&season=2025 HTTP/1.1
Host: localhost:8000
Accept: application/json
```

**Success Response:**
- Status: `200 OK`

```json
{
  "data": [
    {
      "id": 1234566,
      "date": "2026-01-10T20:00:00+00:00",
      "venue": {
        "name": "Tottenham Hotspur Stadium",
        "city": "London"
      },
      "league": {
        "id": 39,
        "name": "Premier League",
        "round": "Regular Season - 20",
        "logo": "https://media.api-sports.io/football/leagues/39.png"
      },
      "home": {
        "id": 47,
        "name": "Tottenham",
        "logo": "https://media.api-sports.io/football/teams/47.png",
        "goals": 1
      },
      "away": {
        "id": 42,
        "name": "Arsenal",
        "logo": "https://media.api-sports.io/football/teams/42.png",
        "goals": 2
      },
      "score": {
        "home": 1,
        "away": 2,
        "display": "1 - 2",
        "halftime": {
          "home": 0,
          "away": 1
        }
      },
      "arsenal": {
        "is_home": false,
        "opponent": "Tottenham",
        "opponent_logo": "https://media.api-sports.io/football/teams/47.png",
        "venue_type": "A",
        "goals_for": 2,
        "goals_against": 1,
        "result": "W"
      }
    }
  ],
  "meta": {
    "total": 5,
    "season": 2025
  }
}
```

---

### 23. Get Last Match Result

Get Arsenal's most recent match result.

**Endpoint:** `GET /api/football/results/last`

**Authentication Required:** No

**Request:**
```http
GET /api/football/results/last HTTP/1.1
Host: localhost:8000
Accept: application/json
```

**Success Response:**
- Status: `200 OK`

```json
{
  "data": {
    "id": 1234566,
    "date": "2026-01-10T20:00:00+00:00",
    "venue": {
      "name": "Emirates Stadium",
      "city": "London"
    },
    "league": {
      "id": 39,
      "name": "Premier League",
      "round": "Regular Season - 20",
      "logo": "https://media.api-sports.io/football/leagues/39.png"
    },
    "home": {
      "id": 42,
      "name": "Arsenal",
      "logo": "https://media.api-sports.io/football/teams/42.png",
      "goals": 3
    },
    "away": {
      "id": 40,
      "name": "Liverpool",
      "logo": "https://media.api-sports.io/football/teams/40.png",
      "goals": 1
    },
    "score": {
      "home": 3,
      "away": 1,
      "display": "3 - 1",
      "halftime": {
        "home": 1,
        "away": 0
      }
    },
    "arsenal": {
      "is_home": true,
      "opponent": "Liverpool",
      "opponent_logo": "https://media.api-sports.io/football/teams/40.png",
      "venue_type": "H",
      "goals_for": 3,
      "goals_against": 1,
      "result": "W"
    }
  }
}
```

**No Results Response:**
```json
{
  "data": null,
  "message": "No match results found"
}
```

---

### 24. Get Match Report

Get detailed match report including events (goals, cards, substitutions), lineups (formations, starting XI, substitutes), and match statistics (possession, shots, passes, etc.).

**Endpoint:** `GET /api/football/results/{fixture_id}/report`

**Authentication Required:** No

**Path Parameters:**

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `fixture_id` | integer | Yes | The fixture ID from the match result (obtained from `/api/football/results` endpoint) |

**Request:**
```http
GET /api/football/results/1234566/report HTTP/1.1
Host: localhost:8000
Accept: application/json
```

**Success Response:**
- Status: `200 OK`

The response contains four main sections: `match`, `events`, `lineups`, and `statistics`.

```json
{
  "data": {
    "match": { ... },
    "events": [ ... ],
    "lineups": [ ... ],
    "statistics": [ ... ]
  }
}
```

---

#### Response Section: `match`

Basic match information including teams, score, and venue.

```json
{
  "match": {
    "id": 1234566,
    "date": "2026-01-10T20:00:00+00:00",
    "venue": {
      "name": "Emirates Stadium",
      "city": "London"
    },
    "league": {
      "id": 39,
      "name": "Premier League",
      "round": "Regular Season - 20",
      "logo": "https://media.api-sports.io/football/leagues/39.png"
    },
    "home": {
      "id": 42,
      "name": "Arsenal",
      "logo": "https://media.api-sports.io/football/teams/42.png",
      "goals": 3
    },
    "away": {
      "id": 40,
      "name": "Liverpool",
      "logo": "https://media.api-sports.io/football/teams/40.png",
      "goals": 1
    },
    "score": {
      "home": 3,
      "away": 1,
      "display": "3 - 1",
      "halftime": {
        "home": 1,
        "away": 0
      }
    },
    "arsenal": {
      "is_home": true,
      "opponent": "Liverpool",
      "opponent_logo": "https://media.api-sports.io/football/teams/40.png",
      "venue_type": "H",
      "goals_for": 3,
      "goals_against": 1,
      "result": "W"
    }
  }
}
```

---

#### Response Section: `events`

Array of match events in chronological order. Events include goals, cards, substitutions, and VAR decisions.

**Event Object Fields:**

| Field | Type | Description |
|-------|------|-------------|
| `time.elapsed` | integer | Minute when the event occurred (1-90+) |
| `time.extra` | integer\|null | Extra/injury time minutes (e.g., 45+2 = elapsed:45, extra:2) |
| `team.id` | integer | Team ID that the event belongs to |
| `team.name` | string | Team name |
| `team.logo` | string | Team logo URL |
| `player.id` | integer | Player ID who performed the action |
| `player.name` | string | Player name |
| `assist.id` | integer\|null | Assisting player ID (for goals) |
| `assist.name` | string\|null | Assisting player name |
| `type` | string | Event type: `Goal`, `Card`, `subst`, `Var` |
| `detail` | string | Event detail (see table below) |
| `comments` | string\|null | Additional comments |

**Event Types and Details:**

| Type | Possible Details | Description |
|------|------------------|-------------|
| `Goal` | `Normal Goal`, `Own Goal`, `Penalty`, `Missed Penalty` | Goal scored or penalty |
| `Card` | `Yellow Card`, `Red Card`, `Second Yellow card` | Card shown to player |
| `subst` | `Substitution 1`, `Substitution 2`, etc. | Player substitution (player = out, assist = in) |
| `Var` | `Goal cancelled`, `Penalty confirmed`, etc. | VAR decision |

**Example Events Array:**

```json
{
  "events": [
    {
      "time": { "elapsed": 23, "extra": null },
      "team": {
        "id": 42,
        "name": "Arsenal",
        "logo": "https://media.api-sports.io/football/teams/42.png"
      },
      "player": { "id": 1234, "name": "B. Saka" },
      "assist": { "id": 5678, "name": "M. Odegaard" },
      "type": "Goal",
      "detail": "Normal Goal",
      "comments": null
    },
    {
      "time": { "elapsed": 45, "extra": 2 },
      "team": {
        "id": 40,
        "name": "Liverpool",
        "logo": "https://media.api-sports.io/football/teams/40.png"
      },
      "player": { "id": 9876, "name": "M. Salah" },
      "assist": { "id": null, "name": null },
      "type": "Card",
      "detail": "Yellow Card",
      "comments": "Foul"
    },
    {
      "time": { "elapsed": 65, "extra": null },
      "team": {
        "id": 42,
        "name": "Arsenal",
        "logo": "https://media.api-sports.io/football/teams/42.png"
      },
      "player": { "id": 1111, "name": "G. Jesus" },
      "assist": { "id": 2222, "name": "K. Havertz" },
      "type": "subst",
      "detail": "Substitution 1",
      "comments": null
    },
    {
      "time": { "elapsed": 78, "extra": null },
      "team": {
        "id": 42,
        "name": "Arsenal",
        "logo": "https://media.api-sports.io/football/teams/42.png"
      },
      "player": { "id": 2222, "name": "K. Havertz" },
      "assist": { "id": 1234, "name": "B. Saka" },
      "type": "Goal",
      "detail": "Normal Goal",
      "comments": null
    },
    {
      "time": { "elapsed": 90, "extra": 3 },
      "team": {
        "id": 42,
        "name": "Arsenal",
        "logo": "https://media.api-sports.io/football/teams/42.png"
      },
      "player": { "id": 3333, "name": "M. Odegaard" },
      "assist": { "id": null, "name": null },
      "type": "Goal",
      "detail": "Penalty",
      "comments": null
    }
  ]
}
```

**Frontend Usage - Display Match Timeline:**

```typescript
// TypeScript interfaces
interface MatchEvent {
  time: { elapsed: number; extra: number | null };
  team: { id: number; name: string; logo: string };
  player: { id: number; name: string };
  assist: { id: number | null; name: string | null };
  type: 'Goal' | 'Card' | 'subst' | 'Var';
  detail: string;
  comments: string | null;
}

// Filter goals only
const goals = events.filter(e => e.type === 'Goal');

// Get Arsenal goals
const arsenalGoals = events.filter(e => e.type === 'Goal' && e.team.id === 42);

// Format time display
const formatTime = (event: MatchEvent) => {
  if (event.time.extra) {
    return `${event.time.elapsed}+${event.time.extra}'`;
  }
  return `${event.time.elapsed}'`;
};

// Build goal scorers string: "Saka 23', Havertz 78', Odegaard 90+3' (pen)"
const goalScorers = goals
  .filter(g => g.team.id === 42)
  .map(g => {
    const time = formatTime(g);
    const pen = g.detail === 'Penalty' ? ' (pen)' : '';
    return `${g.player.name} ${time}${pen}`;
  })
  .join(', ');
```

---

#### Response Section: `lineups`

Array containing lineup information for both teams (home team first, away team second).

**Lineup Object Fields:**

| Field | Type | Description |
|-------|------|-------------|
| `team.id` | integer | Team ID |
| `team.name` | string | Team name |
| `team.logo` | string | Team logo URL |
| `team.colors` | object | Kit colors for rendering |
| `formation` | string | Team formation (e.g., "4-3-3", "4-2-3-1") |
| `coach.id` | integer | Coach ID |
| `coach.name` | string | Coach name |
| `coach.photo` | string | Coach photo URL |
| `startXI` | array | Array of 11 starting players |
| `substitutes` | array | Array of substitute players |

**Player Object Fields (startXI):**

| Field | Type | Description |
|-------|------|-------------|
| `id` | integer | Player ID |
| `name` | string | Player name |
| `number` | integer | Shirt number |
| `pos` | string | Position: `G` (Goalkeeper), `D` (Defender), `M` (Midfielder), `F` (Forward) |
| `grid` | string | Position on pitch grid (e.g., "1:1" for GK, "2:4" for right-back) |

**Grid System Explained:**

The `grid` field uses format `"row:column"` to position players on the pitch:
- Row 1 = Goalkeeper line
- Row 2 = Defenders line
- Row 3 = Midfielders line (or defensive midfield)
- Row 4 = Attacking midfield / Wingers
- Row 5 = Forwards line
- Columns are numbered left to right (1-5)

Example for 4-3-3 formation:
```
          GK (1:1)
RB(2:4) CB(2:3) CB(2:2) LB(2:1)
    CM(3:3) CM(3:2) CM(3:1)
  RW(4:3)   ST(4:2)   LW(4:1)
```

**Example Lineups Array:**

```json
{
  "lineups": [
    {
      "team": {
        "id": 42,
        "name": "Arsenal",
        "logo": "https://media.api-sports.io/football/teams/42.png",
        "colors": {
          "player": { "primary": "ff0000", "number": "ffffff", "border": "ff0000" },
          "goalkeeper": { "primary": "00ff00", "number": "000000", "border": "00ff00" }
        }
      },
      "formation": "4-3-3",
      "coach": {
        "id": 123,
        "name": "M. Arteta",
        "photo": "https://media.api-sports.io/football/coachs/123.png"
      },
      "startXI": [
        { "id": 100, "name": "D. Raya", "number": 22, "pos": "G", "grid": "1:1" },
        { "id": 101, "name": "B. White", "number": 4, "pos": "D", "grid": "2:4" },
        { "id": 102, "name": "W. Saliba", "number": 2, "pos": "D", "grid": "2:3" },
        { "id": 103, "name": "G. Magalhaes", "number": 6, "pos": "D", "grid": "2:2" },
        { "id": 104, "name": "J. Timber", "number": 12, "pos": "D", "grid": "2:1" },
        { "id": 105, "name": "T. Partey", "number": 5, "pos": "M", "grid": "3:2" },
        { "id": 106, "name": "M. Odegaard", "number": 8, "pos": "M", "grid": "3:3" },
        { "id": 107, "name": "D. Rice", "number": 41, "pos": "M", "grid": "3:1" },
        { "id": 108, "name": "B. Saka", "number": 7, "pos": "F", "grid": "4:3" },
        { "id": 109, "name": "G. Martinelli", "number": 11, "pos": "F", "grid": "4:1" },
        { "id": 110, "name": "K. Havertz", "number": 29, "pos": "F", "grid": "4:2" }
      ],
      "substitutes": [
        { "id": 200, "name": "N. Ramsdale", "number": 1, "pos": "G" },
        { "id": 201, "name": "K. Tierney", "number": 3, "pos": "D" },
        { "id": 202, "name": "G. Jesus", "number": 9, "pos": "F" },
        { "id": 203, "name": "L. Trossard", "number": 19, "pos": "F" },
        { "id": 204, "name": "J. Zinchenko", "number": 35, "pos": "D" },
        { "id": 205, "name": "F. Vieira", "number": 21, "pos": "M" },
        { "id": 206, "name": "E. Nketiah", "number": 14, "pos": "F" }
      ]
    },
    {
      "team": {
        "id": 40,
        "name": "Liverpool",
        "logo": "https://media.api-sports.io/football/teams/40.png",
        "colors": {
          "player": { "primary": "ff0000", "number": "ffffff", "border": "ff0000" },
          "goalkeeper": { "primary": "000000", "number": "ffffff", "border": "000000" }
        }
      },
      "formation": "4-3-3",
      "coach": {
        "id": 456,
        "name": "A. Slot",
        "photo": "https://media.api-sports.io/football/coachs/456.png"
      },
      "startXI": [
        { "id": 300, "name": "Alisson", "number": 1, "pos": "G", "grid": "1:1" },
        { "id": 301, "name": "T. Alexander-Arnold", "number": 66, "pos": "D", "grid": "2:4" },
        { "id": 302, "name": "I. Konate", "number": 5, "pos": "D", "grid": "2:3" },
        { "id": 303, "name": "V. van Dijk", "number": 4, "pos": "D", "grid": "2:2" },
        { "id": 304, "name": "A. Robertson", "number": 26, "pos": "D", "grid": "2:1" },
        { "id": 305, "name": "A. Mac Allister", "number": 10, "pos": "M", "grid": "3:2" },
        { "id": 306, "name": "D. Szoboszlai", "number": 8, "pos": "M", "grid": "3:3" },
        { "id": 307, "name": "R. Gravenberch", "number": 38, "pos": "M", "grid": "3:1" },
        { "id": 308, "name": "M. Salah", "number": 11, "pos": "F", "grid": "4:3" },
        { "id": 309, "name": "L. Diaz", "number": 7, "pos": "F", "grid": "4:1" },
        { "id": 310, "name": "D. Nunez", "number": 9, "pos": "F", "grid": "4:2" }
      ],
      "substitutes": [
        { "id": 400, "name": "C. Kelleher", "number": 62, "pos": "G" },
        { "id": 401, "name": "J. Gomez", "number": 2, "pos": "D" },
        { "id": 402, "name": "C. Gakpo", "number": 18, "pos": "F" },
        { "id": 403, "name": "W. Endo", "number": 3, "pos": "M" },
        { "id": 404, "name": "D. Jota", "number": 20, "pos": "F" }
      ]
    }
  ]
}
```

**Frontend Usage - Render Formation:**

```typescript
// TypeScript interfaces
interface Player {
  id: number;
  name: string;
  number: number;
  pos: string;
  grid?: string;
}

interface Lineup {
  team: { id: number; name: string; logo: string; colors: any };
  formation: string;
  coach: { id: number; name: string; photo: string };
  startXI: Player[];
  substitutes: Player[];
}

// Get Arsenal lineup (first element is home team)
const arsenalLineup = lineups.find(l => l.team.id === 42);

// Group players by position
const groupByPosition = (players: Player[]) => ({
  goalkeeper: players.filter(p => p.pos === 'G'),
  defenders: players.filter(p => p.pos === 'D'),
  midfielders: players.filter(p => p.pos === 'M'),
  forwards: players.filter(p => p.pos === 'F'),
});

// Parse grid position for CSS positioning
const parseGrid = (grid: string) => {
  const [row, col] = grid.split(':').map(Number);
  return { row, col };
};

// Calculate player position on pitch (percentage-based)
const getPlayerPosition = (grid: string, formation: string) => {
  const { row, col } = parseGrid(grid);
  const totalRows = 5;
  const totalCols = 5;

  return {
    top: `${(row / totalRows) * 100}%`,
    left: `${(col / totalCols) * 100}%`,
  };
};
```

---

#### Response Section: `statistics`

Array containing match statistics for both teams. Statistics are provided as key-value pairs.

**Statistics Object Fields:**

| Field | Type | Description |
|-------|------|-------------|
| `team.id` | integer | Team ID |
| `team.name` | string | Team name |
| `team.logo` | string | Team logo URL |
| `statistics` | object | Key-value pairs of statistics |

**Available Statistics Keys:**

| Key | Type | Description |
|-----|------|-------------|
| `shots_on_goal` | integer | Shots on target |
| `shots_off_goal` | integer | Shots off target |
| `total_shots` | integer | Total shots attempted |
| `blocked_shots` | integer | Shots blocked by defenders |
| `shots_insidebox` | integer | Shots from inside the box |
| `shots_outsidebox` | integer | Shots from outside the box |
| `fouls` | integer | Fouls committed |
| `corner_kicks` | integer | Corner kicks taken |
| `offsides` | integer | Offsides called |
| `ball_possession` | string | Ball possession percentage (e.g., "62%") |
| `yellow_cards` | integer | Yellow cards received |
| `red_cards` | integer | Red cards received |
| `goalkeeper_saves` | integer | Saves made by goalkeeper |
| `total_passes` | integer | Total passes attempted |
| `passes_accurate` | integer | Accurate passes completed |
| `passes_%` | string | Pass accuracy percentage (e.g., "88%") |
| `expected_goals` | string\|null | Expected goals (xG) if available |

**Example Statistics Array:**

```json
{
  "statistics": [
    {
      "team": {
        "id": 42,
        "name": "Arsenal",
        "logo": "https://media.api-sports.io/football/teams/42.png"
      },
      "statistics": {
        "shots_on_goal": 8,
        "shots_off_goal": 5,
        "total_shots": 15,
        "blocked_shots": 2,
        "shots_insidebox": 10,
        "shots_outsidebox": 5,
        "fouls": 12,
        "corner_kicks": 7,
        "offsides": 2,
        "ball_possession": "62%",
        "yellow_cards": 1,
        "red_cards": 0,
        "goalkeeper_saves": 3,
        "total_passes": 542,
        "passes_accurate": 478,
        "passes_%": "88%",
        "expected_goals": "2.45"
      }
    },
    {
      "team": {
        "id": 40,
        "name": "Liverpool",
        "logo": "https://media.api-sports.io/football/teams/40.png"
      },
      "statistics": {
        "shots_on_goal": 4,
        "shots_off_goal": 3,
        "total_shots": 9,
        "blocked_shots": 2,
        "shots_insidebox": 5,
        "shots_outsidebox": 4,
        "fouls": 15,
        "corner_kicks": 4,
        "offsides": 1,
        "ball_possession": "38%",
        "yellow_cards": 2,
        "red_cards": 0,
        "goalkeeper_saves": 5,
        "total_passes": 328,
        "passes_accurate": 267,
        "passes_%": "81%",
        "expected_goals": "0.89"
      }
    }
  ]
}
```

**Frontend Usage - Display Statistics Comparison:**

```typescript
// TypeScript interfaces
interface TeamStatistics {
  team: { id: number; name: string; logo: string };
  statistics: Record<string, number | string>;
}

// Get statistics for comparison display
const [homeStats, awayStats] = statistics;

// Build comparison data
const statLabels: Record<string, string> = {
  ball_possession: 'Possession',
  total_shots: 'Total Shots',
  shots_on_goal: 'Shots on Target',
  corner_kicks: 'Corners',
  fouls: 'Fouls',
  yellow_cards: 'Yellow Cards',
  passes_accurate: 'Accurate Passes',
  'passes_%': 'Pass Accuracy',
};

const comparisonData = Object.entries(statLabels).map(([key, label]) => ({
  label,
  home: homeStats.statistics[key],
  away: awayStats.statistics[key],
}));

// Parse percentage for bar width calculation
const parsePercentage = (value: string | number): number => {
  if (typeof value === 'string' && value.includes('%')) {
    return parseInt(value.replace('%', ''));
  }
  return typeof value === 'number' ? value : 0;
};

// Example React component
const StatBar = ({ label, home, away }: { label: string; home: any; away: any }) => {
  const homeNum = parsePercentage(home);
  const awayNum = parsePercentage(away);
  const total = homeNum + awayNum || 1;

  return (
    <div className="stat-row">
      <span className="home-value">{home}</span>
      <div className="bar-container">
        <div className="home-bar" style={{ width: `${(homeNum / total) * 100}%` }} />
        <div className="away-bar" style={{ width: `${(awayNum / total) * 100}%` }} />
      </div>
      <span className="label">{label}</span>
      <span className="away-value">{away}</span>
    </div>
  );
};
```

---

#### Complete Frontend Example (Next.js)

```typescript
// app/match/[fixtureId]/page.tsx
import { useEffect, useState } from 'react';

interface MatchReport {
  match: Match;
  events: MatchEvent[];
  lineups: Lineup[];
  statistics: TeamStatistics[];
}

export default function MatchReportPage({ params }: { params: { fixtureId: string } }) {
  const [report, setReport] = useState<MatchReport | null>(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    fetch(`http://localhost:8000/api/football/results/${params.fixtureId}/report`)
      .then(res => res.json())
      .then(data => {
        setReport(data.data);
        setLoading(false);
      });
  }, [params.fixtureId]);

  if (loading) return <div>Loading...</div>;
  if (!report) return <div>Match not found</div>;

  const { match, events, lineups, statistics } = report;

  return (
    <div className="match-report">
      {/* Match Header */}
      <div className="match-header">
        <TeamLogo team={match.home} />
        <div className="score">{match.score.display}</div>
        <TeamLogo team={match.away} />
      </div>

      {/* Match Timeline */}
      <div className="timeline">
        <h3>Match Events</h3>
        {events.map((event, i) => (
          <EventItem key={i} event={event} />
        ))}
      </div>

      {/* Lineups */}
      <div className="lineups">
        <h3>Lineups</h3>
        <div className="formations">
          {lineups.map((lineup, i) => (
            <FormationView key={i} lineup={lineup} />
          ))}
        </div>
      </div>

      {/* Statistics */}
      <div className="statistics">
        <h3>Match Statistics</h3>
        <StatisticsComparison
          home={statistics[0]}
          away={statistics[1]}
        />
      </div>
    </div>
  );
}
```

---

**Not Found Response:**
- Status: `404 Not Found`

```json
{
  "data": null,
  "message": "Match not found"
}
```

---

### 25. Get League Standings

Get league table/standings.

**Endpoint:** `GET /api/football/standings`

**Authentication Required:** No

**Query Parameters:**

| Parameter | Type | Required | Default | Description |
|-----------|------|----------|---------|-------------|
| `season` | integer | No | current | Season year (e.g., 2025) |
| `league_id` | integer | No | 39 | League ID (39 = Premier League) |

**Request:**
```http
GET /api/football/standings?season=2025 HTTP/1.1
Host: localhost:8000
Accept: application/json
```

**Success Response:**
- Status: `200 OK`

```json
{
  "data": [
    {
      "rank": 1,
      "team": {
        "id": 42,
        "name": "Arsenal",
        "logo": "https://media.api-sports.io/football/teams/42.png"
      },
      "points": 50,
      "goals_diff": 32,
      "form": "WWWDW",
      "description": "Promotion - Champions League (Group Stage: )",
      "stats": {
        "played": 20,
        "won": 15,
        "drawn": 5,
        "lost": 0,
        "goals_for": 45,
        "goals_against": 13
      },
      "home": {
        "played": 10,
        "won": 8,
        "drawn": 2,
        "lost": 0,
        "goals_for": 25,
        "goals_against": 5
      },
      "away": {
        "played": 10,
        "won": 7,
        "drawn": 3,
        "lost": 0,
        "goals_for": 20,
        "goals_against": 8
      }
    },
    {
      "rank": 2,
      "team": {
        "id": 40,
        "name": "Liverpool",
        "logo": "https://media.api-sports.io/football/teams/40.png"
      },
      "points": 48,
      "goals_diff": 28,
      "form": "WDWWW",
      "description": "Promotion - Champions League (Group Stage: )",
      "stats": {
        "played": 20,
        "won": 14,
        "drawn": 6,
        "lost": 0,
        "goals_for": 42,
        "goals_against": 14
      },
      "home": {
        "played": 10,
        "won": 8,
        "drawn": 2,
        "lost": 0,
        "goals_for": 24,
        "goals_against": 6
      },
      "away": {
        "played": 10,
        "won": 6,
        "drawn": 4,
        "lost": 0,
        "goals_for": 18,
        "goals_against": 8
      }
    }
  ],
  "team": {
    "rank": 1,
    "team": {
      "id": 42,
      "name": "Arsenal",
      "logo": "https://media.api-sports.io/football/teams/42.png"
    },
    "points": 50,
    "goals_diff": 32,
    "form": "WWWDW",
    "description": "Promotion - Champions League (Group Stage: )",
    "stats": {
      "played": 20,
      "won": 15,
      "drawn": 5,
      "lost": 0,
      "goals_for": 45,
      "goals_against": 13
    },
    "home": {
      "played": 10,
      "won": 8,
      "drawn": 2,
      "lost": 0,
      "goals_for": 25,
      "goals_against": 5
    },
    "away": {
      "played": 10,
      "won": 7,
      "drawn": 3,
      "lost": 0,
      "goals_for": 20,
      "goals_against": 8
    }
  },
  "meta": {
    "total": 20,
    "season": 2025,
    "league_id": 39,
    "league_name": "Premier League"
  }
}
```

---

### 26. Get All Competition Standings

Get standings for all competitions Arsenal is participating in. This endpoint returns league tables for Premier League, Champions League groups, FA Cup (if applicable), etc.

**Endpoint:** `GET /api/football/standings/all`

**Authentication Required:** No

**Query Parameters:**

| Parameter | Type | Required | Default | Description |
|-----------|------|----------|---------|-------------|
| `season` | integer | No | current | Season year (e.g., 2025) |

**Request:**
```http
GET /api/football/standings/all HTTP/1.1
Host: localhost:8000
Accept: application/json
```

**Success Response:**
- Status: `200 OK`

The response contains standings for each competition. League competitions have a single `standings` array, while cup competitions with groups (like Champions League) have a `groups` array.

```json
{
  "data": [
    {
      "competition": {
        "id": 39,
        "name": "Premier League",
        "type": "League",
        "logo": "https://media.api-sports.io/football/leagues/39.png",
        "country": {
          "name": "England",
          "code": "GB",
          "flag": "https://media.api-sports.io/flags/gb.svg"
        },
        "season": {
          "year": 2025,
          "start": "2025-08-16",
          "end": "2026-05-24"
        }
      },
      "team_position": {
        "rank": 1,
        "team": {
          "id": 42,
          "name": "Arsenal",
          "logo": "https://media.api-sports.io/football/teams/42.png"
        },
        "points": 50,
        "goals_diff": 32,
        "form": "WWWDW",
        "description": "Promotion - Champions League",
        "stats": {
          "played": 20,
          "won": 15,
          "drawn": 5,
          "lost": 0,
          "goals_for": 45,
          "goals_against": 13
        },
        "home": { "played": 10, "won": 8, "drawn": 2, "lost": 0, "goals_for": 25, "goals_against": 5 },
        "away": { "played": 10, "won": 7, "drawn": 3, "lost": 0, "goals_for": 20, "goals_against": 8 }
      },
      "standings": [
        {
          "rank": 1,
          "team": { "id": 42, "name": "Arsenal", "logo": "..." },
          "points": 50,
          "goals_diff": 32,
          "form": "WWWDW",
          "description": "Promotion - Champions League",
          "stats": { "played": 20, "won": 15, "drawn": 5, "lost": 0, "goals_for": 45, "goals_against": 13 },
          "home": { "played": 10, "won": 8, "drawn": 2, "lost": 0, "goals_for": 25, "goals_against": 5 },
          "away": { "played": 10, "won": 7, "drawn": 3, "lost": 0, "goals_for": 20, "goals_against": 8 }
        },
        {
          "rank": 2,
          "team": { "id": 40, "name": "Liverpool", "logo": "..." },
          "points": 48,
          "goals_diff": 28,
          "form": "WDWWW"
        }
      ]
    },
    {
      "competition": {
        "id": 2,
        "name": "UEFA Champions League",
        "type": "Cup",
        "logo": "https://media.api-sports.io/football/leagues/2.png",
        "country": {
          "name": "World",
          "code": null,
          "flag": null
        },
        "season": {
          "year": 2025,
          "start": "2025-09-17",
          "end": "2026-05-30"
        }
      },
      "team_position": {
        "rank": 1,
        "team": { "id": 42, "name": "Arsenal", "logo": "..." },
        "points": 12,
        "goals_diff": 8,
        "form": "WWWW",
        "description": "Qualified to Round of 16"
      },
      "groups": [
        {
          "name": "Group B",
          "standings": [
            {
              "rank": 1,
              "team": { "id": 42, "name": "Arsenal", "logo": "..." },
              "points": 12,
              "goals_diff": 8,
              "form": "WWWW",
              "description": "Qualified"
            },
            {
              "rank": 2,
              "team": { "id": 157, "name": "Bayern Munich", "logo": "..." },
              "points": 9,
              "goals_diff": 5,
              "form": "WWLD"
            },
            {
              "rank": 3,
              "team": { "id": 496, "name": "PSV", "logo": "..." },
              "points": 4,
              "goals_diff": -2,
              "form": "LDWD"
            },
            {
              "rank": 4,
              "team": { "id": 211, "name": "Sevilla", "logo": "..." },
              "points": 1,
              "goals_diff": -11,
              "form": "LLLD"
            }
          ]
        }
      ]
    }
  ],
  "meta": {
    "total_competitions": 2,
    "season": 2025
  }
}
```

**Response Structure:**

| Field | Type | Description |
|-------|------|-------------|
| `competition` | object | Competition details (id, name, type, logo, country, season) |
| `team_position` | object\|null | Arsenal's position in this competition |
| `standings` | array | League table (for league competitions) |
| `groups` | array | Groups with standings (for cup competitions with group stages) |

**Frontend Usage - Display All Standings:**

```typescript
// TypeScript interfaces
interface StandingTeam {
  rank: number;
  team: { id: number; name: string; logo: string };
  points: number;
  goals_diff: number;
  form: string;
  description: string;
  stats: { played: number; won: number; drawn: number; lost: number; goals_for: number; goals_against: number };
  home: { played: number; won: number; drawn: number; lost: number; goals_for: number; goals_against: number };
  away: { played: number; won: number; drawn: number; lost: number; goals_for: number; goals_against: number };
}

interface CompetitionStanding {
  competition: Competition;
  team_position: StandingTeam | null;
  standings?: StandingTeam[];
  groups?: { name: string; standings: StandingTeam[] }[];
}

// React component example
const AllStandings = () => {
  const [data, setData] = useState<CompetitionStanding[]>([]);

  useEffect(() => {
    fetch('http://localhost:8000/api/football/standings/all')
      .then(res => res.json())
      .then(res => setData(res.data));
  }, []);

  return (
    <div className="standings-container">
      {data.map(comp => (
        <div key={comp.competition.id} className="competition-standings">
          <div className="competition-header">
            <img src={comp.competition.logo} alt={comp.competition.name} width="32" />
            <h3>{comp.competition.name}</h3>
            {comp.team_position && (
              <span className="arsenal-position">
                Arsenal: {comp.team_position.rank}
                {getOrdinalSuffix(comp.team_position.rank)} ({comp.team_position.points} pts)
              </span>
            )}
          </div>

          {comp.standings && (
            <StandingsTable standings={comp.standings} />
          )}

          {comp.groups && comp.groups.map(group => (
            <div key={group.name} className="group">
              <h4>{group.name}</h4>
              <StandingsTable standings={group.standings} />
            </div>
          ))}
        </div>
      ))}
    </div>
  );
};

// Helper function
const getOrdinalSuffix = (n: number) => {
  const s = ['th', 'st', 'nd', 'rd'];
  const v = n % 100;
  return s[(v - 20) % 10] || s[v] || s[0];
};
```

---

### 27. Get Arsenal Season Statistics

Get Arsenal's season statistics summary.

**Endpoint:** `GET /api/football/arsenal/stats`

**Authentication Required:** No

**Query Parameters:**

| Parameter | Type | Required | Default | Description |
|-----------|------|----------|---------|-------------|
| `season` | integer | No | current | Season year (e.g., 2025) |

**Request:**
```http
GET /api/football/arsenal/stats?season=2025 HTTP/1.1
Host: localhost:8000
Accept: application/json
```

**Success Response:**
- Status: `200 OK`

```json
{
  "data": {
    "season": 2025,
    "matches": {
      "played": 25,
      "won": 18,
      "drawn": 5,
      "lost": 2
    },
    "goals": {
      "for": 55,
      "against": 18,
      "difference": 37
    },
    "form": "WWDWW",
    "league_position": {
      "rank": 1,
      "points": 59,
      "played": 25
    }
  }
}
```

---

### Football API Response Fields

**Arsenal Object:**

| Field | Type | Description |
|-------|------|-------------|
| `is_home` | boolean | Whether Arsenal is the home team |
| `opponent` | string | Opponent team name |
| `opponent_logo` | string | Opponent team logo URL |
| `venue_type` | string | `H` for home, `A` for away |
| `goals_for` | integer | Goals scored by Arsenal (results only) |
| `goals_against` | integer | Goals conceded by Arsenal (results only) |
| `result` | string | `W` = Win, `D` = Draw, `L` = Loss (results only) |

**League IDs:**

| ID | League |
|----|--------|
| 39 | Premier League |
| 2 | UEFA Champions League |
| 3 | UEFA Europa League |
| 45 | FA Cup |
| 48 | EFL Cup (League Cup) |

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
