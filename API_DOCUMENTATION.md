# Spark Todo API Documentation

Base URL: `http://localhost:8000/api`

## Authentication

All protected endpoints require `Authorization: Bearer {token}` header.

---

## Endpoints

### 1. Google Login (Public)
**POST** `/auth/google`

Login menggunakan Firebase ID Token dari Android app.

**Request Body:**
```json
{
  "id_token": "eyJhbGciOiJSUzI1NiIsImtpZCI6..."
}
```

**Response (Success - 200):**
```json
{
  "success": true,
  "message": "Login successful",
  "data": {
    "user": {
      "id": 1,
      "name": "Maya Okafor",
      "email": "maya@spark.app"
    },
    "token": "1|abcdef123456..."
  }
}
```

**Response (Error - 401):**
```json
{
  "success": false,
  "message": "Invalid Firebase token",
  "error": "Token expired"
}
```

---

### 2. Get Current User (Protected)
**GET** `/auth/me`

Get authenticated user information.

**Headers:**
```
Authorization: Bearer {token}
```

**Response (200):**
```json
{
  "success": true,
  "data": {
    "user": {
      "id": 1,
      "name": "Maya Okafor",
      "email": "maya@spark.app",
      "created_at": "2026-04-18T07:16:25.000000Z"
    }
  }
}
```

---

### 3. Logout (Protected)
**POST** `/auth/logout`

Revoke current access token.

**Headers:**
```
Authorization: Bearer {token}
```

**Response (200):**
```json
{
  "success": true,
  "message": "Logged out successfully"
}
```

---

### 4. Get All Tasks (Protected)
**GET** `/tasks`

Get all tasks for the authenticated user, sorted by done status and creation date.

**Headers:**
```
Authorization: Bearer {token}
```

**Response (200):**
```json
{
  "success": true,
  "data": {
    "tasks": [
      {
        "id": 1,
        "user_id": 1,
        "title": "Finish Q2 roadmap",
        "tag": "Work",
        "time": "14:00",
        "priority": "high",
        "done": false,
        "created_at": "2026-04-18T07:30:00.000000Z",
        "updated_at": "2026-04-18T07:30:00.000000Z"
      }
    ]
  }
}
```

---

### 5. Create Task (Protected)
**POST** `/tasks`

Create a new task.

**Headers:**
```
Authorization: Bearer {token}
Content-Type: application/json
```

**Request Body:**
```json
{
  "title": "Review pull requests",
  "tag": "Work",
  "time": "15:30",
  "priority": "med"
}
```

**Fields:**
- `title` (required, string, max 255)
- `tag` (optional, string, default: "Work")
- `time` (optional, string)
- `priority` (optional, enum: low/med/high, default: "med")

**Response (201):**
```json
{
  "success": true,
  "message": "Task created successfully",
  "data": {
    "task": {
      "id": 2,
      "user_id": 1,
      "title": "Review pull requests",
      "tag": "Work",
      "time": "15:30",
      "priority": "med",
      "done": false,
      "created_at": "2026-04-18T08:00:00.000000Z",
      "updated_at": "2026-04-18T08:00:00.000000Z"
    }
  }
}
```

---

### 6. Get Single Task (Protected)
**GET** `/tasks/{id}`

Get a specific task by ID.

**Headers:**
```
Authorization: Bearer {token}
```

**Response (200):**
```json
{
  "success": true,
  "data": {
    "task": {
      "id": 1,
      "user_id": 1,
      "title": "Finish Q2 roadmap",
      "tag": "Work",
      "time": "14:00",
      "priority": "high",
      "done": false,
      "created_at": "2026-04-18T07:30:00.000000Z",
      "updated_at": "2026-04-18T07:30:00.000000Z"
    }
  }
}
```

**Response (404):**
```json
{
  "success": false,
  "message": "Task not found"
}
```

---

### 7. Update Task (Protected)
**PUT/PATCH** `/tasks/{id}`

Update an existing task. All fields are optional.

**Headers:**
```
Authorization: Bearer {token}
Content-Type: application/json
```

**Request Body:**
```json
{
  "title": "Finish Q2 roadmap (updated)",
  "priority": "low",
  "done": true
}
```

**Response (200):**
```json
{
  "success": true,
  "message": "Task updated successfully",
  "data": {
    "task": {
      "id": 1,
      "user_id": 1,
      "title": "Finish Q2 roadmap (updated)",
      "tag": "Work",
      "time": "14:00",
      "priority": "low",
      "done": true,
      "created_at": "2026-04-18T07:30:00.000000Z",
      "updated_at": "2026-04-18T08:15:00.000000Z"
    }
  }
}
```

---

### 8. Delete Task (Protected)
**DELETE** `/tasks/{id}`

Delete a task.

**Headers:**
```
Authorization: Bearer {token}
```

**Response (200):**
```json
{
  "success": true,
  "message": "Task deleted successfully"
}
```

---

### 9. Toggle Task Status (Protected)
**POST** `/tasks/{id}/toggle`

Toggle task's done status (true ↔ false).

**Headers:**
```
Authorization: Bearer {token}
```

**Response (200):**
```json
{
  "success": true,
  "message": "Task status toggled",
  "data": {
    "task": {
      "id": 1,
      "user_id": 1,
      "title": "Finish Q2 roadmap",
      "tag": "Work",
      "time": "14:00",
      "priority": "high",
      "done": true,
      "created_at": "2026-04-18T07:30:00.000000Z",
      "updated_at": "2026-04-18T08:20:00.000000Z"
    }
  }
}
```

---

## Error Responses

### Validation Error (422)
```json
{
  "message": "The title field is required.",
  "errors": {
    "title": [
      "The title field is required."
    ]
  }
}
```

### Unauthorized (401)
```json
{
  "message": "Unauthenticated."
}
```

### Not Found (404)
```json
{
  "success": false,
  "message": "Task not found"
}
```

---

## Next Steps

1. **Setup Firebase Project**
   - Create Firebase project di console.firebase.google.com
   - Download service account JSON
   - Save path di `.env`: `FIREBASE_CREDENTIALS=/path/to/firebase-credentials.json`

2. **Test API**
   - Use Postman/Thunder Client/cURL
   - Test authentication flow
   - Test CRUD operations

3. **Connect Android App**
   - Add Retrofit/Ktor dependency
   - Create API service
   - Implement authentication
   - Replace mock data with API calls
