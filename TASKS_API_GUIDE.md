# Tasks API Documentation 📋

## تم إنشاء نظام Tasks كامل يطابق TaskModel الخاص بـ Flutter! ✅

---

## Database Structure

### Tasks Table:
```sql
- id (Primary Key)
- user_id (Foreign Key → users.id)
- title (String)
- description (Text, nullable)
- start_time (Time format: HH:mm)
- end_time (Time format: HH:mm)
- status (Enum: 'todo', 'progress', 'done') - Default: 'todo'
- color_index (Integer: 0-4) - Default: 0
- created_at, updated_at (Timestamps)
```

### Task Colors:
```php
0 => '#2196F3', // Blue
1 => '#4CAF50', // Green  
2 => '#FF9800', // Orange
3 => '#9C27B0', // Purple
4 => '#F44336', // Red
```

---

## API Endpoints

### 🔐 Authentication Required
All endpoints require `Authorization: Bearer {jwt_token}`

### 1. **Get All Tasks**
```http
GET /api/tasks
GET /api/tasks?status=todo     // Filter by status
GET /api/tasks?status=progress
GET /api/tasks?status=done
```

**Response:**
```json
{
    "status": "success",
    "message": "Tasks retrieved successfully",
    "data": [
        {
            "id": 1,
            "user_id": 1,
            "title": "Complete Project",
            "description": "Finish the mobile app development",
            "start_time": "09:00",
            "end_time": "17:00", 
            "status": "progress",
            "color_index": 1,
            "color_hex": "#4CAF50",
            "created_at": "2025-10-03T11:40:15.000000Z",
            "updated_at": "2025-10-03T11:40:15.000000Z"
        }
    ],
    "count": 1
}
```

### 2. **Create New Task**
```http
POST /api/tasks
Content-Type: application/json

{
    "title": "Meeting with Client",
    "description": "Discuss project requirements",
    "start_time": "10:00",
    "end_time": "11:30",
    "status": "todo",
    "color_index": 2
}
```

**Validation Rules:**
- `title`: Required, max 255 characters
- `description`: Optional, text
- `start_time`: Required, format HH:mm
- `end_time`: Required, format HH:mm, must be after start_time
- `status`: Optional, one of: todo, progress, done
- `color_index`: Optional, integer 0-4

### 3. **Get Single Task**
```http
GET /api/tasks/{id}
```

### 4. **Update Task**
```http
PUT /api/tasks/{id}
Content-Type: application/json

{
    "title": "Updated Title",
    "status": "done"
}
```
*Note: All fields are optional for updates*

### 5. **Delete Task**
```http
DELETE /api/tasks/{id}
```

---

## Error Responses

### **Validation Error (422):**
```json
{
    "status": "error",
    "message": "Validation failed",
    "errors": {
        "end_time": ["The end time must be after start time."]
    }
}
```

### **Not Found (404):**
```json
{
    "status": "error",
    "message": "Task not found"
}
```

### **Unauthorized (401):**
```json
{
    "status": "error",
    "message": "Unauthenticated"
}
```

---

## Flutter Integration

### TaskModel يطابق تماماً API Response:
```dart
enum TaskStatus { todo, progress, done }

class TaskModel {
  final int id;
  final String title;
  final String description;
  final String startTime;  // "HH:mm" format
  final String endTime;    // "HH:mm" format
  final TaskStatus status;
  final int colorIndex;
  final String colorHex;   // من API
  
  // ... rest of your model
}
```

### Example API Call in Flutter:
```dart
// Get all tasks
final response = await http.get(
  Uri.parse('$baseUrl/api/tasks'),
  headers: {
    'Authorization': 'Bearer $token',
    'Content-Type': 'application/json',
  },
);

// Create task
final response = await http.post(
  Uri.parse('$baseUrl/api/tasks'),
  headers: {
    'Authorization': 'Bearer $token',
    'Content-Type': 'application/json',
  },
  body: jsonEncode({
    'title': 'New Task',
    'description': 'Task description',
    'start_time': '09:00',
    'end_time': '10:00',
    'status': 'todo',
    'color_index': 0,
  }),
);
```

---

## Features ✨

- ✅ **User Isolation**: Each user sees only their tasks
- ✅ **Status Filtering**: Filter tasks by todo/progress/done
- ✅ **Color System**: 5 predefined colors matching Flutter
- ✅ **Time Validation**: End time must be after start time
- ✅ **Full CRUD**: Create, Read, Update, Delete
- ✅ **Error Handling**: Comprehensive validation and error responses
- ✅ **Logging**: All operations logged for debugging
- ✅ **Security**: JWT authentication required

**Ready to integrate with your Flutter app! 🚀**