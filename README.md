## Создание чата
**URL:** `POST /api/chat/create/{name|required|minlength:3|maxlength:50}`
#### Успешный ответ
- Code: `200 OK { name, key, active }`
#### Ответ с ошибками
- Code: `400 BAD REQUEST { "field": "error message", ... }`
## Проверка существования чата
**URL:** `GET /api/chat/exists/{key}`
#### Успешный ответ
- Code: `200 OK { name, key, active }`
#### Ответ с ошибками
- Code: `404 NOT FOUND { "message": "error message" }`
## Обновление активности чата
**URL:** `PUT /api/chat/refresh/{key}`
#### Данные запроса
- `Headers { "X-AUTH-TOKEN": "token" }`
#### Успешный ответ
- Code: `200 OK { name, key, active }`
#### Ответ с ошибками
- Code: `401 UNAUTHORIZED { "message": "error message" }`
- Code: `403 FORBIDDEN { "message": "error message" }`
## Вход пользователя в чат
**URL:** `POST /api/users/login/{key}`
#### Данные запроса
- `FormData { name|required|minlength:3|maxlength:50 }`
#### Успешный ответ
- Code: `200 OK { key, username, token, chatname }`
#### Ответ с ошибками
- Code: `400 BAD REQUEST { "field": "error message", ... }`
- Code: `403 FORBIDDEN { "message": "error message" }`
## Получение списка сообщений
**URL:** `GET /api/message/{key}`
#### Данные запроса
- `Headers { "X-AUTH-TOKEN": "token" }`
#### Успешный ответ
- Code: `200 OK [ { id, text, timecreated, author }, ... ]`
#### Ответ с ошибками
- Code: `401 UNAUTHORIZED { "message": "error message" }`
- Code: `403 FORBIDDEN { "message": "error message" }`
## Получение одного сообщения
**URL:** `GET /api/message/{id}/{key}`
#### Данные запроса
- `Headers { "X-AUTH-TOKEN": "token" }`
#### Успешный ответ
- Code: `200 OK { id, text, timecreated, author }`
#### Ответ с ошибками
- Code: `401 UNAUTHORIZED { "message": "error message" }`
- Code: `403 FORBIDDEN { "message": "error message" }`
- Code: `404 NOT FOUND { "message": "error message" }`
## Отправка сообщения
**URL:** `POST /api/message/{key}`
#### Данные запроса
- `Headers { "X-AUTH-TOKEN": "token" }`
- `FormData { text|required|maxlength:1000 }`
#### Успешный ответ
- Code: `200 OK { id, text, timecreated, author }`
#### Ответ с ошибками
- Code: `400 BAD REQUEST { "field": "error message", ... }`
- Code: `401 UNAUTHORIZED { "message": "error message" }`
- Code: `403 FORBIDDEN { "message": "error message" }`
## Обновление сообщения
**URL:** `PUT /api/message/{id}/{key}`
#### Данные запроса
- `Headers { "X-AUTH-TOKEN": "token" }`
- `FormData { text|required|maxlength:1000 }`
#### Успешный ответ
- Code: `200 OK { id, text, timecreated, author }`
#### Ответ с ошибками
- Code: `400 BAD REQUEST { "field": "error message", ... }`
- Code: `401 UNAUTHORIZED { "message": "error message" }`
- Code: `403 FORBIDDEN { "message": "error message" }`
- Code: `404 NOT FOUND { "message": "error message" }`
## Удаление сообщения
**URL:** `DELETE /api/message/{id}/{key}`
#### Данные запроса
- `Headers { "X-AUTH-TOKEN": "token" }`
#### Успешный ответ
- Code: `200 OK { id, text, timecreated, author }`
#### Ответ с ошибками
- Code: `401 UNAUTHORIZED { "message": "error message" }`
- Code: `403 FORBIDDEN { "message": "error message" }`
- Code: `404 NOT FOUND { "message": "error message" }`
