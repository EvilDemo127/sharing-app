# Real-time Chat Message Flow

## Sender Side

```text
Sender
 |
 | axios POST
 |
 ↓
Laravel Controller
 |
 | Save message to database
 |
 ↓
Broadcast MessageSent Event
 |
 ↓
Return response with message data
 |
 ↓
newMessage.push(response.data.message)
 |
 ↓
UI updates immediately
```

### Detail

1. User types a message.
2. Vue sends the message using Axios.

```js
axios.post(route('store_message'), {
    receiver_id: 3,
    message: "Hello"
});
```

3. Laravel receives the request.

```php
$message = Message::create([
    'sender_id' => Auth::id(),
    'receiver_id' => $request->receiver_id,
    'message' => $request->message
]);
```

4. Laravel broadcasts the event.

```php
broadcast(new MessageSent($message))->toOthers();
```

5. Laravel returns the created message.

```php
return response()->json([
    'message' => $message->load('sender')
]);
```

6. Vue adds the message to the current chat.

```js
newMessage.value.push(response.data.message);
```

7. The message appears immediately without page reload.


---

# Receiver Side

```text
Receiver
 |
 ↓
Private Channel (chat.receiver_id)
 |
 ↓
Echo listens MessageSent Event
 |
 ↓
Receive message data
 |
 ↓
newMessage.push(event)
 |
 ↓
UI updates immediately
```

### Detail

1. Receiver subscribes to private channel.

```js
window.Echo
    .private(`chat.${authId}`)
    .listen(".App\\Events\\MessageSent", (e) => {

        newMessage.value.push(e);

    });
```

2. Laravel sends the event to:

```php
PrivateChannel('chat.' . $message->receiver_id)
```

Example:

```text
sender_id   = 5
receiver_id = 3

Event Channel:
chat.3
```

3. Receiver with user ID `3` receives the message.

4. Vue pushes the message into the array.

```js
newMessage.value.push(e);
```

5. Vue reactive system updates the UI.


---

# Complete Flow

```text
                 SEND MESSAGE

Sender Vue
    |
    | axios POST
    ↓
Laravel Controller
    |
    | Message::create()
    ↓
Database
    |
    | MessageSent Event
    ↓
Private Channel chat.receiver_id
    |
    +----------------+
    |                |
    ↓                ↓

Sender UI          Receiver UI

newMessage        Echo Listener
.push()              |
    |                |
    ↓                ↓
Instant UI       newMessage.push()
Update               |
                     ↓
                Instant UI Update
```

---

# Read Status Flow (Seen)

```text
Receiver opens chat
        |
        ↓
Send read request
        |
        ↓
Laravel update is_read=true
        |
        ↓
Broadcast MessageRead Event
        |
        ↓
Sender receives event
        |
        ↓
message.is_read=true
        |
        ↓
✓ changes to ✓✓
```