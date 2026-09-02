# Sohni API Contracts - Backend Integration Guide

## 📋 Overview

This document defines the API contracts between the **Sohni Frontend** and the **Backend Services**. All responses should be JSON.

---

## 🔐 Authentication API

### **1. Sign Up**
```
POST /api/auth/signup
Content-Type: application/json

Request Body:
{
    "fullname": "John Doe",
    "email": "john@gmail.com",
    "password": "password123"
}

Response (Success - 201):
{
    "success": true,
    "message": "Account created successfully",
    "data": {
        "user_id": "user_12345",
        "email": "john@gmail.com",
        "name": "John Doe",
        "verification_status": "pending"
    }
}

Response (Error - 400):
{
    "success": false,
    "message": "Email already exists",
    "errors": {
        "email": ["Email already registered"]
    }
}

Response (Error - 422):
{
    "success": false,
    "message": "Validation failed",
    "errors": {
        "email": ["Must be a valid Gmail address"],
        "password": ["Password must be at least 6 characters"]
    }
}
```

### **2. Sign In**
```
POST /api/auth/signin
Content-Type: application/json

Request Body:
{
    "email": "john@gmail.com",
    "password": "password123"
}

Response (Success - 200):
{
    "success": true,
    "message": "Login successful",
    "data": {
        "token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...",
        "user": {
            "id": "user_12345",
            "email": "john@gmail.com",
            "name": "John Doe",
            "profile_status": "incomplete", // or "complete"
            "sohni_id": "sohni_4821736",
            "avatar": "https://...",
            "verified": true
        }
    }
}

Response (Error - 401):
{
    "success": false,
    "message": "Invalid email or password"
}
```

### **3. Send Verification Email**
```
POST /api/auth/send-verification-email
Content-Type: application/json
Authorization: Bearer {token}

Request Body:
{
    "email": "john@gmail.com"
}

Response (Success - 200):
{
    "success": true,
    "message": "Verification email sent to john@gmail.com",
    "data": {
        "email": "john@gmail.com",
        "expires_in": 3600  // seconds
    }
}

Response (Error - 400):
{
    "success": false,
    "message": "Email not found or already verified"
}
```

### **4. Verify Email**
```
POST /api/auth/verify-email
Content-Type: application/json

Request Body:
{
    "email": "john@gmail.com",
    "token": "verification_token_from_email"
}

Response (Success - 200):
{
    "success": true,
    "message": "Email verified successfully",
    "data": {
        "verified": true,
        "email": "john@gmail.com",
        "next_step": "profile_setup"
    }
}

Response (Error - 400):
{
    "success": false,
    "message": "Invalid or expired verification token"
}
```

### **5. Logout**
```
POST /api/auth/logout
Authorization: Bearer {token}

Response (Success - 200):
{
    "success": true,
    "message": "Logged out successfully"
}
```

---

## 👤 Profile API

### **1. Create Profile**
```
POST /api/profile/create
Content-Type: application/json
Authorization: Bearer {token}

Request Body:
{
    "first_name": "John",
    "last_name": "Doe",
    "phone": "+92 300 1234567",
    "email": "john@gmail.com",
    "sohni_id": "sohni_4821736",  // auto-generated or premium selected
    "sohni_id_type": "free",      // or "premium"
    "address": "Lahore, Punjab",
    "education": "BS Computer Science, LUMS",  // optional
    "profile_image": "base64_or_file_blob"     // optional
}

Response (Success - 201):
{
    "success": true,
    "message": "Profile created successfully",
    "data": {
        "profile_id": "profile_12345",
        "user_id": "user_12345",
        "first_name": "John",
        "last_name": "Doe",
        "sohni_id": "sohni_4821736",
        "phone": "+92 300 1234567",
        "address": "Lahore, Punjab",
        "education": "BS Computer Science, LUMS",
        "avatar_url": "https://cdn.sohni.com/avatars/user_12345.jpg",
        "profile_complete": true,
        "created_at": "2026-01-15T10:30:00Z"
    }
}

Response (Error - 409):
{
    "success": false,
    "message": "Sohni ID already taken",
    "errors": {
        "sohni_id": ["This ID is already in use"]
    }
}
```

### **2. Get Profile**
```
GET /api/profile/
Authorization: Bearer {token}

Response (Success - 200):
{
    "success": true,
    "data": {
        "profile_id": "profile_12345",
        "user_id": "user_12345",
        "first_name": "John",
        "last_name": "Doe",
        "sohni_id": "sohni_4821736",
        "phone": "+92 300 1234567",
        "email": "john@gmail.com",
        "address": "Lahore, Punjab",
        "education": "BS Computer Science, LUMS",
        "avatar_url": "https://...",
        "profile_complete": true,
        "bio": null,
        "status": "online",
        "created_at": "2026-01-15T10:30:00Z"
    }
}
```

### **3. Update Profile**
```
PUT /api/profile/update
Content-Type: application/json
Authorization: Bearer {token}

Request Body:
{
    "first_name": "John",
    "last_name": "Doe",
    "phone": "+92 300 1234567",
    "address": "Karachi, Sindh",
    "education": "BS Software Engineering",
    "bio": "Foodie & traveler"
}

Response (Success - 200):
{
    "success": true,
    "message": "Profile updated successfully",
    "data": { /* Updated profile object */ }
}
```

### **4. Upload Profile Image**
```
POST /api/profile/upload-image
Content-Type: multipart/form-data
Authorization: Bearer {token}

Form Data:
{
    "image": <File: PNG/JPG/GIF, max 5MB>
}

Response (Success - 200):
{
    "success": true,
    "message": "Image uploaded successfully",
    "data": {
        "avatar_url": "https://cdn.sohni.com/avatars/user_12345_v2.jpg",
        "uploaded_at": "2026-01-15T11:45:00Z"
    }
}

Response (Error - 422):
{
    "success": false,
    "message": "File validation failed",
    "errors": {
        "image": ["File must be PNG, JPG, or GIF", "File size must not exceed 5MB"]
    }
}
```

---

## 🆔 Sohni ID API

### **1. Generate Free ID**
```
GET /api/sohni-ids/generate
Authorization: Bearer {token}

Response (Success - 200):
{
    "success": true,
    "data": {
        "sohni_id": "sohni_4821736",
        "type": "free",
        "digits": 10,
        "available": true,
        "created_at": "2026-01-15T10:30:00Z"
    }
}
```

### **2. Get Available Premium IDs**
```
GET /api/sohni-ids/available
Authorization: Bearer {token}

Response (Success - 200):
{
    "success": true,
    "data": [
        {
            "sohni_id": "JohnDoe_2026",  // 14-digit or custom
            "type": "premium",
            "price": 2999,
            "currency": "PKR",
            "duration": "1 year",
            "features": [
                "Custom 14-digit ID",
                "Premium badge on profile",
                "Early access to features",
                "Priority support"
            ],
            "available": true
        },
        // ... more options
    ]
}
```

### **3. Check ID Availability**
```
POST /api/sohni-ids/check-availability
Content-Type: application/json

Request Body:
{
    "sohni_id": "john_doe_123"
}

Response (Success - 200):
{
    "success": true,
    "data": {
        "sohni_id": "john_doe_123",
        "available": true
    }
}

Response (Not Available - 200):
{
    "success": true,
    "data": {
        "sohni_id": "john_doe_123",
        "available": false
    }
}
```

---

## 💬 Chat API

### **1. Get Conversations**
```
GET /api/chat/conversations?page=1&limit=20
Authorization: Bearer {token}

Response (Success - 200):
{
    "success": true,
    "data": [
        {
            "conversation_id": "conv_12345",
            "name": "Lahore Foodies 🍛",
            "type": "group",  // or "direct"
            "avatar": "https://...",
            "last_message": "See you tomorrow!",
            "last_message_time": "2026-01-15T14:30:00Z",
            "unread_count": 2,
            "member_count": 142,
            "is_online": true,
            "updated_at": "2026-01-15T14:30:00Z"
        },
        // ... more conversations
    ],
    "pagination": {
        "current_page": 1,
        "total_pages": 5,
        "total": 100
    }
}
```

### **2. Get Messages**
```
GET /api/chat/messages/{conversationId}?page=1&limit=50
Authorization: Bearer {token}

Response (Success - 200):
{
    "success": true,
    "data": [
        {
            "message_id": "msg_12345",
            "sender_id": "user_12345",
            "sender_name": "John Doe",
            "sender_avatar": "https://...",
            "content": "Best nihari in Lahore? 🤔",
            "type": "text",  // or "image", "file", "voice"
            "read": true,
            "created_at": "2026-01-15T14:25:00Z",
            "updated_at": "2026-01-15T14:25:00Z"
        },
        // ... more messages
    ],
    "pagination": {
        "current_page": 1,
        "total_pages": 10,
        "total": 500
    }
}
```

### **3. Send Message**
```
POST /api/chat/messages
Content-Type: application/json
Authorization: Bearer {token}

Request Body:
{
    "conversation_id": "conv_12345",
    "content": "Waris Nihari hands down! 🔥",
    "type": "text"  // or "image", "file", "voice"
}

Response (Success - 201):
{
    "success": true,
    "message": "Message sent",
    "data": {
        "message_id": "msg_12346",
        "conversation_id": "conv_12345",
        "sender_id": "user_12345",
        "content": "Waris Nihari hands down! 🔥",
        "type": "text",
        "read": false,
        "created_at": "2026-01-15T14:30:00Z"
    }
}
```

### **4. Search Conversations**
```
GET /api/chat/search?q=foodies&type=group
Authorization: Bearer {token}

Response (Success - 200):
{
    "success": true,
    "data": [
        {
            "conversation_id": "conv_12345",
            "name": "Lahore Foodies 🍛",
            "type": "group",
            "avatar": "https://...",
            "member_count": 142
        }
    ]
}
```

### **5. Delete Message**
```
DELETE /api/chat/messages/{messageId}
Authorization: Bearer {token}

Response (Success - 200):
{
    "success": true,
    "message": "Message deleted"
}
```

---

## 👥 User API

### **1. Get User Profile**
```
GET /api/user/profile
Authorization: Bearer {token}

Response (Success - 200):
{
    "success": true,
    "data": {
        "user_id": "user_12345",
        "email": "john@gmail.com",
        "name": "John Doe",
        "sohni_id": "sohni_4821736",
        "avatar": "https://...",
        "bio": "Foodie & traveler",
        "status": "online",  // or "offline", "away"
        "verified": true,
        "created_at": "2026-01-15T10:30:00Z"
    }
}
```

### **2. Update User Profile**
```
PUT /api/user/profile
Content-Type: application/json
Authorization: Bearer {token}

Request Body:
{
    "bio": "Foodie & coffee lover ☕",
    "status": "online"
}

Response (Success - 200):
{
    "success": true,
    "message": "Profile updated",
    "data": { /* Updated user object */ }
}
```

### **3. Get User Settings**
```
GET /api/user/settings
Authorization: Bearer {token}

Response (Success - 200):
{
    "success": true,
    "data": {
        "notifications_enabled": true,
        "email_notifications": true,
        "push_notifications": true,
        "message_notifications": true,
        "privacy": "public",  // or "private"
        "show_online_status": true,
        "two_factor_enabled": false,
        "language": "en"
    }
}
```

### **4. Update User Settings**
```
PUT /api/user/settings
Content-Type: application/json
Authorization: Bearer {token}

Request Body:
{
    "notifications_enabled": false,
    "privacy": "private",
    "language": "ur"
}

Response (Success - 200):
{
    "success": true,
    "message": "Settings updated",
    "data": { /* Updated settings */ }
}
```

---

## ✅ Standard Response Format

### **Success Response**
```json
{
    "success": true,
    "message": "Operation successful",
    "data": { /* Response data */ }
}
```

### **Error Response**
```json
{
    "success": false,
    "message": "Error description",
    "errors": {
        "field_name": ["Error 1", "Error 2"]
    }
}
```

### **HTTP Status Codes**
- `200` — Success
- `201` — Created
- `400` — Bad Request
- `401` — Unauthorized
- `403` — Forbidden
- `404` — Not Found
- `409` — Conflict (e.g., duplicate ID)
- `422` — Validation Failed
- `500` — Server Error

---

## 🔑 Authentication

All endpoints except signup/signin require:
```
Authorization: Bearer {token}
```

Token should be stored in:
- LocalStorage: `localStorage.setItem('auth_token', token)`
- Or SessionStorage
- Or HTTP-only Cookie (recommended)

---

## 📊 Database Requirements

### **Users Table**
```sql
CREATE TABLE users (
    id UUID PRIMARY KEY,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    name VARCHAR(255),
    verified_at TIMESTAMP,
    email_verified_at TIMESTAMP,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

### **User Profiles Table**
```sql
CREATE TABLE user_profiles (
    id UUID PRIMARY KEY,
    user_id UUID FOREIGN KEY,
    first_name VARCHAR(100),
    last_name VARCHAR(100),
    sohni_id VARCHAR(50) UNIQUE,
    phone VARCHAR(20),
    address VARCHAR(255),
    education VARCHAR(255),
    bio TEXT,
    avatar_url VARCHAR(255),
    status VARCHAR(20),  -- online, offline, away
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

### **Conversations Table**
```sql
CREATE TABLE conversations (
    id UUID PRIMARY KEY,
    name VARCHAR(255),
    type VARCHAR(20),  -- direct, group
    creator_id UUID FOREIGN KEY,
    avatar_url VARCHAR(255),
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

### **Messages Table**
```sql
CREATE TABLE messages (
    id UUID PRIMARY KEY,
    conversation_id UUID FOREIGN KEY,
    sender_id UUID FOREIGN KEY,
    content TEXT,
    type VARCHAR(20),  -- text, image, file, voice
    read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

---

## 🧪 Testing with cURL

```bash
# Sign Up
curl -X POST http://127.0.0.1:8000/api/auth/signup \
  -H "Content-Type: application/json" \
  -d '{"fullname":"John Doe","email":"john@gmail.com","password":"pass123"}'

# Sign In
curl -X POST http://127.0.0.1:8000/api/auth/signin \
  -H "Content-Type: application/json" \
  -d '{"email":"john@gmail.com","password":"pass123"}'

# Get Profile (with token)
curl -X GET http://127.0.0.1:8000/api/profile/ \
  -H "Authorization: Bearer {token}"
```

---

## 📝 Notes

- All timestamps should be ISO 8601 format
- Use UUIDs for IDs (not sequential integers)
- Passwords should be hashed with bcrypt
- Email verification should use random tokens (not predictable)
- Implement rate limiting on auth endpoints
- Use CORS headers for frontend requests
- Implement proper error logging
- Use middleware for authentication & authorization

---

**This API contract is ready for backend implementation!** 🚀
