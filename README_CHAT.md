# Chat en Tiempo Real con Laravel Reverb

Este proyecto implementa un sistema de chat en tiempo real utilizando Laravel Reverb para la comunicación WebSocket.

## Requisitos previos

- PHP 8.1 o superior
- Composer
- Node.js y npm
- Laravel Reverb (ya instalado)

## Configuración

1. Ejecuta las migraciones para crear las tablas necesarias:

```bash
php artisan migrate
```

2. Compila los assets de JavaScript:

```bash
npm install
npm run dev
```

3. Inicia el servidor Reverb:

```bash
php artisan reverb:start
```

4. En otra terminal, inicia el servidor web de Laravel:

```bash
php artisan serve
```

## Uso

1. Accede a la aplicación en tu navegador: http://localhost:8000
2. Regístrate o inicia sesión
3. Navega a la ruta `/chat` para acceder al chat en tiempo real
4. ¡Comienza a chatear!

## Características

- Chat en tiempo real con actualizaciones instantáneas
- Indicador de usuarios en línea
- Historial de mensajes
- Interfaz de usuario intuitiva

## Estructura del proyecto

- `app/Models/Message.php`: Modelo para los mensajes del chat
- `app/Events/MessageSent.php`: Evento que se dispara cuando se envía un mensaje
- `app/Http/Controllers/ChatController.php`: Controlador para manejar las acciones del chat
- `resources/views/chat.blade.php`: Vista principal del chat
- `routes/channels.php`: Definición del canal de presencia para el chat

## Solución de problemas

Si encuentras problemas con la conexión WebSocket:

1. Asegúrate de que el servidor Reverb esté ejecutándose (`php artisan reverb:start`)
2. Verifica que las variables de entorno en `.env` estén configuradas correctamente
3. Comprueba que estés utilizando la URL correcta para acceder a la aplicación
4. Revisa la consola del navegador para ver posibles errores de conexión