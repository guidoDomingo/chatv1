<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Chat en tiempo real con Laravel Reverb</title>
    <!-- Estilos -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .chat-container {
            height: 70vh;
            overflow-y: auto;
            padding: 20px;
            background-color: #f8f9fa;
            border-radius: 10px;
        }
        .message {
            margin-bottom: 15px;
            padding: 10px 15px;
            border-radius: 10px;
            max-width: 80%;
        }
        .message-sent {
            background-color: #dcf8c6;
            margin-left: auto;
        }
        .message-received {
            background-color: #ffffff;
        }
        .message-info {
            font-size: 0.8rem;
            color: #666;
            margin-bottom: 5px;
        }
        .users-list {
            height: 70vh;
            overflow-y: auto;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 10px;
        }
        .user-item {
            padding: 8px 10px;
            margin-bottom: 5px;
            border-radius: 5px;
            background-color: #e9ecef;
        }
        .user-online {
            width: 10px;
            height: 10px;
            background-color: #28a745;
            border-radius: 50%;
            display: inline-block;
            margin-right: 5px;
        }
    </style>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-9">
                <div class="row">
                    <div class="col-md-9">
                        <div class="card shadow">
                            <div class="card-header bg-primary text-white">
                                <h3 class="mb-0">Chat en tiempo real</h3>
                            </div>
                            <div class="card-body">
                                <div class="chat-container mb-3" id="chat-messages">
                                    <!-- Los mensajes se cargarán aquí dinámicamente -->
                                </div>
                                
                                @auth
                                    <form id="message-form">
                                        <div class="input-group">
                                            <input type="text" id="message-input" class="form-control" placeholder="Escribe tu mensaje..." required>
                                            <button class="btn btn-primary" type="submit">Enviar</button>
                                        </div>
                                    </form>
                                @else
                                    <div class="alert alert-warning">
                                        Por favor <a href="{{ route('login') }}">inicia sesión</a> para participar en el chat.
                                    </div>
                                @endauth
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card shadow">
                            <div class="card-header bg-primary text-white">
                                <h3 class="mb-0">Usuarios</h3>
                            </div>
                            <div class="card-body p-0">
                                <div class="users-list" id="users-list">
                                    <!-- Los usuarios se cargarán aquí dinámicamente -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            @auth
                const userId = {{ auth()->id() }};
                const userName = '{{ auth()->user()->name }}';
                
                // Cargar mensajes existentes
                fetch('/chat/messages')
                    .then(response => response.json())
                    .then(messages => {
                        const chatContainer = document.getElementById('chat-messages');
                        // Invertir el orden para que los mensajes más antiguos aparezcan primero
                        messages.reverse().forEach(message => {
                            appendMessage(message);
                        });
                        chatContainer.scrollTop = chatContainer.scrollHeight;
                    });
                
                // Configurar el formulario de envío de mensajes
                const messageForm = document.getElementById('message-form');
                const messageInput = document.getElementById('message-input');
                
                messageForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    
                    if (messageInput.value.trim() === '') return;
                    
                    const message = messageInput.value;
                    messageInput.value = '';
                    
                    // Enviar mensaje al servidor
                    fetch('/chat/messages', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({ message: message })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            // Crear un objeto de mensaje para mostrar inmediatamente
                            const newMessage = {
                                content: message,
                                user_id: userId,
                                user: {
                                    id: userId,
                                    name: userName
                                },
                                created_at: new Date().toISOString()
                            };
                            
                            // Añadir el mensaje al chat sin esperar a que llegue por Echo
                            appendMessage(newMessage);
                            const chatContainer = document.getElementById('chat-messages');
                            chatContainer.scrollTop = chatContainer.scrollHeight;
                        }
                    });
                });
                
                // Escuchar eventos de Echo
                window.Echo.join('chat')
                    .here(users => {
                        updateUsersList(users);
                    })
                    .joining(user => {
                        console.log('Usuario se unió:', user.name);
                        // Actualizar la lista de usuarios cuando alguien se une
                        const usersList = document.getElementById('users-list');
                        const userItem = createUserItem(user);
                        usersList.appendChild(userItem);
                    })
                    .leaving(user => {
                        console.log('Usuario se fue:', user.name);
                        // Eliminar usuario de la lista cuando se va
                        const userElement = document.getElementById(`user-${user.id}`);
                        if (userElement) {
                            userElement.remove();
                        }
                    })
                    .listen('MessageSent', (e) => {
                        console.log('Mensaje recibido:', e.message);
                        // Solo añadir el mensaje si no fue enviado por el usuario actual
                        // para evitar duplicados, ya que ya lo añadimos localmente al enviarlo
                        if (e.message.user_id !== userId) {
                            // Añadir mensaje sin necesidad de refrescar
                            appendMessage(e.message);
                            const chatContainer = document.getElementById('chat-messages');
                            chatContainer.scrollTop = chatContainer.scrollHeight;
                        }
                    });
                
                // Función para actualizar la lista de usuarios
                function updateUsersList(users) {
                    const usersList = document.getElementById('users-list');
                    usersList.innerHTML = '';
                    
                    users.forEach(user => {
                        const userItem = createUserItem(user);
                        usersList.appendChild(userItem);
                    });
                }
                
                // Función para crear un elemento de usuario
                function createUserItem(user) {
                    const userItem = document.createElement('div');
                    userItem.classList.add('user-item');
                    userItem.id = `user-${user.id}`;
                    
                    const userOnline = document.createElement('span');
                    userOnline.classList.add('user-online');
                    
                    const userName = document.createElement('span');
                    userName.textContent = user.name;
                    
                    userItem.appendChild(userOnline);
                    userItem.appendChild(userName);
                    
                    return userItem;
                }
                
                // Función para agregar mensajes al chat
                function appendMessage(message) {
                    const chatContainer = document.getElementById('chat-messages');
                    const messageDiv = document.createElement('div');
                    
                    messageDiv.classList.add('message');
                    if (message.user_id === userId) {
                        messageDiv.classList.add('message-sent');
                    } else {
                        messageDiv.classList.add('message-received');
                    }
                    
                    const messageInfo = document.createElement('div');
                    messageInfo.classList.add('message-info');
                    messageInfo.textContent = message.user.name + ' · ' + new Date(message.created_at).toLocaleTimeString();
                    
                    const messageContent = document.createElement('div');
                    messageContent.textContent = message.content;
                    
                    messageDiv.appendChild(messageInfo);
                    messageDiv.appendChild(messageContent);
                    chatContainer.appendChild(messageDiv);
                    
                    // Asegurar que el scroll siempre vaya al último mensaje
                    chatContainer.scrollTop = chatContainer.scrollHeight;
                }
            @endauth
        });
    </script>
</body>
</html>