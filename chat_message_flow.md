# Real-time Chat Message Flow

## Stack

- Laravel
- Inertia.js
- Vue 3
- Laravel Echo
- Reverb / Pusher
- Private Channel

---

# Send Message Flow

```
Sender Vue
    |
    | Axios POST
    ↓
Laravel Controller
    |
    | Save Message
    ↓
Database
    |
    | Broadcast MessageSent
    ↓
Private Channel
chat.receiver_id
    |
    ↓
Receiver Echo Listener
    |
    ↓
UI Update
```

---

## Sender Side

User sends message:

```javascript
axios.post(
    route("store_message"),
    form.data()
)
```

Laravel:

```php
$message = Message::create([
    'sender_id' => Auth::id(),
    'receiver_id' => $request->receiver_id,
    'message' => $request->message
]);

broadcast(new MessageSent($message))->toOthers();
```

After response:

```javascript
newMessage.value.push(message);
```

Sender UI updates immediately.

---

# Receiver Side

Receiver listens:

```javascript
window.Echo
.private(`chat.${authId}`)
.listen(".App\\Events\\MessageSent", (e)=>{

    newMessage.value.push(e);

});
```

Example:

```
sender_id   = 3
receiver_id = 5

Channel:

chat.5
```

Only user 5 receives the message.

---

# Read Status Flow

```
Receiver opens chat
        |
        ↓
makeReas(message)
        |
        ↓
POST /message/read/{id}
        |
        ↓
Update is_read = true
        |
        ↓
Broadcast MessageRead
        |
        ↓
Sender receives event
        |
        ↓
message.is_read = true
        |
        ↓
✓ → ✓✓
```

---

## Read Controller

```php
public function read_message(Message $message)
{
    $message->update([
        'is_read' => true
    ]);

    broadcast(
        new MessageRead($message)
    );

    return response()->json([
        'success' => true
    ]);
}
```

---

## MessageRead Channel

MessageRead is sent back to sender:

```php
return new PrivateChannel(
    'chat.' . $message->sender_id
);
```

Example:

```
User 3 sends message
        |
        ↓
User 5 reads
        |
        ↓
MessageRead → chat.3
        |
        ↓
User 3 gets ✓✓
```

---

# Complete Flow

```
SEND

Vue
 ↓
Axios
 ↓
Laravel
 ↓
Database
 ↓
MessageSent
 ↓
Receiver


READ

Receiver
 ↓
Read Request
 ↓
Database Update
 ↓
MessageRead
 ↓
Sender
 ↓
✓✓
```

---

# Features

✅ Real-time messaging  
✅ Private user channel  
✅ No page reload  
✅ Unread count  
✅ Read / Seen status  
✅ Real-time UI update  