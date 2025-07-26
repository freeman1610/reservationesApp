Cómo Probar la Aplicación USUARIO
Puedes usar Postman, Insomnia o cualquier cliente HTTP para probar las API.

1. Registro de un usuario (por defecto será 'user'):

POST /api/register

Headers: Content-Type: application/json

Body (raw JSON):

JSON

{
    "name": "Usuario Normal",
    "email": "user@example.com",
    "password": "password",
    "password_confirmation": "password",
    "role": "user"
}
(Puedes omitir role y avatar para que use los valores por defecto)

2. Registro de un usuario administrador:

POST /api/register

Headers: Content-Type: application/json

Body (raw JSON):

JSON

{
    "name": "Admin User",
    "email": "admin@example.com",
    "password": "password",
    "password_confirmation": "password",
    "role": "admin"
}
3. Login de un usuario:

POST /api/login

Headers: Content-Type: application/json

Body (raw JSON):

JSON

{
    "email": "admin@example.com",
    "password": "password"
}
Esto te devolverá un access_token. Guarda este token, lo necesitarás para las rutas protegidas.

4. Obtener perfil del usuario autenticado:

GET /api/profile

Headers:

Content-Type: application/json

Authorization: Bearer <your_access_token>

5. Actualizar perfil del usuario autenticado:

POST /api/profile

Headers:

Content-Type: application/json (o multipart/form-data si subes avatar)

Authorization: Bearer <your_access_token>

Body (raw JSON):

JSON

{
    "name": "Nuevo Nombre",
    "email": "new_email@example.com",
    "remove_avatar": true // Para eliminar el avatar actual y volver al predeterminado
}
Para subir un avatar, usa multipart/form-data y añade un campo avatar de tipo file.

6. Obtener todos los usuarios (solo Admin):

GET /api/users

Headers:

Content-Type: application/json

Authorization: Bearer <admin_access_token>

7. Crear un nuevo usuario (solo Admin):

POST /api/users

Headers:

Content-Type: application/json (o multipart/form-data si subes avatar)

Authorization: Bearer <admin_access_token>

Body (raw JSON):

JSON

{
    "name": "Nuevo Usuario Creado por Admin",
    "email": "newuser@example.com",
    "password": "password",
    "role": "user",
    "max_simultaneous_reservations": 10
}
8. Obtener un usuario por ID (solo Admin):

GET /api/users/{id} (reemplaza {id} con el ID del usuario)

Headers:

Content-Type: application/json

Authorization: Bearer <admin_access_token>

9. Actualizar un usuario por ID (solo Admin):

POST /api/users/{id} (reemplaza {id} con el ID del usuario)

Importante: Usa POST y añade _method: PUT en el body si tu cliente HTTP no soporta PUT con multipart/form-data. Si solo envías JSON, puedes usar PUT.

Headers:

Content-Type: application/json (o multipart/form-data si subes avatar)

Authorization: Bearer <admin_access_token>

Body (raw JSON):

JSON

{
    "name": "Usuario Actualizado",
    "email": "updated@example.com",
    "role": "admin",
    "password": "newpassword",
    "remove_avatar": true,
    "max_simultaneous_reservations": 7,
    "_method": "PUT"
}
10. Eliminar un usuario por ID (solo Admin):

DELETE /api/users/{id} (reemplaza {id} con el ID del usuario)

Headers:

Authorization: Bearer <admin_access_token>

11. Logout:

POST /api/logout

Headers:

Authorization: Bearer <your_access_token>


ONLY ADMIN

CREATED SPACE
URL /api/spaces
Authorization: Bearer <your_access_token>
Content-Type: application/json
Accept: application/json
METHOD GET 
{
    "name": "Sala de Juntas 'Innovación'",
    "type": "room",
    "description": "Sala principal para reuniones de equipo y presentaciones a clientes. Equipada con proyector y pizarra.",
    "capacity": 12,
    "location": "Piso 3, Ala Norte",
    "availability": "{\"lunes\": [{\"start\": \"09:00\", \"end\": \"13:00\"}, {\"start\": \"15:00\", \"end\": \"18:00\"}], \"miercoles\": [{\"start\": \"10:00\", \"end\": \"16:00\"}], \"viernes\": [{\"start\": \"09:00\", \"end\": \"20:00\"}]}"
}

UPDATE SPACE
URL /api/spaces/{id}
Authorization: Bearer <your_access_token>
Content-Type: application/json
Accept: application/json
METHOD GET 
{
    "name": "Sala de Juntas 'Innovación'",
    "type": "room",
    "description": "Sala principal para reuniones de equipo y presentaciones a clientes. Equipada con proyector y pizarra.",
    "capacity": 12,
    "location": "Piso 3, Ala Norte",
    "availability": "{\"lunes\": [{\"start\": \"09:00\", \"end\": \"13:00\"}, {\"start\": \"15:00\", \"end\": \"18:00\"}], \"miercoles\": [{\"start\": \"10:00\", \"end\": \"16:00\"}], \"viernes\": [{\"start\": \"09:00\", \"end\": \"20:00\"}]}"
}