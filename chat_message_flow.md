# Real-time Chat System Architecture

## Stack

- Laravel
- Inertia.js
- Vue 3
- Laravel Echo
- Reverb / Pusher
- Private Channel
- Presence Channel


# 1. SEND MESSAGE FLOW


```
USER A (Sender)

        |
        |
        | Axios POST
        ↓

Vue Component

        |
        ↓

Laravel Controller

        |
        |
        ├── Validate Request
        |
        ├── Save Message
        |
        ↓

Database

        |
        |
        ├── MessageSent Event
        |
        ↓

Private Channel

chat.receiver_id

        |
        ↓

USER B (Receiver)

        |
        ↓

Echo Listener

        |
        ↓

Update Chat UI
```


## Sender Vue

```javascript
axios.post(
    route("store_message"),
    form.data()
);
```


Sender UI update:

```javascript
messages.value.push(message);
```


---

## Laravel Controller

```php
$message = Message::create([

    'sender_id'   => Auth::id(),

    'receiver_id' => $request->receiver_id,

    'message'     => $request->message,

]);


broadcast(
    new MessageSent($message)
)->toOthers();
```


`toOthers()` it mean not send back to sender


---

# Receiver Echo Listener

```javascript
window.Echo
.private(`chat.${authId}`)

.listen(
    ".App\\Events\\MessageSent",

    (e)=>{

        messages.value.push(e);

    }
);
```


Example:

```
User A

id = 3


User B

id = 5


Channel:

chat.5
```

User 5 only user-5 can recieve


---


# 2. READ / SEEN STATUS FLOW


```
USER B Opens Message

        |
        ↓

markRead(message)

        |
        ↓

POST /message/read/{id}

        |
        ↓

Update Database

is_read = true

        |
        ↓

Broadcast MessageRead

        |
        ↓

Sender Channel

chat.sender_id

        |
        ↓

USER A

        |
        ↓

Update UI

✓ → ✓✓
```


## Read Controller

```php
public function read_message(Message $message)
{

    $message->update([
        'is_read'=>true
    ]);


    broadcast(
        new MessageRead($message)
    );


    return response()->json([
        'success'=>true
    ]);
}
```


## MessageRead Channel

```php
return new PrivateChannel(
    "chat.".$message->sender_id
);
```


Flow:

```
User A sends message

        ↓

User B reads

        ↓

MessageRead

        ↓

chat.3

        ↓

User A gets ✓✓
```


---


# 3. ONLINE STATUS FLOW

Using Presence Channel


```
USER Login

        |
        ↓

Echo.join("onlineUser")

        |
        |
        ├─────────────┐
        ↓             ↓

     here()       joining()

        |             |

 Existing Users    New User


        |
        ↓

 onlineUsers[]

        |
        ↓

 UI Update


🟢 Online

⚪ Offline
```


---

## Vue Presence Listener


```javascript
window.Echo.join("onlineUser")


.here((users)=>{

    onlineUsers.value =
        users.filter(
            user => user.id !== authId
        );

})


.joining((user)=>{

    onlineUsers.value.push(user);

})


.leaving((user)=>{

    onlineUsers.value =
        onlineUsers.value.filter(
            u => u.id !== user.id
        );

});
```


---

# Online User UI


```vue
<div 
v-for="user in users"
:key="user.id"
>

{{user.name}}


<span
v-if="
onlineUsers.some(
    online => online.id === user.id
)
"
class="text-success small"
>
Online
</span>


<span
v-else
class="text-muted small"
>
Offline
</span>


</div>
```


---


# Complete Architecture


```
                 USER A
                   |
                   |
             Send Message
                   |
                   ↓
              Axios POST
                   |
                   ↓
              Laravel
                   |
        ┌──────────┴──────────┐
        ↓                     ↓

 Save Database          Broadcast Event

                              |
                              ↓

                     Private Channel

                         chat.userB

                              |
                              ↓

                         USER B



========================================



                 READ STATUS


USER B Reads Message

        |
        ↓

POST /read/{id}

        |
        ↓

Database

is_read=true

        |
        ↓

MessageRead Event

        |
        ↓

chat.userA

        |
        ↓

USER A

✓✓



========================================



                 ONLINE STATUS


User Opens App

        |
        ↓

Presence Channel

onlineUser

        |
        ├── here()
        |
        ├── joining()
        |
        └── leaving()

        |
        ↓

onlineUsers[]

        |
        ↓

🟢 / ⚪
```


# Features

✅ Real-time messaging  
✅ Private user channel  
✅ No page reload  
✅ Message persistence  
✅ Read / Seen status  
✅ Online indicator  
✅ Presence tracking  
✅ Unread count  
✅ Last seen (later add)  
✅ Notification ( later add)  
✅ Typing indicator (later add)